<?php
/**
 * Timeline widget.
 *
 * A vertical timeline of milestone events. Renders an optional section heading
 * plus a line of events (year/date, title, description, optional link) in
 * either an alternating (zig-zag) or left-column layout. All values are
 * sanitized before output.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Elementor\Control\TimelineContentControls;
use Vector\ElementorWidgets\Elementor\Control\TimelineStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timeline widget.
 */
final class Timeline extends BaseWidget {

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-timeline';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Timeline', 'vector-elementor-widgets' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon(): string {
		return 'eicon-time-line';
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
		return array( 'timeline', 'milestone', 'history', 'events', 'roadmap', 'vertical' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new TimelineContentControls() )->register( $this );
		( new TimelineStyleControls() )->register( $this );
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
					'orientation' => 'string',
					'events'      => 'array',
				)
			)
		);

		$orientation = isset( $safe['orientation'] ) ? sanitize_key( $safe['orientation'] ) : 'alternating';
		$orientation = in_array( $orientation, array( 'alternating', 'left' ), true ) ? $orientation : 'alternating';
		$events      = $safe['events'] ?? array();
		$orient_class = 'vew-timeline--' . $orientation;

		$heading = SectionHeading::render(
			$safe,
			array( 'block_class' => 'vew-timeline' )
		);
		?>
		<section class="vew-timeline <?php echo esc_attr( $orient_class ); ?>" aria-label="<?php echo esc_attr__( 'Timeline', 'vector-elementor-widgets' ); ?>">
			<div class="vew-timeline__inner">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- component escapes all values.
				echo $heading;
				?>
				<?php if ( is_array( $events ) && count( $events ) > 0 ) : ?>
					<ol class="vew-timeline__list">
						<?php foreach ( $events as $event ) : ?>
							<?php
							$event = is_array( $event ) ? $event : array();
							$year  = isset( $event['year'] ) ? sanitize_text_field( (string) $event['year'] ) : '';
							$title = isset( $event['title'] ) ? sanitize_text_field( (string) $event['title'] ) : '';
							$text  = isset( $event['text'] ) ? sanitize_text_field( (string) $event['text'] ) : '';
							$link  = isset( $event['link']['url'] ) ? esc_url_raw( (string) $event['link']['url'] ) : '';
							if ( '' === $title ) {
								continue;
							}
							?>
							<li class="vew-timeline__item">
								<?php if ( '' !== $year ) : ?>
									<span class="vew-timeline__year"><?php echo esc_html( $year ); ?></span>
								<?php endif; ?>
								<div class="vew-timeline__dot" aria-hidden="true"></div>
								<div class="vew-timeline__card">
									<?php if ( '' !== $title ) : ?>
										<?php if ( '' !== $link ) : ?>
											<a class="vew-timeline__title" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a>
										<?php else : ?>
											<h3 class="vew-timeline__title"><?php echo esc_html( $title ); ?></h3>
										<?php endif; ?>
									<?php endif; ?>
									<?php if ( '' !== $text ) : ?>
										<p class="vew-timeline__text"><?php echo esc_html( $text ); ?></p>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ol>
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
		return array( 'vew-section-heading', 'vew-timeline' );
	}
}
