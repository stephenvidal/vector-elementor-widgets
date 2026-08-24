<?php
/**
 * Gallery widget.
 *
 * Port of the vidal/ember-oak/hearth-crumb gallery mosaic: a heading block +
 * a REPEATER of images rendered as a mosaic grid. Per the control-mapping
 * contract `data-copy` → TEXT, `data-gallery` → REPEATER of MEDIA.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\GalleryContentControls;
use Vector\ElementorWidgets\Elementor\Control\GalleryStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gallery widget.
 */
final class Gallery extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-gallery';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Gallery', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
	}

	/**
	 * Widget category.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( 'vector-widgets' );
	}

	/**
	 * Widget keywords.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'gallery', 'images', 'photos', 'mosaic', 'grid' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new GalleryContentControls() )->register( $this );
		( new GalleryStyleControls() )->register( $this );
	}

	/**
	 * Render the widget.
	 *
	 * The ONLY render path. No `_content_template()` override.
	 *
	 * @return void
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$safe     = $this->sanitize_settings(
			$settings,
			array(
				'eyebrow'      => 'string',
				'title'        => 'string',
				'title_accent' => 'string',
				'title_after'  => 'string',
				'images'       => 'array',
			)
		);

		$images       = $safe['images'] ?? array();
		$style        = isset( $settings['gallery_style'] ) ? sanitize_key( (string) $settings['gallery_style'] ) : 'mosaic';
		$style        = in_array( $style, array( 'mosaic', 'editorial', 'grid', 'carousel' ), true ) ? $style : 'mosaic';
		$modifier     = 'editorial' === $style ? ' vew-gallery--editorial' : ( 'carousel' === $style ? ' vew-gallery--carousel' : ( 'grid' === $style ? ' vew-gallery--grid' : ' vew-gallery--mosaic' ) );
		$is_editorial = 'editorial' === $style;
		$is_carousel  = 'carousel' === $style;
		$is_mosaic    = 'mosaic' === $style;
		$last_index   = is_array( $images ) ? count( $images ) - 1 : -1;
		$item_seq     = 0; // Sequential index of RENDERED editorial items (skips empty-URL entries).
		?>
		<section class="vew-gallery<?php echo $modifier; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $modifier is a whitelist-safe static string. ?>" aria-label="gallery">
			<div class="vew-gallery__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-gallery' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<?php if ( is_array( $images ) && count( $images ) > 0 ) : ?>
				<div class="vew-gallery__grid<?php echo $is_carousel ? ' vew-gallery__carousel' : ''; ?>">
					<?php foreach ( $images as $index => $image ) : ?>
						<?php
						$img   = is_array( $image ) ? $image : array();
						$url   = isset( $img['url'] ) ? ( is_array( $img['url'] ) ? ( $img['url']['url'] ?? '' ) : (string) $img['url'] ) : '';
						$alt   = isset( $img['alt'] ) ? sanitize_text_field( (string) $img['alt'] ) : '';
						$label = isset( $img['label'] ) ? sanitize_text_field( (string) $img['label'] ) : '';
						if ( '' === $url ) {
							continue;
						}
						$img_alt = '' !== $alt ? esc_attr( $alt ) : esc_attr( $label );
						if ( $is_editorial ) :
							?>
							<button type="button" class="vew-gallery__item" data-gallery-index="<?php echo esc_attr( (string) $item_seq ); ?>" aria-label="<?php echo esc_attr( __( 'Open image: ', 'vector-elementor-widgets' ) . $img_alt ); ?>">
								<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" <?php echo 0 === $item_seq ? '' : 'loading="lazy"'; ?>>
								<?php if ( '' !== $label ) : ?>
									<span class="vew-gallery__label"><?php echo esc_html( $label ); ?></span>
								<?php endif; ?>
								<span class="vew-gallery__expand" aria-hidden="true">↗</span>
							</button>
							<?php
							++$item_seq;
						else :
							?>
							<figure class="vew-gallery__item<?php echo ( $is_mosaic && 0 === $index ) ? ' vew-gallery__item--feature' : ''; ?><?php echo ( $is_mosaic && $index === $last_index && $last_index > 0 ) ? ' vew-gallery__item--wide' : ''; ?>">
								<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" loading="lazy">
								<?php if ( $is_carousel && '' !== $label ) : ?>
									<figcaption class="vew-gallery__caption"><?php echo esc_html( $label ); ?></figcaption>
								<?php endif; ?>
							</figure>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
				<?php if ( $is_carousel ) : ?>
					<div class="vew-gallery__carousel-nav" data-vew-carousel-nav>
						<button type="button" class="vew-gallery__nav vew-gallery__nav--prev" data-direction="-1" aria-label="<?php echo esc_attr__( 'Previous image', 'vector-elementor-widgets' ); ?>">←</button>
						<div class="vew-gallery__dots" data-vew-carousel-dots role="tablist" aria-label="<?php echo esc_attr__( 'Choose image', 'vector-elementor-widgets' ); ?>"></div>
						<button type="button" class="vew-gallery__nav vew-gallery__nav--next" data-direction="1" aria-label="<?php echo esc_attr__( 'Next image', 'vector-elementor-widgets' ); ?>">→</button>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-gallery' );
	}

	/**
	 * Declare the script dependency (lightbox for the editorial style).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-gallery' );
	}
}
