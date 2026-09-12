<?php
/**
 * Stats widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Elementor\Widget\Stats;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class StatsTest extends TestCase {

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
	 * The Stats widget owns a full-width surface and a safe internal shell.
	 */
	public function test_stats_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/Stats.php' );
		$css    = $this->source( 'widgets/Stats/vew-stats.css' );

		$this->assertStringContainsString( 'class="vew-stats__inner"', $widget );
		// The breakout must subtract the classic-scrollbar width (--vew-sb) or it
		// overshoots the viewport; see FullBleedBreakoutTest.
		$this->assertStringContainsString( 'calc(100vw - var(--vew-sb, 0px))', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The Stats widget renders value/label pairs from a repeater.
	 */
	public function test_stats_renders_value_label_pairs(): void {
		$widget = $this->source( 'src/Elementor/Widget/Stats.php' );

		$this->assertStringContainsString( 'vew-stats__value', $widget );
		$this->assertStringContainsString( 'vew-stats__label', $widget );
		$this->assertStringContainsString( "'value'", $widget );
		$this->assertStringContainsString( "'label'", $widget );
	}

	/**
	 * The Stats widget consumes the shared section-heading component.
	 */
	public function test_stats_consumes_shared_heading(): void {
		$widget = $this->source( 'src/Elementor/Widget/Stats.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( "'title_accent'", $widget );
		$this->assertStringContainsString( 'vew-section-heading', $widget );
	}

	/**
	 * The Stats widget is registered in the catalog and plugin.
	 */
	public function test_stats_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'Stats::class', $plugin );
		$this->assertStringContainsString( 'vew-stats', $plugin );
		$this->assertStringContainsString( 'Stats::class', $catalog );
		$this->assertStringContainsString( "'vew-stats'", $catalog );
	}
}
