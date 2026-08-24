<?php
/**
 * Watch widget.
 *
 * Port of the vidal/rore/rorecclesia "watch" band: a dark brand-dark surface
 * with an intro + CTA links on one side and a media image with a play pin on
 * the other. Per the control-mapping contract `data-copy` → TEXT,
 * `data-image` → MEDIA.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\WatchContentControls;
use Vector\ElementorWidgets\Elementor\Control\WatchStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Watch widget.
 */
final class Watch extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-watch';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Watch', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-play';
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
		return array( 'watch', 'video', 'live', 'broadcast', 'media' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new WatchContentControls() )->register( $this );
		( new WatchStyleControls() )->register( $this );
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
				'intro'        => 'string',
				'image'        => 'array',
				'cta_text'     => 'string',
				'cta_url'      => 'array',
				'secondary_text' => 'string',
				'secondary_url'  => 'array',
			)
		);

		$image = $safe['image'] ?? array();
		$image_url = is_array( $image ) && isset( $image['url'] ) ? ( is_array( $image['url'] ) ? ( $image['url']['url'] ?? '' ) : (string) $image['url'] ) : '';
		$image_alt = is_array( $image ) && isset( $image['alt'] ) ? (string) $image['alt'] : '';
		$cta_text = $safe['cta_text'] ?? '';
		$cta_url = is_array( $safe['cta_url'] ?? null ) ? ( $safe['cta_url']['url'] ?? '' ) : '';
		$secondary_text = $safe['secondary_text'] ?? '';
		$secondary_url = is_array( $safe['secondary_url'] ?? null ) ? ( $safe['secondary_url']['url'] ?? '' ) : '';
		?>
		<section class="vew-watch" aria-label="watch">
			<div class="vew-watch__inner">
				<div class="vew-watch__grid">
					<div class="vew-watch__intro">
					<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-watch' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

					<?php if ( '' !== $cta_text && '' !== $cta_url ) : ?>
						<div class="vew-watch__links">
							<a class="vew-watch__cta" href="<?php echo esc_url( $cta_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $cta_text ); ?> <span aria-hidden="true">→</span></a>
							<?php if ( '' !== $secondary_text && '' !== $secondary_url ) : ?>
								<a class="vew-watch__secondary" href="<?php echo esc_url( $secondary_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $secondary_text ); ?> <span aria-hidden="true">↘</span></a>
							<?php endif; ?>
						</div>
					<?php endif; ?>
					</div>

					<?php if ( '' !== $image_url ) : ?>
						<div class="vew-watch__media">
							<img class="vew-watch__image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
							<span class="vew-watch__play" aria-hidden="true">▶</span>
						</div>
					<?php endif; ?>
				</div>
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
		return array( 'vew-section-heading', 'vew-watch' );
	}
}
