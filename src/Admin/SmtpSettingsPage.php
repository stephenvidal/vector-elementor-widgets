<?php
/**
 * SMTP settings admin screen.
 *
 * Adds a "SMTP" submenu under Vector Widgets that lets an administrator
 * configure the outgoing mail transport (host, port, encryption, credentials)
 * and send a test email.
 *
 * Uses the same admin_post + nonce + capability pattern as the other admin
 * screens, and — unlike them — emits WordPress's required hidden `action`
 * field so the handler actually fires.
 *
 * @package Vector\ElementorWidgets\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Admin;

use Vector\ElementorWidgets\Support\SmtpMailer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMTP settings page.
 */
final class SmtpSettingsPage {

	/**
	 * Parent menu slug.
	 */
	public const PARENT_SLUG = 'vew-widget-manager';

	/**
	 * Menu slug for this screen.
	 */
	public const MENU_SLUG = 'vew-smtp';

	/**
	 * Capability required.
	 */
	public const CAPABILITY = 'manage_options';

	/**
	 * Optional add_submenu_page seam for tests.
	 *
	 * @var callable|null
	 */
	private $add_submenu_page_cb;

	/**
	 * Optional admin_url seam for tests.
	 *
	 * @var callable|null
	 */
	private $admin_url_cb;

	/**
	 * Optional wp_safe_redirect seam for tests.
	 *
	 * @var callable|null
	 */
	private $redirect_cb;

	/**
	 * Constructor.
	 *
	 * @param callable|null $add_submenu_page_cb Optional add_submenu_page seam.
	 * @param callable|null $admin_url_cb Optional admin_url seam.
	 * @param callable|null $redirect_cb Optional wp_safe_redirect seam.
	 */
	public function __construct(
		?callable $add_submenu_page_cb = null,
		?callable $admin_url_cb = null,
		?callable $redirect_cb = null
	) {
		$this->add_submenu_page_cb = $add_submenu_page_cb;
		$this->admin_url_cb        = $admin_url_cb;
		$this->redirect_cb         = $redirect_cb;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_menu', $this->register_menu( ... ) );
		add_action( 'admin_post_' . SmtpMailer::ACTION, $this->handle_action( ... ) );
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
				__( 'SMTP', 'vector-elementor-widgets' ),
				__( 'SMTP', 'vector-elementor-widgets' ),
				self::CAPABILITY,
				self::MENU_SLUG,
				$this->render_page( ... )
			);
			return;
		}
		add_submenu_page(
			self::PARENT_SLUG,
			__( 'SMTP', 'vector-elementor-widgets' ),
			__( 'SMTP', 'vector-elementor-widgets' ),
			self::CAPABILITY,
			self::MENU_SLUG,
			$this->render_page( ... )
		);
	}

	/**
	 * Render the settings screen.
	 *
	 * @return void
	 */
	public function render_page(): void {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'vector-elementor-widgets' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only status flags for the notice.
		$status  = isset( $_GET['vew_smtp_status'] ) ? sanitize_key( wp_unslash( $_GET['vew_smtp_status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nonce-free read-only notice text; sanitized by sanitize_text_field() after URL decoding.
		$message = isset( $_GET['vew_smtp_message'] ) ? sanitize_text_field( rawurldecode( wp_unslash( (string) $_GET['vew_smtp_message'] ) ) ) : '';

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'SMTP', 'vector-elementor-widgets' ) . '</h1>';
		echo '<p>' . esc_html__( 'Route outgoing email (contact and newsletter forms) through your own mail server instead of PHP mail().', 'vector-elementor-widgets' ) . '</p>';

		// The view is the escaping layer: every value it emits is passed through
		// esc_html()/esc_attr()/esc_url() inside SmtpSettingsView::render().
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo SmtpSettingsView::render(
			SmtpMailer::settings(),
			wp_create_nonce( SmtpMailer::NONCE_ACTION ),
			SmtpMailer::ACTION,
			$this->admin_url( 'admin.php?page=' . self::MENU_SLUG ),
			$status,
			$message
		);
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		echo '</div>';
	}

	/**
	 * Handle save / test actions.
	 *
	 * @return void
	 */
	public function handle_action(): void {
		$nonce = isset( $_POST['_vew_smtp_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_vew_smtp_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, SmtpMailer::NONCE_ACTION ) ) {
			wp_die( esc_html__( 'Security check failed.', 'vector-elementor-widgets' ) );
		}
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'vector-elementor-widgets' ) );
		}

		$action = isset( $_POST['vew_smtp_action'] ) ? sanitize_key( wp_unslash( $_POST['vew_smtp_action'] ) ) : 'save';

		if ( 'test' === $action ) {
			// Save first so a test reflects what is on screen.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in SmtpMailer::sanitize().
			$raw = isset( $_POST['vew_smtp'] ) && is_array( $_POST['vew_smtp'] ) ? wp_unslash( $_POST['vew_smtp'] ) : array();
			SmtpMailer::save( $raw );

			$to     = isset( $_POST['vew_smtp_test_to'] ) ? sanitize_email( wp_unslash( (string) $_POST['vew_smtp_test_to'] ) ) : '';
			$result = SmtpMailer::send_test( $to );

			$this->redirect(
				$this->status_url( $result['success'] ? 'sent' : 'failed', (string) $result['message'] )
			);
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in SmtpMailer::sanitize().
		$raw = isset( $_POST['vew_smtp'] ) && is_array( $_POST['vew_smtp'] ) ? wp_unslash( $_POST['vew_smtp'] ) : array();
		SmtpMailer::save( $raw );

		$this->redirect( $this->status_url( 'saved', '' ) );
	}

	/**
	 * Build the post-redirect URL carrying the status flags.
	 *
	 * @param string $status  Status key.
	 * @param string $message Optional message.
	 *
	 * @return string
	 */
	private function status_url( string $status, string $message ): string {
		return add_query_arg(
			array(
				'vew_smtp_status'  => $status,
				'vew_smtp_message' => rawurlencode( $message ),
			),
			$this->admin_url( 'admin.php?page=' . self::MENU_SLUG )
		);
	}

	/**
	 * Redirect (seam-friendly).
	 *
	 * @param string $url Destination.
	 *
	 * @return void
	 */
	private function redirect( string $url ): void {
		if ( null !== $this->redirect_cb ) {
			( $this->redirect_cb )( $url );
			return;
		}
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Admin URL (seam-friendly).
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
}
