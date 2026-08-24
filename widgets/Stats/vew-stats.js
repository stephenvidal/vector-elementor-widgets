/**
 * Vector Elementor Widgets — Stats count-up animation.
 * Vanilla JS, no dependencies. Scope all selectors to .vew-stats.
 * Animates the leading numeric target of each .vew-stats__value from 0 to its
 * data-count on scroll-into-view. Gated by prefers-reduced-motion. Prefix and
 * suffix (e.g. "+", "%", "k") are preserved from the rendered text.
 */
( function () {
	'use strict';

	if ( window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return; // Respect reduced-motion: leave the static values in place.
	}

	if ( ! ( 'IntersectionObserver' in window ) ) {
		return; // No IO support — static values are already correct.
	}

	document.querySelectorAll( '.vew-stats[data-vew-stats]' ).forEach( function ( root ) {
		var values = Array.prototype.slice.call( root.querySelectorAll( '.vew-stats__value[data-count]' ) );

		if ( ! values.length ) {
			return;
		}

		function format( target, current ) {
			var decimals = ( String( target ).split( '.' )[ 1 ] || '' ).length;
			return decimals ? current.toFixed( decimals ) : String( Math.round( current ) );
		}

		function animate( el ) {
			var raw = el.getAttribute( 'data-count' );
			var target = parseFloat( raw );
			if ( isNaN( target ) ) {
				return;
			}
			var full = el.textContent; // e.g. "42%", "+12", "1.5k"
			var prefix = ( full.match( /^[^\d.-]+/ ) || [ '' ] )[ 0 ];
			var suffix = ( full.match( /[^\d.-]+$/ ) || [ '' ] )[ 0 ];
			var decimals = ( String( raw ).split( '.' )[ 1 ] || '' ).length;

			var duration = 1400;
			var start = null;
			function step( ts ) {
				if ( start === null ) { start = ts; }
				var p = Math.min( 1, ( ts - start ) / duration );
				// easeOutCubic
				var eased = 1 - Math.pow( 1 - p, 3 );
				var val = target * eased;
				var out = decimals ? val.toFixed( decimals ) : String( Math.round( val ) );
				el.textContent = prefix + out + suffix;
				if ( p < 1 ) {
					requestAnimationFrame( step );
				} else {
					el.textContent = prefix + ( decimals ? target.toFixed( decimals ) : String( target ) ) + suffix;
				}
			}
			requestAnimationFrame( step );
		}

		var observer = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					animate( entry.target );
					obs.unobserve( entry.target );
				}
			} );
		}, { threshold: 0.4 } );

		values.forEach( function ( el ) { observer.observe( el ); } );
	} );
} )();
