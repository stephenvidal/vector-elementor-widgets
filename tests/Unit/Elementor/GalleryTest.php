<?php
/**
 * Gallery widget contract.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class GalleryTest extends TestCase {

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
	 * The Gallery widget owns a full-width surface and a safe internal shell.
	 */
	public function test_gallery_owns_full_width_surface_and_internal_shell(): void {
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );
		$css    = $this->source( 'widgets/Gallery/vew-gallery.css' );

		$this->assertStringContainsString( 'class="vew-gallery__inner"', $widget );
		$this->assertStringContainsString( 'width: 100vw;', $css );
		$this->assertStringContainsString( 'width: min(100%, 1288px);', $css );
		$this->assertStringContainsString( 'padding-inline: 24px;', $css );
		$this->assertStringContainsString( 'padding-inline: 20px;', $css );
	}

	/**
	 * The Gallery widget renders a mosaic grid with a feature item.
	 */
	public function test_gallery_renders_mosaic_grid(): void {
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );
		$css    = $this->source( 'widgets/Gallery/vew-gallery.css' );

		$this->assertStringContainsString( 'vew-gallery__grid', $widget );
		$this->assertStringContainsString( 'vew-gallery__item--feature', $widget );
		$this->assertStringContainsString( 'grid-template-columns: repeat(3, 1fr);', $css );
		$this->assertStringContainsString( "'images'", $widget );
	}

	/**
	 * The Gallery widget consumes the shared section-heading component.
	 */
	public function test_gallery_consumes_shared_heading(): void {
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );

		$this->assertStringContainsString( 'SectionHeading::render', $widget );
		$this->assertStringContainsString( "'title_accent'", $widget );
	}

	/**
	 * The Gallery widget is registered in the catalog and plugin.
	 */
	public function test_gallery_is_registered(): void {
		$plugin  = $this->source( 'src/Plugin.php' );
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringContainsString( 'Gallery::class', $plugin );
		$this->assertStringContainsString( 'vew-gallery', $plugin );
		$this->assertStringContainsString( 'Gallery::class', $catalog );
		$this->assertStringContainsString( "'vew-gallery'", $catalog );
	}

	/**
	 * The Gallery widget exposes a gallery_style control with mosaic + editorial.
	 */
	public function test_gallery_has_style_control(): void {
		$controls = $this->source( 'src/Elementor/Control/GalleryStyleControls.php' );
		$css      = $this->source( 'widgets/Gallery/vew-gallery.css' );

		$this->assertStringContainsString( "'gallery_style'", $controls );
		$this->assertStringContainsString( "'mosaic'", $controls );
		$this->assertStringContainsString( "'editorial'", $controls );
		$this->assertStringContainsString( 'vew-gallery--editorial', $css );
	}

	/**
	 * The editorial style renders labelled buttons + a lightbox script.
	 */
	public function test_gallery_editorial_renders_lightbox(): void {
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );
		$js     = $this->source( 'widgets/Gallery/vew-gallery.js' );

		$this->assertStringContainsString( 'vew-gallery--editorial', $widget );
		$this->assertStringContainsString( 'vew-gallery__label', $widget );
		$this->assertStringContainsString( 'vew-gallery__expand', $widget );
		$this->assertStringContainsString( "'label'", $widget );
		$this->assertStringContainsString( 'vew-lightbox', $js );
		$this->assertStringContainsString( 'get_script_depends', $widget );
	}

	/**
	 * The Gallery widget offers all four layout styles.
	 */
	public function test_gallery_offers_all_styles(): void {
		$controls = $this->source( 'src/Elementor/Control/GalleryStyleControls.php' );
		$widget   = $this->source( 'src/Elementor/Widget/Gallery.php' );

		foreach ( array( 'mosaic', 'editorial', 'grid', 'carousel' ) as $style ) {
			$this->assertStringContainsString( "'{$style}'", $controls );
			$this->assertStringContainsString( "'{$style}'", $widget );
		}
	}

	/**
	 * The grid and carousel styles have dedicated CSS.
	 */
	public function test_gallery_grid_and_carousel_css(): void {
		$css    = $this->source( 'widgets/Gallery/vew-gallery.css' );
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );

		$this->assertStringContainsString( '.vew-gallery--grid .vew-gallery__grid', $css );
		$this->assertStringContainsString( '.vew-gallery--carousel .vew-gallery__grid', $css );
		$this->assertStringContainsString( 'vew-gallery__carousel', $widget );
		$this->assertStringContainsString( 'scroll-snap-type', $css );
	}

	/**
	 * The mosaic style spans its trailing image full-width (no dangling item).
	 */
	public function test_mosaic_wide_trailing_item(): void {
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );
		$css    = $this->source( 'widgets/Gallery/vew-gallery.css' );

		$this->assertStringContainsString( 'vew-gallery--mosaic', $widget );
		$this->assertStringContainsString( 'vew-gallery__item--wide', $widget );
		$this->assertStringContainsString( '.vew-gallery--mosaic .vew-gallery__item--wide', $css );
		$this->assertStringContainsString( 'grid-column: 1 / -1', $css );
	}

	/**
	 * The editorial lightbox index is SEQUENTIAL over rendered items, not the
	 * raw repeater index — so empty-URL entries (skipped via continue) can
	 * never desync the click-to-open mapping.
	 */
	public function test_editorial_uses_sequential_rendered_index(): void {
		$widget = $this->source( 'src/Elementor/Widget/Gallery.php' );

		// A sequential rendered-item counter exists and increments.
		$this->assertStringContainsString( '$item_seq', $widget );
		$this->assertStringContainsString( '++$item_seq', $widget );
		// The data attribute uses the sequential counter, not the repeater key.
		$this->assertStringContainsString( 'data-gallery-index="<?php echo esc_attr( (string) $item_seq ); ?>"', $widget );
	}

	/**
	 * The gallery lightbox builds its DOM via createElement/textContent, never
	 * innerHTML from editor-supplied data, and scopes items per-gallery so
	 * multiple editorial galleries cannot cross-map.
	 */
	public function test_lightbox_js_is_xss_safe_and_per_gallery(): void {
		$js = $this->source( 'widgets/Gallery/vew-gallery.js' );

		// No innerHTML built from data.
		$this->assertStringNotContainsString( 'innerHTML', $js );
		// DOM built via createElement + textContent/setAttribute.
		$this->assertStringContainsString( 'document.createElement', $js );
		$this->assertStringContainsString( 'caption.textContent = item.label', $js );
		$this->assertStringContainsString( 'img.src = item.src', $js );
		// Items are scoped per grid (stored on the element), not concatenated globally.
		$this->assertStringContainsString( '__vewGalleryItems', $js );
		$this->assertStringContainsString( 'collectGallery', $js );
	}
}
