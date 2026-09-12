<?php
/**
 * Broadcast widget.
 *
 * Shows the next recurring service time as a thumbnail with a live countdown,
 * and reveals a play button to the stream URL once the segment starts.
 *
 * Scheduling is resolved SERVER-SIDE in the site timezone because a zone-less
 * datetime is parsed by JavaScript in the visitor's timezone, which puts the
 * countdown hours out for remote viewers. The rendered page therefore carries:
 *
 *   - the resolved active segment (correct first paint, JS or no JS), and
 *   - `data-vew-broadcast` — every upcoming occurrence as JSON — so a page
 *     served from the cache can roll forward on its own without a reload.
 *
 * @package Vector\ElementorWidgets\Elementor\Widget
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Elementor\Widget;

use Vector\ElementorWidgets\Broadcast\Schedule;
use Vector\ElementorWidgets\Broadcast\Segment;
use Vector\ElementorWidgets\Elementor\Control\BroadcastContentControls;
use Vector\ElementorWidgets\Elementor\Control\BroadcastStyleControls;
use Vector\ElementorWidgets\Elementor\Component\SectionHeading;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Broadcast widget.
 */
final class Broadcast extends BaseWidget {

	/**
	 * Current render state, consumed by render_json_ld().
	 *
	 * @var string
	 */
	private string $current_state = Segment::STATE_UPCOMING;

	/**
	 * Whether the editor preview offset is in force for this render.
	 *
	 * @var bool
	 */
	private bool $preview_active = false;

	/**
	 * Widget slug — stable forever.
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'vew-broadcast';
	}

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return __( 'Broadcast', 'vector-elementor-widgets' );
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
		return array( 'broadcast', 'live', 'stream', 'countdown', 'service', 'watch', 'video' );
	}

	/**
	 * Register controls.
	 *
	 * @return void
	 */
	protected function register_controls(): void {
		( new BroadcastContentControls() )->register( $this );
		( new BroadcastStyleControls() )->register( $this );
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
					'segments'              => 'array',
					'timezone_note'         => 'string',
					'starting_soon_minutes' => 'int',
					'visitor_time'          => 'string',
					'calendar_links'        => 'string',
					'live_label'            => 'string',
					'upcoming_label'        => 'string',
					'starting_soon_label'   => 'string',
					'watch_label'           => 'string',
					'secondary_label'       => 'string',
					'calendar_label'        => 'string',
					'empty_text'            => 'string',
					'preview_offset'        => 'string',
				)
			)
		);

		$segments = $this->build_segments( $safe['segments'] ?? array() );

		$starting_soon_minutes = isset( $safe['starting_soon_minutes'] ) ? (int) $safe['starting_soon_minutes'] : 10;
		$schedule              = new Schedule( $segments, null, $starting_soon_minutes );

		$now      = $this->reference_time( $safe['preview_offset'] ?? 'off', $schedule );
		$active   = $schedule->active( $now );
		$upcoming = $schedule->upcoming_payload( $now );

		$heading = SectionHeading::render( $safe, array( 'block_class' => 'vew-broadcast' ) );

		$labels = array(
			'live'          => $this->label( $safe, 'live_label', __( 'Live now', 'vector-elementor-widgets' ) ),
			'upcoming'      => $this->label( $safe, 'upcoming_label', __( 'Begins in', 'vector-elementor-widgets' ) ),
			'starting_soon' => $this->label( $safe, 'starting_soon_label', __( 'Starting soon', 'vector-elementor-widgets' ) ),
			'watch'         => $this->label( $safe, 'watch_label', __( 'Watch now', 'vector-elementor-widgets' ) ),
			'secondary'     => $this->label( $safe, 'secondary_label', __( 'Also on Facebook', 'vector-elementor-widgets' ) ),
			'calendar'      => $this->label( $safe, 'calendar_label', __( 'Add to calendar', 'vector-elementor-widgets' ) ),
		);

		$show_calendar = 'yes' === ( $safe['calendar_links'] ?? 'yes' );
		$show_visitor  = 'yes' === ( $safe['visitor_time'] ?? 'yes' );
		$timezone_note = (string) ( $safe['timezone_note'] ?? '' );

		$segment = $active['segment'];
		$state   = $active['state'];

		// Consumed by render_json_ld() so the markup does not need to re-derive it.
		$this->current_state = $state;

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- every value below is escaped or pre-sanitised in Segment/this method.
		// The play action is revealed server-side in the live state so the button
		// is actionable before JS runs (and without JS at all). It stays hidden
		// for every other state.
		$is_live = Segment::STATE_LIVE === $state;
		?>
		<section class="vew-broadcast" data-vew-broadcast
			data-state="<?php echo esc_attr( $state ); ?>"
			data-starting-soon-minutes="<?php echo esc_attr( (string) $starting_soon_minutes ); ?>"
			data-visitor-time="<?php echo esc_attr( $show_visitor ? '1' : '0' ); ?>"
			data-site-timezone="<?php echo esc_attr( wp_timezone_string() ); ?>"
			data-preview="<?php echo esc_attr( $this->is_preview() ? '1' : '0' ); ?>"
			data-labels="<?php echo esc_attr( wp_json_encode( $labels ) ); ?>"
			data-segments="<?php echo esc_attr( wp_json_encode( $upcoming ) ); ?>"
			aria-label="<?php echo esc_attr__( 'Upcoming broadcast', 'vector-elementor-widgets' ); ?>">
			<div class="vew-broadcast__inner">
				<?php echo $heading; ?>

				<?php if ( null === $segment ) : ?>
					<p class="vew-broadcast__empty"><?php echo esc_html( $this->label( $safe, 'empty_text', __( 'Our next gathering time will be posted soon.', 'vector-elementor-widgets' ) ) ); ?></p>
				<?php else : ?>
					<div class="vew-broadcast__grid">
						<div class="vew-broadcast__meta">
							<span class="vew-broadcast__badge vew-broadcast__badge--<?php echo esc_attr( $state ); ?>" data-broadcast-badge>
								<?php echo esc_html( $this->state_text( $state, $labels ) ); ?>
							</span>

							<h3 class="vew-broadcast__segment" data-broadcast-label><?php echo esc_html( $segment->label() ); ?></h3>

							<p class="vew-broadcast__when" data-broadcast-when>
								<?php echo esc_html( $this->format_start( $active['starts_at'], $labels ) ); ?>
							</p>

							<?php if ( $show_visitor ) : ?>
								<p class="vew-broadcast__local" data-broadcast-local hidden></p>
							<?php endif; ?>

							<div class="vew-broadcast__clock" data-broadcast-clock>
								<span class="vew-broadcast__clock-label" data-broadcast-clock-label><?php echo esc_html( $labels['upcoming'] ); ?></span>
								<?php // aria-hidden: a per-second tick must never be announced. See the polite status node below. ?>
								<strong class="vew-broadcast__clock-value" data-broadcast-clock-value aria-hidden="true">--</strong>
							</div>

							<?php // Announced only at meaningful thresholds, never every second. ?>
							<span class="vew-screen-reader-text" data-broadcast-announce role="status" aria-live="polite"></span>

							<div class="vew-broadcast__actions" data-broadcast-actions>
								<?php // No URL means no button: an empty "Watch now" link is worse than none. ?>
								<?php if ( '' !== $segment->stream_url() ) : ?>
								<a class="vew-broadcast__watch" data-broadcast-watch<?php echo $is_live ? '' : ' hidden'; ?>
									href="<?php echo esc_url( $segment->stream_url() ); ?>"
									target="_blank" rel="noopener">
									<span class="vew-broadcast__play" aria-hidden="true"></span>
									<?php echo esc_html( $labels['watch'] ); ?>
								</a>
								<?php endif; ?>

								<?php if ( '' !== $segment->secondary_url() ) : ?>
									<a class="vew-broadcast__secondary" data-broadcast-secondary<?php echo $is_live ? '' : ' hidden'; ?>
										href="<?php echo esc_url( $segment->secondary_url() ); ?>"
										target="_blank" rel="noopener"><?php echo esc_html( $labels['secondary'] ); ?></a>
								<?php endif; ?>

								<?php if ( $show_calendar && null !== $active['starts_at'] ) : ?>
									<?php
									// The .ics payload is a data: URL, which esc_url() strips
									// (its protocol allow-list excludes data:), so it is emitted
									// through a dedicated escaper instead.
									$calendar_href = $this->calendar_url( $segment->label(), $active['starts_at'], $active['ends_at'] );
									?>
									<?php if ( '' !== $calendar_href ) : ?>
										<a class="vew-broadcast__calendar"
											href="<?php echo esc_attr( $calendar_href ); ?>"
											download="<?php echo esc_attr( 'service.ics' ); ?>"><?php echo esc_html( $labels['calendar'] ); ?></a>
									<?php endif; ?>
								<?php endif; ?>
							</div>

							<?php if ( '' !== $timezone_note ) : ?>
								<p class="vew-broadcast__tz-note"><?php echo esc_html( $timezone_note ); ?></p>
							<?php endif; ?>
						</div>

						<div class="vew-broadcast__media">
							<?php if ( '' !== $segment->thumbnail_url() ) : ?>
								<img class="vew-broadcast__thumb" data-broadcast-thumb
									src="<?php echo esc_url( $segment->thumbnail_url() ); ?>"
									alt="<?php echo esc_attr( $segment->thumbnail_alt() ); ?>"
									loading="lazy" decoding="async" />
							<?php endif; ?>
							<span class="vew-broadcast__scrim" aria-hidden="true"></span>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped

		$this->render_json_ld( $segment, $active['starts_at'], $active['ends_at'] );
	}

	/**
	 * Emit Schema.org BroadcastEvent markup for the active segment.
	 *
	 * @param Segment|null $segment   Active segment.
	 * @param string|null  $starts_at ISO start.
	 * @param string|null  $ends_at   ISO end.
	 *
	 * @return void
	 */
	private function render_json_ld( ?Segment $segment, ?string $starts_at, ?string $ends_at ): void {
		if ( null === $segment || null === $starts_at ) {
			return;
		}

		$data = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'BroadcastEvent',
			'name'       => $segment->label(),
			'startDate'  => $starts_at,
			'endDate'    => $ends_at,
			'isLiveBroadcast' => Segment::STATE_LIVE === $this->current_state,
			'eventStatus'     => 'https://schema.org/EventScheduled',
		);

		if ( '' !== $segment->stream_url() ) {
			$data['url'] = $segment->stream_url();
		}

		echo '<script type="application/ld+json">'
			. wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
			. '</script>';
	}

	/**
	 * Build Segments from the raw repeater value.
	 *
	 * @param mixed $raw Raw repeater rows.
	 *
	 * @return array<int, Segment>
	 */
	private function build_segments( $raw ): array {
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$out = array();
		foreach ( $raw as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			$segment = Segment::from_raw( $row );
			if ( null !== $segment ) {
				$out[] = $segment;
			}
		}

		return $out;
	}

	/**
	 * Whether the active preview offset is in force for this request.
	 *
	 * @return bool
	 */
	private function is_preview(): bool {
		return $this->preview_active;
	}

	/**
	 * The instant the widget should treat as "now".
	 *
	 * In preview, the offset is anchored to the NEXT SEGMENT START rather than
	 * to the current time. A relative offset from "now" is useless for
	 * verification: the service is usually hours away, so "+5 min" never
	 * reaches the start and every preview looks identical to upcoming.
	 *
	 * @param string   $preview_offset Raw preview offset.
	 * @param Schedule $schedule       Schedule, for the anchor.
	 *
	 * @return \DateTimeImmutable
	 */
	private function reference_time( string $preview_offset, Schedule $schedule ): \DateTimeImmutable {
		$now = new \DateTimeImmutable( 'now', wp_timezone() );

		if ( 'off' === $preview_offset || '' === $preview_offset ) {
			return $now;
		}

		// Editors only: a visitor must never see a shifted clock.
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
			return $now;
		}

		$allowed = array( '-1 day', '-1 hour', '-5 min', '+5 min', '+2 hours' );
		if ( ! in_array( $preview_offset, $allowed, true ) ) {
			return $now;
		}

		// Anchor to the soonest upcoming start so each offset demonstrates a
		// distinct state regardless of when the editor looks.
		$anchor = $this->next_start( $schedule, $now );
		if ( null === $anchor ) {
			return $now;
		}

		$this->preview_active = true;

		return $anchor->modify( $preview_offset );
	}

	/**
	 * The soonest upcoming segment start at or after an instant.
	 *
	 * @param Schedule           $schedule Schedule.
	 * @param \DateTimeImmutable $now      Reference instant.
	 *
	 * @return \DateTimeImmutable|null
	 */
	private function next_start( Schedule $schedule, \DateTimeImmutable $now ): ?\DateTimeImmutable {
		foreach ( $schedule->upcoming_payload( $now, 1 ) as $row ) {
			if ( ! empty( $row['starts_at'] ) ) {
				try {
					return new \DateTimeImmutable( (string) $row['starts_at'] );
				} catch ( \Exception $e ) {
					continue;
				}
			}
		}

		return null;
	}

	/**
	 * Human label for the current state.
	 *
	 * @param string                $state  State key.
	 * @param array<string, string> $labels Configured labels.
	 *
	 * @return string
	 */
	private function state_text( string $state, array $labels ): string {
		if ( Segment::STATE_LIVE === $state ) {
			return $labels['live'];
		}
		if ( Segment::STATE_STARTING_SOON === $state ) {
			return $labels['starting_soon'];
		}
		if ( Segment::STATE_ENDED === $state ) {
			return $labels['upcoming'];
		}

		return $labels['upcoming'];
	}

	/**
	 * Formatted start description, e.g. "Saturday, September 19 at 11:00 am".
	 *
	 * @param string|null           $starts_at ISO start.
	 * @param array<string, string> $labels    Labels (unused; kept for symmetry).
	 *
	 * @return string
	 */
	private function format_start( ?string $starts_at, array $labels ): string {
		unset( $labels );

		if ( null === $starts_at ) {
			return '';
		}

		try {
			$dt = new \DateTimeImmutable( $starts_at );
		} catch ( \Exception $e ) {
			return '';
		}

		$dt = $dt->setTimezone( wp_timezone() );

		return sprintf(
			/* translators: 1: weekday and date, 2: time. */
			__( '%1$s at %2$s', 'vector-elementor-widgets' ),
			wp_date( 'l, F j', $dt->getTimestamp() ),
			wp_date( 'g:i a', $dt->getTimestamp() )
		);
	}

	/**
	 * Build an .ics data URL for the segment.
	 *
	 * @param string      $label     Event title.
	 * @param string|null $starts_at ISO start.
	 * @param string|null $ends_at   ISO end.
	 *
	 * @return string
	 */
	private function calendar_url( string $label, ?string $starts_at, ?string $ends_at ): string {
		if ( null === $starts_at || null === $ends_at ) {
			return '';
		}

		try {
			$start = new \DateTimeImmutable( $starts_at );
			$end   = new \DateTimeImmutable( $ends_at );
		} catch ( \Exception $e ) {
			return '';
		}

		$lines = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//Vector Elementor Widgets//Broadcast//EN',
			'CALSCALE:GREGORIAN',
			'BEGIN:VEVENT',
			'UID:' . md5( $label . $start->format( 'c' ) ) . '@' . wp_parse_url( home_url(), PHP_URL_HOST ),
			'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
			'DTSTART:' . $start->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' ),
			'DTEND:' . $end->setTimezone( new \DateTimeZone( 'UTC' ) )->format( 'Ymd\THis\Z' ),
			'SUMMARY:' . $this->ics_escape( $label ),
			'END:VEVENT',
			'END:VCALENDAR',
		);

		return 'data:text/calendar;charset=utf-8,' . rawurlencode( implode( "\r\n", $lines ) );
	}

	/**
	 * Escape a value for an iCalendar text field.
	 *
	 * @param string $value Raw value.
	 *
	 * @return string
	 */
	private function ics_escape( string $value ): string {
		return str_replace(
			array( '\\', ';', ',', "\n" ),
			array( '\\\\', '\\;', '\\,', '\\n' ),
			$value
		);
	}

	/**
	 * Read a configured label with a translated fallback.
	 *
	 * @param array<string, mixed> $safe     Sanitized settings.
	 * @param string               $key      Setting key.
	 * @param string               $fallback Fallback text.
	 *
	 * @return string
	 */
	private function label( array $safe, string $key, string $fallback ): string {
		$value = isset( $safe[ $key ] ) ? trim( (string) $safe[ $key ] ) : '';

		return '' !== $value ? $value : $fallback;
	}

	/**
	 * Declare the stylesheet dependency.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( 'vew-section-heading', 'vew-broadcast' );
	}

	/**
	 * Declare the script dependency (countdown + state transitions).
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( 'vew-broadcast' );
	}
}
