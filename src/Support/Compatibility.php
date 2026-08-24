<?php
/**
 * Compatibility checker.
 *
 * Phase 1: hard-validates PHP and WordPress minimums; non-fatally
 * reports Elementor absence / version-mismatch.
 *
 * The Plugin kernel calls this on every `plugins_loaded` boot.
 * Results are exposed via:
 *  - hard_compatible() — true if PHP+WP meet minimums; gate on activation
 *  - notices()         — array of user-visible admin notices
 *  - has_minimum_elementor() / elementor_version() — for the Elementor layer
 *
 * @package Vector\ElementorWidgets\Support
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Support;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compatibility checker.
 *
 * Stateless — safe to instantiate on every request.
 */
final class Compatibility {

	/**
	 * Run a full compatibility check.
	 *
	 * @return CompatibilityReport
	 */
	public function check(): CompatibilityReport {
		$notices         = array();
		$hard_incompat   = null;
		$hard_compat     = true;
		$elementor_ver   = null;
		$elementor_meets = false;

		// PHP — hard.
		if ( version_compare( PHP_VERSION, VEW_MIN_PHP, '<' ) ) {
			$hard_compat   = false;
			$hard_incompat = sprintf(
				/* translators: 1: required PHP version, 2: actual PHP version */
				__( 'Vector Elementor Widgets requires PHP %1$s or higher. The site is running PHP %2$s.', 'vector-elementor-widgets' ),
				VEW_MIN_PHP,
				PHP_VERSION
			);
		}

		// WordPress — hard (only if function `get_bloginfo` is available, i.e. we're in a WP context).
		if ( function_exists( 'get_bloginfo' ) ) {
			$wp_version = get_bloginfo( 'version' );
			if ( version_compare( (string) $wp_version, VEW_MIN_WP, '<' ) ) {
				$hard_compat   = false;
				$hard_incompat = sprintf(
					/* translators: 1: required WP version, 2: actual WP version */
					__( 'Vector Elementor Widgets requires WordPress %1$s or higher. The site is running WordPress %2$s.', 'vector-elementor-widgets' ),
					VEW_MIN_WP,
					(string) $wp_version
				);
			}
		}

		// Elementor — non-fatal.
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$elementor_ver   = (string) ELEMENTOR_VERSION;
			$elementor_meets = version_compare( $elementor_ver, VEW_MIN_ELEMENTOR, '>=' );
			if ( ! $elementor_meets ) {
				$notices[] = sprintf(
					/* translators: 1: required Elementor version, 2: actual Elementor version */
					__( 'Vector Elementor Widgets recommends Elementor %1$s or higher. The site is running Elementor %2$s — the Elementor widgets are disabled but the plugin still functions.', 'vector-elementor-widgets' ),
					VEW_MIN_ELEMENTOR,
					$elementor_ver
				);
			}
		} else {
			$notices[] = __( 'Vector Elementor Widgets: Elementor is not installed. The Elementor widgets are disabled.', 'vector-elementor-widgets' );
		}

		return new CompatibilityReport(
			$hard_compat,
			$hard_incompat,
			$notices,
			$elementor_ver,
			$elementor_meets,
		);
	}

	/**
	 * Convenience: hard PHP+WP compatibility only.
	 *
	 * @return bool
	 */
	public function hard_compatible(): bool {
		return $this->check()->hard_compatible;
	}

	/**
	 * Convenience: Elementor installed and meets minimum.
	 *
	 * @return bool
	 */
	public function has_minimum_elementor(): bool {
		$r = $this->check();
		return (bool) $r->elementor_meets_min;
	}
}
