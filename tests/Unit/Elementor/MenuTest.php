<?php
/**
 * Menu widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class MenuTest extends TestCase {

	/**
	 * Read a project file.
	 *
	 * @param string $relative_path Project-relative path.
	 * @return string
	 */
	private function source( string $relative_path ): string {
		$path = dirname( __DIR__, 3 ) . '/' . $relative_path;
		$this->assertFileExists( $path );

		$source = file_get_contents( $path );
		$this->assertIsString( $source );
		return $source;
	}

	/**
	 * The Menu widget owns a full-width surface and a safe internal shell.
	 */
	public function test_menu_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/Menu.php' );
		$css    = $this->source( 'widgets/Menu/vew-menu.css' );

		$this->assertStringContainsString( 'class="vew-menu__inner"', $widget );
		// The breakout must subtract the classic-scrollbar width (--vew-sb) or it
		// overshoots the viewport; see FullBleedBreakoutTest.
		$this->assertStringContainsString( 'calc(100vw - var(--vew-sb, 0px))', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The Menu widget renders name, description, price, and tag.
	 */
	public function test_menu_renders_items(): void {
		$widget = $this->source( 'src/Elementor/Widget/Menu.php' );

		$this->assertStringContainsString( 'vew-menu__item-title', $widget );
		$this->assertStringContainsString( 'vew-menu__item-desc', $widget );
		$this->assertStringContainsString( 'vew-menu__price', $widget );
		$this->assertStringContainsString( 'vew-menu__tag', $widget );
		$this->assertStringContainsString( "'items'", $widget );
	}

	/**
	 * The Menu widget consumes the shared section-heading component.
	 */
	public function test_menu_consumes_shared_heading(): void {
		$widget = $this->source( 'src/Elementor/Widget/Menu.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( "'title_accent'", $widget );
	}

	/**
	 * The Menu widget is registered in the catalog and plugin.
	 */
	public function test_menu_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'Menu::class', $plugin );
		$this->assertStringContainsString( 'vew-menu', $plugin );
		$this->assertStringContainsString( 'Menu::class', $catalog );
		$this->assertStringContainsString( "'vew-menu'", $catalog );
	}
}
