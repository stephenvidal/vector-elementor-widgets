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
	 * The play button must be visible in the LIVE state server-side.
	 *
	 * Recomputing state client-side is not enough: with `hidden` baked into the
	 * markup for every state (the original implementation), the button stayed
	 * display:none even while live. It also means the control works with JS
	 * disabled, and for crawlers.
	 *
	 * @return void
	 */
	public function test_watch_and_secondary_render_visible_when_live(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( '$is_live = Segment::STATE_LIVE === $state;', $widget );
		$this->assertStringContainsString( "echo \$is_live ? '' : ' hidden';", $widget );
		$this->assertSame(
			2,
			substr_count( $widget, "echo \$is_live ? '' : ' hidden';" ),
			'Both the watch and secondary stream links must honour the live state.'
		);
	}

	/**
	 * An empty stream URL must not render a dead "Watch now" button.
	 *
	 * @return void
	 */
	public function test_watch_button_requires_a_stream_url(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( "if ( '' !== \$segment->stream_url() ) :", $widget );
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

	/**
	 * The calendar link survives escaping.
	 *
	 * The .ics payload is a data: URL, and esc_url() strips it (its protocol
	 * allow-list excludes data:). Escaping it with esc_url() shipped an empty
	 * href — the link rendered but did nothing.
	 *
	 * @return void
	 */
	public function test_calendar_href_is_not_escaped_through_esc_url(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringNotContainsString(
			'esc_url( $this->calendar_url(',
			$widget,
			'esc_url() strips data: URLs, emptying the .ics href.'
		);
		$this->assertStringContainsString( 'href="<?php echo esc_attr( $calendar_href ); ?>"', $widget );
	}

	/**
	 * The site-time line must be formatted in the SITE timezone.
	 *
	 * Formatting it in the visitor's zone contradicted the widget's own
	 * "times shown in Eastern Time" note and disagreed with the countdown
	 * (which targets the site-time instant). Live check: a UTC viewer saw
	 * "3:00 PM" for an 11:00 AM ET service.
	 *
	 * @return void
	 */
	public function test_primary_time_line_uses_site_timezone(): void {
		$js     = $this->source( 'widgets/Broadcast/vew-broadcast.js' );
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'data-site-timezone', $widget, 'site zone must reach the client' );
		$this->assertStringContainsString( 'data-site-timezone', $js );
		$this->assertStringContainsString( 'whenOpts.timeZone = siteZone', $js,
			'The primary time line must be formatted in the site timezone.' );
	}

	/**
	 * In preview mode the client must not recompute state from the real clock,
	 * or the server's deliberately shifted preview would be undone in the
	 * browser and no state could ever be previewed.
	 *
	 * @return void
	 */
	public function test_preview_mode_is_not_overridden_by_the_client_clock(): void {
		$js     = $this->source( 'widgets/Broadcast/vew-broadcast.js' );
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'data-preview', $widget, 'the flag must reach the client' );
		$this->assertStringContainsString( 'data-preview', $js );
		$this->assertStringContainsString( 'if ( previewActive )', $js, 'preview must short-circuit the tick loop' );
	}

	/**
	 * Preview offsets must anchor to the NEXT SEGMENT START, not to "now".
	 *
	 * Anchored to now, a relative offset is useless for verification: the
	 * service is normally hours away, so "+5 min" never reaches the start and
	 * every preview renders identically to "upcoming".
	 *
	 * @return void
	 */
	public function test_preview_offsets_anchor_to_next_start(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		$this->assertStringContainsString( 'function next_start(', $widget );
		$this->assertStringContainsString( 'return $anchor->modify( $preview_offset );', $widget );
		$this->assertStringNotContainsString( 'return $now->modify( $preview_offset );', $widget );
	}

	/**
	 * A preview must still show a countdown duration rather than a placeholder.
	 *
	 * @return void
	 */
	public function test_preview_fills_the_countdown(): void {
		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( "'upcoming' === previewState", $js );
		$this->assertStringContainsString( 'clockValue.textContent = humanise(', $js );
	}

	/**
	 * Visitor-local time complements the site line rather than replacing it,
	 * and is suppressed when the viewer is already in the site zone.
	 *
	 * @return void
	 */
	public function test_visitor_local_time_is_supplementary(): void {
		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( 'function renderLocalTime(', $js );
		$this->assertStringContainsString( "node.hidden = true", $js, 'suppress when redundant' );
		$this->assertStringContainsString( "'data-local-prefix'", $js );
	}

	/**
	 * The embedded live player (HLS) render path is gated on the embed_live
	 * toggle and an embeddable stream URL. It is deliberately NOT gated on the
	 * live state: a page cached before the service starts must already carry
	 * the player so the client can reveal and start it on the transition.
	 *
	 * @return void
	 */
	public function test_embedded_player_requires_toggle_and_embeddable_url(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		// Toggle is read from sanitized settings.
		$this->assertStringContainsString( "'embed_live'            => 'string',", $widget );
		$this->assertStringContainsString( 'can_embed( $safe, $segment )', $widget );
		// Player + data-hls-url + lib URL are emitted.
		$this->assertStringContainsString( 'data-broadcast-player', $widget );
		$this->assertStringContainsString( 'data-hls-url', $widget );
		$this->assertStringContainsString( 'data-hls-lib', $widget );
		// The deep-link fallback stays for non-embeddable streams.
		$this->assertStringContainsString( 'data-broadcast-watch', $widget );
	}

	/**
	 * hls.js is self-hosted (tracked in assets/) and registered so the
	 * broadcast JS can inject it lazily, rather than a fragile external CDN.
	 *
	 * @return void
	 */
	public function test_live_segment_is_prepended_to_payload_for_client_resolution(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		// A live segment must ride in `data-segments` so a cached page's client
		// re-resolve keeps it live instead of recomputing it to "upcoming".
		$this->assertStringContainsString( 'upcoming_payload( $now )', $widget );
		$this->assertStringContainsString( "STATE_LIVE === ( \$active['state'] ?? '' )", $widget );
		$this->assertStringContainsString( 'array_unshift( $upcoming, $live_row )', $widget );
		$this->assertStringContainsString( "=> \$active['starts_at']", $widget );
	}

	/**
	 * hls.js is self-hosted (tracked in assets/) and registered so the
	 * broadcast JS can inject it lazily, rather than a fragile external CDN.
	 *
	 * @return void
	 */
	public function test_hls_library_is_self_hosted_and_registered(): void {
		$this->assertFileExists(
			dirname( __DIR__, 3 ) . '/assets/js/hls.min.js',
			'hls.js must ship with the plugin, not load from a CDN.'
		);

		$plugin = $this->source( 'src/Plugin.php' );
		$this->assertStringContainsString( "'vew-hlsjs', 'assets/js/hls.min.js'", $plugin );

		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );
		$this->assertStringContainsString( 'function initHlsPlayer(', $js );
		$this->assertStringContainsString( 'Hls.isSupported()', $js );
	}

	/**
	 * The embedded player uses native HLS playback where the browser supports
	 * it (Safari) and falls back to hls.js otherwise, so no code path depends
	 * on hls.js being present for native-capable browsers.
	 *
	 * @return void
	 */
	public function test_player_falls_back_to_native_hls(): void {
		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( 'application/vnd.apple.mpegurl', $js, 'the native HLS MIME is probed' );
		$this->assertStringContainsString( 'window.Hls', $js );
	}

	/**
	 * Clicking "Watch now" starts the in-page player instead of deep-linking.
	 *
	 * An .m3u8 opened in a new tab is a playlist — a download, or a wall of
	 * text — not the service. The click must be intercepted whenever an inline
	 * player exists, while the href (and modifier-clicks) still work for no-JS,
	 * crawlers, and a visitor who deliberately wants the stream itself.
	 *
	 * @return void
	 */
	public function test_watch_action_starts_the_inline_player(): void {
		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( 'watch.addEventListener(', $js, 'the play action is intercepted' );
		$this->assertStringContainsString( 'event.preventDefault()', $js );
		$this->assertStringContainsString( 'startPlayer()', $js );
		// Modifier-clicks must still reach the stream URL.
		$this->assertStringContainsString( 'event.metaKey', $js );
		$this->assertStringContainsString( 'event.ctrlKey', $js );
		$this->assertStringContainsString( 'event.shiftKey', $js );
	}

	/**
	 * A page cached before the service started must still be able to play it.
	 *
	 * The player is server-rendered for every embeddable segment (not only the
	 * live state) precisely so a full-page-cached render carries one; the
	 * client reveals and starts it when the segment rolls live, and stops it
	 * again when the service ends.
	 *
	 * @return void
	 */
	public function test_player_is_rendered_for_cached_pages_and_toggled_by_state(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );

		// Rendered on embeddability alone, no longer gated on the live state.
		$this->assertStringContainsString( '$can_embed = $this->can_embed( $safe, $segment );', $widget );
		$this->assertStringContainsString( '<?php if ( $can_embed ) : ?>', $widget );
		$this->assertStringNotContainsString( '$is_live && $this->can_embed(', $widget );

		// Visibility is attribute-toggled, and the preview never reveals it.
		$this->assertStringContainsString( '$show_player = $is_live && $can_embed && ! $this->is_preview();', $widget );
		$this->assertStringContainsString( '$thumb_attr = $show_player ? \' hidden\' : \'\';', $widget );

		// The client owns the state transitions.
		$js = $this->source( 'widgets/Broadcast/vew-broadcast.js' );
		$this->assertStringContainsString( 'function startPlayer()', $js );
		$this->assertStringContainsString( 'function stopPlayer()', $js );
		$this->assertStringContainsString( 'function showPlayer(', $js );
	}

	/**
	 * The widget tells the client whether it may embed at all, so the button
	 * can fall back to the deep-link when embedding is switched off.
	 *
	 * @return void
	 */
	public function test_widget_exposes_the_embed_toggle_to_the_client(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );
		$js     = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( 'data-embed-live=', $widget );
		$this->assertStringContainsString( "'data-embed-live'", $js );
		$this->assertStringContainsString( 'embedLive', $js, 'the client honours the toggle' );
	}

	/**
	 * A hidden <video src> still prefetches, so a direct-play URL is parked on
	 * data-src until the player is revealed; an HLS URL is never resolved by
	 * the browser on its own and stays on data-hls-url.
	 *
	 * @return void
	 */
	public function test_direct_stream_url_is_deferred_until_playback(): void {
		$widget = $this->source( 'src/Elementor/Widget/Broadcast.php' );
		$js     = $this->source( 'widgets/Broadcast/vew-broadcast.js' );

		$this->assertStringContainsString( "data-src=", $widget, 'the direct URL is parked while hidden' );
		$this->assertStringContainsString( "'data-hls-url=\"'", $widget, 'HLS stays on its own attribute' );
		$this->assertStringContainsString( "player.getAttribute( 'data-src' )", $js );
		$this->assertStringContainsString( "player.setAttribute( 'src', directSrc )", $js );
	}

	/**
	 * Both media elements set `display`, so the UA's `[hidden] { display:none }`
	 * loses to them and they would stack. The explicit rules are load-bearing.
	 *
	 * @return void
	 */
	public function test_media_hidden_rules_are_explicit(): void {
		$css = $this->source( 'widgets/Broadcast/vew-broadcast.css' );

		$this->assertStringContainsString( '.vew-broadcast__player[hidden],', $css );
		$this->assertStringContainsString( '.vew-broadcast__thumb[hidden] {', $css );
		$this->assertStringContainsString( 'display: none;', $css );
	}
}
