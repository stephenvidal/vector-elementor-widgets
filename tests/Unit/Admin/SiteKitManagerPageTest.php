<?php
/**
 * Site Kit Manager page tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Admin\SiteKitManagerPage;
use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Kit\KitStore;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

/**
 * Page controller contract.
 */
final class SiteKitManagerPageTest extends TestCase {

	/**
	 * Stub WP functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'esc_html__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'esc_html' )->alias( static fn ( string $s ): string => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( 'esc_attr' )->alias( static fn ( string $s ): string => htmlspecialchars( $s, ENT_QUOTES, 'UTF-8' ) );
		Functions\when( 'esc_url' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'esc_js' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'esc_textarea' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->alias( static fn ( $v ) => $v );
		Functions\when( 'wp_create_nonce' )->alias( static fn ( string $a ): string => 'nonce-' . $a );
		Functions\when( 'wp_verify_nonce' )->alias( static fn ( string $n, string $a ): bool => 'nonce-' . $a === $n );
		Functions\when( 'current_user_can' )->alias( static fn ( string $cap ): bool => true );
		Functions\when( 'wp_nonce_field' )->alias( static fn ( string $a, string $n ): string => '<input type="hidden" name="' . $n . '" />' );
		Functions\when( 'update_option' )->alias( static fn ( string $o, $v ): bool => true );
		Functions\when( 'wp_json_encode' )->alias( static fn ( $v, int $f = 0 ): string => json_encode( $v, $f ) );
		Functions\when( 'nocache_headers' )->returnArg();
	}

	/**
	 * Build a page with capturing seams.
	 *
	 * @param array<string, mixed> $post $_POST simulation.
	 *
	 * @return array{page: SiteKitManagerPage, holder: \stdClass}
	 */
	private function page_with_seams( array $post ): array {
		$holder = new \stdClass();
		$holder->redirects = array();
		$holder->dies      = array();

		$store = $this->createMock( KitStore::class );
		$store->method( 'all' )->willReturn( array() );
		$store->method( 'get' )->willReturnCallback(
			static fn ( string $slug ): ?Kit => 'acme' === $slug
				? Kit::from_array( array( 'slug' => 'acme', 'label' => 'Acme' ) )
				: null
		);
		$store->method( 'has' )->willReturnCallback( static fn ( string $slug ): bool => 'acme' === $slug );
		$store->method( 'save' )->willReturn( 1 );
		$store->method( 'delete' )->willReturn( true );

		$page = new SiteKitManagerPage(
			$store,
			null,
			null,
			static fn ( string $path ): string => 'http://example.test/wp-admin/' . ltrim( $path, '/' ),
			static function ( string $url ) use ( $holder ): void {
				$holder->redirects[] = $url;
			},
			static function ( string $msg ) use ( $holder ): void {
				$holder->dies[] = $msg;
				throw new \RuntimeException( 'vew-die:' . $msg );
			}
		);

		$_POST = $post;

		return array(
			'page'   => $page,
			'holder' => $holder,
		);
	}

	/**
	 * A valid save persists the kit and redirects.
	 *
	 * @return void
	 */
	public function test_save_persists_and_redirects(): void {
		$saved = array();
		$store = $this->createMock( KitStore::class );
		$store->method( 'save' )->willReturnCallback(
			static function ( Kit $kit ) use ( &$saved ): int {
				$saved[] = $kit->slug();
				return 1;
			}
		);

		$holder = new \stdClass();
		$holder->redirects = array();
		$holder->dies      = array();

		$page = new SiteKitManagerPage(
			$store,
			null,
			null,
			static fn ( string $path ): string => 'http://example.test/wp-admin/' . ltrim( $path, '/' ),
			static function ( string $url ) use ( $holder ): void {
				$holder->redirects[] = $url;
			},
			static function ( string $msg ) use ( $holder ): void {
				$holder->dies[] = $msg;
				throw new \RuntimeException( 'vew-die:' . $msg );
			}
		);

		$_POST = array(
			'_vew_site_kit_nonce' => 'nonce-vew_site_kit_manager',
			'vew_site_kit_action' => 'save',
			'vew_kit'             => array(
				'slug'        => 'acme',
				'label'       => 'Acme',
				'tokens'      => array( 'brand' => '#1c628f' ),
				'fonts'       => array(),
				'layout'      => array(),
			),
		);

		$page->handle_action();

		$this->assertSame( array( 'acme' ), $saved );
		$this->assertCount( 1, $holder->redirects );
		$this->assertCount( 0, $holder->dies );
	}

	/**
	 * An invalid nonce dies without saving.
	 *
	 * @return void
	 */
	public function test_invalid_nonce_dies(): void {
		$result = $this->page_with_seams( array(
			'_vew_site_kit_nonce' => 'wrong',
			'vew_site_kit_action' => 'save',
			'vew_kit'             => array( 'slug' => 'acme' ),
		) );

		try {
			$result['page']->handle_action();
		} catch ( \RuntimeException $e ) {
			// Expected — die seam halts.
		}

		$this->assertCount( 1, $result['holder']->dies );
		$this->assertCount( 0, $result['holder']->redirects );
	}

	/**
	 * set_global persists the option and redirects.
	 *
	 * @return void
	 */
	public function test_set_global_updates_option(): void {
		$updated = array();
		Functions\when( 'update_option' )->alias(
			static function ( string $o, $v ) use ( &$updated ): bool {
				$updated[ $o ] = $v;
				return true;
			}
		);

		$result = $this->page_with_seams( array(
			'_vew_site_kit_nonce'     => 'nonce-vew_site_kit_manager',
			'vew_site_kit_action'     => 'set_global',
			'vew_global_site_kit'     => 'acme',
		) );

		$result['page']->handle_action();

		$this->assertSame( 'acme', $updated[ \Vector\ElementorWidgets\Elementor\SiteKit::GLOBAL_OPTION ] );
		$this->assertCount( 1, $result['holder']->redirects );
	}

	/**
	 * register_hooks wires the submenu + admin_post action.
	 *
	 * @return void
	 */
	public function test_register_hooks_wires_actions(): void {
		$added = array();
		Functions\when( 'add_action' )->alias(
			static function ( string $hook, $cb, int $priority = 10, int $accepted = 1 ) use ( &$added ): void {
				$added[] = $hook;
			}
		);

		$store = $this->createMock( KitStore::class );
		$page  = new SiteKitManagerPage( $store );
		$page->register_hooks();

		$this->assertContains( 'admin_menu', $added );
		$this->assertContains( 'admin_post_vew_site_kit_manager', $added );
	}
}
