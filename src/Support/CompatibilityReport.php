<?php
/**
 * Compatibility report value object.
 *
 * @package Vector\ElementorWidgets\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Immutable result of a compatibility check.
 */
final class CompatibilityReport {

	/**
	 * Whether PHP + WordPress meet the hard minimums.
	 *
	 * @var bool
	 */
	public bool $hard_compatible;

	/**
	 * Human-readable hard-incompatibility message, or null.
	 *
	 * @var string|null
	 */
	public ?string $hard_incompat;

	/**
	 * User-visible admin notices (non-fatal).
	 *
	 * @var array<int, string>
	 */
	public array $notices;

	/**
	 * Detected Elementor version, or null if not installed.
	 *
	 * @var string|null
	 */
	public ?string $elementor_version;

	/**
	 * Whether Elementor is installed and meets the minimum.
	 *
	 * @var bool
	 */
	public bool $elementor_meets_min;

	/**
	 * Constructor.
	 *
	 * @param bool               $hard_compatible     PHP+WP meet minimums.
	 * @param string|null        $hard_incompat       Hard-incompatibility message.
	 * @param array<int, string> $notices             Non-fatal admin notices.
	 * @param string|null        $elementor_version   Detected Elementor version.
	 * @param bool               $elementor_meets_min Elementor meets minimum.
	 */
	public function __construct(
		bool $hard_compatible,
		?string $hard_incompat,
		array $notices,
		?string $elementor_version,
		bool $elementor_meets_min
	) {
		$this->hard_compatible     = $hard_compatible;
		$this->hard_incompat       = $hard_incompat;
		$this->notices             = $notices;
		$this->elementor_version   = $elementor_version;
		$this->elementor_meets_min = $elementor_meets_min;
	}
}
