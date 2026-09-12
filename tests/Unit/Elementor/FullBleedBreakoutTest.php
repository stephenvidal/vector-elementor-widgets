<?php
/**
 * Guard the full-bleed breakout idiom.
 *
 * Several widgets span the viewport edge-to-edge by escaping their Elementor
 * container with `width: 100vw` plus a negative inline margin. `100vw` counts
 * the CLASSIC scrollbar while `clientWidth` does not, so a bare `100vw` width
 * made every such widget overshoot the viewport by the scrollbar width —
 * producing a horizontal scrollbar on desktop. Measured on live: 1440 vs 1425,
 * i.e. an 8px overflow after the centring margin split the difference.
 *
 * The fix sizes the breakout as `calc(100vw - var(--vew-sb, 0px))`, where
 * `--vew-sb` is published by `assets/js/scrollbar-metric.js`. The 0px fallback
 * matters: it keeps overlay-scrollbar browsers (iOS, Android, macOS) correct,
 * where `100vw` already equals `clientWidth`.
 *
 * These tests are lexical/source guards, matching the suite's existing
 * convention, because a CSS regression here is invisible to PHP.
 *
 * @package Vector\ElementorWidgets\Tests\Unit
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class FullBleedBreakoutTest extends TestCase {

	/**
	 * Absolute path to the plugin root.
	 *
	 * @return string
	 */
	private function root(): string {
		return dirname( __DIR__, 3 );
	}

	/**
	 * Every widget stylesheet, slug => contents.
	 *
	 * @return array<string, string>
	 */
	private function widget_styles(): array {
		$out = array();
		foreach ( glob( $this->root() . '/widgets/*/*.css' ) ?: array() as $path ) {
			$contents = file_get_contents( $path );
			if ( is_string( $contents ) ) {
				$out[ basename( dirname( $path ) ) ] = $contents;
			}
		}
		$this->assertNotEmpty( $out, 'No widget stylesheets found.' );
		return $out;
	}

	/**
	 * No widget may size its breakout with a bare `100vw` width.
	 *
	 * @return void
	 */
	public function test_no_bare_100vw_width(): void {
		foreach ( $this->widget_styles() as $slug => $css ) {
			$this->assertStringNotContainsString(
				'width: 100vw;',
				$css,
				"{$slug}: `width: 100vw` counts the classic scrollbar and overflows the viewport. "
				. 'Use calc(100vw - var(--vew-sb, 0px)).'
			);
		}
	}

	/**
	 * No widget may pair a bare `50vw` centring margin with the breakout.
	 *
	 * @return void
	 */
	public function test_no_bare_50vw_margin(): void {
		foreach ( $this->widget_styles() as $slug => $css ) {
			$this->assertStringNotContainsString(
				'calc(50% - 50vw)',
				$css,
				"{$slug}: the centring margin must account for --vew-sb like the width does."
			);
		}
	}

	/**
	 * Every widget that breaks out must subtract --vew-sb, and fall back to 0px.
	 *
	 * @return void
	 */
	public function test_breakout_uses_scrollbar_variable_with_safe_fallback(): void {
		$checked = 0;

		foreach ( $this->widget_styles() as $slug => $css ) {
			if ( ! str_contains( $css, '100vw' ) ) {
				continue;
			}
			++$checked;

			$this->assertStringContainsString(
				'calc(100vw - var(--vew-sb, 0px))',
				$css,
				"{$slug}: full-bleed sizing must subtract --vew-sb."
			);
			$this->assertStringContainsString(
				'var(--vew-sb, 0px)',
				$css,
				"{$slug}: --vew-sb needs a 0px fallback for overlay scrollbars."
			);
		}

		$this->assertGreaterThan( 15, $checked, 'Expected most widget stylesheets to use 100vw.' );
	}

	/**
	 * The metric must actually be published, or every breakout collapses.
	 *
	 * @return void
	 */
	public function test_scrollbar_metric_is_shipped_and_enqueued(): void {
		$script = $this->root() . '/assets/js/scrollbar-metric.js';
		$this->assertFileExists( $script );

		$js = (string) file_get_contents( $script );
		$this->assertStringContainsString( '--vew-sb', $js );
		$this->assertStringContainsString( 'innerWidth - root.clientWidth', $js );

		$site_kit = (string) file_get_contents( $this->root() . '/src/Elementor/SiteKit.php' );
		$this->assertStringContainsString( 'assets/js/scrollbar-metric.js', $site_kit, 'must be enqueued' );
		$this->assertStringContainsString( "'vew-scrollbar-metric'", $site_kit );
	}
}
