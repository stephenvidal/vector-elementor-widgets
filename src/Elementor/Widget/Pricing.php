<?php
/**
 * Pricing widget.
 *
 * Port of the vidal-studio pricing component: a heading block + a grid of
 * pricing cards (REPEATER). Each card has a plan name, price, unit, a
 * featured flag, a feature list, and a CTA. Per the control-mapping contract
 * `data-copy` → TEXT, `data-pricing` → REPEATER.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\PricingContentControls;
use Vector\ElementorWidgets\Elementor\Control\PricingStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pricing widget.
 */
final class Pricing extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-pricing';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Pricing', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-price-table';
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
		return array( 'pricing', 'price', 'plans', 'packages', 'cost' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new PricingContentControls() )->register( $this );
		( new PricingStyleControls() )->register( $this );
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
				'note'    => 'string',
				'plans'   => 'array',
			)
		);

		$eyebrow = $safe['eyebrow'] ?? '';
		$title   = $safe['title'] ?? '';
		$note    = $safe['note'] ?? '';
		$plans   = $safe['plans'] ?? array();

		?>
		<section class="vew-pricing" aria-label="pricing">
			<div class="vew-pricing__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-pricing' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<?php if ( is_array( $plans ) && count( $plans ) > 0 ) : ?>
				<div class="vew-pricing__cards">
					<?php foreach ( $plans as $plan ) : ?>
						<?php
						$p_plan     = isset( $plan['plan'] ) ? sanitize_text_field( (string) $plan['plan'] ) : '';
						$p_price    = isset( $plan['price'] ) ? sanitize_text_field( (string) $plan['price'] ) : '';
						$p_unit     = isset( $plan['unit'] ) ? sanitize_text_field( (string) $plan['unit'] ) : '';
						$p_featured = ! empty( $plan['featured'] );
						$p_cta      = isset( $plan['cta'] ) ? sanitize_text_field( (string) $plan['cta'] ) : '';
						$p_cta_url  = isset( $plan['cta_url']['url'] ) ? esc_url_raw( (string) $plan['cta_url']['url'] ) : '';
						$p_features = isset( $plan['features'] ) && is_array( $plan['features'] ) ? $plan['features'] : array();
						if ( '' === $p_plan ) {
							continue;
						}
						?>
						<article class="vew-pricing__card<?php echo $p_featured ? ' vew-pricing__card--featured' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- boolean-driven class. ?>">
							<?php if ( $p_featured ) : ?>
								<span class="vew-pricing__badge"><?php echo esc_html__( 'Most popular', 'vector-elementor-widgets' ); ?></span>
							<?php endif; ?>
							<div class="vew-pricing__plan"><?php echo esc_html( $p_plan ); ?></div>
							<?php if ( '' !== $p_price ) : ?>
								<div class="vew-pricing__price"><?php echo esc_html( $p_price ); ?><small><?php echo esc_html__( '/ project', 'vector-elementor-widgets' ); ?></small></div>
							<?php endif; ?>
							<?php if ( '' !== $p_unit ) : ?>
								<div class="vew-pricing__unit"><?php echo esc_html( $p_unit ); ?></div>
							<?php endif; ?>
							<?php if ( count( $p_features ) > 0 ) : ?>
								<ul class="vew-pricing__features">
									<?php foreach ( $p_features as $feature ) : ?>
										<?php
										$f = is_array( $feature ) ? ( $feature['text'] ?? '' ) : $feature;
										$f = sanitize_text_field( (string) $f );
										if ( '' === $f ) {
											continue;
										}
										?>
										<li><?php echo esc_html( $f ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( '' !== $p_cta ) : ?>
								<?php if ( '' !== $p_cta_url ) : ?>
									<a class="vew-pricing__cta" href="<?php echo esc_url( $p_cta_url ); ?>"><?php echo esc_html( $p_cta ); ?> <span aria-hidden="true">→</span></a>
								<?php else : ?>
									<span class="vew-pricing__cta"><?php echo esc_html( $p_cta ); ?> <span aria-hidden="true">→</span></span>
								<?php endif; ?>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( '' !== $note ) : ?>
				<p class="vew-pricing__note"><?php echo esc_html( $note ); ?></p>
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
		return array( 'vew-section-heading', 'vew-pricing' );
	}
}
