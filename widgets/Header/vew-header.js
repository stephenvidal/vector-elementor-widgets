/**
 * Vector Elementor Widgets — Header mobile menu toggle.
 * Vanilla JS, no dependencies. Scope all selectors to .vew-header.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.vew-header' ).forEach( function ( root ) {
		var host = root.closest( '.e-con' );
		var brand = root.querySelector( '.vew-header__brand' );
		var toggle = root.querySelector( '.vew-header__toggle' );
		var menu = root.querySelector( '.vew-header__mobile' );
		if ( host ) {
			host.classList.add( 'vew-header-host' );
		}
		if ( ! toggle || ! menu ) {
			return;
		}

		function setOpen( open ) {
			menu.hidden = !open;
			toggle.setAttribute( 'aria-expanded', String( open ) );
			toggle.setAttribute( 'aria-label', open ? 'Close menu' : 'Open menu' );
		}

		toggle.addEventListener( 'click', function () {
			setOpen( menu.hidden );
		} );

		if ( brand ) {
			brand.addEventListener( 'click', function () {
				setOpen( false );
			} );
		}

		menu.querySelectorAll( 'a' ).forEach( function ( a ) {
			a.addEventListener( 'click', function () {
				setOpen( false );
			} );
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( toggle.getAttribute( 'aria-expanded' ) !== 'true' ) {
				return;
			}
			if ( event.key === 'Escape' ) {
				setOpen( false );
				toggle.focus();
			}
		} );
	} );
} )();
