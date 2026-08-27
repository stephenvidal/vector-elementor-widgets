/**
 * PortfolioIndex widget frontend behaviour — "Load more" gating.
 *
 * Cards hidden behind the load-more button carry the `--hidden` modifier and
 * are not visually rendered until revealed, so browsers do not request their
 * (lazy) preview images up front.
 *
 * Responsive start: the number of cards shown on first paint is configurable
 * per breakpoint. Desktop/tablet show `data-vew-visible` (default 6 = two rows
 * of three); mobile (single column) shows `data-vew-visible-mobile` (default 4
 * = one column of four rows). On "Load more" click we reveal `data-vew-per-click`
 * additional cards (default 6), updating the button until nothing remains.
 *
 * Vanilla JS, no dependencies. Scope all selectors to .vew-portfolio-index.
 */
( function () {
	'use strict';

	var MOBILE_QUERY = '(max-width: 820px)';

	function isMobile() {
		return window.matchMedia( MOBILE_QUERY ).matches;
	}

	function cardIndex( card ) {
		// Cards are rendered in DOM order; their order in the grid is stable.
		var grid = card.closest( '.vew-portfolio-index__grid' );
		if ( ! grid ) { return 0; }
		return Array.prototype.indexOf.call(
			grid.querySelectorAll( '.vew-portfolio-index__card' ),
			card
		);
	}

	document.querySelectorAll( '[data-vew-portfolio-index]' ).forEach( function ( root ) {
		var more = root.querySelector( '[data-vew-portfolio-more]' );
		if ( ! more ) {
			return;
		}
		var grid = root.querySelector( '.vew-portfolio-index__grid' );
		if ( ! grid ) {
			return;
		}

		var cards = Array.prototype.slice.call(
			grid.querySelectorAll( '.vew-portfolio-index__card' )
		);

		var readNumber = function ( key, fallback ) {
			var v = root.getAttribute( key );
			var n = parseInt( v || '', 10 );
			return isNaN( n ) || n < 1 ? fallback : n;
		};

		var perClick = readNumber( 'data-vew-per-click', 6 );
		var visibleDesktop = readNumber( 'data-vew-visible', 6 );
		var visibleMobile = readNumber( 'data-vew-visible-mobile', 4 );

		// Reveal the correct starting batch for the current breakpoint.
		var applyStart = function () {
			var startCount = isMobile() ? visibleMobile : visibleDesktop;
			cards.forEach( function ( card, i ) {
				var hidden = i >= startCount;
				card.classList.toggle( 'vew-portfolio-index__card--hidden', hidden );
			} );
			updateButton();
		};

		var revealCount = function () {
			var hidden = grid.querySelectorAll( '.vew-portfolio-index__card--hidden' );
			var reveal = Math.min( perClick, hidden.length );
			var toShow = Array.prototype.slice.call( hidden ).slice( 0, reveal );
			toShow.forEach( function ( card ) {
				card.classList.remove( 'vew-portfolio-index__card--hidden' );
			} );
			updateButton();
		};

		var updateButton = function () {
			var hidden = grid.querySelectorAll( '.vew-portfolio-index__card--hidden' ).length;
			if ( hidden === 0 ) {
				more.hidden = true;
				return;
			}
			more.hidden = false;
			more.setAttribute( 'aria-expanded', 'true' );
			more.textContent = 'Load more';
		};

		// Reset the visible batch when the breakpoint flips (desktop -> mobile).
		window.matchMedia( MOBILE_QUERY ).addListener( function () {
			applyStart();
		} );

		more.addEventListener( 'click', function () {
			revealCount();
		} );

		// Initial state.
		applyStart();
	} );
} )();
