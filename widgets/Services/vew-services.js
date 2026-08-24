/**
 * Vector Elementor Widgets — Services WAI-ARIA tabs.
 * Vanilla JS, no dependencies. Scope all selectors to .vew-services.
 * Implements the tabs pattern: role="tablist"/"tab"/"tabpanel", aria-selected,
 * arrow-key roving focus (Left/Right), and Home/End. Moves the sliding
 * indicator and swaps the active panel.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.vew-services' ).forEach( function ( root ) {
		var tabs = Array.prototype.slice.call( root.querySelectorAll( '.vew-services__tab' ) );
		var indicator = root.querySelector( '.vew-services__indicator' );
		var panels = Array.prototype.slice.call( root.querySelectorAll( '.vew-services__grid' ) );

		function moveIndicator( active ) {
			if ( ! indicator || ! active || ! indicator.parentElement ) {
				return;
			}
			var activeRect = active.getBoundingClientRect();
			var switchRect = indicator.parentElement.getBoundingClientRect();
			var translateX = activeRect.left - switchRect.left - indicator.offsetLeft;
			indicator.style.width = activeRect.width + 'px';
			indicator.style.transform = 'translateX(' + translateX + 'px)';
		}

		// Select a tab; when moveFocus is true, move keyboard focus to it too.
		function selectTab( tab, moveFocus ) {
			tabs.forEach( function ( t ) {
				var on = t === tab;
				t.setAttribute( 'aria-selected', String( on ) );
				t.tabIndex = on ? 0 : -1;
				if ( on && moveFocus ) {
					t.focus();
				}
			} );
			var name = tab.getAttribute( 'data-variant' );
			panels.forEach( function ( panel ) {
				panel.hidden = panel.getAttribute( 'data-variant-panel' ) !== name;
			} );
			moveIndicator( tab );
		}

		tabs.forEach( function ( tab, index ) {
			// Mouse click selects without moving focus back.
			tab.addEventListener( 'click', function () {
				selectTab( tab, false );
			} );
			// Arrow keys rove focus AND select the target tab.
			tab.addEventListener( 'keydown', function ( e ) {
				var nextIndex;
				switch ( e.key ) {
					case 'ArrowLeft':
					case 'ArrowUp':
						e.preventDefault();
						nextIndex = ( index - 1 + tabs.length ) % tabs.length;
						selectTab( tabs[ nextIndex ], true );
						break;
					case 'ArrowRight':
					case 'ArrowDown':
						e.preventDefault();
						nextIndex = ( index + 1 ) % tabs.length;
						selectTab( tabs[ nextIndex ], true );
						break;
					case 'Home':
						e.preventDefault();
						selectTab( tabs[ 0 ], true );
						break;
					case 'End':
						e.preventDefault();
						selectTab( tabs[ tabs.length - 1 ], true );
						break;
				}
			} );
		} );

		// Initial position + responsive reflow.
		var initial = root.querySelector( '.vew-services__tab[aria-selected="true"]' ) || tabs[ 0 ];
		if ( initial ) {
			moveIndicator( initial );
		}
		window.addEventListener( 'resize', function () {
			var active = root.querySelector( '.vew-services__tab[aria-selected="true"]' ) || tabs[ 0 ];
			moveIndicator( active );
		} );
	} );
} )();
