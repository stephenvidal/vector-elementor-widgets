/**
 * Vector Elementor Widgets — scrollbar metric.
 *
 * Several widgets render full-bleed by using `width: 100vw` plus a negative
 * inline margin, so a widget placed inside a boxed Elementor container still
 * spans the viewport edge to edge.
 *
 * `100vw` counts the classic scrollbar; `document.documentElement.clientWidth`
 * does not. On a desktop browser with a 15px scrollbar that difference makes the
 * breakout overshoot the viewport and produce a horizontal scrollbar. Widget CSS
 * therefore sizes the breakout as `calc(100vw - var(--vew-sb, 0px))`, and this
 * file publishes the measured difference.
 *
 * The value defaults to 0px, so browsers with overlay scrollbars (iOS, Android,
 * macOS with "show scroll bars: when scrolling") are unaffected — there `100vw`
 * already equals `clientWidth`.
 *
 * Kept in one tiny file rather than per-widget scripts so the metric is measured
 * once for the page.
 */
( function () {
	'use strict';

	var root = document.documentElement;

	/**
	 * Publish the scrollbar width as --vew-sb.
	 *
	 * `innerWidth - clientWidth` is exactly the space the scrollbar occupies
	 * (0 when it overlays the content).
	 *
	 * @return {void}
	 */
	function measure() {
		var width = window.innerWidth - root.clientWidth;
		if ( ! isFinite( width ) || width < 0 ) {
			width = 0;
		}
		root.style.setProperty( '--vew-sb', width + 'px' );
	}

	measure();

	// The scrollbar appears/disappears as content grows or shrinks, so re-measure
	// once everything (including images) has settled, and on viewport changes.
	window.addEventListener( 'load', measure );
	window.addEventListener( 'resize', measure, { passive: true } );

	if ( window.ResizeObserver ) {
		var observer = new ResizeObserver( measure );
		observer.observe( root );
	}
} )();
