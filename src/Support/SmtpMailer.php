<?php
/**
 * SMTP mailer.
 *
 * Makes `wp_mail()` deliver through a real SMTP server instead of PHP's
 * `mail()`. Without this, a contact-form submission on a host with no local
 * MTA fails silently (or prompts the visitor to open their own mail client).
 *
 * Configuration is stored in a single option (`vew_smtp_settings`) and edited
 * on the Vector Widgets → SMTP settings screen. All hooks are additive: when
 * SMTP is disabled or unconfigured the class does nothing and `wp_mail()`
 * keeps its stock behaviour.
 *
 * @package Vector\ElementorWidgets\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Configures PHPMailer for SMTP + exposes a test send.
 */
final class SmtpMailer {

	/**
	 * Option name holding the settings array.
	 */
	public const OPTION = 'vew_smtp_settings';

	/**
	 * The `admin-post.php` action name for saving settings.
	 */
	public const ACTION = 'vew_smtp_settings';

	/**
	 * Nonce action for the settings form.
	 */
	public const NONCE_ACTION = 'vew_smtp_settings';

	/**
	 * Register the PHPMailer hook.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'phpmailer_init', array( $this, 'configure' ) );
	}

	/**
	 * Default settings.
	 *
	 * @return array<string, mixed>
	 */
	public static function defaults(): array {
		return array(
			'enabled'    => false,
			'host'       => '',
			'port'       => 587,
			'encryption' => 'tls',
			'auth'       => true,
			'username'   => '',
			'password'   => '',
			'from_email' => '',
			'from_name'  => '',
		);
	}

	/**
	 * Stored settings merged over the defaults.
	 *
	 * @return array<string, mixed>
	 */
	public static function settings(): array {
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( self::defaults(), $stored );
	}

	/**
	 * Whether SMTP is enabled and has the minimum required fields.
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {
		$settings = self::settings();
		return ! empty( $settings['enabled'] ) && '' !== trim( (string) $settings['host'] );
	}

	/**
	 * Sanitize a raw settings payload (e.g. from `$_POST`).
	 *
	 * @param array<string, mixed> $raw Raw input.
	 *
	 * @return array<string, mixed> Sanitized settings.
	 */
	public static function sanitize( array $raw ): array {
		$current = self::settings();

		$host = isset( $raw['host'] ) ? sanitize_text_field( (string) $raw['host'] ) : '';
		$host = preg_replace( '#^[a-z]+://#i', '', $host );
		$host = rtrim( (string) $host, '/' );

		$port = isset( $raw['port'] ) ? (int) $raw['port'] : 587;
		if ( $port < 1 || $port > 65535 ) {
			$port = 587;
		}

		$encryption = isset( $raw['encryption'] ) ? strtolower( sanitize_key( (string) $raw['encryption'] ) ) : 'tls';
		if ( ! in_array( $encryption, array( 'tls', 'ssl', 'none' ), true ) ) {
			$encryption = 'tls';
		}

		$password = isset( $raw['password'] ) ? (string) $raw['password'] : '';
		// An empty password field means "keep the stored one" so the admin can
		// edit the other fields without re-typing the secret.
		if ( '' === trim( $password ) ) {
			$password = (string) $current['password'];
		}

		$from_email = isset( $raw['from_email'] ) ? sanitize_email( (string) $raw['from_email'] ) : '';
		if ( '' === $from_email ) {
			$from_email = (string) $current['from_email'];
		}
		if ( '' === $from_email ) {
			$from_email = (string) get_option( 'admin_email' );
		}

		return array(
			'enabled'    => ! empty( $raw['enabled'] ),
			'host'       => $host,
			'port'       => $port,
			'encryption' => $encryption,
			'auth'       => ! empty( $raw['auth'] ),
			'username'   => isset( $raw['username'] ) ? sanitize_text_field( (string) $raw['username'] ) : '',
			'password'   => $password,
			'from_email' => $from_email,
			'from_name'  => isset( $raw['from_name'] ) ? sanitize_text_field( (string) $raw['from_name'] ) : '',
		);
	}

	/**
	 * Persist sanitized settings.
	 *
	 * @param array<string, mixed> $raw Raw input.
	 *
	 * @return void
	 */
	public static function save( array $raw ): void {
		update_option( self::OPTION, self::sanitize( $raw ) );
	}

	/**
	 * Apply the stored settings to a PHPMailer instance.
	 *
	 * Hooked to `phpmailer_init`. Runs for every `wp_mail()` call, so it is a
	 * no-op unless SMTP is enabled and configured.
	 *
	 * @param object $phpmailer PHPMailer instance (typed loosely — the class is
	 *                          not loaded at hook-registration time).
	 *
	 * @return void
	 */
	public function configure( $phpmailer ): void {
		// PHPMailer exposes these as fixed camel/PascalCase public properties;
		// renaming them is not possible, so the naming sniff is disabled here.
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		if ( ! is_object( $phpmailer ) || ! self::is_configured() ) {
			return;
		}

		$settings = self::settings();

		if ( method_exists( $phpmailer, 'isSMTP' ) ) {
			$phpmailer->isSMTP();
		} else {
			$phpmailer->Mailer = 'smtp';
		}

		$phpmailer->Host = (string) $settings['host'];
		$phpmailer->Port = (int) $settings['port'];

		// PHPMailer defaults to a 300-second connect timeout, so a blocked or
		// mismatched port leaves the admin staring at a spinner for five
		// minutes. Fail fast instead and surface the error as a notice.
		$phpmailer->Timeout = 15;
		if ( isset( $phpmailer->SMTPKeepAlive ) ) {
			$phpmailer->SMTPKeepAlive = false;
		}

		$auth             = ! empty( $settings['auth'] );
		$phpmailer->SMTPAuth = $auth;
		if ( $auth ) {
			$phpmailer->Username = (string) $settings['username'];
			$phpmailer->Password = (string) $settings['password'];
		}

		switch ( (string) $settings['encryption'] ) {
			case 'ssl':
				$phpmailer->SMTPSecure = 'ssl';
				break;
			case 'tls':
				$phpmailer->SMTPSecure = 'tls';
				break;
			default:
				$phpmailer->SMTPSecure = '';
				if ( isset( $phpmailer->SMTPAutoTLS ) ) {
					$phpmailer->SMTPAutoTLS = false;
				}
				break;
		}

		$from_email = (string) $settings['from_email'];
		if ( '' !== $from_email && method_exists( $phpmailer, 'setFrom' ) ) {
			$phpmailer->setFrom( $from_email, (string) $settings['from_name'] );
		}
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
	}

	/**
	 * Quick socket pre-flight.
	 *
	 * Verifies the transport can be reached with the configured encryption
	 * before handing off to wp_mail(), so a bad host/port/encryption combination
	 * reports a specific reason instead of hanging on PHPMailer's timeout.
	 *
	 * @return string|null Null when the transport looks reachable, else a reason.
	 */
	private static function preflight(): ?string {
		$settings = self::settings();
		$host     = (string) $settings['host'];
		$port     = (int) $settings['port'];
		$enc      = (string) $settings['encryption'];

		// Catches the classic mismatch: implicit-SSL port 465 paired with the
		// STARTTLS option (or vice versa), which cannot complete a handshake.
		if ( 465 === $port && 'tls' === $enc ) {
			return __( 'Port 465 is implicit SSL. Set Encryption to "SSL (usually port 465)" — with "TLS" PHPMailer waits for a STARTTLS handshake that never arrives.', 'vector-elementor-widgets' );
		}
		if ( 587 === $port && 'ssl' === $enc ) {
			return __( 'Port 587 uses STARTTLS, not implicit SSL. Set Encryption to "TLS (STARTTLS — usually port 587)", or switch the port to 465.', 'vector-elementor-widgets' );
		}

		$errno  = 0;
		$errstr = '';
		$conn   = @fsockopen( $host, $port, $errno, $errstr, 8 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- a failed connect is the expected path; the error is reported below.
		if ( ! is_resource( $conn ) ) {
			return sprintf(
				/* translators: 1: host, 2: port, 3: error detail. */
				__( 'Could not connect to %1$s:%2$s — %3$s', 'vector-elementor-widgets' ),
				$host,
				(string) $port,
				'' !== $errstr ? $errstr : __( 'connection refused or timed out', 'vector-elementor-widgets' )
			);
		}
		fclose( $conn );

		return null;
	}

	/**
	 * Send a test email through the configured transport.
	 *
	 * @param string $to Recipient. Defaults to the configured From address.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function send_test( string $to = '' ): array {
		if ( ! self::is_configured() ) {
			return array(
				'success' => false,
				'message' => __( 'SMTP is not enabled or the host is empty.', 'vector-elementor-widgets' ),
			);
		}

		$settings = self::settings();
		if ( '' === $to ) {
			$to = (string) $settings['from_email'];
		}
		$to = sanitize_email( $to );
		if ( '' === $to || ! is_email( $to ) ) {
			return array(
				'success' => false,
				'message' => __( 'A valid recipient email address is required.', 'vector-elementor-widgets' ),
			);
		}

		// Pre-flight: catch an unreachable/mismatched transport in seconds with a
		// clear reason, instead of letting wp_mail() block on a socket timeout.
		$preflight = self::preflight();
		if ( null !== $preflight ) {
			return array(
				'success' => false,
				'message' => $preflight,
			);
		}

		$subject = __( '[Vector Widgets] SMTP test email', 'vector-elementor-widgets' );
		$body    = __( 'This is a test email sent by the Vector Elementor Widgets plugin. If you received it, your SMTP settings are working.', 'vector-elementor-widgets' );

		$captured = null;
		$hook     = static function ( $error ) use ( &$captured ) {
			$captured = $error;
		};
		add_action( 'wp_mail_failed', $hook );

		$sent = wp_mail( $to, $subject, $body );

		remove_action( 'wp_mail_failed', $hook );

		if ( $sent ) {
			return array(
				'success' => true,
				/* translators: %s: recipient email address. */
				'message' => sprintf( __( 'Test email sent to %s.', 'vector-elementor-widgets' ), $to ),
			);
		}

		$detail = '';
		if ( $captured instanceof \WP_Error ) {
			$detail = $captured->get_error_message();
		}

		return array(
			'success' => false,
			'message' => '' !== $detail
				? $detail
				: __( 'wp_mail() returned false — check the host, port, and credentials.', 'vector-elementor-widgets' ),
		);
	}
}
