<?php
/**
 * SMTP mailer tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Support;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Support\SmtpMailer;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

require_once dirname( __DIR__, 2 ) . '/Support/wp-stubs.php';

/**
 * SMTP transport configuration contract.
 */
final class SmtpMailerTest extends TestCase {

	/**
	 * Stub WP functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name ? array() : $default;
			}
		);
		Functions\when( 'update_option' )->alias( static fn ( string $o, $v ): bool => true );
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'sanitize_email' )->alias( static fn ( string $e ): string => trim( $e ) );
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'is_email' )->alias( static fn ( string $e ): bool => (bool) filter_var( $e, FILTER_VALIDATE_EMAIL ) );
		Functions\when( 'add_action' )->justReturn( true );
		Functions\when( 'remove_action' )->justReturn( true );
	}

	/**
	 * Defaults are sane and SMTP is off by default (opt-in — must not alter
	 * existing mail behaviour until an admin configures it).
	 *
	 * @return void
	 */
	public function test_defaults_disable_smtp(): void {
		$defaults = SmtpMailer::defaults();

		$this->assertFalse( $defaults['enabled'] );
		$this->assertSame( '', $defaults['host'] );
		$this->assertSame( 587, $defaults['port'] );
		$this->assertSame( 'tls', $defaults['encryption'] );
		$this->assertTrue( $defaults['auth'] );
	}

	/**
	 * Not configured while disabled, even with a host.
	 *
	 * @return void
	 */
	public function test_is_configured_requires_enabled_and_host(): void {
		$this->assertFalse( SmtpMailer::is_configured() );

		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name
					? array( 'enabled' => true, 'host' => '' )
					: $default;
			}
		);
		$this->assertFalse( SmtpMailer::is_configured(), 'enabled but no host must not be configured' );
	}

	/**
	 * Configured once enabled with a host.
	 *
	 * @return void
	 */
	public function test_is_configured_true_when_enabled_with_host(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name
					? array( 'enabled' => true, 'host' => 'mail.example.test' )
					: $default;
			}
		);

		$this->assertTrue( SmtpMailer::is_configured() );
	}

	/**
	 * Sanitize strips a scheme from the host and clamps an invalid port.
	 *
	 * @return void
	 */
	public function test_sanitize_normalises_host_and_port(): void {
		$out = SmtpMailer::sanitize(
			array(
				'host'       => 'ssl://mail.rorecclesia.com/',
				'port'       => 999999,
				'encryption' => 'BOGUS',
			)
		);

		$this->assertSame( 'mail.rorecclesia.com', $out['host'] );
		$this->assertSame( 587, $out['port'], 'out-of-range port falls back to 587' );
		$this->assertSame( 'tls', $out['encryption'], 'unknown encryption falls back to tls' );
	}

	/**
	 * A blank password keeps the stored one so the admin can edit other fields
	 * without re-typing the secret.
	 *
	 * @return void
	 */
	public function test_sanitize_keeps_stored_password_when_blank(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				if ( SmtpMailer::OPTION === $name ) {
					return array( 'password' => 'existing-secret' );
				}
				return 'admin@example.test' === $name ? 'admin@example.test' : $default;
			}
		);

		$out = SmtpMailer::sanitize( array( 'host' => 'mail.example.test', 'password' => '   ' ) );

		$this->assertSame( 'existing-secret', $out['password'] );
	}

	/**
	 * An explicit password replaces the stored one.
	 *
	 * @return void
	 */
	public function test_sanitize_replaces_password_when_supplied(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name ? array( 'password' => 'old' ) : $default;
			}
		);

		$out = SmtpMailer::sanitize( array( 'host' => 'h', 'password' => 'new-secret' ) );

		$this->assertSame( 'new-secret', $out['password'] );
	}

	/**
	 * From Email falls back to the site admin address when neither supplied nor
	 * stored, so a send always has a resolvable sender.
	 *
	 * @return void
	 */
	public function test_sanitize_falls_back_to_admin_email(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				if ( SmtpMailer::OPTION === $name ) {
					return array();
				}
				return 'admin_email' === $name ? 'admin@example.test' : $default;
			}
		);

		$out = SmtpMailer::sanitize( array( 'host' => 'h' ) );

		$this->assertSame( 'admin@example.test', $out['from_email'] );
	}

	/**
	 * Encryption choice maps to the PHPMailer SMTPSecure value.
	 *
	 * @return void
	 */
	public function test_configure_maps_encryption_to_phpmailer(): void {
		foreach ( array( 'ssl' => 'ssl', 'tls' => 'tls', 'none' => '' ) as $in => $expected ) {
			Functions\when( 'get_option' )->alias(
				static function ( string $name, $default = false ) use ( $in ) {
					return SmtpMailer::OPTION === $name
						? array(
							'enabled'    => true,
							'host'       => 'mail.example.test',
							'port'       => 465,
							'encryption' => $in,
							'auth'       => false,
						)
						: $default;
				}
			);

			$phpmailer = $this->fake_phpmailer();
			( new SmtpMailer() )->configure( $phpmailer );

			$this->assertSame( $expected, $phpmailer->SMTPSecure, "encryption {$in}" );
			$this->assertSame( 'mail.example.test', $phpmailer->Host );
			$this->assertSame( 465, $phpmailer->Port );
		}
	}

	/**
	 * Auth credentials are only pushed when auth is enabled.
	 *
	 * @return void
	 */
	public function test_configure_sets_credentials_only_when_auth_enabled(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name
					? array(
						'enabled'    => true,
						'host'       => 'mail.example.test',
						'port'       => 465,
						'encryption' => 'ssl',
						'auth'       => true,
						'username'   => 'contact@rorecclesia.com',
						'password'   => 'secret',
					)
					: $default;
			}
		);

		$phpmailer = $this->fake_phpmailer();
		( new SmtpMailer() )->configure( $phpmailer );

		$this->assertTrue( $phpmailer->SMTPAuth );
		$this->assertSame( 'contact@rorecclesia.com', $phpmailer->Username );
		$this->assertSame( 'secret', $phpmailer->Password );
	}

	/**
	 * A no-op unless configured — critical: an unconfigured plugin must not
	 * change wp_mail() at all.
	 *
	 * @return void
	 */
	public function test_configure_is_noop_when_not_configured(): void {
		$phpmailer = $this->fake_phpmailer();
		$phpmailer->Host = 'untouched';

		( new SmtpMailer() )->configure( $phpmailer );

		$this->assertSame( 'untouched', $phpmailer->Host, 'disabled SMTP must not mutate PHPMailer' );
	}

	/**
	 * From address is applied via setFrom when present.
	 *
	 * @return void
	 */
	public function test_configure_sets_from(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name
					? array(
						'enabled'    => true,
						'host'       => 'mail.example.test',
						'from_email' => 'contact@rorecclesia.com',
						'from_name'  => 'R.O.R.E.',
					)
					: $default;
			}
		);

		$phpmailer = $this->fake_phpmailer();
		( new SmtpMailer() )->configure( $phpmailer );

		$this->assertSame(
			array( 'contact@rorecclesia.com', 'R.O.R.E.' ),
			$phpmailer->from_call
		);
	}

	/**
	 * send_test refuses when not configured.
	 *
	 * @return void
	 */
	public function test_send_test_reports_unconfigured(): void {
		$result = SmtpMailer::send_test( 'someone@example.test' );

		$this->assertFalse( $result['success'] );
		$this->assertNotSame( '', $result['message'] );
	}

	/**
	 * send_test refuses an invalid recipient.
	 *
	 * @return void
	 */
	public function test_send_test_rejects_invalid_recipient(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name
					? array( 'enabled' => true, 'host' => 'mail.example.test', 'from_email' => 'contact@rorecclesia.com' )
					: $default;
			}
		);

		$result = SmtpMailer::send_test( 'not-an-email' );

		$this->assertFalse( $result['success'] );
	}

	/**
	 * send_test reports success when wp_mail() returns true.
	 *
	 * @return void
	 */
	public function test_send_test_success(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name
					? array( 'enabled' => true, 'host' => 'mail.example.test', 'from_email' => 'contact@rorecclesia.com' )
					: $default;
			}
		);
		Functions\when( 'wp_mail' )->justReturn( true );

		$result = SmtpMailer::send_test( 'someone@example.test' );

		$this->assertTrue( $result['success'] );
		$this->assertStringContainsString( 'someone@example.test', $result['message'] );
	}

	/**
	 * send_test surfaces the WP_Error detail when wp_mail() fails.
	 *
	 * @return void
	 */
	public function test_send_test_surfaces_wp_error_detail(): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $name, $default = false ) {
				return SmtpMailer::OPTION === $name
					? array( 'enabled' => true, 'host' => 'mail.example.test', 'from_email' => 'contact@rorecclesia.com' )
					: $default;
			}
		);
		Functions\when( 'wp_mail' )->justReturn( false );
		// Trigger the failure hook so the captured error is populated.
		Functions\when( 'add_action' )->alias(
			static function ( string $hook, $cb = null ) {
				if ( 'wp_mail_failed' === $hook && is_callable( $cb ) ) {
					$cb( new \WP_Error( 'smtp_error', 'SMTP connect() failed' ) );
				}
				return true;
			}
		);

		$result = SmtpMailer::send_test( 'someone@example.test' );

		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'SMTP connect() failed', $result['message'] );
	}

	/**
	 * A stand-in PHPMailer exposing the public properties the mailer writes.
	 *
	 * @return object
	 */
	private function fake_phpmailer(): object {
		return new class() {
			/**
			 * Mailer backend.
			 *
			 * @var string
			 */
			public $Mailer = 'mail';

			/**
			 * Host.
			 *
			 * @var string
			 */
			public $Host = '';

			/**
			 * Port.
			 *
			 * @var int
			 */
			public $Port = 25;

			/**
			 * Auth flag.
			 *
			 * @var bool
			 */
			public $SMTPAuth = false;

			/**
			 * Username.
			 *
			 * @var string
			 */
			public $Username = '';

			/**
			 * Password.
			 *
			 * @var string
			 */
			public $Password = '';

			/**
			 * Encryption.
			 *
			 * @var string
			 */
			public $SMTPSecure = '';

			/**
			 * Auto-TLS flag.
			 *
			 * @var bool
			 */
			public $SMTPAutoTLS = true;

			/**
			 * Captured setFrom() args.
			 *
			 * @var array<int, string>
			 */
			public array $from_call = array();

			/**
			 * Switch to SMTP.
			 *
			 * @return void
			 */
			public function isSMTP(): void {
				$this->Mailer = 'smtp';
			}

			/**
			 * Capture the From identity.
			 *
			 * @param string $email Email.
			 * @param string $name  Name.
			 *
			 * @return bool
			 */
			public function setFrom( string $email, string $name = '' ): bool {
				$this->from_call = array( $email, $name );
				return true;
			}
		};
	}
}
