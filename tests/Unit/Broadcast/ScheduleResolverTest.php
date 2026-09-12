<?php
/**
 * Schedule resolver tests.
 *
 * The two failure modes these guard are both silent: a viewer in another
 * timezone getting the wrong countdown target, and recurrence drifting an hour
 * after a DST transition.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Broadcast
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Broadcast;

use Vector\ElementorWidgets\Broadcast\ScheduleResolver;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class ScheduleResolverTest extends TestCase {

	/**
	 * Site timezone fixture.
	 *
	 * @return \DateTimeZone
	 */
	private function ny(): \DateTimeZone {
		return new \DateTimeZone( 'America/New_York' );
	}

	/**
	 * Thursday reference used across tests.
	 *
	 * @param string $stamp Local timestamp.
	 *
	 * @return \DateTimeImmutable
	 */
	private function at( string $stamp ): \DateTimeImmutable {
		return new \DateTimeImmutable( $stamp, $this->ny() );
	}

	/**
	 * Resolves the next Saturday from a Thursday.
	 *
	 * @return void
	 */
	public function test_resolves_next_occurrence_in_site_timezone(): void {
		$resolver = new ScheduleResolver( $this->ny() );
		$next     = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2026-09-17 09:00:00' ) );

		$this->assertNotNull( $next );
		$this->assertSame( '2026-09-19 11:00:00', $next->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( 'America/New_York', $next->getTimezone()->getName() );
	}

	/**
	 * The wall clock survives a DST transition — the core correctness property.
	 *
	 * Fixed-step recurrence (adding 604800 seconds) drifts to 10:00 EST here.
	 *
	 * @return void
	 */
	public function test_occurrence_keeps_wall_clock_across_dst_end(): void {
		$resolver = new ScheduleResolver( $this->ny() );

		// DST ends Sun 2026-11-01 02:00 ET.
		$edt = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2026-10-30 12:00:00' ) );
		$est = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2026-11-06 12:00:00' ) );

		$this->assertNotNull( $edt );
		$this->assertNotNull( $est );

		$this->assertSame( '2026-10-31 11:00:00', $edt->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( '2026-11-07 11:00:00', $est->format( 'Y-m-d H:i:s' ) );

		$this->assertSame( '11:00:00', $edt->format( 'H:i:s' ) );
		$this->assertSame( '11:00:00', $est->format( 'H:i:s' ), 'Wall clock must not drift across DST.' );
		$this->assertNotSame( $edt->getOffset(), $est->getOffset(), 'The UTC offset must change across DST.' );
	}

	/**
	 * The wall clock also survives spring-forward.
	 *
	 * @return void
	 */
	public function test_occurrence_keeps_wall_clock_across_dst_start(): void {
		$resolver = new ScheduleResolver( $this->ny() );

		// DST starts Sun 2027-03-14 02:00 ET.
		$before = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2027-03-12 12:00:00' ) );
		$after  = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2027-03-19 12:00:00' ) );

		$this->assertSame( '2027-03-13 11:00:00', $before->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( '2027-03-20 11:00:00', $after->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( '11:00:00', $after->format( 'H:i:s' ) );
	}

	/**
	 * A same-day occurrence that already passed rolls to the following week.
	 *
	 * @return void
	 */
	public function test_same_day_occurrence_already_passed_rolls_forward(): void {
		$resolver = new ScheduleResolver( $this->ny() );
		$next     = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2026-09-19 12:30:00' ) );

		$this->assertSame( '2026-09-26', $next->format( 'Y-m-d' ) );
	}

	/**
	 * An occurrence later today is still returned.
	 *
	 * @return void
	 */
	public function test_same_day_occurrence_still_ahead_is_returned(): void {
		$resolver = new ScheduleResolver( $this->ny() );
		$next     = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2026-09-19 09:00:00' ) );

		$this->assertSame( '2026-09-19 11:00:00', $next->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * With several weekdays, the soonest wins.
	 *
	 * @return void
	 */
	public function test_multi_day_segment_picks_the_soonest(): void {
		$resolver = new ScheduleResolver( $this->ny() );

		// From Thursday 2026-09-17: Saturday 09-19 is sooner than Tuesday 09-22.
		$next = $resolver->next_occurrence( array( 6, 2 ), '11:00', $this->at( '2026-09-17 09:00:00' ) );
		$this->assertSame( '2026-09-19', $next->format( 'Y-m-d' ), 'Saturday is soonest from a Thursday.' );

		// From Sunday 2026-09-20: Tuesday 09-22 is sooner than Saturday 09-26.
		$next_b = $resolver->next_occurrence( array( 6, 2 ), '11:00', $this->at( '2026-09-20 09:00:00' ) );
		$this->assertSame( '2026-09-22', $next_b->format( 'Y-m-d' ), 'Tuesday is soonest from a Sunday.' );

		// Wednesday and Friday: Friday 09-18 is sooner than Wednesday 09-23.
		$next2 = $resolver->next_occurrence( array( 3, 5 ), '09:00', $this->at( '2026-09-17 09:00:00' ) );
		$this->assertSame( '2026-09-18', $next2->format( 'Y-m-d' ) );
	}

	/**
	 * The real church schedule: Saturday 11:00 and Tuesday 19:30.
	 *
	 * @return void
	 */
	public function test_real_church_schedule(): void {
		$resolver = new ScheduleResolver( $this->ny() );

		$sat = $resolver->next_occurrence( array( 6 ), '11:00', $this->at( '2026-09-17 09:00:00' ) );
		$tue = $resolver->next_occurrence( array( 2 ), '19:30', $this->at( '2026-09-17 09:00:00' ) );

		$this->assertSame( '2026-09-19 11:00:00', $sat->format( 'Y-m-d H:i:s' ) );
		$this->assertSame( '2026-09-22 19:30:00', $tue->format( 'Y-m-d H:i:s' ) );
	}

	/**
	 * Invalid input yields null rather than throwing or guessing.
	 *
	 * @return void
	 */
	public function test_invalid_input_returns_null(): void {
		$resolver = new ScheduleResolver( $this->ny() );
		$now      = $this->at( '2026-09-17 09:00:00' );

		$this->assertNull( $resolver->next_occurrence( array(), '11:00', $now ), 'no days' );
		$this->assertNull( $resolver->next_occurrence( array( 6 ), '', $now ), 'no time' );
		$this->assertNull( $resolver->next_occurrence( array( 6 ), '25:00', $now ), 'bad hour' );
		$this->assertNull( $resolver->next_occurrence( array( 6 ), '11:70', $now ), 'bad minute' );
		$this->assertNull( $resolver->next_occurrence( array( 6 ), 'noon', $now ), 'not a clock' );
		$this->assertNull( $resolver->next_occurrence( array( 0, 9, -3 ), '11:00', $now ), 'out of range days' );
	}

	/**
	 * Duplicate and string weekdays are normalised.
	 *
	 * @return void
	 */
	public function test_weekdays_are_normalised(): void {
		$resolver = new ScheduleResolver( $this->ny() );
		$next     = $resolver->next_occurrence( array( '6', 6, 6 ), '11:00', $this->at( '2026-09-17 09:00:00' ) );

		$this->assertSame( '2026-09-19', $next->format( 'Y-m-d' ) );
	}

	/**
	 * A duration window is inclusive of start, exclusive of end.
	 *
	 * @return void
	 */
	public function test_is_within_segment_window(): void {
		$resolver  = new ScheduleResolver( $this->ny() );
		$starts_at = $this->at( '2026-09-19 11:00:00' );

		$this->assertFalse( $resolver->is_within( $starts_at, 90, $this->at( '2026-09-19 10:59:59' ) ) );
		$this->assertTrue( $resolver->is_within( $starts_at, 90, $this->at( '2026-09-19 11:00:00' ) ), 'inclusive start' );
		$this->assertTrue( $resolver->is_within( $starts_at, 90, $this->at( '2026-09-19 12:29:59' ) ) );
		$this->assertFalse( $resolver->is_within( $starts_at, 90, $this->at( '2026-09-19 12:30:00' ) ), 'exclusive end' );
	}

	/**
	 * Uses the injected zone rather than the ambient default.
	 *
	 * @return void
	 */
	public function test_zone_is_configurable(): void {
		$resolver = new ScheduleResolver( new \DateTimeZone( 'America/Chicago' ) );
		$this->assertSame( 'America/Chicago', $resolver->zone()->getName() );
	}
}
