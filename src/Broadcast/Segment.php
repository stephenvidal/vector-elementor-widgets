<?php
/**
 * Broadcast time segment.
 *
 * One recurring slot in the church's schedule ("Saturday Worship, Sat 11:00,
 * 90 minutes, thumbnail X, stream Y"). Immutable; built from raw Elementor
 * repeater data so untrusted input is sanitised in exactly one place.
 *
 * @package Vector\ElementorWidgets\Broadcast
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Broadcast;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A recurring broadcast time segment.
 */
final class Segment {

	/**
	 * Segment states, in lifecycle order.
	 */
	public const STATE_UPCOMING      = 'upcoming';
	public const STATE_STARTING_SOON = 'starting_soon';
	public const STATE_LIVE          = 'live';
	public const STATE_ENDED         = 'ended';

	/**
	 * Label shown to visitors.
	 *
	 * @var string
	 */
	private string $label;

	/**
	 * ISO-8601 weekday numbers (1=Mon … 7=Sun).
	 *
	 * @var array<int, int>
	 */
	private array $weekdays;

	/**
	 * Wall-clock start time, 'HH:MM', in the site timezone.
	 *
	 * @var string
	 */
	private string $time;

	/**
	 * Length in minutes.
	 *
	 * @var int
	 */
	private int $duration;

	/**
	 * Thumbnail image URL.
	 *
	 * @var string
	 */
	private string $thumbnail_url;

	/**
	 * Thumbnail alt text.
	 *
	 * @var string
	 */
	private string $thumbnail_alt;

	/**
	 * Primary watch URL.
	 *
	 * @var string
	 */
	private string $stream_url;

	/**
	 * Optional secondary watch URL (e.g. a simulcast).
	 *
	 * @var string
	 */
	private string $secondary_url;

	/**
	 * Constructor.
	 *
	 * @param string          $label         Visitor-facing label.
	 * @param array<int, int> $weekdays      ISO weekday numbers.
	 * @param string          $time          'HH:MM' wall clock.
	 * @param int             $duration      Minutes.
	 * @param string          $thumbnail_url Image URL.
	 * @param string          $thumbnail_alt Image alt text.
	 * @param string          $stream_url    Primary watch URL.
	 * @param string          $secondary_url Secondary watch URL.
	 */
	private function __construct(
		string $label,
		array $weekdays,
		string $time,
		int $duration,
		string $thumbnail_url,
		string $thumbnail_alt,
		string $stream_url,
		string $secondary_url
	) {
		$this->label         = $label;
		$this->weekdays      = $weekdays;
		$this->time          = $time;
		$this->duration      = $duration;
		$this->thumbnail_url = $thumbnail_url;
		$this->thumbnail_alt = $thumbnail_alt;
		$this->stream_url    = $stream_url;
		$this->secondary_url = $secondary_url;
	}

	/**
	 * Build a Segment from raw repeater data, sanitising every field.
	 *
	 * @param array<string, mixed> $raw       Raw repeater row.
	 * @param int                  $fallback_duration Default duration when unset.
	 *
	 * @return self|null Null when the row has no usable schedule.
	 */
	public static function from_raw( array $raw, int $fallback_duration = 90 ): ?self {
		$label = isset( $raw['label'] ) ? sanitize_text_field( (string) $raw['label'] ) : '';

		$weekdays = array();
		if ( isset( $raw['days'] ) ) {
			$raw_days = is_array( $raw['days'] ) ? $raw['days'] : array( $raw['days'] );
			foreach ( $raw_days as $day ) {
				$n = (int) $day;
				if ( $n >= 1 && $n <= 7 && ! in_array( $n, $weekdays, true ) ) {
					$weekdays[] = $n;
				}
			}
			sort( $weekdays );
		}

		$time = isset( $raw['time'] ) ? sanitize_text_field( (string) $raw['time'] ) : '';
		if ( ! preg_match( '/^\d{1,2}:\d{2}$/', $time ) ) {
			return null;
		}

		$duration = isset( $raw['duration'] ) && '' !== $raw['duration']
			? absint( $raw['duration'] )
			: $fallback_duration;
		if ( $duration < 1 ) {
			$duration = $fallback_duration;
		}
		if ( $duration > 1440 ) {
			$duration = 1440;
		}

		if ( array() === $weekdays || '' === $time ) {
			return null;
		}

		$thumbnail_url = '';
		$thumbnail_alt = '';
		if ( isset( $raw['thumbnail'] ) ) {
			$media = $raw['thumbnail'];
			if ( is_array( $media ) ) {
				$thumb_url     = $media['url'] ?? '';
				$thumbnail_url = is_array( $thumb_url ) ? (string) ( $thumb_url['url'] ?? '' ) : (string) $thumb_url;
				$thumbnail_alt = isset( $media['alt'] ) ? sanitize_text_field( (string) $media['alt'] ) : '';
			} elseif ( is_string( $media ) ) {
				$thumbnail_url = $media;
			}
		}
		$thumbnail_url = esc_url_raw( $thumbnail_url );

		return new self(
			$label,
			$weekdays,
			$time,
			$duration,
			$thumbnail_url,
			$thumbnail_alt,
			self::url_from_raw( $raw['stream_url'] ?? null ),
			self::url_from_raw( $raw['secondary_url'] ?? null )
		);
	}

	/**
	 * Pull a URL out of an Elementor URL-control value.
	 *
	 * @param mixed $value Raw control value.
	 *
	 * @return string
	 */
	private static function url_from_raw( $value ): string {
		if ( is_array( $value ) ) {
			$value = $value['url'] ?? '';
		}
		if ( ! is_string( $value ) ) {
			return '';
		}

		return esc_url_raw( $value );
	}

	/**
	 * Label.
	 *
	 * @return string
	 */
	public function label(): string {
		return $this->label;
	}

	/**
	 * ISO weekday numbers.
	 *
	 * @return array<int, int>
	 */
	public function weekdays(): array {
		return $this->weekdays;
	}

	/**
	 * Wall-clock 'HH:MM'.
	 *
	 * @return string
	 */
	public function time(): string {
		return $this->time;
	}

	/**
	 * Duration in minutes.
	 *
	 * @return int
	 */
	public function duration(): int {
		return $this->duration;
	}

	/**
	 * Thumbnail URL.
	 *
	 * @return string
	 */
	public function thumbnail_url(): string {
		return $this->thumbnail_url;
	}

	/**
	 * Thumbnail alt text.
	 *
	 * @return string
	 */
	public function thumbnail_alt(): string {
		return $this->thumbnail_alt;
	}

	/**
	 * Primary watch URL.
	 *
	 * @return string
	 */
	public function stream_url(): string {
		return $this->stream_url;
	}

	/**
	 * Secondary watch URL.
	 *
	 * @return string
	 */
	public function secondary_url(): string {
		return $this->secondary_url;
	}

	/**
	 * Whether this segment can be watched online.
	 *
	 * @return bool
	 */
	public function has_stream(): bool {
		return '' !== $this->stream_url || '' !== $this->secondary_url;
	}

	/**
	 * Whether the primary stream URL is an HLS (.m3u8) endpoint.
	 *
	 * HLS can only be played in-page by a <video> + hls.js (Chrome/Firefox) or
	 * natively (Safari). When true the widget may embed a player instead of
	 * deep-linking out. Empty/other URLs return false so the existing
	 * "Watch now" link remains the fallback for non-HLS streams.
	 *
	 * @return bool
	 */
	public function is_hls(): bool {
		return '' !== $this->stream_url && preg_match( '/\.m3u8(\?.*)?$/i', $this->stream_url ) === 1;
	}
}
