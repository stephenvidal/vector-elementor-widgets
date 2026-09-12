/**
 * Vector Elementor Widgets — Header mobile menu toggle.
 * Vanilla JS, no dependencies. Scope all selectors to .vew-header.
 */
( function () {
	'use strict';

	/**
	 * Resolve the top-level Elementor wrapper that should own stickiness.
	 *
	 * A `position: sticky` element only stays pinned while its containing block
	 * is in view, so the header must be sticky on the outermost Elementor
	 * wrapper of its own section. That wrapper differs by Elementor version:
	 * newer builds emit flex containers (`.e-con`), while older/legacy builds
	 * emit section → column → widget. Support both, preferring the outermost
	 * match so the pinned bar spans the full viewport width.
	 *
	 * @param {Element} root The .vew-header element.
	 * @return {Element|null} The wrapper to mark, or null when none is found.
	 */
	function findStickyHost( root ) {
		var selectors = [ '.e-con', '.elementor-section', '.elementor-top-section' ];
		var best = null;
		for ( var i = 0; i < selectors.length; i++ ) {
			var match = root.closest( selectors[ i ] );
			if ( ! match ) {
				continue;
			}
			// Keep the outermost (fewest ancestors) match.
			if ( ! best || isAncestorOf( match, best ) ) {
				best = match;
			}
		}
		return best;
	}

	/**
	 * Whether `candidate` contains `node`.
	 *
	 * @param {Element} candidate Possible ancestor.
	 * @param {Element} node      Possible descendant.
	 * @return {boolean} True when candidate is an ancestor of node.
	 */
	function isAncestorOf( candidate, node ) {
		return candidate !== node && candidate.contains( node );
	}

	document.querySelectorAll( '.vew-header' ).forEach( function ( root ) {
		var host = findStickyHost( root );
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

	// Publish the real header height so anchor jumps offset by exactly the
	// sticky bar's height. A hardcoded offset is wrong the moment the header
	// wraps, the announcement bar is added/removed, or the logo resizes.
	( function () {
		var hosts = document.querySelectorAll( '.vew-header-host' );
		if ( ! hosts.length ) {
			return;
		}

		var root = document.documentElement;

		function measure() {
			var max = 0;
			hosts.forEach( function ( host ) {
				var h = host.getBoundingClientRect().height;
				if ( h > max ) {
					max = h;
				}
			} );
			if ( max > 0 ) {
				root.style.setProperty( '--header-offset', Math.ceil( max ) + 'px' );
			}
		}

		measure();
		window.addEventListener( 'resize', measure, { passive: true } );
		window.addEventListener( 'load', measure );
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( measure );
		}
	} )();

	// Add a subtle lift once the bar is actually pinned, so the header reads as
	// floating above the content rather than blending into it.
	( function () {
		var hosts = document.querySelectorAll( '.vew-header-host' );
		if ( ! hosts.length ) {
			return;
		}

		var ticking = false;

		function update() {
			ticking = false;
			var scrolled = window.scrollY > 8;
			hosts.forEach( function ( host ) {
				host.classList.toggle( 'vew-header-host--scrolled', scrolled );
			} );
		}

		window.addEventListener(
			'scroll',
			function () {
				if ( ! ticking ) {
					ticking = true;
					window.requestAnimationFrame( update );
				}
			},
			{ passive: true }
		);

		update();
	} )();
} )();
