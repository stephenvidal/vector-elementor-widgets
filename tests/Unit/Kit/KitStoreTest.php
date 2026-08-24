<?php
/**
 * Kit store tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Kit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Kit;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Kit\Kit;
use Vector\ElementorWidgets\Kit\KitPostType;
use Vector\ElementorWidgets\Kit\KitStore;
use Vector\ElementorWidgets\Tests\Unit\TestCase;
use Vector\ElementorWidgets\TestSupport\KitStoreStub;

require_once dirname( __DIR__, 2 ) . '/Support/wp-stubs.php';

/**
 * Kit store persistence contract.
 */
final class KitStoreTest extends TestCase {

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

		Functions\when( 'wp_insert_post' )->alias( static fn ( array $a ): int => 100 );
		Functions\when( 'wp_update_post' )->returnArg();
		Functions\when( 'update_post_meta' )->alias( static fn ( int $id, string $key, $value ): bool => true );
		Functions\when( 'get_post_meta' )->alias(
			static function ( int $id, string $key, bool $single = false ) {
				if ( KitPostType::META_KEY === $key ) {
					return array( 'slug' => 'x', 'label' => 'X', 'description' => '', 'tokens' => array( 'brand' => '#1c628f' ), 'fonts' => array(), 'layout' => array() );
				}
				return '';
			}
		);
		Functions\when( 'wp_delete_post' )->alias( static fn ( int $id, bool $force = false ): bool => true );
	}

	/**
	 * A helper to build a WP_Post stand-in.
	 *
	 * @param int    $id    Post ID.
	 * @param string $slug  Post name.
	 *
	 * @return \WP_Post
	 */
	private function wp_post( int $id, string $slug ): \WP_Post {
		$post          = new \WP_Post();
		$post->ID      = $id;
		$post->post_name = $slug;
		return $post;
	}

	/**
	 * save() persists a kit and returns the post id.
	 *
	 * @return void
	 */
	public function test_save_returns_post_id(): void {
		$captured_meta = array();
		Functions\when( 'update_post_meta' )->alias(
			static function ( int $id, string $key, $value ) use ( &$captured_meta ): bool {
				$captured_meta[ $key ] = $value;
				return true;
			}
		);

		$kit = Kit::from_array( array( 'slug' => 'acme', 'label' => 'Acme', 'tokens' => array( 'brand' => '#1c628f' ) ) );
		$id  = ( new KitStore() )->save( $kit );

		$this->assertSame( 100, $id );
		$this->assertArrayHasKey( KitPostType::META_KEY, $captured_meta );
	}

	/**
	 * get() hydrates a kit from its post + meta.
	 *
	 * @return void
	 */
	public function test_get_hydrates_kit(): void {
		KitStoreStub::set_query_results( array( $this->wp_post( 7, 'acme' ) ) );

		$kit = ( new KitStore() )->get( 'acme' );

		$this->assertNotNull( $kit );
		$this->assertSame( 'acme', $kit->slug() );
		$this->assertSame( '#1c628f', $kit->token( 'brand' ) );
	}

	/**
	 * get() returns null when the slug is not found.
	 *
	 * @return void
	 */
	public function test_get_returns_null_when_missing(): void {
		KitStoreStub::set_query_results( array() );

		$this->assertNull( ( new KitStore() )->get( 'missing' ) );
	}

	/**
	 * all_slugs lists the stored kit slugs sorted.
	 *
	 * @return void
	 */
	public function test_all_slugs_sorted(): void {
		KitStoreStub::set_query_results( array( $this->wp_post( 1, 'zz' ), $this->wp_post( 2, 'aa' ) ) );

		$this->assertSame( array( 'aa', 'zz' ), ( new KitStore() )->all_slugs() );
	}

	/**
	 * delete() returns true when a post existed.
	 *
	 * @return void
	 */
	public function test_delete_existing(): void {
		KitStoreStub::set_query_results( array( $this->wp_post( 7, 'acme' ) ) );

		$this->assertTrue( ( new KitStore() )->delete( 'acme' ) );
	}

	/**
	 * delete() returns false when no post exists.
	 *
	 * @return void
	 */
	public function test_delete_missing_returns_false(): void {
		KitStoreStub::set_query_results( array() );

		$this->assertFalse( ( new KitStore() )->delete( 'nope' ) );
	}
}
