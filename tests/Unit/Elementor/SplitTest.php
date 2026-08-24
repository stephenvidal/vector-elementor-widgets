<?php
/**
 * Split widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class SplitTest extends TestCase {

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
	 * The Split widget owns a full-width surface and a safe internal shell.
	 */
	public function test_split_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/Split.php' );
		$css    = $this->source( 'widgets/Split/vew-split.css' );

		$this->assertStringContainsString( 'class="vew-split__inner"', $widget );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The Split widget renders image, paragraphs, quote, and signature.
	 */
	public function test_split_renders_image_copy_quote_signature(): void {
		$widget = $this->source( 'src/Elementor/Widget/Split.php' );

		$this->assertStringContainsString( 'vew-split__image', $widget );
		$this->assertStringContainsString( 'vew-split__paragraph', $widget );
		$this->assertStringContainsString( 'vew-split__quote', $widget );
		$this->assertStringContainsString( 'vew-split__signature', $widget );
		$this->assertStringContainsString( "'image'", $widget );
		$this->assertStringContainsString( "'paragraphs'", $widget );
	}

	/**
	 * The Split widget consumes the shared section-heading component.
	 */
	public function test_split_consumes_shared_heading(): void {
		$widget = $this->source( 'src/Elementor/Widget/Split.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( "'title_accent'", $widget );
	}

	/**
	 * The Split widget is registered in the catalog and plugin.
	 */
	public function test_split_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'Split::class', $plugin );
		$this->assertStringContainsString( 'vew-split', $plugin );
		$this->assertStringContainsString( 'Split::class', $catalog );
		$this->assertStringContainsString( "'vew-split'", $catalog );
	}
}
