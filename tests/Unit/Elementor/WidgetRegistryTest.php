<?php
/**
 * Widget registry tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Elementor\WidgetRegistry;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class WidgetRegistryTest extends TestCase {

	/**
	 * Stub get_option to return a given value for the enabled-widgets option.
	 *
	 * @param mixed $value Value to return.
	 *
	 * @return void
	 */
	private function stub_enabled_option( $value ): void {
		Functions\when( 'get_option' )->alias(
			static function ( string $option, $default = false ) use ( $value ) {
				if ( VEW_OPTION_ENABLED_WIDGETS === $option ) {
					return $value;
				}
				return $default;
			}
		);
	}

	/**
	 * When the option is empty (never saved), every widget is enabled.
	 *
	 * @return void
	 */
	public function test_empty_option_enables_everything(): void {
		$this->stub_enabled_option( array() );
		$registry = new WidgetRegistry();
		$registry->register( 'A\\Widget' );
		$registry->register( 'B\\Widget' );

		$this->assertTrue( $registry->is_enabled( 'A\\Widget' ) );
		$this->assertTrue( $registry->is_enabled( 'B\\Widget' ) );
		$this->assertSame( array( 'A\\Widget', 'B\\Widget' ), $registry->enabled_classes() );
	}

	/**
	 * When the option is not an array, everything is enabled (defensive).
	 *
	 * @return void
	 */
	public function test_non_array_option_enables_everything(): void {
		$this->stub_enabled_option( 'garbage' );
		$registry = new WidgetRegistry();
		$registry->register( 'A\\Widget' );

		$this->assertTrue( $registry->is_enabled( 'A\\Widget' ) );
	}

	/**
	 * When the option lists classes, only those are enabled.
	 *
	 * @return void
	 */
	public function test_option_gates_enabled_classes(): void {
		$this->stub_enabled_option( array( 'A\\Widget' ) );
		$registry = new WidgetRegistry();
		$registry->register( 'A\\Widget' );
		$registry->register( 'B\\Widget' );

		$this->assertTrue( $registry->is_enabled( 'A\\Widget' ) );
		$this->assertFalse( $registry->is_enabled( 'B\\Widget' ) );
		$this->assertSame( array( 'A\\Widget' ), $registry->enabled_classes() );
	}

	/**
	 * all() returns registered classes in order.
	 *
	 * @return void
	 */
	public function test_all_returns_registered_classes(): void {
		$registry = new WidgetRegistry();
		$registry->register( 'A\\Widget' );
		$registry->register( 'B\\Widget' );

		$this->assertSame( array( 'A\\Widget', 'B\\Widget' ), $registry->all() );
	}
}
