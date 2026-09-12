<?php
/**
 * Countdown widget.
 *
 * A live countdown timer to a configured target datetime. Renders an optional
 * section heading plus Days / Hours / Minutes / Seconds cells. The frontend
 * script (vew-countdown.js) ticks each second from the `data-end` attribute
 * and swaps in the expiry text when the target passes. All values are
 * sanitized; the target is emitted as an ISO-8601 datetime string only.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\CountdownContentControls;
use Vector\ElementorWidgets\Elementor\Control\CountdownStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Countdown widget.
 */
final class Countdown extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-countdown';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Countdown', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-countdown';
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
		return array( 'countdown', 'timer', 'clock', 'days', 'hours', 'deadline' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new CountdownContentControls() )->register( $this );
		( new CountdownStyleControls() )->register( $this );
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
				array(
					'end'          => 'string',
					'expired_text' => 'string',
				)
			)
		);

		$end          = isset( $safe['end'] ) ? trim( (string) $safe['end'] ) : '';
		$expired_text = isset( $safe['expired_text'] ) ? (string) $safe['expired_text'] : '';
		if ( '' === $end ) {
			return;
		}

		// Resolve the stored wall-clock value in the SITE timezone and re-emit it
		// as ISO-8601 with an explicit offset. Emitting the raw 'Y-m-d H:i' string
		// is a silent correctness bug: JavaScript's `new Date()` parses a
		// zone-less string in the VISITOR's timezone, so a 11:00 ET target reads
		// as 11:00 local for someone in Chicago (+1h) or Los Angeles (+3h).
		$zone = wp_timezone();
		$dt   = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i', $end, $zone );
		if ( false === $dt ) {
			try {
				$dt = new \DateTimeImmutable( $end, $zone );
			} catch ( \Exception $e ) {
				return;
			}
		}
		$end = $dt->format( 'c' );

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-countdown' )
		);

		$labels = array(
			'days'    => __( 'Days', 'vector-elementor-widgets' ),
			'hours'   => __( 'Hours', 'vector-elementor-widgets' ),
			'minutes' => __( 'Minutes', 'vector-elementor-widgets' ),
			'seconds' => __( 'Seconds', 'vector-elementor-widgets' ),
		);
		?>
		<section class="vew-countdown" aria-label="<?php echo esc_attr__( 'Countdown', 'vector-elementor-widgets' ); ?>">
			<div class="vew-countdown__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<div class="vew-countdown__clock" data-end="<?php echo esc_attr( $end ); ?>" data-expired="<?php echo esc_attr( $expired_text ); ?>">
					<?php foreach ( $labels as $key => $label ) : ?>
						<div class="vew-countdown__unit" data-unit="<?php echo esc_attr( $key ); ?>">
							<strong class="vew-countdown__number">0</strong>
							<span class="vew-countdown__label"><?php echo esc_html( $label ); ?></span>
						</div>
					<?php endforeach; ?>
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
		return array( 'vew-section-heading', 'vew-countdown' );
	}

	/**
	 * Declare the script dependency (timer tick).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-countdown' );
	}
}
