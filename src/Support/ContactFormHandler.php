<?php
/**
 * Contact form submission handler.
 *
 * Turns the Contact widget's form into a real submission path. Handles
 * `admin-post` submissions: verifies the nonce, sanitizes every field,
 * validates the required fields, and emails the message to the configured
 * recipient. On success/failure it redirects back to the referring page with
 * a query-string flag that the widget render can surface as a notice.
 *
 * The handler is decoupled from the widget's render: it reads the recipient
 * from the submitted `vew_contact_recipient` field (rendered from the
 * widget's Email control), sanitized to a valid email address.
 *
 * @package Vector\ElementorWidgets\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stateless contact form submit + mailer.
 */
final class ContactFormHandler {

	/**
	 * The `admin-post.php` action name.
	 */
	public const ACTION = 'vew_contact_form';

	/**
	 * Nonce action.
	 */
	public const NONCE_ACTION = 'vew_contact_nonce';

	/**
	 * Register the admin-post handlers.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'admin_post_' . self::ACTION, array( $this, 'handle' ) );
		add_action( 'admin_post_nopriv_' . self::ACTION, array( $this, 'handle' ) );
	}

	/**
	 * Render the nonce field (used by the widget).
	 *
	 * @return void
	 */
	public static function nonce_field(): void {
		wp_nonce_field( self::NONCE_ACTION, 'vew_contact_nonce' );
	}

	/**
	 * Handle an inbound contact submission.
	 *
	 * @return void
	 */
	public function handle(): void {
		$referer = $this->referer_url();

		$nonce = isset( $_POST['vew_contact_nonce'] ) ? sanitize_key( wp_unslash( $_POST['vew_contact_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			$this->redirect( $referer, 'vew_contact_error' );
		}

		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
		$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$business = isset( $_POST['business'] ) ? sanitize_text_field( wp_unslash( $_POST['business'] ) ) : '';
		$recipient = isset( $_POST['vew_contact_recipient'] ) ? sanitize_email( wp_unslash( $_POST['vew_contact_recipient'] ) ) : '';

		// Required fields.
		if ( '' === $name || '' === $email || '' === $message ) {
			$this->redirect( $referer, 'vew_contact_failed' );
		}

		// A recipient must resolve, else fall back to the site admin email.
		if ( '' === $recipient ) {
			$recipient = get_option( 'admin_email' );
		}

		$subject = sprintf(
			/* translators: %s: sender name. */
			__( '[Website] Contact from %s', 'vector-elementor-widgets' ),
			$name
		);

		$body  = __( 'New website contact form submission:', 'vector-elementor-widgets' ) . "\n\n";
		// translators: %s: sender name.
		$body .= sprintf( __( 'Name: %s', 'vector-elementor-widgets' ) . "\n", $name );
		if ( '' !== $business ) {
			// translators: %s: business name.
			$body .= sprintf( __( 'Business: %s', 'vector-elementor-widgets' ) . "\n", $business );
		}
		// translators: %s: sender email.
		$body .= sprintf( __( 'Email: %s', 'vector-elementor-widgets' ) . "\n", $email );
		if ( '' !== $phone ) {
			// translators: %s: sender phone.
			$body .= sprintf( __( 'Phone: %s', 'vector-elementor-widgets' ) . "\n", $phone );
		}
		$body .= "\n" . __( 'Message:', 'vector-elementor-widgets' ) . "\n" . $message . "\n";

		$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

		$sent = wp_mail( $recipient, $subject, $body, $headers );

		$this->redirect( $referer, $sent ? 'vew_contact_sent' : 'vew_contact_failed' );
	}

	/**
	 * Safely redirect back to the referer page with a status flag.
	 *
	 * @param string $url    Destination.
	 * @param string $status Status flag appended as a query arg.
	 *
	 * @return void
	 */
	private function redirect( string $url, string $status ): void {
		$url = add_query_arg( 'vew_contact_status', rawurlencode( $status ), $url );
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * The referer URL, or the home URL if none / invalid.
	 *
	 * @return string
	 */
	private function referer_url(): string {
		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$ref = esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) );
			if ( $ref ) {
				return $ref;
			}
		}
		return home_url( '/' );
	}
}
