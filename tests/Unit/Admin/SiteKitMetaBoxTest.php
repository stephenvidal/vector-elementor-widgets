<?php
/**
 * Site Kit meta box tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Admin
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Admin;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Admin\SiteKitMetaBox;
use Vector\ElementorWidgets\Elementor\SiteKit;
use Vector\ElementorWidgets\Kit\KitStore;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

require_once dirname( __DIR__, 2 ) . '/Support/wp-stubs.php';

/**
 * Per-page kit meta box contract.
 */
final class SiteKitMetaBoxTest extends TestCase {

	/**
	 * Stub WP functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'esc_html__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
		Functions\when( 'esc_html' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'esc_attr' )->alias( static fn ( string $s ): string => $s );
		Functions\when( 'sanitize_key' )->returnArg();
		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'wp_unslash' )->alias( static fn ( $v ) => $v );
		Functions\when( 'wp_nonce_field' )->alias( static fn ( string $a, string $n, bool $ref = true, bool $echo = true ): string => '' );
		Functions\when( 'wp_verify_nonce' )->alias( static fn ( string $n, string $a ): bool => 'nonce-' . $a === $n );
		Functions\when( 'current_user_can' )->alias( static fn ( string $cap, int $id = 0 ): bool => true );
		Functions\when( 'get_post_meta' )->alias( static fn ( int $id, string $key, bool $single = false ): string => '' );
		Functions\when( 'update_post_meta' )->alias( static fn ( int $id, string $key, $value ): int => 1 );
		Functions\when( 'delete_post_meta' )->alias( static fn ( int $id, string $key ): bool => true );
		Functions\when( 'selected' )->alias( static fn ( string $a, string $b, bool $echo = true ): string => ( $a === $b ? ' selected="selected"' : '' ) );
	}

	/**
	 * Build a page with a store stubbed to have one kit.
	 *
	 * @param array $kits Map of slug => exists.
	 *
	 * @return SiteKitMetaBox
	 */
	private function metabox_with_store( array $kits = array() ): SiteKitMetaBox {
		$store = $this->createMock( KitStore::class );
		$store->method( 'all' )->willReturn( array() );
		$store->method( 'has' )->willReturnCallback(
			static fn ( string $slug ): bool => in_array( $slug, $kits, true )
		);
		return new SiteKitMetaBox( $store );
	}

	/**
	 * register_hooks wires add_meta_boxes and save_post.
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

		$this->metabox_with_store()->register_hooks();

		$this->assertContains( 'add_meta_boxes', $added );
		$this->assertContains( 'save_post', $added );
	}

	/**
	 * save() persists a valid kit slug.
	 *
	 * @return void
	 */
	public function test_save_persists_valid_kit(): void {
		$updated = array();
		Functions\when( 'update_post_meta' )->alias(
			static function ( int $id, string $key, $value ) use ( &$updated ): int {
				$updated[ $key ] = $value;
				return 1;
			}
		);

		$_POST = array(
			'_vew_site_kit_meta_nonce' => 'nonce-vew_site_kit_metabox',
			'vew_site_kit'             => 'acme',
		);

		$post          = new \WP_Post();
		$post->ID      = 42;
		$post->post_name = 'acme';
		$post->post_type = 'page';

		$this->metabox_with_store( array( 'acme' ) )->save( 42, $post );

		$this->assertSame( 'acme', $updated[ SiteKit::META_KEY ] );
	}

	/**
	 * save() clears the meta when the select is reset to global.
	 *
	 * @return void
	 */
	public function test_save_clears_when_empty(): void {
		$deleted = false;
		Functions\when( 'delete_post_meta' )->alias(
			static function ( int $id, string $key ) use ( &$deleted ): bool {
				$deleted = true;
				return true;
			}
		);

		$_POST = array(
			'_vew_site_kit_meta_nonce' => 'nonce-vew_site_kit_metabox',
			'vew_site_kit'             => '',
		);

		$post          = new \WP_Post();
		$post->ID      = 42;
		$post->post_type = 'page';

		$this->metabox_with_store()->save( 42, $post );

		$this->assertTrue( $deleted );
	}

	/**
	 * save() ignores an unknown kit slug.
	 *
	 * @return void
	 */
	public function test_save_ignores_unknown_kit(): void {
		$updated = array();
		Functions\when( 'update_post_meta' )->alias(
			static function ( int $id, string $key, $value ) use ( &$updated ): int {
				$updated[ $key ] = $value;
				return 1;
			}
		);

		$_POST = array(
			'_vew_site_kit_meta_nonce' => 'nonce-vew_site_kit_metabox',
			'vew_site_kit'             => 'ghost',
		);

		$post          = new \WP_Post();
		$post->ID      = 42;
		$post->post_type = 'page';

		$this->metabox_with_store( array( 'acme' ) )->save( 42, $post );

		$this->assertCount( 0, $updated );
	}
}
