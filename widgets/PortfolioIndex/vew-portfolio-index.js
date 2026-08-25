/**
 * PortfolioIndex widget frontend behaviour — "Load more" gating.
 *
 * Cards hidden behind the load-more button carry the `--hidden` modifier and
 * are not visually rendered until the button is clicked, so browsers do not
 * request their (lazy) preview images up front. On click, reveal the next
 * batch and update the button, hiding it once nothing remains.
 *
 * Vanilla JS, no dependencies. Scope all selectors to .vew-portfolio-index.
 */
( function () {
	'use strict';

	document.querySelectorAll( '[data-vew-portfolio-index]' ).forEach( function ( root ) {
		var more = root.querySelector( '[data-vew-portfolio-more]' );
		if ( ! more ) {
			return;
		}
		var grid = root.querySelector( '.vew-portfolio-index__grid' );
		if ( ! grid ) {
			return;
		}

		more.addEventListener( 'click', function () {
			var hidden = Array.prototype.slice.call(
				grid.querySelectorAll( '.vew-portfolio-index__card--hidden' )
			);
			if ( hidden.length === 0 ) {
				more.hidden = true;
				return;
			}
			hidden.forEach( function ( card ) {
				card.classList.remove( 'vew-portfolio-index__card--hidden' );
			} );
			more.setAttribute( 'aria-expanded', 'true' );

			// After revealing, drop the button if nothing is left behind.
			var remaining = grid.querySelectorAll( '.vew-portfolio-index__card--hidden' ).length;
			if ( remaining === 0 ) {
				more.hidden = true;
			} else {
				var label = remaining + ' more';
				more.textContent = label;
			}
		} );
	} );
} )();
