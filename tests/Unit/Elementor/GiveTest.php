<?php
/**
 * Give widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class GiveTest extends TestCase {

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
	 * The Give widget owns a full-width surface and a safe internal shell.
	 */
	public function test_give_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/Give.php' );
		$css    = $this->source( 'widgets/Give/vew-give.css' );

		$this->assertStringContainsString( 'class="vew-give__inner"', $widget );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The Give widget renders quote, verse reference, and CTA.
	 */
	public function test_give_renders_quote_verse_cta(): void {
		$widget = $this->source( 'src/Elementor/Widget/Give.php' );

		$this->assertStringContainsString( 'vew-give__quote', $widget );
		$this->assertStringContainsString( 'vew-give__verse', $widget );
		$this->assertStringContainsString( 'vew-give__cta', $widget );
		$this->assertStringContainsString( "'verse_ref'", $widget );
		$this->assertStringContainsString( "'cta_url'", $widget );
	}

	/**
	 * The Give widget consumes the shared section-heading component.
	 */
	public function test_give_consumes_shared_heading(): void {
		$widget = $this->source( 'src/Elementor/Widget/Give.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( "'title_accent'", $widget );
	}

	/**
	 * The Give widget is registered in the catalog and plugin.
	 */
	public function test_give_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'Give::class', $plugin );
		$this->assertStringContainsString( 'vew-give', $plugin );
		$this->assertStringContainsString( 'Give::class', $catalog );
		$this->assertStringContainsString( "'vew-give'", $catalog );
	}
}
