<?php
/**
 * Watch widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class WatchTest extends TestCase {

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
	 * The Watch widget owns a full-width surface and a safe internal shell.
	 */
	public function test_watch_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/Watch.php' );
		$css    = $this->source( 'widgets/Watch/vew-watch.css' );

		$this->assertStringContainsString( 'class="vew-watch__inner"', $widget );
		// The breakout must subtract the classic-scrollbar width (--vew-sb) or it
		// overshoots the viewport; see FullBleedBreakoutTest.
		$this->assertStringContainsString( 'calc(100vw - var(--vew-sb, 0px))', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The Watch widget renders intro, CTA links, and media with play pin.
	 */
	public function test_watch_renders_intro_cta_media(): void {
		$widget = $this->source( 'src/Elementor/Widget/Watch.php' );

		$this->assertStringContainsString( 'vew-watch__cta', $widget );
		$this->assertStringContainsString( 'vew-watch__secondary', $widget );
		$this->assertStringContainsString( 'vew-watch__media', $widget );
		$this->assertStringContainsString( 'vew-watch__play', $widget );
		$this->assertStringContainsString( "'cta_url'", $widget );
		$this->assertStringContainsString( "'image'", $widget );
	}

	/**
	 * The Watch widget consumes the shared section-heading component.
	 */
	public function test_watch_consumes_shared_heading(): void {
		$widget = $this->source( 'src/Elementor/Widget/Watch.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( "'title_accent'", $widget );
	}

	/**
	 * The Watch widget is registered in the catalog and plugin.
	 */
	public function test_watch_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'Watch::class', $plugin );
		$this->assertStringContainsString( 'vew-watch', $plugin );
		$this->assertStringContainsString( 'Watch::class', $catalog );
		$this->assertStringContainsString( "'vew-watch'", $catalog );
	}
}
