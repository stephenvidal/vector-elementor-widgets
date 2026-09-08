/**
 * PostGallery widget — lightbox + carousel.
 *
 * Clicking a gallery item opens a full-size lightbox with prev/next
 * navigation, keyboard support (Esc, arrows), and body scroll-lock. The
 * carousel style gets prev/next + dot controls. Items link to their posts,
 * so the lightbox is opened on the anchor's click (preventDefault) and the
 * post link is preserved as the lightbox caption link.
 *
 * Translatable strings are read from data-* attributes emitted by render()
 * (the plugin's escaping-safe path), never hardcoded English.
 *
 * @package Vector\ElementorWidgets
 */
( function () {
	'use strict';

	var lightbox = null;
	var lightboxIndex = 0;
	var currentItems = [];
	var lastTrigger = null;
	var i18n = {
		viewer: 'Post image viewer',
		close: 'Close image viewer',
		prev: 'Previous image',
		next: 'Next image',
		viewPost: 'View post \u2192',
		goToImage: 'Go to image'
	};

	/**
	 * Build the lightbox overlay DOM using createElement + textContent only
	 * (never string-concatenating editor-supplied data into markup).
	 *
	 * @return {Element} The overlay element.
	 */
	function buildOverlay() {
		var overlay = document.createElement( 'div' );
		overlay.className = 'vew-post-gallery__lightbox';
		overlay.setAttribute( 'role', 'dialog' );
		overlay.setAttribute( 'aria-modal', 'true' );
		overlay.setAttribute( 'aria-label', i18n.viewer );
		overlay.setAttribute( 'tabindex', '-1' );

		var close = document.createElement( 'button' );
		close.type = 'button';
		close.className = 'vew-post-gallery__lightbox-close';
		close.setAttribute( 'aria-label', i18n.close );
		close.textContent = '\u00d7';

		var prev = document.createElement( 'button' );
		prev.type = 'button';
		prev.className = 'vew-post-gallery__lightbox-prev';
		prev.setAttribute( 'aria-label', i18n.prev );
		prev.textContent = '\u2190';

		var next = document.createElement( 'button' );
		next.type = 'button';
		next.className = 'vew-post-gallery__lightbox-next';
		next.setAttribute( 'aria-label', i18n.next );
		next.textContent = '\u2192';

		var figure = document.createElement( 'figure' );
		var img = document.createElement( 'img' );
		var caption = document.createElement( 'figcaption' );
		var link = document.createElement( 'a' );

		figure.appendChild( img );
		figure.appendChild( caption );
		figure.appendChild( link );

		overlay.appendChild( close );
		overlay.appendChild( prev );
		overlay.appendChild( figure );
		overlay.appendChild( next );

		return overlay;
	}

	function openLightbox( index ) {
		if ( ! currentItems.length || index < 0 || index >= currentItems.length ) {
			return;
		}
		lightboxIndex = index;
		var item = currentItems[ lightboxIndex ];

		var overlay = buildOverlay();
		var img = overlay.querySelector( 'img' );
		var caption = overlay.querySelector( 'figcaption' );
		var link = overlay.querySelector( 'a' );

		img.src = item.src;
		img.alt = item.title || '';
		caption.textContent = item.title || '';
		link.href = item.permalink || '#';
		link.textContent = item.title ? i18n.viewPost : '';

		document.body.appendChild( overlay );
		document.body.style.overflow = 'hidden';
		lightbox = overlay;
		overlay.querySelector( '.vew-post-gallery__lightbox-close' ).focus();
	}

	function closeLightbox() {
		if ( lightbox ) {
			lightbox.remove();
			lightbox = null;
			document.body.style.overflow = '';
			if ( lastTrigger ) {
				lastTrigger.focus();
				lastTrigger = null;
			}
		}
	}

	function moveLightbox( dir ) {
		if ( ! currentItems.length ) {
			return;
		}
		lightboxIndex = ( lightboxIndex + dir + currentItems.length ) % currentItems.length;
		var item = currentItems[ lightboxIndex ];
		if ( lightbox ) {
			var img = lightbox.querySelector( 'img' );
			var cap = lightbox.querySelector( 'figcaption' );
			var link = lightbox.querySelector( 'a' );
			if ( img ) { img.src = item.src; img.alt = item.title || ''; }
			if ( cap ) { cap.textContent = item.title || ''; }
			if ( link ) { link.href = item.permalink || '#'; link.textContent = item.title ? i18n.viewPost : ''; }
		}
	}

	/**
	 * Collect this gallery grid's items and store the array ON the grid element.
	 * Scope per-gallery so multiple galleries never desync indices.
	 */
	function collectGallery( grid ) {
		var items = Array.prototype.map.call(
			grid.querySelectorAll( '.vew-post-gallery__link[data-gallery-index]' ),
			function ( el ) {
				var img = el.querySelector( 'img' );
				var cap = el.querySelector( '.vew-post-gallery__caption' );
				return {
					src: img ? img.getAttribute( 'src' ) : '',
					title: cap ? cap.textContent : ( img ? img.getAttribute( 'alt' ) : '' ),
					permalink: el.getAttribute( 'href' ) || ''
				};
			}
		);
		grid.__vewPostGalleryItems = items;
	}

	function bindEvents() {
		document.addEventListener( 'click', function ( e ) {
			var trigger = e.target.closest( '.vew-post-gallery__link[data-gallery-index]' );
			if ( trigger ) {
				var grid = trigger.closest( '.vew-post-gallery__grid' );
				var gridItems = grid ? grid.__vewPostGalleryItems : [];
				var index = parseInt( trigger.getAttribute( 'data-gallery-index' ), 10 );
				if ( ! isNaN( index ) && gridItems.length ) {
					e.preventDefault();
					currentItems = gridItems;
					lastTrigger = trigger;
					openLightbox( index );
				}
				return;
			}
			if ( e.target.closest( '.vew-post-gallery__lightbox-close' ) ) { closeLightbox(); return; }
			if ( e.target.closest( '.vew-post-gallery__lightbox-prev' ) ) { moveLightbox( -1 ); return; }
			if ( e.target.closest( '.vew-post-gallery__lightbox-next' ) ) { moveLightbox( 1 ); return; }
			if ( e.target.closest( '.vew-post-gallery__lightbox' ) === lightbox && e.target === lightbox ) {
				closeLightbox();
			}
		} );

		document.addEventListener( 'keydown', function ( e ) {
			if ( ! lightbox ) { return; }
			if ( e.key === 'Escape' ) { closeLightbox(); return; }
			if ( e.key === 'ArrowLeft' ) { e.preventDefault(); moveLightbox( -1 ); return; }
			if ( e.key === 'ArrowRight' ) { e.preventDefault(); moveLightbox( 1 ); return; }
			if ( e.key === 'Tab' ) {
				var focusable = lightbox.querySelectorAll( 'button, a' );
				if ( ! focusable.length ) { return; }
				var first = focusable[ 0 ];
				var last = focusable[ focusable.length - 1 ];
				var active = document.activeElement;
				// If focus has escaped the dialog (e.g. clicked the image), pull it back in.
				if ( ! lightbox.contains( active ) ) {
					e.preventDefault();
					first.focus();
					return;
				}
				if ( e.shiftKey ) {
					if ( active === first ) {
						e.preventDefault();
						last.focus();
					}
				} else if ( active === last ) {
					e.preventDefault();
					first.focus();
				}
			}
		} );
	}

	function initCarousel( grid ) {
		var nav = grid.closest( '.vew-post-gallery--carousel' ) && grid.parentElement.querySelector( '[data-vew-carousel-nav]' );
		if ( ! nav ) { return; }
		var dots = nav.querySelector( '[data-vew-carousel-dots]' );
		var items = Array.prototype.slice.call( grid.children );
		var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		// Make the scroll container keyboard-focusable (WCAG 2.1.1).
		grid.setAttribute( 'tabindex', '0' );

		items.forEach( function ( item, i ) {
			var dot = document.createElement( 'button' );
			dot.type = 'button';
			dot.className = 'vew-post-gallery__dot';
			dot.setAttribute( 'aria-label', i18n.goToImage + ' ' + ( i + 1 ) );
			dot.addEventListener( 'click', function () { scrollToItem( i ); } );
			dots.appendChild( dot );
		} );

		function scrollToItem( i ) {
			var item = items[ i ];
			if ( ! item ) { return; }
			grid.scrollTo( { left: item.offsetLeft - grid.offsetLeft, behavior: reduceMotion ? 'auto' : 'smooth' } );
			updateDots( i );
		}

		function updateDots( i ) {
			Array.prototype.forEach.call( dots.children, function ( d, di ) {
				d.classList.toggle( 'is-active', di === i );
				if ( di === i ) {
					d.setAttribute( 'aria-current', 'true' );
				} else {
					d.removeAttribute( 'aria-current' );
				}
			} );
		}

		function currentIndex() {
			var scrollLeft = grid.scrollLeft;
			var idx = 0;
			items.forEach( function ( item, i ) {
				if ( item.offsetLeft - grid.offsetLeft <= scrollLeft + 4 ) { idx = i; }
			} );
			return idx;
		}

		nav.addEventListener( 'click', function ( e ) {
			var dirEl = e.target.closest( '[data-direction]' );
			if ( ! dirEl ) { return; }
			var dir = parseInt( dirEl.getAttribute( 'data-direction' ), 10 );
			scrollToItem( Math.max( 0, Math.min( items.length - 1, currentIndex() + dir ) ) );
		} );

		grid.addEventListener( 'scroll', function () { updateDots( currentIndex() ); }, { passive: true } );
		updateDots( 0 );
	}

	function init() {
		// Read translatable strings from the section's data attributes.
		var root = document.querySelector( '.vew-post-gallery' );
		if ( root ) {
			i18n.viewer = root.getAttribute( 'data-i18n-viewer' ) || i18n.viewer;
			i18n.close = root.getAttribute( 'data-i18n-close' ) || i18n.close;
			i18n.prev = root.getAttribute( 'data-i18n-prev' ) || i18n.prev;
			i18n.next = root.getAttribute( 'data-i18n-next' ) || i18n.next;
			i18n.viewPost = root.getAttribute( 'data-i18n-view-post' ) || i18n.viewPost;
			i18n.goToImage = root.getAttribute( 'data-i18n-go-to-image' ) || i18n.goToImage;
		}

		document.querySelectorAll( '.vew-post-gallery__grid' ).forEach( function ( grid ) {
			collectGallery( grid );
		} );
		document.querySelectorAll( '.vew-post-gallery--carousel .vew-post-gallery__grid' ).forEach( function ( grid ) {
			initCarousel( grid );
		} );
		bindEvents();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
