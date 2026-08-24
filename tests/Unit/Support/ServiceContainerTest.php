<?php
/**
 * Service container behavior tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Support;

use Vector\ElementorWidgets\Support\ServiceContainer;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class ServiceContainerTest extends TestCase {

	/**
	 * Registering a factory and resolving it returns a fresh instance,
	 * and subsequent resolves return the same (cached) instance.
	 */
	public function test_register_and_get_returns_singleton(): void {
		$container = new ServiceContainer();

		$container->register( 'widget_registry', static function (): object {
			return new \stdClass();
		} );

		$first  = $container->get( 'widget_registry' );
		$second = $container->get( 'widget_registry' );

		$this->assertSame( $first, $second );
	}

	/**
	 * has() reports registration state.
	 */
	public function test_has_reflects_registration(): void {
		$container = new ServiceContainer();

		$this->assertFalse( $container->has( 'nope' ) );

		$container->register( 'x', static fn (): object => new \stdClass() );

		$this->assertTrue( $container->has( 'x' ) );
	}

	/**
	 * Getting an unregistered service throws.
	 */
	public function test_get_unregistered_throws(): void {
		$container = new ServiceContainer();

		$this->expectException( \RuntimeException::class );
		$container->get( 'missing' );
	}

	/**
	 * reset() clears resolved instances but keeps factories.
	 */
	public function test_reset_clears_instances_keeps_factories(): void {
		$container = new ServiceContainer();
		$container->register( 'x', static function (): object {
			return new \stdClass();
		} );

		$first = $container->get( 'x' );
		$container->reset();
		$second = $container->get( 'x' );

		$this->assertNotSame( $first, $second );
	}

	/**
	 * flush() clears factories + instances.
	 */
	public function test_flush_clears_everything(): void {
		$container = new ServiceContainer();
		$container->register( 'x', static fn (): object => new \stdClass() );
		$container->get( 'x' );

		$container->flush();

		$this->assertFalse( $container->has( 'x' ) );
	}

	/**
	 * ids() lists registered factories.
	 */
	public function test_ids_lists_registered(): void {
		$container = new ServiceContainer();
		$container->register( 'a', static fn (): object => new \stdClass() );
		$container->register( 'b', static fn (): object => new \stdClass() );

		$this->assertContains( 'a', $container->ids() );
		$this->assertContains( 'b', $container->ids() );
	}
}
