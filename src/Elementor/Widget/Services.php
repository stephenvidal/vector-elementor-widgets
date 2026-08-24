<?php
/**
 * Services widget.
 *
 * Port of the vidal-studio services component: a heading block + a variant
 * toggle (Design / Build / Launch) with a per-variant grid of service cards.
 * Per the control-mapping contract, the N-state toggle = a SELECT `variant`
 * control plus one REPEATER per variant, each gated by `condition`. The
 * sliding indicator is CSS/JS-driven, reading `data-variant` from the wrapper.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\ServicesContentControls;
use Vector\ElementorWidgets\Elementor\Control\ServicesStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Services widget.
 */
final class Services extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-services';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Services', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-toggle';
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
		return array( 'services', 'toggle', 'variant', 'tabs', 'switch' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new ServicesContentControls() )->register( $this );
		( new ServicesStyleControls() )->register( $this );
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
				'variant' => 'string',
				'design'  => 'array',
				'build'   => 'array',
				'launch'  => 'array',
			)
		);

		$eyebrow = $safe['eyebrow'] ?? '';
		$title   = $safe['title'] ?? '';
		$intro   = $safe['intro'] ?? '';
		$variant = $safe['variant'] ?? 'design';
		$groups  = array(
			'design' => $safe['design'] ?? array(),
			'build'  => $safe['build'] ?? array(),
			'launch' => $safe['launch'] ?? array(),
		);

		$instance_id = wp_unique_id( 'vew-services-' );

		?>
		<section class="vew-services" aria-label="services" data-vew-services data-instance="<?php echo esc_attr( $instance_id ); ?>">
			<div class="vew-services__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-services' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<div class="vew-services__switch" role="tablist" aria-label="<?php echo esc_attr__( 'Service focus', 'vector-elementor-widgets' ); ?>">
				<span class="vew-services__indicator" aria-hidden="true"></span>
				<?php foreach ( array_keys( $groups ) as $i => $key ) : ?>
					<button
						type="button"
						role="tab"
						id="<?php echo esc_attr( $instance_id ); ?>-tab-<?php echo esc_attr( $key ); ?>"
						class="vew-services__tab"
						data-variant="<?php echo esc_attr( $key ); ?>"
						aria-controls="<?php echo esc_attr( $instance_id ); ?>-panel-<?php echo esc_attr( $key ); ?>"
						aria-selected="<?php echo $variant === $key ? 'true' : 'false'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- boolean-driven. ?>"
						tabindex="<?php echo $variant === $key ? '0' : '-1'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- boolean-driven. ?>"
					><?php echo esc_html( ucfirst( $key ) ); ?></button>
				<?php endforeach; ?>
			</div>

			<?php foreach ( $groups as $key => $cards ) : ?>
				<div
					class="vew-services__grid"
					role="tabpanel"
					id="<?php echo esc_attr( $instance_id ); ?>-panel-<?php echo esc_attr( $key ); ?>"
					aria-labelledby="<?php echo esc_attr( $instance_id ); ?>-tab-<?php echo esc_attr( $key ); ?>"
					data-variant-panel="<?php echo esc_attr( $key ); ?>"
					<?php echo $variant === $key ? '' : ' hidden'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- boolean-driven. ?>
				>
					<?php foreach ( $cards as $card ) : ?>
						<?php
						$c_icon  = isset( $card['icon'] ) ? sanitize_text_field( (string) $card['icon'] ) : '';
						$c_title = isset( $card['title'] ) ? sanitize_text_field( (string) $card['title'] ) : '';
						$c_text  = isset( $card['text'] ) ? sanitize_text_field( (string) $card['text'] ) : '';
						if ( '' === $c_title ) {
							continue;
						}
						?>
						<article class="vew-services__card">
							<?php if ( '' !== $c_icon ) : ?>
								<div class="vew-services__icon" aria-hidden="true"><?php echo esc_html( $c_icon ); ?></div>
							<?php endif; ?>
							<h3 class="vew-services__card-title"><?php echo esc_html( $c_title ); ?></h3>
							<?php if ( '' !== $c_text ) : ?>
								<p class="vew-services__card-text"><?php echo esc_html( $c_text ); ?></p>
							<?php endif; ?>
						</article>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
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
		return array( 'vew-section-heading', 'vew-services' );
	}

	/**
	 * Declare the script dependency (variant toggle).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-services' );
	}
}
