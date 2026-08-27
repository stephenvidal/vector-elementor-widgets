<?php
/**
 * Elementor Detector notice tests.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Brain\Monkey\Functions;
use Vector\ElementorWidgets\Elementor\Detector;
use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class DetectorTest extends TestCase {

	/**
	 * When Elementor is installed and at/above the minimum, no notice is
	 * rendered — the version-mismatch warning must NOT fire for a satisfied
	 * minimum.
	 *
	 * (ELEMENTOR_VERSION is pinned to 4.1.4 in bootstrap, which is >= the
	 * 3.18.0 minimum, so meets_min is true.)
	 *
	 * @return void
	 */
	public function test_no_notice_when_elementor_meets_minimum(): void {
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( '' );

		$detector = new Detector();

		ob_start();
		$detector->maybe_render_notice();
		$output = ob_get_clean();

		$this->assertSame( '', $output, 'No notice should render when Elementor meets the minimum.' );
	}
}
