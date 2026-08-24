<?php
/**
 * Elementor integration bootstrap.
 *
 * Registers the "Vector Widgets" category and all registered widgets with
 * Elementor. Widgets live in Widget\*; controls are split into per-tab
 * classes in Control\*.
 *
 * Phase 1: this file is the only Elementor-side registration required.
 * Future phases may add more widgets here without changing the
 * integration shape.
 *
 * @package Vector\ElementorWidgets\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor;

use Elementor\Elements_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor integration bootstrap.
 */
final class Plugin {

	/**
	 * Addon category slug.
	 *
	 * Elementor groups widgets by category in the editor panel. The
	 * category is registered once on the `elementor/elements/categories_registered`
	 * action and the widgets are added once on `elementor/widgets/register`.
	 */
	public const CATEGORY_SLUG = 'vector-widgets';

	/**
	 * Widget registry.
	 *
	 * @var WidgetRegistry
	 */
	private WidgetRegistry $registry;

	/**
	 * Asset manager.
	 *
	 * @var AssetManager
	 */
	private AssetManager $assets;

	/**
	 * Constructor.
	 *
	 * @param WidgetRegistry $registry Widget registry.
	 * @param AssetManager   $assets   Asset manager.
	 */
	public function __construct( WidgetRegistry $registry, AssetManager $assets ) {
		$this->registry = $registry;
		$this->assets   = $assets;
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		add_action( 'elementor/elements/categories_registered', $this->register_category( ... ) );
		add_action( 'elementor/widgets/register', $this->register_widgets( ... ) );
	}

	/**
	 * Register the "Vector Widgets" elementor category.
	 *
	 * @param Elements_Manager $elements_manager Elementor elements manager.
	 *
	 * @return void
	 */
	public function register_category( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			self::CATEGORY_SLUG,
			array(
				'title' => __( 'Vector Widgets', 'vector-elementor-widgets' ),
				'icon'  => 'eicon-code',
			)
		);
	}

	/**
	 * Register all enabled widgets.
	 *
	 * Widgets are constructed with no arguments. Elementor will call
	 * their `__construct( array $data = [], array $args = null )` when
	 * it loads a saved page document, and the widget resolves its
	 * services from the service container inside `render()`.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 *
	 * @return void
	 */
	public function register_widgets( $widgets_manager ): void {
		$this->registry->register_all( $widgets_manager );
	}

	/**
	 * Get the asset manager (for widgets to declare dependencies).
	 *
	 * @return AssetManager
	 */
	public function assets(): AssetManager {
		return $this->assets;
	}
}
