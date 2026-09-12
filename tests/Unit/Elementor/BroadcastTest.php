<?php
/**
 * Broadcast widget contract tests.
 *
 * Lexical/source guards, matching the suite's existing convention of asserting
 * against source because building a real Elementor control stack needs
 * Elementor\Widget_Base. Each assertion here pins a requirement that would
 * otherwise fail silently on the live page.
 *
 * @package Vector\ElementorWidgets\Tests\Unit\Elementor
 */

declare( strict_types=1 );

namespace Vector\ElementorWidgets\Tests\Unit\Elementor;

use Vector\ElementorWidgets\Tests\Unit\TestCase;

final class BroadcastTest extends TestCase {

	/**
	 * Read a plugin source file.
	 *
	 * @param string $relative Path relative to the plugin root.
	 *
	 * @return string
	 */
	private function source( string $relative ): string {
		$path = dirname( __DIR__, 3 ) . '/' . $relative;
		$this->assertFileExists( $path, "Missing source file: {$relative}" );
		$contents = file_get_contents( $path );
		$this->assertIsString( $contents );
		return $contents;
	}

	/**
	 * The widget registers under a stable slug with its assets.
	 *
	 * @return void
	 */
	public function test_widget_identity_and_assets(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( "return 'vew-broadcast';", $widget );
		$this->assertStringContainsString( "'vew-section-heading', 'vew-broadcast'", $widget, 'stylesheet deps' );
		$this->assertStringContainsString( "return array( 'vew-broadcast' );", $widget, 'script deps' );
	}

	/**
	 * The schedule is resolved server-side in the site timezone — the whole
	 * point of the design. A client-side `new Date()` on a zone-less string is
	 * parsed in the visitor's zone and puts the countdown hours out.
	 *
	 * @return void
	 */
	public function test_schedule_resolved_server_side_in_site_timezone(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'wp_timezone()', $widget );
		$this->assertStringContainsString( 'new Schedule(', $widget );
	}

	/**
	 * Every upcoming occurrence is emitted so a cached page can roll forward
	 * without a reload.
	 *
	 * @return void
	 */
	public function test_payload_ships_all_upcoming_segments(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'upcoming_payload', $widget );
		$this->assertStringContainsString( 'data-segments', $widget );
	}

	/**
	 * The play button exists in the markup but is hidden until live, so the
	 * link is present for SEO/no-JS while never being actionable early.
	 *
	 * @return void
	 */
	public function test_watch_link_hidden_until_live(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'data-broadcast-watch', $widget );
		$this->assertStringContainsString( 'hidden', $widget );
	}

	/**
	 * A per-second countdown must never be announced to screen readers.
	 *
	 * @return void
	 */
	public function test_countdown_is_not_a_live_region(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );
		$js     = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( 'aria-hidden="true"', $widget, 'visual counter must be aria-hidden' );
		$this->assertStringContainsString( 'aria-live="polite"', $widget, 'a polite status node exists' );

		// The polite node is a separate threshold-only announcer.
		$this->assertStringContainsString( 'data-broadcast-announce', $js );
		$this->assertStringNotContainsString(
			'clockValue.setAttribute( \'aria-live\'',
			$js,
			'The ticking node must not itself be a live region.'
		);
	}

	/**
	 * Motion is suppressed for users who ask for it.
	 *
	 * @return void
	 */
	public function test_respects_reduced_motion(): void {
		$css = $this->source( 'widgets/Broadcast/vew-broadcast.css' );

		$this->assertStringContainsString( 'prefers-reduced-motion: reduce', $css );
	}

	/**
	 * The empty state renders something, so a misconfigured block is never a
	 * blank hole in the page.
	 *
	 * @return void
	 */
	public function test_empty_state_has_fallback_copy(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );
		$css    = $this->source( 'widgets/Broadcast/vew-broadcast.css' );

		$this->assertStringContainsString( 'vew-broadcast__empty', $widget );
		$this->assertStringContainsString( '.vew-broadcast__empty', $css );
	}

	/**
	 * Responsive collapse: single column under 900px, tighter gutters under
	 * 820px. Mobile QA is mandatory for every deliverable.
	 *
	 * @return void
	 */
	public function test_responsive_rules_present(): void {
		$css = $this->source( 'widgets/Broadcast/vew-broadcast.css' );

		$this->assertStringContainsString( '@media (max-width: 900px)', $css );
		$this->assertStringContainsString( '@media (max-width: 820px)', $css );
		$this->assertStringContainsString( 'grid-template-columns: 1fr', $css );
	}

	/**
	 * Long labels must never clip horizontally.
	 *
	 * @return void
	 */
	public function test_long_text_cannot_clip(): void {
		$css = $this->source( 'widgets/Broadcast/vew-broadcast.css' );

		$this->assertStringContainsString( 'min-width: 0', $css );
		$this->assertStringContainsString( 'overflow-wrap: anywhere', $css );
	}

	/**
	 * The theme is kit-driven, not hardcoded: no baked background on the themed
	 * surface, so a dark kit renders correctly.
	 *
	 * @return void
	 */
	public function test_uses_kit_tokens(): void {
		$css = $this->source( 'widgets/Broadcast/vew-broadcast.css' );

		$this->assertStringContainsString( 'var(--brand', $css );
		$this->assertStringContainsString( 'var(--accent', $css );
		$this->assertStringContainsString( 'var(--on-brand', $css );
	}

	/**
	 * The preview override must be gated on capability, or a visitor would see
	 * a shifted clock and the wrong segment.
	 *
	 * @return void
	 */
	public function test_preview_override_is_capability_gated(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'current_user_can', $widget );
		$this->assertStringContainsString( "'edit_posts'", $widget );
		$this->assertStringContainsString( 'preview_offset', $widget );
	}

	/**
	 * The preview offset is validated against an allow-list, so a tampered
	 * value cannot shift the clock arbitrarily.
	 *
	 * @return void
	 */
	public function test_preview_offset_is_allow_listed(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( "\$allowed = array(", $widget );
		$this->assertStringContainsString( "in_array( \$preview_offset, \$allowed, true )", $widget );
	}

	/**
	 * A calendar link is offered for the next segment.
	 *
	 * @return void
	 */
	public function test_calendar_link_is_generated(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'BEGIN:VCALENDAR', $widget );
		$this->assertStringContainsString( 'BEGIN:VEVENT', $widget );
		$this->assertStringContainsString( 'text/calendar', $widget );
	}

	/**
	 * Structured data is emitted for the active broadcast.
	 *
	 * @return void
	 */
	public function test_emits_broadcast_event_schema(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'BroadcastEvent', $widget );
		$this->assertStringContainsString( 'application/ld+json', $widget );
	}

	/**
	 * The countdown timer pauses when the tab is hidden, so background tabs do
	 * not tick once a second forever.
	 *
	 * @return void
	 */
	public function test_timer_pauses_when_tab_hidden(): void {
		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( 'visibilitychange', $js );
		$this->assertStringContainsString( 'clearInterval', $js );
	}

	/**
	 * The client re-derives the active segment rather than trusting the
	 * server-rendered one, which is what makes a cached page self-correcting.
	 *
	 * @return void
	 */
	public function test_client_resolves_active_segment(): void {
		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( 'pickActive', $js );
		$this->assertStringContainsString( 'data-segments', $js );
	}

	/**
	 * The catalog entry is finished (no scaffold placeholder).
	 *
	 * @return void
	 */
	public function test_catalog_entry_is_complete(): void {
		$catalog = $this->source( 'src/Elementor/WidgetCatalog.php' );

		$this->assertStringNotContainsString( 'A Broadcast component', $catalog );
		$this->assertStringContainsString( 'vew-broadcast', $catalog );
		$this->assertStringContainsString( 'eicon-countdown', $catalog );
	}

	/**
	 * The countdown widget's timezone fix landed (Phase A2): it must emit
	 * ISO-8601 with an explicit offset.
	 *
	 * @return void
	 */
	public function test_countdown_emits_timezone_aware_iso(): void {
		$widget = $this->source( 'src/Elementor/Widget/Countdown.php' );

		$this->assertStringContainsString( 'wp_timezone()', $widget );
		$this->assertMatchesRegularExpression(
			'/format\(\s*[\'"]c[\'"]\s*\)/',
			$widget,
			'Countdown must emit ISO-8601 (format "c") so the offset is explicit.'
		);
	}
}
