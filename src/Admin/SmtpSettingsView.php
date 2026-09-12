<?php
/**
 * SMTP settings view.
 *
 * Static HTML emitter for the SMTP settings screen: the connection form plus
 * a test-send form. Pure emitter — no DI, no `$this`; the caller handles the
 * action, nonce, and capability check.
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
		$notice = self::notice( $status, $message );

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

		return sprintf(
			'<div class="vew-smtp">%1$s
			<form method="post" action="%2$s" class="vew-smtp__form">
				%3$s
				<input type="hidden" name="action" value="%4$s" />
				<input type="hidden" name="vew_smtp_action" value="save" />
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">%5$s</th>
						<td><label for="vew_smtp_enabled"><input type="checkbox" id="vew_smtp_enabled" name="vew_smtp[enabled]" value="1"%6$s /> %7$s</label></td>
					</tr>
					<tr>
						<th scope="row"><label for="vew_smtp_host">%8$s</label></th>
						<td><input type="text" id="vew_smtp_host" name="vew_smtp[host]" class="regular-text" value="%9$s" placeholder="smtp.example.com" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vew_smtp_port">%10$s</label></th>
						<td><input type="number" id="vew_smtp_port" name="vew_smtp[port]" class="small-text" min="1" max="65535" value="%11$s" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vew_smtp_encryption">%12$s</label></th>
						<td><select id="vew_smtp_encryption" name="vew_smtp[encryption]">%13$s</select></td>
					</tr>
					<tr>
						<th scope="row">%14$s</th>
						<td><label for="vew_smtp_auth"><input type="checkbox" id="vew_smtp_auth" name="vew_smtp[auth]" value="1"%15$s /> %16$s</label></td>
					</tr>
					<tr>
						<th scope="row"><label for="vew_smtp_username">%17$s</label></th>
						<td><input type="text" id="vew_smtp_username" name="vew_smtp[username]" class="regular-text" value="%18$s" autocomplete="off" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vew_smtp_password">%19$s</label></th>
						<td><input type="password" id="vew_smtp_password" name="vew_smtp[password]" class="regular-text" value="" placeholder="%20$s" autocomplete="new-password" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vew_smtp_from_email">%21$s</label></th>
						<td><input type="email" id="vew_smtp_from_email" name="vew_smtp[from_email]" class="regular-text" value="%22$s" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="vew_smtp_from_name">%23$s</label></th>
						<td><input type="text" id="vew_smtp_from_name" name="vew_smtp[from_name]" class="regular-text" value="%24$s" /></td>
					</tr>
				</table>
				<p class="submit"><button type="submit" class="button button-primary">%25$s</button></p>
			</form>
			<hr />
			<h2>%26$s</h2>
			<p>%27$s</p>
			<form method="post" action="%2$s" class="vew-smtp__test">
				%3$s
				<input type="hidden" name="action" value="%4$s" />
				<input type="hidden" name="vew_smtp_action" value="test" />
				<input type="hidden" name="vew_smtp[enabled]" value="1" />
				<input type="hidden" name="vew_smtp[host]" value="%9$s" />
				<input type="hidden" name="vew_smtp[port]" value="%11$s" />
				<input type="hidden" name="vew_smtp[encryption]" value="%13$s" />
				<input type="hidden" name="vew_smtp[auth]" value="%28$s" />
				<input type="hidden" name="vew_smtp[username]" value="%18$s" />
				<input type="hidden" name="vew_smtp[from_email]" value="%22$s" />
				<input type="hidden" name="vew_smtp[from_name]" value="%24$s" />
				<label for="vew_smtp_test_to">%29$s</label>
				<input type="email" id="vew_smtp_test_to" name="vew_smtp_test_to" class="regular-text" value="%30$s" />
				<button type="submit" class="button">%31$s</button>
			</form>
			</div>',
			$notice,
			esc_url( admin_url( 'admin-post.php' ) ),
			wp_nonce_field( $action, '_vew_smtp_nonce', true, false ),
			esc_attr( $action ),
			esc_html__( 'Enable SMTP', 'vector-elementor-widgets' ),
			checked( $enabled, true, false ),
			esc_html__( 'Send WordPress email through this SMTP server', 'vector-elementor-widgets' ),
			esc_html__( 'SMTP Host', 'vector-elementor-widgets' ),
			esc_attr( $host ),
			esc_html__( 'Port', 'vector-elementor-widgets' ),
			esc_attr( (string) $port ),
			esc_html__( 'Encryption', 'vector-elementor-widgets' ),
			$enc_options, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- options escaped above.
			esc_html__( 'Authentication', 'vector-elementor-widgets' ),
			checked( $auth, true, false ),
			esc_html__( 'My server requires a username and password', 'vector-elementor-widgets' ),
			esc_html__( 'Username', 'vector-elementor-widgets' ),
			esc_attr( $username ),
			esc_html__( 'Password', 'vector-elementor-widgets' ),
			esc_attr( $pass_placeholder ),
			esc_html__( 'From Email', 'vector-elementor-widgets' ),
			esc_attr( $from_email ),
			esc_html__( 'From Name', 'vector-elementor-widgets' ),
			esc_attr( $from_name ),
			esc_html__( 'Save Settings', 'vector-elementor-widgets' ),
			esc_html__( 'Send a test email', 'vector-elementor-widgets' ),
			esc_html__( 'Saves the settings above, then sends one message so you can confirm delivery.', 'vector-elementor-widgets' ),
			$auth ? '1' : '',
			esc_html__( 'Send test to', 'vector-elementor-widgets' ),
			esc_attr( $from_email ),
			esc_html__( 'Send test email', 'vector-elementor-widgets' )
		);
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

		$text = '';
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
