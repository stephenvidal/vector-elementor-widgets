<?php
/**
 * SiteKit contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Elementor\SiteKit;
use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Kit\KitCssCompiler;
use Vector\ElementorWidgets\Kit\KitStore;
use Vector\ElementorWidgets\Tests\Unit\TestCase;
use Vector\ElementorWidgets\TestSupport\KitStoreStub;

require_once dirname( __DIR__, 2 ) . '/Support/wp-stubs.php';

/**
 * SiteKit resolution + enqueue contract.
 */
final class SiteKitTest extends TestCase {

	/**
	 * Set up WP stubs.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		KitStoreStub::reset();

		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_enqueue_style' )->returnArg();
		Functions\when( 'wp_add_inline_style' )->returnArg();
		Functions\when( 'wp_register_style' )->returnArg();
		// The scrollbar metric script is enqueued alongside the a11y stylesheet.
		Functions\when( 'wp_register_script' )->returnArg();
		Functions\when( 'wp_enqueue_script' )->returnArg();
		Functions\when( 'get_the_ID' )->alias( static fn (): int => 0 );
		Functions\when( 'get_post_meta' )->alias( static fn ( int $id, string $key, bool $single = false ): string => '' );
		Functions\when( 'get_option' )->alias( static fn ( string $o, $d = false ) => $d );
		Functions\when( 'get_stylesheet' )->returnArg();
	}

	/**
	 * Build a SiteKit with a store stubbed to return a given kit.
	 *
	 * @param string|null $slug Kit slug the store will return.
	 *
	 * @return SiteKit
	 */
	private function sitekit_with_store( ?string $slug ): SiteKit {
		$store = $this->createMock( KitStore::class );
		$store->method( 'get' )->willReturn(
			null === $slug ? null : Kit::from_array( array( 'slug' => $slug, 'tokens' => array( 'brand' => '#1c628f' ) ) )
		);

		return new SiteKit( $store, new KitCssCompiler(), 'http://example.test/', '/tmp/' );
	}

	/**
	 * Build a SiteKit with a store that returns no kits.
	 *
	 * @return SiteKit
	 */
	private function sitekit_with_empty_store(): SiteKit {
		$store = $this->createMock( KitStore::class );
		$store->method( 'get' )->willReturn( null );

		return new SiteKit( $store, new KitCssCompiler(), 'http://example.test/', dirname( __DIR__, 3 ) . '/' );
	}

	/**
	 * resolve_kit returns null when no page meta and no global kit.
	 *
	 * @return void
	 */
	public function test_resolve_kit_returns_null_when_no_kit_available(): void {
		$site = $this->sitekit_with_store( null );

		$this->assertNull( $site->resolve_kit() );
	}

	/**
	 * resolve_kit honors the page meta first.
	 *
	 * @return void
	 */
	public function test_resolve_kit_uses_page_meta(): void {
		Functions\when( 'get_the_ID' )->justReturn( 42 );
		Functions\when( 'get_post_meta' )->alias( static fn ( int $id, string $key, bool $single = false ): string => 'rore' );

		$site  = $this->sitekit_with_store( 'rore' );
		$found = $site->resolve_kit();

		$this->assertNotNull( $found );
		$this->assertSame( 'rore', $found->slug() );
	}

	/**
	 * resolve_kit falls back to the global option when no page meta.
	 *
	 * @return void
	 */
	public function test_resolve_kit_uses_global_option(): void {
		Functions\when( 'get_the_ID' )->alias( static fn (): int => 0 );
		Functions\when( 'get_option' )->alias( static fn ( string $key, $d = '' ) => 'gslg' );

		$site  = $this->sitekit_with_store( 'gslg' );
		$found = $site->resolve_kit();

		$this->assertNotNull( $found );
		$this->assertSame( 'gslg', $found->slug() );
	}

	/**
	 * enqueue adds the compiled inline style when a kit is resolved.
	 *
	 * @return void
	 */
	public function test_enqueue_adds_inline_style_when_kit_resolved(): void {
		Functions\when( 'get_the_ID' )->alias( static fn (): int => 42 );
		Functions\when( 'get_post_meta' )->alias( static fn ( int $id, string $key, bool $single = false ): string => 'acme' );

		$added = array();
		Functions\when( 'wp_add_inline_style' )->alias(
			static function ( string $handle, string $css ) use ( &$added ): void {
				$added[ $handle ] = $css;
			}
		);

		$site = $this->sitekit_with_store( 'acme' );
		$site->enqueue();

		$this->assertArrayHasKey( SiteKit::HANDLE, $added );
		$this->assertStringContainsString( '--brand: #1c628f', $added[ SiteKit::HANDLE ] );
	}

	/**
	 * enqueue falls back to the file kit when no DB kit resolves.
	 *
	 * @return void
	 */
	public function test_enqueue_falls_back_to_file_kit(): void {
		Functions\when( 'get_the_ID' )->alias( static fn (): int => 0 );
		Functions\when( 'get_option' )->alias( static fn ( string $key, $d = '' ) => '' );

		$registered = array();
		Functions\when( 'wp_register_style' )->alias(
			static function ( string $handle, string $src = '', array $deps = array(), $ver = false ) use ( &$registered ): void {
				$registered[ $handle ] = $src;
			}
		);

		$site = $this->sitekit_with_empty_store();
		$site->enqueue();

		$this->assertStringContainsString( 'assets/site-kit.css', $registered[ SiteKit::HANDLE ] ?? '' );
	}
}
