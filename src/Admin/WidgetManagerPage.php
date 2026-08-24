<?php
/**
 * Widget Manager admin page.
 *
 * Registers the "Vector Widgets" admin menu, renders the Widget Manager
 * screen (via WidgetManagerView), and handles the save action via
 * `admin_post` with nonce + capability checks.
 *
 * The class is `final` and uses optional ctor-callables for the WP API
 * seams (add_menu_page, admin_url, wp_redirect, etc.) so tests can compose
 * a capturing double instead of subclassing.
 *
 * @package Vector\ElementorWidgets\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Admin;

use Vector\ElementorWidgets\Elementor\WidgetCatalog;
use Vector\ElementorWidgets\Elementor\WidgetRegistry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget Manager admin page.
 */
final class WidgetManagerPage {

	/**
	 * Menu slug for the Widget Manager screen.
	 */
	public const MENU_SLUG = 'vew-widget-manager';

	/**
	 * Form action name (also the admin_post hook suffix).
	 */
	public const ACTION = 'vew_save_widget_manager';

	/**
	 * Capability required to view + save.
	 */
	public const CAPABILITY = 'manage_options';

	/**
	 * Widget registry.
	 *
	 * @var WidgetRegistry
	 */
	private WidgetRegistry $registry;

	/**
	 * Optional WP API seams (test doubles).
	 *
	 * @var callable|null
	 */
	private $add_menu_page_cb;

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
	 * @param WidgetRegistry $registry Widget registry.
	 * @param callable|null  $add_menu_page_cb Optional add_menu_page seam.
	 * @param callable|null  $admin_url_cb      Optional admin_url seam.
	 * @param callable|null  $wp_redirect_cb    Optional wp_redirect seam.
	 * @param callable|null  $wp_die_cb         Optional wp_die seam.
	 */
	public function __construct(
		WidgetRegistry $registry,
		?callable $add_menu_page_cb = null,
		?callable $admin_url_cb = null,
		?callable $wp_redirect_cb = null,
		?callable $wp_die_cb = null
	) {
		$this->registry          = $registry;
		$this->add_menu_page_cb  = $add_menu_page_cb;
		$this->admin_url_cb      = $admin_url_cb;
		$this->wp_redirect_cb    = $wp_redirect_cb;
		$this->wp_die_cb         = $wp_die_cb;
	}

	/**
	 * Register the admin hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', $this->register_menu( ... ) );
		add_action( 'admin_post_' . self::ACTION, $this->handle_save( ... ) );
	}

	/**
	 * Register the admin menu page.
	 *
	 * @return void
	 */
	public function register_menu(): void {
		$cb = $this->add_menu_page_cb;
		if ( null !== $cb ) {
			$cb(
				__( 'Vector Widgets', 'vector-elementor-widgets' ),
				__( 'Vector Widgets', 'vector-elementor-widgets' ),
				self::CAPABILITY,
				self::MENU_SLUG,
				$this->render_page( ... )
			);
			return;
		}
		add_menu_page(
			__( 'Vector Widgets', 'vector-elementor-widgets' ),
			__( 'Vector Widgets', 'vector-elementor-widgets' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			$this->render_page( ... )
		);
	}

	/**
	 * Render the Widget Manager page.
	 *
	 * @return void
	 */
	public function render_page(): void {
		$widgets = WidgetCatalog::all();
		$enabled = $this->registry->enabled_slugs();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Vector Widgets', 'vector-elementor-widgets' ) . '</h1>';
		echo '<p>' . esc_html__( 'Enable or disable the Vector Elementor widgets available in the Elementor editor.', 'vector-elementor-widgets' ) . '</p>';
		echo WidgetManagerView::render( $widgets, $enabled, wp_create_nonce( self::ACTION ), self::ACTION ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- view escapes all output.
		echo '</div>';
	}

	/**
	 * Handle the save action (admin_post).
	 *
	 * Verifies the nonce + capability, sanitizes the submitted widget
	 * classes against the catalog, persists the option, and redirects back.
	 *
	 * @return void
	 */
	public function handle_save(): void {
		$nonce = isset( $_POST['_vew_widget_manager_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_vew_widget_manager_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::ACTION ) ) {
			$this->die( esc_html__( 'Security check failed.', 'vector-elementor-widgets' ) );
		}

		if ( ! current_user_can( self::CAPABILITY ) ) {
			$this->die( esc_html__( 'You do not have permission to do this.', 'vector-elementor-widgets' ) );
		}

		$submitted = isset( $_POST['vew_widgets'] ) && is_array( $_POST['vew_widgets'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['vew_widgets'] ) )
			: array();

		// Map submitted slugs back to catalog classes (slugs have no
		// backslashes, so wp_unslash is safe here). Unknown slugs are dropped.
		$enabled = array();
		foreach ( WidgetCatalog::all() as $def ) {
			if ( in_array( $def['slug'], $submitted, true ) ) {
				$enabled[] = $def['class'];
			}
		}

		update_option( VEW_OPTION_ENABLED_WIDGETS, $enabled );

		$url = $this->admin_url( 'admin.php?page=' . self::MENU_SLUG . '&vew_saved=1' );
		$this->redirect( $url );
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
