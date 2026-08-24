<?php
/**
 * Newsletter subscription handler.
 *
 * Captures email signups from the Newsletter widget via `admin-post`. Verifies
 * the nonce, sanitizes the email, appends the subscriber to a lightweight
 * site option (so a client can see who signed up), and returns a status flag
 * the widget renders as a success/error notice. An optional notification is
 * mailed to the site admin.
 *
 * @package Vector\ElementorWidgets\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stateless newsletter subscribe handler.
 */
final class NewsletterFormHandler {

	/**
	 * The `admin-post.php` action name.
	 */
	public const ACTION = 'vew_newsletter_signup';

	/**
	 * Nonce action.
	 */
	public const NONCE_ACTION = 'vew_newsletter_nonce';

	/**
	 * Option key where collected subscribers are stored.
	 */
	public const STORE_OPTION = 'vew_newsletter_subscribers';

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
		wp_nonce_field( self::NONCE_ACTION, 'vew_newsletter_nonce' );
	}

	/**
	 * Handle an inbound newsletter signup.
	 *
	 * @return void
	 */
	public function handle(): void {
		$referer = $this->referer_url();

		$nonce = isset( $_POST['vew_newsletter_nonce'] ) ? sanitize_key( wp_unslash( $_POST['vew_newsletter_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			$this->redirect( $referer, 'vew_newsletter_error' );
		}

		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		if ( ! is_email( $email ) ) {
			$this->redirect( $referer, 'vew_newsletter_failed' );
		}

		// Persist the subscriber (dedupe by email).
		$subscribers = get_option( self::STORE_OPTION, array() );
		if ( ! is_array( $subscribers ) ) {
			$subscribers = array();
		}
		$subscribers[ $email ] = current_time( 'mysql' );
		update_option( self::STORE_OPTION, $subscribers, false );

		// Notify the site admin (optional, lightweight).
		$notify = get_option( 'vew_newsletter_notify', true );
		if ( (bool) $notify ) {
			$admin = get_option( 'admin_email' );
			$subject = sprintf(
				/* translators: %s: subscriber email. */
				__( 'New newsletter signup: %s', 'vector-elementor-widgets' ),
				$email
			);
			$body = sprintf(
				/* translators: %s: subscriber email. */
				__( 'A new email subscribed to the newsletter: %s', 'vector-elementor-widgets' ),
				$email
			);
			wp_mail( $admin, $subject, $body );
		}

		$this->redirect( $referer, 'vew_newsletter_sent' );
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
		$url = add_query_arg( 'vew_newsletter_status', rawurlencode( $status ), $url );
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
