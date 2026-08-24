/**
 * Vector Elementor Widgets — Before/After comparison slider.
 * Vanilla JS, no dependencies. Scope all selectors to .vew-before-after__stage.
 * Pointer-drag + keyboard-accessible divider. Horizontal clips the "after"
 * layer from the left; vertical clips from the top.
 */
( function () {
	'use strict';

	document.querySelectorAll( '.vew-before-after__stage' ).forEach( function ( stage ) {
		var after = stage.querySelector( '.vew-before-after__after' );
		var handle = stage.querySelector( '.vew-before-after__handle' );
		if ( ! after || ! handle ) {
			return;
		}
		var orientation = stage.getAttribute( 'data-orientation' ) || 'horizontal';
		var value = 50;

		function apply() {
			if ( orientation === 'vertical' ) {
				after.style.clipPath = 'inset(' + value + '% 0 0 0)';
				handle.style.top = value + '%';
			} else {
				after.style.clipPath = 'inset(0 0 0 ' + value + '%)';
				handle.style.left = value + '%';
			}
			handle.setAttribute( 'aria-valuenow', String( Math.round( value ) ) );
		}

		function setFromClient( clientX, clientY ) {
			var rect = stage.getBoundingClientRect();
			var pct;
			if ( orientation === 'vertical' ) {
				pct = rect.height ? ( ( clientY - rect.top ) / rect.height ) * 100 : 50;
			} else {
				pct = rect.width ? ( ( clientX - rect.left ) / rect.width ) * 100 : 50;
			}
			value = Math.max( 0, Math.min( 100, pct ) );
			apply();
		}

		handle.addEventListener( 'pointerdown', function ( e ) {
			e.preventDefault();
			handle.setPointerCapture( e.pointerId );
			var move = function ( ev ) {
				setFromClient( ev.clientX, ev.clientY );
			};
			var up = function () {
				handle.removeEventListener( 'pointermove', move );
				handle.removeEventListener( 'pointerup', up );
				handle.removeEventListener( 'pointercancel', up );
			};
			handle.addEventListener( 'pointermove', move );
			handle.addEventListener( 'pointerup', up );
			handle.addEventListener( 'pointercancel', up );
		} );

		// Keyboard accessible: Left/Right (or Up/Down) adjust by 5.
		handle.addEventListener( 'keydown', function ( e ) {
			var step = 5;
			if ( e.key === 'ArrowLeft' || e.key === 'ArrowUp' ) {
				e.preventDefault();
				value = Math.max( 0, value - step );
				apply();
			} else if ( e.key === 'ArrowRight' || e.key === 'ArrowDown' ) {
				e.preventDefault();
				value = Math.min( 100, value + step );
				apply();
			} else if ( e.key === 'Home' ) {
				e.preventDefault();
				value = 0;
				apply();
			} else if ( e.key === 'End' ) {
				e.preventDefault();
				value = 100;
				apply();
			}
		} );

		apply();
	} );
} )();
