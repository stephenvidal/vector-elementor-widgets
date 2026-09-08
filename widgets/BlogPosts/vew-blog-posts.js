/**
 * BlogPosts widget — load-more gating.
 *
 * Reveals hidden blog cards in batches when the "Load more" button is
 * clicked, updating aria-expanded and removing the hidden class so lazy
 * images load only when revealed.
 *
 * @package Vector\ElementorWidgets
 */
( function () {
	'use strict';

	document.querySelectorAll( '.vew-blog-posts' ).forEach( function ( root ) {
		var button = root.querySelector( '[data-vew-blog-more]' );
		if ( ! button ) {
			return;
		}
		var grid = root.querySelector( '.vew-blog-posts__grid' );
		if ( ! grid ) {
			return;
		}
		var perClick = parseInt( root.getAttribute( 'data-vew-per-click' ), 10 ) || 3;
		var hidden = Array.prototype.slice.call( grid.querySelectorAll( '.vew-blog-posts__card--hidden' ) );

		button.addEventListener( 'click', function () {
			var batch = hidden.splice( 0, perClick );
			batch.forEach( function ( card ) {
				card.classList.remove( 'vew-blog-posts__card--hidden' );
			} );
			if ( hidden.length === 0 ) {
				button.hidden = true;
				button.setAttribute( 'aria-expanded', 'true' );
			}
		} );
	} );
} )();
