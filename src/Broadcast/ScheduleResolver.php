<?php
/**
 * Weekly schedule resolver.
 *
 * Answers "when is the next occurrence of this weekly schedule?" as an absolute
 * instant in the site timezone.
 *
 * Why this is not trivial, and why it lives in PHP:
 *
 *  - A zone-less datetime string is parsed by JavaScript's `new Date()` in the
 *    VISITOR's timezone, so a 11:00 ET service reads as 11:00 local for someone
 *    in Chicago or Los Angeles and the countdown is hours out.
 *  - Recurrence by adding a fixed number of seconds (7 * 86400) drifts by one
 *    hour after a DST transition, because the UTC offset changes. Verified
 *    against America/New_York: Sat 2026-10-31 11:00 EDT, Sat 2026-11-07 11:00
 *    EST — the fixed-step version lands on 10:00 EST.
 *
 * The fix is to build each candidate as a local wall-clock time on a local
 * date and let the timezone resolve the offset, which `DateTimeImmutable`
 * does correctly.
 *
 * @package Vector\ElementorWidgets\Broadcast
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Broadcast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves recurring weekly time segments to absolute instants.
 */
final class ScheduleResolver {

	/**
	 * Timezone all wall-clock times are interpreted in.
	 *
	 * @var \DateTimeZone
	 */
	private \DateTimeZone $zone;

	/**
	 * Constructor.
	 *
	 * @param \DateTimeZone|null $zone Site timezone. Defaults to wp_timezone().
	 */
	public function __construct( ?\DateTimeZone $zone = null ) {
		$this->zone = $zone ?? wp_timezone();
	}

	/**
	 * The site timezone.
	 *
	 * @return \DateTimeZone
	 */
	public function zone(): \DateTimeZone {
		return $this->zone;
	}

	/**
	 * Next absolute instant for a weekly schedule.
	 *
	 * @param array<int, int|string>  $weekdays ISO-8601 day numbers (1=Mon … 7=Sun).
	 * @param string                  $time     Wall-clock 'HH:MM' in the site timezone.
	 * @param \DateTimeImmutable|null $now     Reference instant (defaults to now).
	 *
	 * @return \DateTimeImmutable|null Null when the input is empty or invalid.
	 */
	public function next_occurrence( array $weekdays, string $time, ?\DateTimeImmutable $now = null ): ?\DateTimeImmutable {
		$days = $this->normalise_weekdays( $weekdays );
		if ( array() === $days ) {
			return null;
		}

		$clock = $this->parse_time( $time );
		if ( null === $clock ) {
			return null;
		}
		list( $hour, $minute ) = $clock;

		$reference = ( $now ?? new \DateTimeImmutable( 'now', $this->zone ) )->setTimezone( $this->zone );
		$best      = null;

		// Search today plus a full week: an 8-day window guarantees we always
		// find the next occurrence for any weekday set, while still picking the
		// soonest one when several days are configured.
		for ( $offset = 0; $offset <= 7; $offset++ ) {
			$day = $reference->setTime( 0, 0, 0 )->modify( '+' . $offset . ' day' );
			if ( ! in_array( (int) $day->format( 'N' ), $days, true ) ) {
				continue;
			}

			// Build the candidate as a LOCAL wall-clock time on this LOCAL date
			// so the offset is resolved for that specific date (DST-safe).
			$candidate = $day->setTime( $hour, $minute, 0 );

			if ( $candidate <= $reference ) {
				continue;
			}

			if ( null === $best || $candidate < $best ) {
				$best = $candidate;
			}
		}

		return $best;
	}

	/**
	 * Whether an instant falls inside a segment.
	 *
	 * @param \DateTimeImmutable $starts_at Segment start.
	 * @param int                $minutes   Segment length in minutes.
	 * @param \DateTimeImmutable $now       Reference instant.
	 *
	 * @return bool
	 */
	public function is_within( \DateTimeImmutable $starts_at, int $minutes, \DateTimeImmutable $now ): bool {
		$minutes = max( 1, $minutes );
		// End is computed by adding real minutes to the resolved instant, which
		// is correct here: the segment is a duration, not a wall-clock time.
		$ends_at = $starts_at->modify( '+' . $minutes . ' minutes' );

		return $now >= $starts_at && $now < $ends_at;
	}

	/**
	 * Reduce a raw weekday list to unique ISO day numbers.
	 *
	 * @param array<int, int|string> $weekdays Raw values.
	 *
	 * @return array<int, int>
	 */
	private function normalise_weekdays( array $weekdays ): array {
		$out = array();
		foreach ( $weekdays as $day ) {
			$n = (int) $day;
			if ( $n >= 1 && $n <= 7 && ! in_array( $n, $out, true ) ) {
				$out[] = $n;
			}
		}
		sort( $out );

		return $out;
	}

	/**
	 * Parse an 'HH:MM' wall-clock string.
	 *
	 * @param string $time Raw time.
	 *
	 * @return array{0:int,1:int}|null [hour, minute], or null when invalid.
	 */
	private function parse_time( string $time ): ?array {
		if ( ! preg_match( '/^(\d{1,2}):(\d{2})$/', trim( $time ), $matches ) ) {
			return null;
		}

		$hour   = (int) $matches[1];
		$minute = (int) $matches[2];

		if ( $hour > 23 || $minute > 59 ) {
			return null;
		}

		return array( $hour, $minute );
	}
}
