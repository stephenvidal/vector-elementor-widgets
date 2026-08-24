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
		return __( 'Portfolio', 'vector-elementor-widgets' );
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
				'eyebrow' => 'string',
				'title'   => 'string',
				'title_accent' => 'string',
				'title_after'  => 'string',
				'intro'   => 'string',
				'items'   => 'array',
			)
		);

		$eyebrow = $safe['eyebrow'] ?? '';
		$title   = $safe['title'] ?? '';
		$intro   = $safe['intro'] ?? '';
		$items   = $safe['items'] ?? array();

		?>
		<section class="vew-portfolio" aria-label="portfolio">
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
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-portfolio' );
	}
}
