/**
 * Vector Elementor Widgets — Countdown timer.
 * Vanilla JS, no dependencies. Scope all selectors to .vew-countdown.
 * Reads the ISO-8601 `data-end` target, ticks Days/Hours/Minutes/Seconds every
 * second, and swaps in `data-expired` text when the target passes.
 */
( function () {
	'use strict';

	/**
	 * Parse a countdown target into epoch milliseconds.
	 *
	 * New markup emits ISO-8601 with an explicit offset, which `new Date()`
	 * handles correctly. Pages saved before that fix still carry a zone-less
	 * 'YYYY-MM-DD HH:MM' string, which `new Date()` would parse in the
	 * VISITOR's timezone — hours out for anyone outside the site timezone.
	 * Treat such a string as UTC-anchored is wrong too, so the server-rendered
	 * offset is required; for legacy values fall back to local parsing (the
	 * previous behaviour) rather than inventing an offset.
	 *
	 * @param {string} raw Attribute value.
	 * @return {number} Epoch milliseconds, or NaN when unparseable.
	 */
	function parseTarget( raw ) {
		if ( ! raw ) {
			return NaN;
		}

		var parsed = Date.parse( raw );
		if ( ! isNaN( parsed ) ) {
			return parsed;
		}

		// Legacy 'YYYY-MM-DD HH:MM' (no zone): normalise the separator so
		// engines with strict ISO parsing still accept it.
		var legacy = /^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})/.exec( raw );
		if ( legacy ) {
			return new Date(
				Number( legacy[ 1 ] ),
				Number( legacy[ 2 ] ) - 1,
				Number( legacy[ 3 ] ),
				Number( legacy[ 4 ] ),
				Number( legacy[ 5 ] )
			).getTime();
		}

		return NaN;
	}

	document.querySelectorAll( '.vew-countdown__clock[data-end]' ).forEach( function ( clock ) {
		var raw = clock.getAttribute( 'data-end' ) || '';
		var target = parseTarget( raw );
		if ( isNaN( target ) ) {
			return;
		}
		var expiredText = clock.getAttribute( 'data-expired' ) || '';
		var numbers = {};
		var units = Array.prototype.slice.call( clock.querySelectorAll( '.vew-countdown__unit' ) );
		units.forEach( function ( unit ) {
			var key = unit.getAttribute( 'data-unit' );
			var num = unit.querySelector( '.vew-countdown__number' );
			if ( key && num ) {
				numbers[ key ] = num;
			}
		} );

		function pad( n ) {
			return String( n ).padStart( 2, '0' );
		}

		function tick() {
			var diff = Math.max( 0, target - Date.now() );
			if ( diff === 0 ) {
				clearInterval( timer );
				if ( expiredText ) {
					clock.textContent = expiredText;
				}
				return;
			}
			var secs = Math.floor( diff / 1000 );
			var days = Math.floor( secs / 86400 );
			var hours = Math.floor( ( secs % 86400 ) / 3600 );
			var minutes = Math.floor( ( secs % 3600 ) / 60 );
			var seconds = secs % 60;
			if ( numbers.days ) { numbers.days.textContent = days; }
			if ( numbers.hours ) { numbers.hours.textContent = pad( hours ); }
			if ( numbers.minutes ) { numbers.minutes.textContent = pad( minutes ); }
			if ( numbers.seconds ) { numbers.seconds.textContent = pad( seconds ); }
		}

		tick();
		var timer = setInterval( tick, 1000 );
	} );
} )();
