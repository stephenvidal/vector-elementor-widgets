<?php
/**
 * Widget catalog tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Elementor\WidgetCatalog;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class WidgetCatalogTest extends TestCase {

	/**
	 * Set up WP function stubs. WidgetCatalog::all() calls __() for titles.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		Functions\when( '__' )->alias( static fn ( string $s, $d = '' ) => (string) $s );
	}

	/**
	 * Every catalog entry has the required keys with non-empty values.
	 *
	 * @return void
	 */
	public function test_every_entry_has_required_keys(): void {
		foreach ( WidgetCatalog::all() as $def ) {
			$this->assertArrayHasKey( 'slug', $def );
			$this->assertArrayHasKey( 'class', $def );
			$this->assertArrayHasKey( 'title', $def );
			$this->assertArrayHasKey( 'description', $def );
			$this->assertArrayHasKey( 'icon', $def );

			$this->assertNotSame( '', $def['slug'] );
			$this->assertNotSame( '', $def['class'] );
			$this->assertNotSame( '', $def['title'] );
		}
	}

	/**
	 * Slugs are unique across the catalog.
	 *
	 * @return void
	 */
	public function test_slugs_are_unique(): void {
		$slugs = array_map( static fn ( array $d ): string => $d['slug'], WidgetCatalog::all() );
		$this->assertSame( count( $slugs ), count( array_unique( $slugs ) ) );
	}

	/**
	 * Classes are unique across the catalog.
	 *
	 * @return void
	 */
	public function test_classes_are_unique(): void {
		$classes = array_map( static fn ( array $d ): string => $d['class'], WidgetCatalog::all() );
		$this->assertSame( count( $classes ), count( array_unique( $classes ) ) );
	}

	/**
	 * find_by_class returns the matching entry.
	 *
	 * @return void
	 */
	public function test_find_by_class_returns_matching_entry(): void {
		$first = WidgetCatalog::all()[0];
		$found = WidgetCatalog::find_by_class( $first['class'] );

		$this->assertNotNull( $found );
		$this->assertSame( $first['slug'], $found['slug'] );
	}

	/**
	 * find_by_class returns null for an unknown class.
	 *
	 * @return void
	 */
	public function test_find_by_class_returns_null_for_unknown(): void {
		$this->assertNull( WidgetCatalog::find_by_class( 'No\\Such\\Widget' ) );
	}

	/**
	 * classes() returns the class names in order.
	 *
	 * @return void
	 */
	public function test_classes_returns_class_names(): void {
		$classes = WidgetCatalog::classes();
		$this->assertSame( count( WidgetCatalog::all() ), count( $classes ) );
		foreach ( WidgetCatalog::all() as $def ) {
			$this->assertContains( $def['class'], $classes );
		}
	}
}
