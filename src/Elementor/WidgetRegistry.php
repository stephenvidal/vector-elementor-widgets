<?php
/**
 * Widget registry.
 *
 * Centralized widget registration. The registry reads the widget catalog
 * (WidgetCatalog) and registers each enabled widget with Elementor. Adding
 * a widget = one entry in WidgetCatalog::all().
 *
 * The registry iterates enabled widgets and calls
 * `$widgets_manager->register( new $widget() )` — no constructor args
 * (Elementor owns the constructor; services resolve lazily inside render()).
 *
 * @package Vector\ElementorWidgets\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget registry — the single registration point for all widgets.
 */
final class WidgetRegistry {

	/**
	 * Registered widget class names, in registration order.
	 *
	 * @var array<int, string>
	 */
	private array $widgets = array();

	/**
	 * Register a widget class.
	 *
	 * @param string $class Fully-qualified widget class name.
	 *
	 * @return void
	 */
	public function register( string $class ): void {
		$this->widgets[] = $class;
	}

	/**
	 * Register all enabled widgets with Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 *
	 * @return void
	 */
	public function register_all( $widgets_manager ): void {
		foreach ( $this->widgets as $class ) {
			if ( ! $this->is_enabled( $class ) ) {
				continue;
			}
			$widgets_manager->register( new $class() );
		}
	}

	/**
	 * List registered widget class names.
	 *
	 * @return array<int, string>
	 */
	public function all(): array {
		return $this->widgets;
	}

	/**
	 * Whether a widget class is enabled.
	 *
	 * Reads the `vew_enabled_widgets` option. When the option is empty
	 * (never saved), every widget is enabled by default. When set, only
	 * the listed classes are enabled.
	 *
	 * @param string $class Widget class name.
	 *
	 * @return bool
	 */
	public function is_enabled( string $class ): bool {
		$enabled = get_option( VEW_OPTION_ENABLED_WIDGETS, array() );
		if ( ! is_array( $enabled ) || array() === $enabled ) {
			return true; // Default: everything enabled.
		}
		return in_array( $class, $enabled, true );
	}

	/**
	 * The set of enabled widget classes.
	 *
	 * @return array<int, string>
	 */
	public function enabled_classes(): array {
		$enabled = array();
		foreach ( $this->widgets as $class ) {
			if ( $this->is_enabled( $class ) ) {
				$enabled[] = $class;
			}
		}
		return $enabled;
	}

	/**
	 * The set of enabled widget slugs (for the admin view).
	 *
	 * @return array<int, string>
	 */
	public function enabled_slugs(): array {
		$slugs = array();
		foreach ( $this->enabled_classes() as $class ) {
			$def = WidgetCatalog::find_by_class( $class );
			if ( null !== $def ) {
				$slugs[] = $def['slug'];
			}
		}
		return $slugs;
	}
}
