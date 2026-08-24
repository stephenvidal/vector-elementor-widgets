<?php
/**
 * Minimal WP core class stubs for unit tests.
 *
 * The unit test suite runs without a live WordPress install, so the WP core
 * classes that {@see KitStore} touches (WP_Post, WP_Query) are not defined.
 * These lightweight stand-ins let the store be exercised in isolation with
 * Brain Monkey stubbing the WP functions.
 *
 * The WP_Query stand-in consults a static query registry set by the test
 * (KitStoreStub::set_query_results()) so each test can control which posts a
 * "query" returns without a live DB.
 *
 * This file is loaded by the KitStore unit test ONLY, and is excluded from
 * PHPCS via the tests/Support/* pattern.
 *
 * @package Vector\ElementorWidgets\TestSupport
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\TestSupport { // phpcs:ignore -- support-only stand-ins.

	/**
	 * Shared state for the WP stubs.
	 */
	final class KitStoreStub {

		/**
		 * Map of query results to return in FIFO order.
		 *
		 * @var array<int, array<int, \WP_Post>>
		 */
		private static array $results = array();

		/**
		 * Queue a set of posts for the next WP_Query construction.
		 *
		 * @param array<int, \WP_Post> $posts Posts to return on next query.
		 *
		 * @return void
		 */
		public static function set_query_results( array $posts ): void {
			self::$results[] = $posts;
		}

		/**
		 * Pop the next result set (FIFO).
		 *
		 * @return array<int, \WP_Post>
		 */
		public static function next_results(): array {
			if ( count( self::$results ) > 0 ) {
				return array_shift( self::$results );
			}
			return array();
		}

		/**
		 * Reset the registry.
		 *
		 * @return void
		 */
		public static function reset(): void {
			self::$results = array();
		}
	}
}

namespace { // phpcs:ignore -- support-only stand-ins, not plugin code.

	if ( ! class_exists( 'WP_Post' ) ) {
		/**
		 * Minimal WP_Post stand-in.
		 */
		class WP_Post {
			/** @var int */
			public $ID = 0;
			/** @var string */
			public $post_name = '';
			/** @var string */
			public $post_type = 'vew_site_kit';
		}
	}

	if ( ! class_exists( 'WP_Query' ) ) {
		/**
		 * Minimal WP_Query stand-in returning the test-registered posts.
		 */
		class WP_Query {
			/** @var array<int, \WP_Post> */
			public $posts = array();

			/**
			 * Constructor.
			 *
			 * @param array<string, mixed> $args Query args (unused in stub).
			 */
			public function __construct( array $args = array() ) {
				$this->posts = \Vector\ElementorWidgets\TestSupport\KitStoreStub::next_results();
			}
		}
	}
}
