<?php
/**
 * PostGallery widget.
 *
 * A post-sourced gallery: queries posts via the shared QueryControls, collects
 * each post's featured image, and renders them in a chosen display style
 * (mosaic / grid / carousel) with a lightbox. Each item links to its post.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\PostGalleryContentControls;
use Vector\ElementorWidgets\Elementor\Control\PostGalleryStyleControls;
use Vector\ElementorWidgets\Elementor\Component\QueryControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PostGallery widget.
 */
final class PostGallery extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-post-gallery';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Post Gallery', 'vector-elementor-widgets' );
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
		return array( 'gallery', 'post', 'images', 'photos', 'mosaic', 'grid', 'carousel' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new PostGalleryContentControls() )->register( $this );
		( new PostGalleryStyleControls() )->register( $this );
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
			array_merge(
				SectionHeading::setting_types(),
				QueryControls::setting_types(),
				array(
					'gallery_style' => 'string',
					'columns'       => 'string',
					'show_caption'  => 'string',
				)
			)
		);

		$style = isset( $safe['gallery_style'] ) ? sanitize_key( (string) $safe['gallery_style'] ) : 'mosaic';
		$style = in_array( $style, array( 'mosaic', 'grid', 'carousel' ), true ) ? $style : 'mosaic';

		$columns = isset( $safe['columns'] ) ? sanitize_key( (string) $safe['columns'] ) : '3';
		$columns = in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3';

		$show_caption = ( isset( $safe['show_caption'] ) && 'yes' === $safe['show_caption'] );

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-post-gallery' )
		);

		$query = new \WP_Query( QueryControls::query_args( $safe, true ) );

		// Collect featured-image items (only posts with a thumbnail).
		$items = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$thumb = has_post_thumbnail() ? (string) get_the_post_thumbnail_url( get_the_ID(), 'large' ) : '';
				if ( '' === $thumb ) {
					continue;
				}
				$items[] = array(
					'permalink' => (string) get_permalink(),
					'title'     => (string) get_the_title(),
					'thumb'     => $thumb,
				);
			}
			wp_reset_postdata();
		}

		$modifier = 'grid' === $style ? ' vew-post-gallery--grid' : ( 'carousel' === $style ? ' vew-post-gallery--carousel' : ' vew-post-gallery--mosaic' );
		$last     = count( $items ) - 1;
		?>
		<section class="vew-post-gallery<?php echo $modifier; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $modifier is a whitelist-safe static string. ?> vew-post-gallery--<?php echo esc_attr( $columns ); ?>" aria-label="<?php echo esc_attr__( 'Post gallery', 'vector-elementor-widgets' ); ?>" data-i18n-viewer="<?php echo esc_attr__( 'Post image viewer', 'vector-elementor-widgets' ); ?>" data-i18n-close="<?php echo esc_attr__( 'Close image viewer', 'vector-elementor-widgets' ); ?>" data-i18n-prev="<?php echo esc_attr__( 'Previous image', 'vector-elementor-widgets' ); ?>" data-i18n-next="<?php echo esc_attr__( 'Next image', 'vector-elementor-widgets' ); ?>" data-i18n-view-post="<?php echo esc_attr__( 'View post', 'vector-elementor-widgets' ); ?>" data-i18n-go-to-image="<?php echo esc_attr__( 'Go to image', 'vector-elementor-widgets' ); ?>">
			<div class="vew-post-gallery__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( ! empty( $items ) ) : ?>
					<div class="vew-post-gallery__grid<?php echo 'carousel' === $style ? ' vew-post-gallery__carousel' : ''; ?>">
						<?php foreach ( $items as $index => $item ) : ?>
							<figure class="vew-post-gallery__item<?php echo ( 'mosaic' === $style && 0 === $index ) ? ' vew-post-gallery__item--feature' : ''; ?><?php echo ( 'mosaic' === $style && $index === $last && $last > 0 ) ? ' vew-post-gallery__item--wide' : ''; ?>">
								<a class="vew-post-gallery__link" href="<?php echo esc_url( $item['permalink'] ); ?>" data-gallery-index="<?php echo esc_attr( (string) $index ); ?>">
									<img src="<?php echo esc_url( $item['thumb'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy">
									<?php if ( $show_caption ) : ?>
										<figcaption class="vew-post-gallery__caption"><?php echo esc_html( $item['title'] ); ?></figcaption>
									<?php endif; ?>
								</a>
							</figure>
						<?php endforeach; ?>
					</div>
					<?php if ( 'carousel' === $style ) : ?>
						<div class="vew-post-gallery__carousel-nav" data-vew-carousel-nav>
							<button type="button" class="vew-post-gallery__nav vew-post-gallery__nav--prev" data-direction="-1" aria-label="<?php echo esc_attr__( 'Previous image', 'vector-elementor-widgets' ); ?>">←</button>
							<div class="vew-post-gallery__dots" data-vew-carousel-dots role="tablist" aria-label="<?php echo esc_attr__( 'Choose image', 'vector-elementor-widgets' ); ?>"></div>
							<button type="button" class="vew-post-gallery__nav vew-post-gallery__nav--next" data-direction="1" aria-label="<?php echo esc_attr__( 'Next image', 'vector-elementor-widgets' ); ?>">→</button>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<p class="vew-post-gallery__empty"><?php echo esc_html__( 'No posts with featured images found.', 'vector-elementor-widgets' ); ?></p>
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
		return array( 'vew-section-heading', 'vew-post-gallery' );
	}

	/**
	 * Declare the script dependency (lightbox + carousel).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-post-gallery' );
	}
}
