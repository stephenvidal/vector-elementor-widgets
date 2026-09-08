<?php
/**
 * PostGallery widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class PostGalleryTest extends TestCase {

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
	 * The PostGallery widget owns a full-width surface and a safe internal shell.
	 */
	public function test_post_gallery_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/PostGallery.php' );
		$css    = $this->source( 'widgets/PostGallery/vew-post-gallery.css' );

		$this->assertStringContainsString( 'class="vew-post-gallery__inner"', $widget );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The PostGallery widget consumes the shared query + heading components.
	 */
	public function test_post_gallery_consumes_shared_components(): void {
		$widget = $this->source( 'src/Elementor/Widget/PostGallery.php' );

		$this->assertStringContainsString( 'QueryControls::query_args', $widget );
		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( 'SectionHeading::setting_types()', $widget );
	}

	/**
	 * The PostGallery widget renders a post-sourced gallery with all styles.
	 */
	public function test_post_gallery_renders_styles_and_lightbox(): void {
		$widget = $this->source( 'src/Elementor/Widget/PostGallery.php' );
		$js     = $this->source( 'widgets/PostGallery/vew-post-gallery.js' );

		foreach ( array( 'mosaic', 'grid', 'carousel' ) as $style ) {
			$this->assertStringContainsString( "'{$style}'", $widget );
		}
		$this->assertStringContainsString( 'vew-post-gallery__item--feature', $widget );
		$this->assertStringContainsString( 'vew-post-gallery__item--wide', $widget );
		$this->assertStringContainsString( 'data-gallery-index', $widget );
		$this->assertStringContainsString( 'vew-post-gallery__lightbox', $js );
		$this->assertStringContainsString( 'document.createElement', $js );
		$this->assertStringContainsString( 'get_script_depends', $widget );
	}

	/**
	 * The PostGallery widget is registered in the catalog and plugin.
	 */
	public function test_post_gallery_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'PostGallery::class', $plugin );
		$this->assertStringContainsString( 'vew-post-gallery', $plugin );
		$this->assertStringContainsString( 'PostGallery::class', $catalog );
		$this->assertStringContainsString( "'vew-post-gallery'", $catalog );
	}

	/**
	 * The PostGallery widget exposes the display-style + caption controls.
	 */
	public function test_post_gallery_has_style_controls(): void {
		$controls = $this->source( 'src/Elementor/Control/PostGalleryContentControls.php' );

		foreach ( array( 'gallery_style', 'columns', 'show_caption' ) as $id ) {
			$this->assertStringContainsString( "'{$id}'", $controls );
		}
		$this->assertStringContainsString( 'QueryControls::register_content_controls', $controls );
	}

	/**
	 * The PostGallery widget escapes all queried output.
	 */
	public function test_post_gallery_escapes_output(): void {
		$widget = $this->source( 'src/Elementor/Widget/PostGallery.php' );

		$this->assertStringContainsString( 'esc_url( $item[\'permalink\'] )', $widget );
		$this->assertStringContainsString( 'esc_url( $item[\'thumb\'] )', $widget );
		$this->assertStringContainsString( 'esc_attr( $item[\'title\'] )', $widget );
	}
}
