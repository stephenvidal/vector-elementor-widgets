<?php
/**
 * PageHero widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class PageHeroTest extends TestCase {

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
	 * The PageHero widget owns a full-width surface and a safe internal shell.
	 */
	public function test_page_hero_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/PageHero.php' );
		$css    = $this->source( 'widgets/PageHero/vew-page-hero.css' );

		$this->assertStringContainsString( 'class="vew-page-hero__inner"', $widget );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding: clamp(52px, 8vw, 96px) 24px 48px;', $css );
		$this->assertStringContainsString( 'padding: 44px 20px 36px;', $css );
	}

	/**
	 * The PageHero widget renders breadcrumb, eyebrow, title, and lead.
	 */
	public function test_page_hero_renders_breadcrumb_eyebrow_title_lead(): void {
		$widget = $this->source( 'src/Elementor/Widget/PageHero.php' );

		$this->assertStringContainsString( 'vew-page-hero__breadcrumb', $widget );
		$this->assertStringContainsString( 'vew-page-hero__eyebrow', $widget );
		$this->assertStringContainsString( 'vew-page-hero__title', $widget );
		$this->assertStringContainsString( 'vew-page-hero__lead', $widget );
		$this->assertStringContainsString( "'breadcrumb_home_url'", $widget );
		$this->assertStringContainsString( "'lead'", $widget );
	}

	/**
	 * The PageHero widget is registered in the catalog and plugin.
	 */
	public function test_page_hero_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'PageHero::class', $plugin );
		$this->assertStringContainsString( 'vew-page-hero', $plugin );
		$this->assertStringContainsString( 'PageHero::class', $catalog );
		$this->assertStringContainsString( "'vew-page-hero'", $catalog );
	}
}
