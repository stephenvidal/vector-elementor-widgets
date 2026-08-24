/**
 * Vector Elementor Widgets — Countdown timer.
 * Vanilla JS, no dependencies. Scope all selectors to .vew-countdown.
 * Reads the ISO-8601 `data-end` target, ticks Days/Hours/Minutes/Seconds every
 * second, and swaps in `data-expired` text when the target passes.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.vew-countdown__clock[data-end]' ).forEach( function ( clock ) {
		var target = new Date( clock.getAttribute( 'data-end' ) ).getTime();
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
