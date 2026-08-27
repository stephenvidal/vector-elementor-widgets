<?php
/**
 * Portfolio widget.
 *
 * Port of the vidal-studio portfolio component: a heading block + a grid of
 * portfolio cards (REPEATER). Each card has an image (MEDIA), a tag, a title,
 * a description, and a link. Per the control-mapping contract `data-copy` →
 * TEXT, `data-image` → MEDIA, `data-portfolio` → REPEATER.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\PortfolioContentControls;
use Vector\ElementorWidgets\Elementor\Control\PortfolioStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Portfolio widget.
 */
final class Portfolio extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-portfolio';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Portfolio (Manual)', 'vector-elementor-widgets' );
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
		return array( 'portfolio', 'work', 'projects', 'gallery', 'case' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new PortfolioContentControls() )->register( $this );
		( new PortfolioStyleControls() )->register( $this );
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
				'eyebrow'     => 'string',
				'title'       => 'string',
				'title_accent' => 'string',
				'title_after'  => 'string',
				'intro'       => 'string',
				'columns'     => 'string',
				'items'       => 'array',
			)
		);

		$eyebrow = $safe['eyebrow'] ?? '';
		$title   = $safe['title'] ?? '';
		$intro   = $safe['intro'] ?? '';
		$items   = $safe['items'] ?? array();

		$columns = isset( $safe['columns'] ) ? sanitize_key( (string) $safe['columns'] ) : '3';
		$columns = in_array( $columns, array( '2', '3', '4' ), true ) ? $columns : '3';

		?>
		<section class="vew-portfolio vew-portfolio--<?php echo esc_attr( $columns ); ?>" aria-label="portfolio">
			<div class="vew-portfolio__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-portfolio' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<?php if ( is_array( $items ) && count( $items ) > 0 ) : ?>
				<div class="vew-portfolio__grid">
					<?php foreach ( $items as $item ) : ?>
						<?php
						$p_title = isset( $item['title'] ) ? sanitize_text_field( (string) $item['title'] ) : '';
						$p_tag   = isset( $item['tag'] ) ? sanitize_text_field( (string) $item['tag'] ) : '';
						$p_text  = isset( $item['text'] ) ? sanitize_text_field( (string) $item['text'] ) : '';
						$p_url   = isset( $item['url'] ) ? ( is_array( $item['url'] ) ? ( $item['url']['url'] ?? '' ) : $item['url'] ) : '';
						$p_url   = esc_url_raw( (string) $p_url );
						$p_label = isset( $item['link_label'] ) ? sanitize_text_field( (string) $item['link_label'] ) : '';
						$p_label = '' !== $p_label ? $p_label : __( 'Visit live site', 'vector-elementor-widgets' );
						$p_external = ( is_array( $item['url'] ?? null ) && ! empty( $item['url']['is_external'] ) );
						$p_alt   = isset( $item['alt'] ) ? sanitize_text_field( (string) $item['alt'] ) : '';
						$p_img   = '';
						if ( isset( $item['image']['url'] ) && is_string( $item['image']['url'] ) ) {
							$p_img = esc_url( $item['image']['url'] );
						}
						// Auto preview source: resolve the linked page's capture when
						// the card URL points to a page on this site; fall back to
						// the manual image if the page has no _portfolio_preview.
						$preview_source = isset( $item['preview_source'] ) ? sanitize_key( (string) $item['preview_source'] ) : 'manual';
						if ( 'auto' === $preview_source && '' !== $p_url ) {
							$auto = $this->resolve_auto_preview( $p_url );
							if ( '' !== $auto ) {
								$p_img = $auto;
							}
						}
						if ( '' === $p_title ) {
							continue;
						}
						?>
						<article class="vew-portfolio__card">
							<?php if ( '' !== $p_img ) : ?>
								<div class="vew-portfolio__thumb">
									<img src="<?php echo esc_url( $p_img ); ?>" alt="<?php echo esc_attr( $p_alt ); ?>" loading="lazy">
								</div>
							<?php endif; ?>
							<div class="vew-portfolio__body">
								<?php if ( '' !== $p_tag ) : ?>
									<span class="vew-portfolio__tag"><?php echo esc_html( $p_tag ); ?></span>
								<?php endif; ?>
								<h3 class="vew-portfolio__card-title"><?php echo esc_html( $p_title ); ?></h3>
								<?php if ( '' !== $p_text ) : ?>
									<p class="vew-portfolio__card-text"><?php echo esc_html( $p_text ); ?></p>
								<?php endif; ?>
								<?php if ( '' !== $p_url ) : ?>
									<a class="vew-portfolio__visit" href="<?php echo esc_url( $p_url ); ?>"<?php echo $p_external ? ' target="_blank" rel="noopener"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- boolean-driven attribute. ?>><?php echo esc_html( $p_label ); ?> <span aria-hidden="true">→</span></a>
								<?php endif; ?>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			</div>
		</section>
		<?php
	}

	/**
	 * Resolve the auto preview image for a card whose URL points to a page
	 * on this site. Reads the linked page's `_portfolio_preview` meta (an
	 * attachment ID or a URL string) and returns the large-size image URL,
	 * or '' when the page is not local / has no preview.
	 *
	 * @param string $url Card link URL.
	 *
	 * @return string Resolved preview URL, or ''.
	 */
	private function resolve_auto_preview( string $url ): string {
		$post_id = url_to_postid( $url );
		if ( $post_id <= 0 ) {
			return '';
		}
		$preview_raw = get_post_meta( $post_id, '_portfolio_preview', true );
		if ( is_numeric( $preview_raw ) && (int) $preview_raw > 0 ) {
			$src = wp_get_attachment_image_src( (int) $preview_raw, 'large' );
			if ( is_array( $src ) && ! empty( $src[0] ) ) {
				return (string) $src[0];
			}
			return '';
		}
		if ( is_string( $preview_raw ) && '' !== $preview_raw ) {
			$candidate = esc_url_raw( $preview_raw );
			return '' !== $candidate ? $candidate : '';
		}
		return '';
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-portfolio' );
	}
}
