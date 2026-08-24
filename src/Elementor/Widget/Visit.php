<?php
/**
 * Visit widget.
 *
 * Port of the vidal/ember-oak/hearth-crumb "visit" pattern: a two-column
 * split with copy (eyebrow/title/intro/details/CTA) on one side and a map
 * image with a pin on the other. Per the control-mapping contract
 * `data-copy` → TEXT, `data-image` → MEDIA.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\VisitContentControls;
use Vector\ElementorWidgets\Elementor\Control\VisitStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visit widget.
 */
final class Visit extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-visit';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Visit', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-map-pin';
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
		return array( 'visit', 'location', 'hours', 'address', 'map' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new VisitContentControls() )->register( $this );
		( new VisitStyleControls() )->register( $this );
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
				'details'      => 'array',
				'cta_text'     => 'string',
				'cta_url'      => 'array',
				'image'        => 'array',
			)
		);

		$intro = $safe['intro'] ?? '';
		$details = $safe['details'] ?? array();
		$cta_text = $safe['cta_text'] ?? '';
		$cta_url = is_array( $safe['cta_url'] ?? null ) ? ( $safe['cta_url']['url'] ?? '' ) : '';
		$image = $safe['image'] ?? array();
		$image_url = is_array( $image ) && isset( $image['url'] ) ? ( is_array( $image['url'] ) ? ( $image['url']['url'] ?? '' ) : (string) $image['url'] ) : '';
		$image_alt = is_array( $image ) && isset( $image['alt'] ) ? (string) $image['alt'] : '';
		?>
		<section class="vew-visit" aria-label="visit">
			<div class="vew-visit__inner">
				<div class="vew-visit__grid">
					<div class="vew-visit__copy">
					<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-visit' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

					<?php if ( '' !== $intro ) : ?>
						<p class="vew-visit__intro"><?php echo esc_html( $intro ); ?></p>
					<?php endif; ?>

					<?php if ( is_array( $details ) && count( $details ) > 0 ) : ?>
						<div class="vew-visit__details">
							<?php foreach ( $details as $detail ) : ?>
								<?php
								$d_label = isset( $detail['label'] ) ? sanitize_text_field( (string) $detail['label'] ) : '';
								$d_value = isset( $detail['value'] ) ? sanitize_text_field( (string) $detail['value'] ) : '';
								if ( '' === $d_label && '' === $d_value ) {
									continue;
								}
								?>
								<div class="vew-visit__detail">
									<?php if ( '' !== $d_label ) : ?>
										<span class="vew-visit__detail-label"><?php echo esc_html( $d_label ); ?></span>
									<?php endif; ?>
									<span class="vew-visit__detail-value"><?php echo esc_html( $d_value ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( '' !== $cta_text && '' !== $cta_url ) : ?>
						<a class="vew-visit__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
					<?php endif; ?>
					</div>

					<?php if ( '' !== $image_url ) : ?>
						<div class="vew-visit__map">
							<img class="vew-visit__image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $image_alt ); ?>" loading="lazy">
							<span class="vew-visit__pin" aria-hidden="true">
								<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
							</span>
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
		return array( 'vew-section-heading', 'vew-visit' );
	}
}
