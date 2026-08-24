/**
 * Vector Elementor Widgets — Gallery lightbox.
 *
 * Powers the editorial gallery style's lightbox: click an item to open the
 * full-size image with prev/next navigation, keyboard support (Esc, arrows),
 * and body scroll-lock while open.
 *
 * @package Vector\ElementorWidgets
 */
(function () {
	'use strict';

	var lightbox = null;
	var lightboxIndex = 0;
	var currentGalleryItems = [];
	var lastTrigger = null;

	/**
	 * Build the lightbox overlay DOM using createElement + textContent only
	 * (never string-concatenating editor-supplied data into markup) so
	 * editor-set alt/label can never break out of an attribute or inject
	 * markup.
	 *
	 * @return {Element} The overlay element.
	 */
	function buildOverlay() {
		var overlay = document.createElement('div');
		overlay.className = 'vew-lightbox';
		overlay.setAttribute('role', 'dialog');
		overlay.setAttribute('aria-modal', 'true');
		overlay.setAttribute('aria-label', 'Project image viewer');

		var close = document.createElement('button');
		close.type = 'button';
		close.className = 'vew-lightbox__close';
		close.setAttribute('aria-label', 'Close image viewer');
		close.textContent = '\u00d7';

		var prev = document.createElement('button');
		prev.type = 'button';
		prev.className = 'vew-lightbox__prev';
		prev.setAttribute('aria-label', 'Previous image');
		prev.textContent = '\u2190';

		var next = document.createElement('button');
		next.type = 'button';
		next.className = 'vew-lightbox__next';
		next.setAttribute('aria-label', 'Next image');
		next.textContent = '\u2192';

		var figure = document.createElement('figure');
		var img = document.createElement('img');
		var caption = document.createElement('figcaption');

		figure.appendChild(img);
		figure.appendChild(caption);

		overlay.appendChild(close);
		overlay.appendChild(prev);
		overlay.appendChild(figure);
		overlay.appendChild(next);

		return overlay;
	}

	function openLightbox(index) {
		if (!currentGalleryItems.length || index < 0 || index >= currentGalleryItems.length) {
			return;
		}
		lightboxIndex = index;
		var item = currentGalleryItems[lightboxIndex];

		var overlay = buildOverlay();
		var img = overlay.querySelector('img');
		var caption = overlay.querySelector('figcaption');

		img.src = item.src;
		img.alt = item.alt || '';
		caption.textContent = item.label || '';

		document.body.appendChild(overlay);
		document.body.style.overflow = 'hidden';
		lightbox = overlay;
		overlay.querySelector('.vew-lightbox__close').focus();
	}

	function closeLightbox() {
		if (lightbox) {
			lightbox.remove();
			lightbox = null;
			document.body.style.overflow = '';
			if (lastTrigger) {
				lastTrigger.focus();
				lastTrigger = null;
			}
		}
	}

	function moveLightbox(dir) {
		if (!currentGalleryItems.length) {
			return;
		}
		lightboxIndex = (lightboxIndex + dir + currentGalleryItems.length) % currentGalleryItems.length;
		var item = currentGalleryItems[lightboxIndex];
		if (lightbox) {
			var img = lightbox.querySelector('img');
			var cap = lightbox.querySelector('figcaption');
			if (img) { img.src = item.src; img.alt = item.alt || ''; }
			if (cap) { cap.textContent = item.label || ''; }
		}
	}

	/**
	 * Collect this gallery grid's items and store the array ON the grid element.
	 * Scope per-gallery so multiple editorial galleries never desync indices.
	 */
	function collectGallery(grid) {
		var galleryItems = Array.prototype.map.call(
			grid.querySelectorAll('.vew-gallery__item[data-gallery-index]'),
			function (el) {
				var img = el.querySelector('img');
				var label = el.querySelector('.vew-gallery__label');
				return {
					src: img ? img.getAttribute('src') : '',
					alt: img ? img.getAttribute('alt') : '',
					label: label ? label.textContent : ''
				};
			}
		);
		grid.__vewGalleryItems = galleryItems;
	}

	function bindEvents() {
		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('.vew-gallery__item[data-gallery-index]');
			if (trigger) {
				var grid = trigger.closest('.vew-gallery--editorial .vew-gallery__grid');
				var gridItems = grid ? grid.__vewGalleryItems : [];
				var index = parseInt(trigger.getAttribute('data-gallery-index'), 10);
				if (!isNaN(index) && gridItems.length) {
					currentGalleryItems = gridItems;
					lastTrigger = trigger;
					openLightbox(index);
				}
				return;
			}
			if (e.target.closest('.vew-lightbox__close')) { closeLightbox(); return; }
			if (e.target.closest('.vew-lightbox__prev')) { moveLightbox(-1); return; }
			if (e.target.closest('.vew-lightbox__next')) { moveLightbox(1); return; }
			if (e.target.closest('.vew-lightbox') === lightbox && e.target === lightbox) {
				closeLightbox();
			}
		});

		document.addEventListener('keydown', function (e) {
			if (!lightbox) { return; }
			if (e.key === 'Escape') { closeLightbox(); return; }
			if (e.key === 'ArrowLeft') { e.preventDefault(); moveLightbox(-1); return; }
			if (e.key === 'ArrowRight') { e.preventDefault(); moveLightbox(1); return; }
			// Trap focus inside the dialog (Tab / Shift+Tab).
			if (e.key === 'Tab') {
				var focusable = lightbox.querySelectorAll('button');
				if (!focusable.length) { return; }
				var first = focusable[0];
				var last = focusable[focusable.length - 1];
				var active = document.activeElement;
				if (e.shiftKey) {
					if (active === first || active === lightbox) {
						e.preventDefault();
						last.focus();
					}
				} else if (active === last) {
					e.preventDefault();
					first.focus();
				}
			}
		});
	}

	function initCarousel(grid) {
		var nav = grid.closest('.vew-gallery--carousel') && grid.parentElement.querySelector('[data-vew-carousel-nav]');
		if (!nav) { return; }
		var dots = nav.querySelector('[data-vew-carousel-dots]');
		var items = Array.prototype.slice.call(grid.children);

		// Build the dot buttons.
		items.forEach(function (item, i) {
			var dot = document.createElement('button');
			dot.type = 'button';
			dot.className = 'vew-gallery__dot';
			dot.setAttribute('aria-label', 'Go to image ' + (i + 1));
			dot.addEventListener('click', function () { scrollToItem(i); });
			dots.appendChild(dot);
		});

		function scrollToItem(i) {
			var item = items[i];
			if (!item) { return; }
			grid.scrollTo({ left: item.offsetLeft - grid.offsetLeft, behavior: 'smooth' });
			updateDots(i);
		}

		function updateDots(i) {
			Array.prototype.forEach.call(dots.children, function (d, di) {
				d.classList.toggle('is-active', di === i);
			});
		}

		function currentIndex() {
			var scrollLeft = grid.scrollLeft;
			var idx = 0;
			items.forEach(function (item, i) {
				if (item.offsetLeft - grid.offsetLeft <= scrollLeft + 4) { idx = i; }
			});
			return idx;
		}

		nav.addEventListener('click', function (e) {
			var dirEl = e.target.closest('[data-direction]');
			if (!dirEl) { return; }
			var dir = parseInt(dirEl.getAttribute('data-direction'), 10);
			scrollToItem(Math.max(0, Math.min(items.length - 1, currentIndex() + dir)));
		});

		grid.addEventListener('scroll', function () { updateDots(currentIndex()); }, { passive: true });
		updateDots(0);
	}

	function init() {
		document.querySelectorAll('.vew-gallery--editorial .vew-gallery__grid').forEach(function (grid) {
			collectGallery(grid);
		});
		document.querySelectorAll('.vew-gallery--carousel .vew-gallery__grid').forEach(function (grid) {
			initCarousel(grid);
		});
		bindEvents();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
