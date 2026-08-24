<?php
/**
 * Site Kit Manager admin page.
 *
 * Registers the "Site Kits" submenu under Vector Widgets, renders the manager
 * screen (via SiteKitManagerView), and handles the save / set-global / delete /
 * export actions via `admin_post` with nonce + capability checks.
 *
 * The class is `final` and uses optional ctor-callables for the WP API seams
 * so tests can compose a capturing double instead of subclassing.
 *
 * @package Vector\ElementorWidgets\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Admin;

use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Kit\KitImporter;
use Vector\ElementorWidgets\Kit\KitStore;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Kit Manager admin page.
 */
final class SiteKitManagerPage {

	/**
	 * Parent menu slug (the Vector Widgets manager).
	 */
	public const PARENT_SLUG = 'vew-widget-manager';

	/**
	 * Menu slug for this screen.
	 */
	public const MENU_SLUG = 'vew-site-kits';

	/**
	 * Form action name (also the admin_post hook suffix).
	 */
	public const ACTION = 'vew_site_kit_manager';

	/**
	 * Capability required.
	 */
	public const CAPABILITY = 'manage_options';

	/**
	 * Kit store.
	 *
	 * @var KitStore
	 */
	private KitStore $store;

	/**
	 * Kit importer.
	 *
	 * @var KitImporter
	 */
	private KitImporter $importer;

	/**
	 * Optional WP API seams.
	 *
	 * @var callable|null
	 */
	private $add_submenu_page_cb;

	/**
	 * Optional admin_url seam.
	 *
	 * @var callable|null
	 */
	private $admin_url_cb;

	/**
	 * Optional wp_redirect seam.
	 *
	 * @var callable|null
	 */
	private $wp_redirect_cb;

	/**
	 * Optional wp_die seam.
	 *
	 * @var callable|null
	 */
	private $wp_die_cb;

	/**
	 * Constructor.
	 *
	 * @param KitStore         $store                Kit store.
	 * @param KitImporter|null $importer           Optional kit importer.
	 * @param callable|null    $add_submenu_page_cb Optional add_submenu_page seam.
	 * @param callable|null    $admin_url_cb        Optional admin_url seam.
	 * @param callable|null    $wp_redirect_cb      Optional wp_redirect seam.
	 * @param callable|null    $wp_die_cb           Optional wp_die seam.
	 */
	public function __construct(
		KitStore $store,
		?KitImporter $importer = null,
		?callable $add_submenu_page_cb = null,
		?callable $admin_url_cb = null,
		?callable $wp_redirect_cb = null,
		?callable $wp_die_cb = null
	) {
		$this->store                = $store;
		$this->importer             = $importer ?? new KitImporter();
		$this->add_submenu_page_cb  = $add_submenu_page_cb;
		$this->admin_url_cb         = $admin_url_cb;
		$this->wp_redirect_cb       = $wp_redirect_cb;
		$this->wp_die_cb            = $wp_die_cb;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', $this->register_menu( ... ) );
		add_action( 'admin_post_' . self::ACTION, $this->handle_action( ... ) );
	}

	/**
	 * Register the submenu page.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		$cb = $this->add_submenu_page_cb;
		if ( null !== $cb ) {
			$cb(
				self::PARENT_SLUG,
				__( 'Site Kits', 'vector-elementor-widgets' ),
				__( 'Site Kits', 'vector-elementor-widgets' ),
				self::CAPABILITY,
				self::MENU_SLUG,
				$this->render_page( ... )
			);
			return;
		}
		add_submenu_page(
			self::PARENT_SLUG,
			__( 'Site Kits', 'vector-elementor-widgets' ),
			__( 'Site Kits', 'vector-elementor-widgets' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			$this->render_page( ... )
		);
	}

	/**
	 * Render the manager page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only; the editing param is validated in handle_action().
		$editing_slug = isset( $_GET['vew_site_kit_edit'] ) ? sanitize_key( wp_unslash( $_GET['vew_site_kit_edit'] ) ) : '';
		$editing      = '' !== $editing_slug ? $this->store->get( $editing_slug ) : null;

		$html = SiteKitManagerView::render(
			$this->store->all(),
			(string) get_option( \Vector\ElementorWidgets\Elementor\SiteKit::GLOBAL_OPTION, '' ),
			wp_create_nonce( self::ACTION ),
			self::ACTION,
			$editing,
			$this->admin_url( 'admin.php?page=' . self::MENU_SLUG )
		);

		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- view escapes all output.
	}

	/**
	 * Handle a posted action (save / set_global / delete / export).
	 *
	 * @return void
	 */
	public function handle_action(): void {
		$nonce = isset( $_POST['_vew_site_kit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_vew_site_kit_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::ACTION ) ) {
			$this->die( esc_html__( 'Security check failed.', 'vector-elementor-widgets' ) );
		}
		if ( ! current_user_can( self::CAPABILITY ) ) {
			$this->die( esc_html__( 'You do not have permission to do this.', 'vector-elementor-widgets' ) );
		}

		$action = isset( $_POST['vew_site_kit_action'] ) ? sanitize_key( wp_unslash( $_POST['vew_site_kit_action'] ) ) : '';

		switch ( $action ) {
			case 'save':
				$this->save();
				break;
			case 'set_global':
				$this->set_global();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'export':
				$this->export();
				break;
			case 'import':
				$this->import_kit();
				break;
			default:
				$this->die( esc_html__( 'Unknown action.', 'vector-elementor-widgets' ) );
		}
	}

	/**
	 * Save / create a kit.
	 *
	 * @return void
	 */
	private function save(): void {
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized recursively by sanitize_raw() below.
		$raw  = isset( $_POST['vew_kit'] ) ? wp_unslash( $_POST['vew_kit'] ) : array();
		$data = is_array( $raw ) ? $this->sanitize_raw( $raw ) : array();

		try {
			$kit = Kit::from_array( $data );
		} catch ( \InvalidArgumentException $e ) {
			$this->die( esc_html__( 'Invalid kit slug.', 'vector-elementor-widgets' ) );
			return; // die() may return in tests via the seam; bail if it does.
		}

		$this->store->save( $kit );

		$url = $this->admin_url( 'admin.php?page=' . self::MENU_SLUG . '&vew_saved=1' );
		$this->redirect( $url );
	}

	/**
	 * Set the global kit.
	 *
	 * @return void
	 */
	private function set_global(): void {
		$slug = isset( $_POST['vew_global_site_kit'] ) ? sanitize_key( wp_unslash( $_POST['vew_global_site_kit'] ) ) : '';
		if ( '' !== $slug && ! $this->store->has( $slug ) ) {
			$this->die( esc_html__( 'Unknown kit.', 'vector-elementor-widgets' ) );
		}
		update_option( \Vector\ElementorWidgets\Elementor\SiteKit::GLOBAL_OPTION, $slug );
		$this->redirect( $this->admin_url( 'admin.php?page=' . self::MENU_SLUG . '&vew_saved=1' ) );
	}

	/**
	 * Delete a kit.
	 *
	 * @return void
	 */
	private function delete(): void {
		$slug = isset( $_POST['vew_site_kit_slug'] ) ? sanitize_key( wp_unslash( $_POST['vew_site_kit_slug'] ) ) : '';
		if ( '' !== $slug ) {
			$this->store->delete( $slug );
		}
		$this->redirect( $this->admin_url( 'admin.php?page=' . self::MENU_SLUG . '&vew_saved=1' ) );
	}

	/**
	 * Export a kit as JSON.
	 *
	 * @return void
	 */
	private function export(): void {
		$slug = isset( $_POST['vew_site_kit_slug'] ) ? sanitize_key( wp_unslash( $_POST['vew_site_kit_slug'] ) ) : '';
		$kit  = '' !== $slug ? $this->store->get( $slug ) : null;
		if ( null === $kit ) {
			$this->die( esc_html__( 'Unknown kit.', 'vector-elementor-widgets' ) );
		}

		$payload = array_merge(
			array( '_vew_site_kit_version' => 1 ),
			$kit->to_array()
		);

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download, not HTML output.
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="site-kit-' . $slug . '.json"' );
		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		exit;
	}

	/**
	 * Import a kit from an uploaded JSON file.
	 *
	 * @return void
	 */
	private function import_kit(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce verified above; file is a local tmp upload read as JSON.
		$file = isset( $_FILES['vew_site_kit_import'] ) ? $_FILES['vew_site_kit_import'] : null;
		if ( ! is_array( $file ) || UPLOAD_ERR_OK !== (int) ( $file['error'] ?? -1 ) ) {
			$this->die( esc_html__( 'No import file received.', 'vector-elementor-widgets' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated below.
		$json = (string) file_get_contents( $file['tmp_name'] );
		if ( '' === $json ) {
			$this->die( esc_html__( 'Import file is empty.', 'vector-elementor-widgets' ) );
		}

		$result = $this->importer->from_json( $json );
		if ( null === $result['kit'] ) {
			$this->die( esc_html( $result['error'] ) );
		}

		$this->store->save( $result['kit'] );
		$this->redirect( $this->admin_url( 'admin.php?page=' . self::MENU_SLUG . '&vew_saved=1&vew_imported=' . urlencode( $result['kit']->slug() ) ) );
	}

	/**
	 * Sanitize a nested array of form values recursively.
	 *
	 * @param mixed $value Raw value.
	 *
	 * @return mixed
	 */
	private function sanitize_raw( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( $this, 'sanitize_raw' ), $value );
		}
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Resolve admin_url (seam).
	 *
	 * @param string $path Path.
	 *
	 * @return string
	 */
	private function admin_url( string $path ): string {
		if ( null !== $this->admin_url_cb ) {
			return (string) ( $this->admin_url_cb )( $path );
		}
		return admin_url( $path );
	}

	/**
	 * Redirect (seam).
	 *
	 * @param string $url URL.
	 *
	 * @return void
	 */
	private function redirect( string $url ): void {
		if ( null !== $this->wp_redirect_cb ) {
			( $this->wp_redirect_cb )( $url );
			return;
		}
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Die (seam).
	 *
	 * @param string $message Message.
	 *
	 * @return void
	 */
	private function die( string $message ): void {
		if ( null !== $this->wp_die_cb ) {
			( $this->wp_die_cb )( $message );
			return;
		}
		wp_die( $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- message is already escaped by caller.
	}
}
