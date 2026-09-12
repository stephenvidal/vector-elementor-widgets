<?php
/**
 * Guard the announcement-strip / sticky-header relationship.
 *
 * Only the header should stay pinned on scroll. The announcement strip renders
 * INSIDE the sticky host, so with `top: 0` on the host the strip pinned along
 * with it — confirmed live: at scrollY=600 the strip was still at top 0 and the
 * shell (brand + nav) sat at 34px, i.e. a permanently pinned 34px band.
 *
 * A sticky element cannot partially unstick, so the host is offset upward by
 * the strip's own height: `top: calc(-1 * var(--vew-header-banner-height, 0px))`.
 * The strip therefore rides off-screen and the header lands flush with the top
 * edge. The runtime publishes the MEASURED height, so a wrapping strip or a
 * resized logo stays correct.
 *
 * Two consequences must hold, or the fix silently regresses:
 *   1. The offset variable must have a 0px fallback, so a page with no
 *      announcement (or no JS) pins the whole bar exactly as before.
 *   2. `--header-offset` (consumed by scroll-margin-top) must be published as
 *      the height WITHOUT the strip. The strip scrolls away, so an offset that
 *      still included it would land every anchor jump a strip-height too low.
 *
 * @package Vector\ElementorWidgets\Tests\Unit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class StickyBannerTest extends TestCase {

	/**
	 * Absolute path to the plugin root.
	 *
	 * @return string
	 */
	private function root(): string {
		return dirname( __DIR__, 3 );
	}

	/**
	 * Read a plugin-relative source file.
	 *
	 * @param string $relative Plugin-relative path.
	 * @return string
	 */
	private function source( string $relative ): string {
		$path = $this->root() . '/' . $relative;
		$this->assertFileExists( $path, "Missing source file: {$relative}" );

		return (string) file_get_contents( $path );
	}

	/**
	 * The sticky host is pinned above the viewport by the strip's height.
	 *
	 * @return void
	 */
	public function test_sticky_host_is_offset_by_the_announcement_height(): void {
		$css = $this->source( 'widgets/Header/vew-header.css' );

		$this->assertStringContainsString(
			'top: calc(-1 * var(--vew-header-banner-height, 0px));',
			$css,
			'The sticky host must be offset upward by the announcement height, '
				. 'otherwise the strip is pinned along with the header.'
		);
		$this->assertStringNotContainsString(
			"position: sticky !important;\n\ttop: 0;",
			$css,
			'The host must no longer be pinned at top: 0 — that is the bug: the '
				. 'announcement strip stays on screen when scrolling.'
		);
	}

	/**
	 * The 0px fallback keeps the no-announcement case behaving as before.
	 *
	 * @return void
	 */
	public function test_offset_falls_back_to_zero_without_an_announcement(): void {
		$css = $this->source( 'widgets/Header/vew-header.css' );

		// A non-zero fallback would shift the header up over a page that has
		// no announcement strip at all, hiding part of the bar.
		$this->assertStringContainsString(
			'var(--vew-header-banner-height, 0px)',
			$css,
			'The offset variable needs a 0px fallback: with no announcement (or '
				. 'no JS) the whole bar must still pin exactly as before.'
		);
	}

	/**
	 * The runtime publishes the MEASURED strip height, not a hardcoded value.
	 *
	 * @return void
	 */
	public function test_runtime_publishes_measured_banner_height(): void {
		$js = $this->source( 'widgets/Header/vew-header.js' );

		$this->assertStringContainsString( '--vew-header-banner-height', $js );
		// Measure the live element so a wrapped strip / resized logo is right.
		$this->assertStringContainsString(
			"host.querySelector( '.vew-header__announcement' )",
			$js,
			'The strip height must be measured from the rendered element.'
		);
		$this->assertStringContainsString( 'getBoundingClientRect().height', $js );
		// A page with no strip must clear the property rather than leave a
		// stale value from a previous measure.
		$this->assertStringContainsString(
			"removeProperty( '--vew-header-banner-height' )",
			$js,
			'Without an announcement the property must be removed, not left stale.'
		);
	}

	/**
	 * Anchor offset must exclude the strip, since the strip scrolls away.
	 *
	 * @return void
	 */
	public function test_anchor_offset_excludes_the_scrolling_strip(): void {
		$js = $this->source( 'widgets/Header/vew-header.js' );

		// The published offset is the host height MINUS the strip.
		$this->assertMatchesRegularExpression(
			'/var bar = host\.getBoundingClientRect\(\)\.height - banner;/',
			$js,
			'--header-offset must be host height minus the strip: the strip '
				. 'scrolls away, so including it lands anchors too low.'
		);
		$this->assertStringContainsString(
			"root.style.setProperty( '--header-offset', Math.ceil( maxBar ) + 'px' )",
			$js
		);
	}

	/**
	 * The pinned-state shadow waits for the strip to finish scrolling away.
	 *
	 * @return void
	 */
	public function test_scrolled_state_waits_for_the_strip_to_leave(): void {
		$js = $this->source( 'widgets/Header/vew-header.js' );

		$this->assertMatchesRegularExpression(
			'/window\.scrollY > Math\.max\( 8, banner \)/',
			$js,
			'The scrolled state must not lift the bar while the strip is still '
				. 'on screen — the bar is not pinned yet at that point.'
		);
	}
}
