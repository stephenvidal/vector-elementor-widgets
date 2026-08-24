<?php
/**
 * Stats widget.
 *
 * Port of the vidal/blackstone stats band: a dark brand-dark surface with a
 * centered heading block and a grid of value + label statistics. Per the
 * control-mapping contract `data-stats` → REPEATER of value/label pairs.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\StatsContentControls;
use Vector\ElementorWidgets\Elementor\Control\StatsStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stats widget.
 */
final class Stats extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-stats';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Stats', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-number-field';
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
		return array( 'stats', 'statistics', 'numbers', 'metrics', 'count' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new StatsContentControls() )->register( $this );
		( new StatsStyleControls() )->register( $this );
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
				'stats'        => 'array',
			)
		);

		$stats = $safe['stats'] ?? array();
		?>
		<section class="vew-stats" aria-label="statistics" data-vew-stats>
			<div class="vew-stats__inner">
			<?php echo SectionHeading::render( $safe, array( 'block_class' => 'vew-stats' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values. ?>

			<?php if ( is_array( $stats ) && count( $stats ) > 0 ) : ?>
				<div class="vew-stats__grid">
					<?php foreach ( $stats as $stat ) : ?>
						<?php
						$s_value = isset( $stat['value'] ) ? sanitize_text_field( (string) $stat['value'] ) : '';
						$s_label = isset( $stat['label'] ) ? sanitize_text_field( (string) $stat['label'] ) : '';
						if ( '' === $s_value ) {
							continue;
						}
						// Numeric target for the count-up animation (leading number).
						preg_match( '/-?\d+(?:\.\d+)?/', $s_value, $m );
						$s_target = isset( $m[0] ) ? $m[0] : '';
						?>
						<div class="vew-stats__stat">
							<strong class="vew-stats__value"<?php echo '' !== $s_target ? ' data-count="' . esc_attr( $s_target ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- numeric value, esc_attr'd. ?>><?php echo esc_html( $s_value ); ?></strong>
							<?php if ( '' !== $s_label ) : ?>
								<span class="vew-stats__label"><?php echo esc_html( $s_label ); ?></span>
							<?php endif; ?>
						</div>
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
		return array( 'vew-section-heading', 'vew-stats' );
	}

	/**
	 * Declare the script dependency (count-up animation).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-stats' );
	}
}
