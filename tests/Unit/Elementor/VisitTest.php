<?php
/**
 * Visit widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class VisitTest extends TestCase {

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
	 * The Visit widget owns a full-width surface and a safe internal shell.
	 */
	public function test_visit_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/Visit.php' );
		$css    = $this->source( 'widgets/Visit/vew-visit.css' );

		$this->assertStringContainsString( 'class="vew-visit__inner"', $widget );
		// The breakout must subtract the classic-scrollbar width (--vew-sb) or it
		// overshoots the viewport; see FullBleedBreakoutTest.
		$this->assertStringContainsString( 'calc(100vw - var(--vew-sb, 0px))', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The Visit widget renders intro, details, CTA, and map image.
	 */
	public function test_visit_renders_details_cta_map(): void {
		$widget = $this->source( 'src/Elementor/Widget/Visit.php' );

		$this->assertStringContainsString( 'vew-visit__detail', $widget );
		$this->assertStringContainsString( 'vew-visit__cta', $widget );
		$this->assertStringContainsString( 'vew-visit__map', $widget );
		$this->assertStringContainsString( 'vew-visit__pin', $widget );
		$this->assertStringContainsString( "'details'", $widget );
		$this->assertStringContainsString( "'image'", $widget );
	}

	/**
	 * The Visit widget consumes the shared section-heading component.
	 */
	public function test_visit_consumes_shared_heading(): void {
		$widget = $this->source( 'src/Elementor/Widget/Visit.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( "'title_accent'", $widget );
	}

	/**
	 * The Visit widget is registered in the catalog and plugin.
	 */
	public function test_visit_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'Visit::class', $plugin );
		$this->assertStringContainsString( 'vew-visit', $plugin );
		$this->assertStringContainsString( 'Visit::class', $catalog );
		$this->assertStringContainsString( "'vew-visit'", $catalog );
	}
}
