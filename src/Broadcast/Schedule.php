<?php
/**
 * Broadcast schedule.
 *
 * Turns a list of recurring {@see Segment}s into the per-segment state machine
 * the widget renders: which segment is active right now, when it starts, and
 * when it ends — all resolved in the site timezone.
 *
 * Every upcoming occurrence is emitted, not just the next one. The page is
 * served behind a full-page cache, so a payload containing only the "next"
 * segment would be wrong the moment the cache outlives that segment; shipping
 * the whole upcoming list lets the client roll forward without a new request.
 *
 * @package Vector\ElementorWidgets\Broadcast
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Broadcast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Computes broadcast state across a recurring schedule.
 */
final class Schedule {

	/**
	 * Minutes before the start that the "starting soon" state begins.
	 */
	public const DEFAULT_STARTING_SOON_MINUTES = 10;

	/**
	 * How many upcoming occurrences of each segment to emit.
	 */
	public const DEFAULT_LOOKAHEAD = 2;

	/**
	 * Segments, in configured order.
	 *
	 * @var array<int, Segment>
	 */
	private array $segments;

	/**
	 * Resolver used for occurrence maths.
	 *
	 * @var ScheduleResolver
	 */
	private ScheduleResolver $resolver;

	/**
	 * Starting-soon window in minutes.
	 *
	 * @var int
	 */
	private int $starting_soon_minutes;

	/**
	 * Constructor.
	 *
	 * @param array<int, Segment>   $segments              Segments.
	 * @param ScheduleResolver|null $resolver            Resolver (defaults to site zone).
	 * @param int                   $starting_soon_minutes Starting-soon window.
	 */
	public function __construct(
		array $segments,
		?ScheduleResolver $resolver = null,
		int $starting_soon_minutes = self::DEFAULT_STARTING_SOON_MINUTES
	) {
		$this->segments              = array_values( $segments );
		$this->resolver              = $resolver ?? new ScheduleResolver();
		$this->starting_soon_minutes = max( 0, $starting_soon_minutes );
	}

	/**
	 * Whether there is anything to render.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return array() === $this->segments;
	}

	/**
	 * The site timezone.
	 *
	 * @return \DateTimeZone
	 */
	public function zone(): \DateTimeZone {
		return $this->resolver->zone();
	}

	/**
	 * State of a segment at an instant.
	 *
	 * @param Segment            $segment Segment.
	 * @param \DateTimeImmutable $now     Reference instant.
	 *
	 * @return array{state:string, starts_at:?\DateTimeImmutable, ends_at:?\DateTimeImmutable}
	 */
	public function state_at( Segment $segment, \DateTimeImmutable $now ): array {
		$starts_at = $this->resolver->next_occurrence( $segment->weekdays(), $segment->time(), $now );

		// The "next occurrence from now" is always in the future, so a segment
		// can only be live if its PREVIOUS occurrence is still running. Check
		// that window explicitly; otherwise a service in progress would read as
		// "upcoming next week".
		$previous = $this->previous_occurrence( $segment, $now );
		if ( null !== $previous ) {
			$ends_at = $previous->modify( '+' . $segment->duration() . ' minutes' );
			if ( $now >= $previous && $now < $ends_at ) {
				return array(
					'state'     => Segment::STATE_LIVE,
					'starts_at' => $previous,
					'ends_at'   => $ends_at,
				);
			}
		}

		$ends_at = null !== $starts_at ? $starts_at->modify( '+' . $segment->duration() . ' minutes' ) : null;

		if ( null !== $starts_at && $this->starting_soon_minutes > 0 ) {
			$soon_from = $starts_at->modify( '-' . $this->starting_soon_minutes . ' minutes' );
			if ( $now >= $soon_from && $now < $starts_at ) {
				return array(
					'state'     => Segment::STATE_STARTING_SOON,
					'starts_at' => $starts_at,
					'ends_at'   => $ends_at,
				);
			}
		}

		return array(
			'state'     => null !== $starts_at ? Segment::STATE_UPCOMING : Segment::STATE_ENDED,
			'starts_at' => $starts_at,
			'ends_at'   => $ends_at,
		);
	}

	/**
	 * The occurrence of a segment that started most recently at/before $now.
	 *
	 * @param Segment            $segment Segment.
	 * @param \DateTimeImmutable $now     Reference instant.
	 *
	 * @return \DateTimeImmutable|null
	 */
	public function previous_occurrence( Segment $segment, \DateTimeImmutable $now ): ?\DateTimeImmutable {
		// Look back a week: the most recent occurrence of a weekly schedule can
		// never be further back than that.
		$lookback = $now->modify( '-8 days' );
		$found    = null;

		foreach ( $segment->weekdays() as $day ) {
			$candidate = $this->resolver->next_occurrence( array( $day ), $segment->time(), $lookback );
			if ( null === $candidate ) {
				continue;
			}
			// Walk forward from the earliest occurrence after the lookback
			// window, keeping the last one that is still <= $now.
			for ( $i = 0; $i < 2; $i++ ) {
				if ( $candidate <= $now ) {
					if ( null === $found || $candidate > $found ) {
						$found = $candidate;
					}
				}
				$candidate = $this->resolver->next_occurrence( array( $day ), $segment->time(), $candidate );
				if ( null === $candidate ) {
					break;
				}
			}
		}

		return $found;
	}

	/**
	 * The segment and state the widget should show now.
	 *
	 * A live segment always wins; otherwise the soonest upcoming one.
	 *
	 * @param \DateTimeImmutable $now Reference instant.
	 *
	 * @return array{segment:?Segment, state:string, starts_at:?string, ends_at:?string}
	 */
	public function active( \DateTimeImmutable $now ): array {
		$live       = null;
		$starting   = null;
		$upcoming   = null;

		foreach ( $this->segments as $segment ) {
			$state = $this->state_at( $segment, $now );

			if ( Segment::STATE_LIVE === $state['state'] ) {
				if ( null === $live || $state['starts_at'] > $live['starts_at'] ) {
					$live = array( 'segment' => $segment ) + $state;
				}
				continue;
			}

			if ( Segment::STATE_STARTING_SOON === $state['state'] ) {
				if ( null === $starting || $state['starts_at'] < $starting['starts_at'] ) {
					$starting = array( 'segment' => $segment ) + $state;
				}
				continue;
			}

			if ( Segment::STATE_UPCOMING === $state['state'] && null !== $state['starts_at'] ) {
				if ( null === $upcoming || $state['starts_at'] < $upcoming['starts_at'] ) {
					$upcoming = array( 'segment' => $segment ) + $state;
				}
			}
		}

		// Priority: something is live now, else about to start, else next up.
		$chosen = $live ?? $starting ?? $upcoming;

		if ( null === $chosen ) {
			return array(
				'segment'   => null,
				'state'     => Segment::STATE_ENDED,
				'starts_at' => null,
				'ends_at'   => null,
			);
		}

		return array(
			'segment'   => $chosen['segment'],
			'state'     => $chosen['state'],
			'starts_at' => $chosen['starts_at'] instanceof \DateTimeImmutable ? $chosen['starts_at']->format( 'c' ) : null,
			'ends_at'   => $chosen['ends_at'] instanceof \DateTimeImmutable ? $chosen['ends_at']->format( 'c' ) : null,
		);
	}

	/**
	 * Every upcoming occurrence across all segments, soonest first.
	 *
	 * This is the payload the client uses to roll forward when a cached page
	 * outlives its target.
	 *
	 * @param \DateTimeImmutable $now       Reference instant.
	 * @param int                $lookahead Occurrences per segment.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function upcoming_payload( \DateTimeImmutable $now, int $lookahead = self::DEFAULT_LOOKAHEAD ): array {
		$rows = array();

		foreach ( $this->segments as $index => $segment ) {
			$cursor = $now;
			for ( $i = 0; $i < max( 1, $lookahead ); $i++ ) {
				$starts_at = $this->resolver->next_occurrence( $segment->weekdays(), $segment->time(), $cursor );
				if ( null === $starts_at ) {
					break;
				}

				$rows[] = array(
					'index'        => $index,
					'label'        => $segment->label(),
					'starts_at'    => $starts_at->format( 'c' ),
					'ends_at'      => $starts_at->modify( '+' . $segment->duration() . ' minutes' )->format( 'c' ),
					'duration'     => $segment->duration(),
					'thumbnail'    => $segment->thumbnail_url(),
					'thumbnailAlt' => $segment->thumbnail_alt(),
					'stream'       => $segment->stream_url(),
					'secondary'    => $segment->secondary_url(),
				);

				$cursor = $starts_at;
			}
		}

		usort(
			$rows,
			static fn( array $a, array $b ): int => strcmp( (string) $a['starts_at'], (string) $b['starts_at'] )
		);

		return $rows;
	}
}
