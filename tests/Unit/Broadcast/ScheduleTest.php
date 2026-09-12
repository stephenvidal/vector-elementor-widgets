<?php
/**
 * Broadcast schedule state tests.
 *
 * The state machine has one subtle trap: "next occurrence" is always in the
 * future, so a service in progress reads as "next week" unless the PREVIOUS
 * occurrence's duration window is checked explicitly. That is the difference
 * between showing a play button during the service and showing a countdown to
 * the same service a week later.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Broadcast
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Broadcast;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Broadcast\Schedule;
use Vector\ElementorWidgets\Broadcast\ScheduleResolver;
use Vector\ElementorWidgets\Broadcast\Segment;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

require_once dirname( __DIR__, 2 ) . '/Support/wp-stubs.php';

final class ScheduleTest extends TestCase {

	/**
	 * Stub the WP sanitizers Segment::from_raw() calls.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'absint' )->alias( static fn ( $v ): int => abs( (int) $v ) );
	}

	/**
	 * Site timezone fixture.
	 *
	 * @return \DateTimeZone
	 */
	private function ny(): \DateTimeZone {
		return new \DateTimeZone( 'America/New_York' );
	}

	/**
	 * Local instant helper.
	 *
	 * @param string $stamp Timestamp.
	 *
	 * @return \DateTimeImmutable
	 */
	private function at( string $stamp ): \DateTimeImmutable {
		return new \DateTimeImmutable( $stamp, $this->ny() );
	}

	/**
	 * Build a segment from raw data.
	 *
	 * @param array<string, mixed> $overrides Field overrides.
	 *
	 * @return Segment
	 */
	private function segment( array $overrides = array() ): Segment {
		$raw = array_merge(
			array(
				'label'      => 'Saturday Worship',
				'days'       => array( 6 ),
				'time'       => '11:00',
				'duration'   => 90,
				'thumbnail'  => array( 'url' => 'https://example.test/sat.jpg', 'alt' => 'Saturday' ),
				'stream_url' => array( 'url' => 'https://example.test/live' ),
			),
			$overrides
		);

		$segment = Segment::from_raw( $raw );
		$this->assertInstanceOf( Segment::class, $segment );

		return $segment;
	}

	/**
	 * Build a schedule around one segment.
	 *
	 * @param array<int, Segment> $segments Segments.
	 *
	 * @return Schedule
	 */
	private function schedule( array $segments ): Schedule {
		return new Schedule( $segments, new ScheduleResolver( $this->ny() ) );
	}

	/**
	 * A segment whose start is still ahead is "upcoming".
	 *
	 * @return void
	 */
	public function test_upcoming_before_start(): void {
		$schedule = $this->schedule( array( $this->segment() ) );
		$state    = $schedule->state_at( $this->segment(), $this->at( '2026-09-19 09:00:00' ) );

		$this->assertSame( Segment::STATE_UPCOMING, $state['state'] );
		$this->assertSame( '2026-09-19 11:00:00', $state['starts_at']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Inside the starting-soon window the state flips, before the start.
	 *
	 * @return void
	 */
	public function test_starting_soon_window(): void {
		$seg      = $this->segment();
		$schedule = $this->schedule( array( $seg ) );

		// 10-minute window: 10:50 onward.
		$this->assertSame(
			Segment::STATE_UPCOMING,
			$schedule->state_at( $seg, $this->at( '2026-09-19 10:49:00' ) )['state']
		);
		$this->assertSame(
			Segment::STATE_STARTING_SOON,
			$schedule->state_at( $seg, $this->at( '2026-09-19 10:50:00' ) )['state']
		);
		$this->assertSame(
			Segment::STATE_STARTING_SOON,
			$schedule->state_at( $seg, $this->at( '2026-09-19 10:59:59' ) )['state']
		);
	}

	/**
	 * At the start instant the segment is live — this is the play-button moment.
	 *
	 * @return void
	 */
	public function test_live_at_exact_start(): void {
		$seg      = $this->segment();
		$schedule = $this->schedule( array( $seg ) );
		$state    = $schedule->state_at( $seg, $this->at( '2026-09-19 11:00:00' ) );

		$this->assertSame( Segment::STATE_LIVE, $state['state'] );
		$this->assertSame( '2026-09-19 11:00:00', $state['starts_at']->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * The duration window is inclusive of start and exclusive of end.
	 *
	 * @return void
	 */
	public function test_live_window_boundaries(): void {
		$seg      = $this->segment();
		$schedule = $this->schedule( array( $seg ) );

		$this->assertSame( Segment::STATE_LIVE, $schedule->state_at( $seg, $this->at( '2026-09-19 12:29:59' ) )['state'] );
		$this->assertSame( Segment::STATE_UPCOMING, $schedule->state_at( $seg, $this->at( '2026-09-19 12:30:00' ) )['state'], 'after the window it is next week, not live' );
	}

	/**
	 * After the window the segment rolls forward to the NEXT week.
	 *
	 * @return void
	 */
	public function test_rolls_forward_after_end(): void {
		$seg      = $this->segment();
		$schedule = $this->schedule( array( $seg ) );
		$state    = $schedule->state_at( $seg, $this->at( '2026-09-19 13:00:00' ) );

		$this->assertSame( Segment::STATE_UPCOMING, $state['state'] );
		$this->assertSame( '2026-09-26', $state['starts_at']->format( 'Y-m-d' ) );
	}

	/**
	 * A live segment beats a sooner-but-not-yet segment.
	 *
	 * @return void
	 */
	public function test_live_segment_wins_over_upcoming(): void {
		$sat = $this->segment( array( 'label' => 'Saturday Worship', 'days' => array( 6 ), 'time' => '11:00' ) );
		$tue = $this->segment( array( 'label' => 'Bible Study', 'days' => array( 2 ), 'time' => '19:30', 'duration' => 60 ) );
		$schedule = $this->schedule( array( $sat, $tue ) );

		// Saturday 11:30 — Saturday is live; Tuesday is upcoming.
		$active = $schedule->active( $this->at( '2026-09-19 11:30:00' ) );
		$this->assertSame( 'Saturday Worship', $active['segment']->label() );
		$this->assertSame( Segment::STATE_LIVE, $active['state'] );
	}

	/**
	 * With nothing live, the soonest upcoming segment is chosen.
	 *
	 * @return void
	 */
	public function test_soonest_upcoming_chosen_when_nothing_live(): void {
		$sat = $this->segment( array( 'label' => 'Saturday Worship', 'days' => array( 6 ), 'time' => '11:00' ) );
		$tue = $this->segment( array( 'label' => 'Bible Study', 'days' => array( 2 ), 'time' => '19:30' ) );
		$schedule = $this->schedule( array( $sat, $tue ) );

		// Sunday: next up is Tuesday's Bible study.
		$active = $schedule->active( $this->at( '2026-09-20 09:00:00' ) );
		$this->assertSame( 'Bible Study', $active['segment']->label() );
		$this->assertSame( '2026-09-22T19:30:00-04:00', $active['starts_at'] );
	}

	/**
	 * An empty schedule reports empty and produces no active segment.
	 *
	 * @return void
	 */
	public function test_empty_schedule(): void {
		$schedule = $this->schedule( array() );

		$this->assertTrue( $schedule->is_empty() );
		$active = $schedule->active( $this->at( '2026-09-19 11:00:00' ) );
		$this->assertNull( $active['segment'] );
		$this->assertSame( Segment::STATE_ENDED, $active['state'] );
	}

	/**
	 * The active payload carries ISO-8601 instants with an explicit offset.
	 *
	 * @return void
	 */
	public function test_active_emits_iso_with_offset(): void {
		$schedule = $this->schedule( array( $this->segment() ) );
		$active   = $schedule->active( $this->at( '2026-09-19 09:00:00' ) );

		$this->assertSame( '2026-09-19T11:00:00-04:00', $active['starts_at'] );
		$this->assertSame( '2026-09-19T12:30:00-04:00', $active['ends_at'] );
	}

	/**
	 * The upcoming payload contains every occurrence, soonest first, so a cached
	 * page can roll forward on its own.
	 *
	 * @return void
	 */
	public function test_upcoming_payload_is_complete_and_sorted(): void {
		$sat = $this->segment( array( 'label' => 'Saturday Worship', 'days' => array( 6 ), 'time' => '11:00' ) );
		$tue = $this->segment( array( 'label' => 'Bible Study', 'days' => array( 2 ), 'time' => '19:30' ) );
		$schedule = $this->schedule( array( $sat, $tue ) );

		$rows = $schedule->upcoming_payload( $this->at( '2026-09-17 09:00:00' ), 2 );

		$this->assertCount( 4, $rows, 'two segments x two occurrences' );

		$starts = array_column( $rows, 'starts_at' );
		$sorted = $starts;
		sort( $sorted );
		$this->assertSame( $sorted, $starts, 'payload must be sorted soonest-first' );

		$this->assertSame( '2026-09-19T11:00:00-04:00', $rows[0]['starts_at'] );
		$this->assertSame( 'Saturday Worship', $rows[0]['label'] );

		foreach ( $rows as $row ) {
			$this->assertArrayHasKey( 'thumbnail', $row );
			$this->assertArrayHasKey( 'stream', $row );
			$this->assertArrayHasKey( 'ends_at', $row );
		}
	}

	/**
	 * Each segment's own thumbnail travels with its occurrences — the core
	 * requirement that the right image shows for the right slot.
	 *
	 * @return void
	 */
	public function test_each_occurrence_carries_its_own_thumbnail(): void {
		$sat = $this->segment( array( 'label' => 'Saturday', 'days' => array( 6 ), 'time' => '11:00', 'thumbnail' => array( 'url' => 'https://example.test/sat.jpg' ) ) );
		$tue = $this->segment( array( 'label' => 'Tuesday', 'days' => array( 2 ), 'time' => '19:30', 'thumbnail' => array( 'url' => 'https://example.test/tue.jpg' ) ) );
		$schedule = $this->schedule( array( $sat, $tue ) );

		$rows = $schedule->upcoming_payload( $this->at( '2026-09-17 09:00:00' ), 1 );
		$by_label = array();
		foreach ( $rows as $row ) {
			$by_label[ $row['label'] ] = $row['thumbnail'];
		}

		$this->assertSame( 'https://example.test/sat.jpg', $by_label['Saturday'] );
		$this->assertSame( 'https://example.test/tue.jpg', $by_label['Tuesday'] );
	}

	/**
	 * Payload instants stay correct across DST because they are absolute.
	 *
	 * @return void
	 */
	public function test_payload_is_dst_correct(): void {
		$schedule = $this->schedule( array( $this->segment() ) );
		$rows     = $schedule->upcoming_payload( $this->at( '2026-10-30 12:00:00' ), 2 );

		// 2026-10-31 11:00 EDT (UTC-4), then 2026-11-07 11:00 EST (UTC-5).
		$this->assertSame( '2026-10-31T11:00:00-04:00', $rows[0]['starts_at'] );
		$this->assertSame( '2026-11-07T11:00:00-05:00', $rows[1]['starts_at'] );
	}
}
