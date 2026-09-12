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
	//
	// The announcement strip scrolls away (the host is pinned above the
	// viewport by the strip's height), so the bar that remains pinned is the
	// header WITHOUT the strip. Publishing the full host height here would
	// leave every anchor jump a strip-height too low.
	( function () {
		var hosts = document.querySelectorAll( '.vew-header-host' );
		if ( ! hosts.length ) {
			return;
		}

		var root = document.documentElement;

		/**
		 * Height of the announcement strip inside a host, or 0 when absent.
		 *
		 * @param {Element} host The sticky host element.
		 * @return {number} Strip height in pixels.
		 */
		function bannerHeight( host ) {
			var banner = host.querySelector( '.vew-header__announcement' );
			return banner ? banner.getBoundingClientRect().height : 0;
		}

		function measure() {
			var maxBar = 0;
			hosts.forEach( function ( host ) {
				var banner = bannerHeight( host );
				// Only a strip that scrolls away may be discounted: the
				// pinned bar is then header-only.
				var bar = host.getBoundingClientRect().height - banner;
				if ( banner > 0 ) {
					root.style.setProperty(
						'--vew-header-banner-height',
						Math.ceil( banner ) + 'px'
					);
				} else {
					root.style.removeProperty( '--vew-header-banner-height' );
				}
				if ( bar > maxBar ) {
					maxBar = bar;
				}
			} );
			if ( maxBar > 0 ) {
				root.style.setProperty( '--header-offset', Math.ceil( maxBar ) + 'px' );
				root.style.setProperty( '--header-offset-mobile', Math.ceil( maxBar ) + 'px' );
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
	// floating above the content rather than blending into it. "Pinned" means
	// the announcement strip has finished scrolling away — applying the shadow
	// while the strip is still on screen would lift a bar that is not stuck yet.
	( function () {
		var hosts = document.querySelectorAll( '.vew-header-host' );
		if ( ! hosts.length ) {
			return;
		}

		var ticking = false;

		function update() {
			ticking = false;
			var banner = parseFloat(
				document.documentElement.style.getPropertyValue(
					'--vew-header-banner-height'
				)
			);
			if ( isNaN( banner ) ) {
				banner = 0;
			}
			var scrolled = window.scrollY > Math.max( 8, banner );
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
