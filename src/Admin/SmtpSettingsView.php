<?php
/**
 * SMTP settings view.
 *
 * Static HTML emitter for the SMTP settings screen. Pure emitter — no DI, no
 * `$this`; the caller handles the action, nonce, and capability check.
 *
 * The test-send control lives INSIDE the settings form as a second submit
 * button, so clicking it submits exactly what is on screen (rather than a
 * hidden copy of the last-saved values, which could drift and silently test
 * stale settings).
 *
 * @package Vector\ElementorWidgets\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SMTP settings view — static HTML emitter.
 */
final class SmtpSettingsView {

	/**
	 * Render the settings screen body.
	 *
	 * @param array<string, mixed> $settings Current settings.
	 * @param string               $nonce    Form nonce value.
	 * @param string               $action   Form action name.
	 * @param string               $list_url Current admin screen URL.
	 * @param string               $status   Status key (saved|sent|failed|'').
	 * @param string               $message  Optional status message.
	 *
	 * @return string
	 */
	public static function render(
		array $settings,
		string $nonce,
		string $action,
		string $list_url,
		string $status = '',
		string $message = ''
	): string {
		$enabled    = ! empty( $settings['enabled'] );
		$host       = (string) ( $settings['host'] ?? '' );
		$port       = (int) ( $settings['port'] ?? 587 );
		$encryption = (string) ( $settings['encryption'] ?? 'tls' );
		$auth       = ! empty( $settings['auth'] );
		$username   = (string) ( $settings['username'] ?? '' );
		$has_pass   = '' !== (string) ( $settings['password'] ?? '' );
		$from_email = (string) ( $settings['from_email'] ?? '' );
		$from_name  = (string) ( $settings['from_name'] ?? '' );

		$enc_options = '';
		foreach ( array(
			'tls'  => __( 'TLS (STARTTLS — usually port 587)', 'vector-elementor-widgets' ),
			'ssl'  => __( 'SSL (usually port 465)', 'vector-elementor-widgets' ),
			'none' => __( 'None (unencrypted)', 'vector-elementor-widgets' ),
		) as $value => $label ) {
			$enc_options .= sprintf(
				'<option value="%1$s"%2$s>%3$s</option>',
				esc_attr( $value ),
				selected( $encryption, $value, false ),
				esc_html( $label )
			);
		}

		$pass_placeholder = $has_pass
			? __( '•••••••• (saved — leave blank to keep)', 'vector-elementor-widgets' )
			: __( 'App password / SMTP password', 'vector-elementor-widgets' );

		$status_notice = self::notice( $status, $message );

		return '<div class="vew-smtp">'
			. $status_notice
			. '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" class="vew-smtp__form">'
			. wp_nonce_field( $action, '_vew_smtp_nonce', true, false )
			. '<input type="hidden" name="action" value="' . esc_attr( $action ) . '" />'
			. '<table class="form-table" role="presentation">'
			. '<tr><th scope="row">' . esc_html__( 'Enable SMTP', 'vector-elementor-widgets' ) . '</th>'
			. '<td><label for="vew_smtp_enabled"><input type="checkbox" id="vew_smtp_enabled" name="vew_smtp[enabled]" value="1"' . checked( $enabled, true, false ) . ' /> '
			. esc_html__( 'Send WordPress email through this SMTP server', 'vector-elementor-widgets' ) . '</label></td></tr>'
			. '<tr><th scope="row"><label for="vew_smtp_host">' . esc_html__( 'SMTP Host', 'vector-elementor-widgets' ) . '</label></th>'
			. '<td><input type="text" id="vew_smtp_host" name="vew_smtp[host]" class="regular-text" value="' . esc_attr( $host ) . '" placeholder="smtp.example.com" /></td></tr>'
			. '<tr><th scope="row"><label for="vew_smtp_port">' . esc_html__( 'Port', 'vector-elementor-widgets' ) . '</label></th>'
			. '<td><input type="number" id="vew_smtp_port" name="vew_smtp[port]" class="small-text" min="1" max="65535" value="' . esc_attr( (string) $port ) . '" /></td></tr>'
			. '<tr><th scope="row"><label for="vew_smtp_encryption">' . esc_html__( 'Encryption', 'vector-elementor-widgets' ) . '</label></th>'
			. '<td><select id="vew_smtp_encryption" name="vew_smtp[encryption]">' . $enc_options . '</select></td></tr>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- options escaped above.
			. '<tr><th scope="row">' . esc_html__( 'Authentication', 'vector-elementor-widgets' ) . '</th>'
			. '<td><label for="vew_smtp_auth"><input type="checkbox" id="vew_smtp_auth" name="vew_smtp[auth]" value="1"' . checked( $auth, true, false ) . ' /> '
			. esc_html__( 'My server requires a username and password', 'vector-elementor-widgets' ) . '</label></td></tr>'
			. '<tr><th scope="row"><label for="vew_smtp_username">' . esc_html__( 'Username', 'vector-elementor-widgets' ) . '</label></th>'
			. '<td><input type="text" id="vew_smtp_username" name="vew_smtp[username]" class="regular-text" value="' . esc_attr( $username ) . '" autocomplete="off" /></td></tr>'
			. '<tr><th scope="row"><label for="vew_smtp_password">' . esc_html__( 'Password', 'vector-elementor-widgets' ) . '</label></th>'
			. '<td><input type="password" id="vew_smtp_password" name="vew_smtp[password]" class="regular-text" value="" placeholder="' . esc_attr( $pass_placeholder ) . '" autocomplete="new-password" /></td></tr>'
			. '<tr><th scope="row"><label for="vew_smtp_from_email">' . esc_html__( 'From Email', 'vector-elementor-widgets' ) . '</label></th>'
			. '<td><input type="email" id="vew_smtp_from_email" name="vew_smtp[from_email]" class="regular-text" value="' . esc_attr( $from_email ) . '" /></td></tr>'
			. '<tr><th scope="row"><label for="vew_smtp_from_name">' . esc_html__( 'From Name', 'vector-elementor-widgets' ) . '</label></th>'
			. '<td><input type="text" id="vew_smtp_from_name" name="vew_smtp[from_name]" class="regular-text" value="' . esc_attr( $from_name ) . '" /></td></tr>'
			. '</table>'
			. '<p class="submit">'
			. '<button type="submit" class="button button-primary" name="vew_smtp_action" value="save">' . esc_html__( 'Save Settings', 'vector-elementor-widgets' ) . '</button>'
			. '</p>'
			. '<hr />'
			. '<h2>' . esc_html__( 'Send a test email', 'vector-elementor-widgets' ) . '</h2>'
			. '<p>' . esc_html__( 'Saves the settings above, then sends one message so you can confirm delivery. Use this to check the configuration end to end.', 'vector-elementor-widgets' ) . '</p>'
			. '<p>'
			. '<label for="vew_smtp_test_to">' . esc_html__( 'Send test to', 'vector-elementor-widgets' ) . '</label> '
			. '<input type="email" id="vew_smtp_test_to" name="vew_smtp_test_to" class="regular-text" value="' . esc_attr( $from_email ) . '" /> '
			. '<button type="submit" class="button" name="vew_smtp_action" value="test">' . esc_html__( 'Send test email', 'vector-elementor-widgets' ) . '</button>'
			. '</p>'
			. '</form>'
			. '</div>';
	}

	/**
	 * Render the status notice.
	 *
	 * @param string $status  Status key.
	 * @param string $message Optional message.
	 *
	 * @return string
	 */
	private static function notice( string $status, string $message ): string {
		if ( '' === $status ) {
			return '';
		}

		$is_ok = in_array( $status, array( 'saved', 'sent' ), true );

		if ( 'saved' === $status ) {
			$text = __( 'SMTP settings saved.', 'vector-elementor-widgets' );
		} elseif ( 'sent' === $status ) {
			$text = '' !== $message
				? $message
				: __( 'Test email sent.', 'vector-elementor-widgets' );
		} else {
			$text = '' !== $message
				? sprintf(
					/* translators: %s: error detail. */
					__( 'Test email failed: %s', 'vector-elementor-widgets' ),
					$message
				)
				: __( 'Test email failed.', 'vector-elementor-widgets' );
		}

		return sprintf(
			'<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
			$is_ok ? 'success' : 'error',
			esc_html( $text )
		);
	}
}
