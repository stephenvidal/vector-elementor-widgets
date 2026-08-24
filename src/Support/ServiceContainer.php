<?php
/**
 * Lightweight service container.
 *
 * Phase 1 uses this to register and resolve the long-lived services
 * (Plugin, Compatibility, AssetManager, Elementor integration). Later
 * phases will swap the registered factories for real implementations.
 *
 * Design notes:
 *  - Singletons only in Phase 1 (one instance per service per request).
 *  - Factories are closures that return the service instance.
 *  - `get()` lazily resolves and caches; `has()` is a presence check.
 *  - `register()` overwrites if a key already exists (useful for testing).
 *  - `reset()` clears the cache (test-only).
 *
 * @package Vector\ElementorWidgets\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service container — Phase 1 simple registry.
 */
final class ServiceContainer {

	/**
	 * Map of service id => factory closure.
	 *
	 * @var array<string, callable(self):object>
	 */
	private array $factories = array();

	/**
	 * Map of service id => resolved instance.
	 *
	 * @var array<string, object>
	 */
	private array $instances = array();

	/**
	 * Register a service factory.
	 *
	 * If a factory is already registered for this id, it is overwritten.
	 *
	 * @param string                $id      Service identifier (case-sensitive).
	 * @param callable(self):object $factory Factory that returns the instance.
	 *
	 * @return void
	 */
	public function register( string $id, callable $factory ): void {
		$this->factories[ $id ] = $factory;
		// Invalidate any previously-resolved instance.
		unset( $this->instances[ $id ] );
	}

	/**
	 * Check whether a service id is registered (factory present).
	 *
	 * @param string $id Service identifier.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->factories[ $id ] );
	}

	/**
	 * Resolve a service instance, lazily instantiating via its factory.
	 *
	 * @param string $id Service identifier.
	 *
	 * @return object The resolved service.
	 *
	 * @throws \RuntimeException If no factory is registered for the id.
	 */
	public function get( string $id ): object {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}
		if ( ! isset( $this->factories[ $id ] ) ) {
			throw new \RuntimeException( "Service not registered: {$id}" ); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
		}
		$instance = ( $this->factories[ $id ] )( $this );
		$this->instances[ $id ] = $instance;
		return $instance;
	}

	/**
	 * List all registered service ids.
	 *
	 * @return array<int, string>
	 */
	public function ids(): array {
		return array_keys( $this->factories );
	}

	/**
	 * Clear resolved instances (factories remain). Test-only.
	 *
	 * @internal
	 *
	 * @return void
	 */
	public function reset(): void {
		$this->instances = array();
	}

	/**
	 * Clear everything (factories + instances). Test-only.
	 *
	 * @internal
	 *
	 * @return void
	 */
	public function flush(): void {
		$this->factories = array();
		$this->instances = array();
	}
}
