/**
 * Vector Elementor Widgets — FAQ accordion.
 *
 * Vanilla, no-dependency toggle. Each `.vew-faq__question` button toggles
 * its sibling `.vew-faq__answer` panel via `aria-expanded` + the `hidden`
 * attribute (matching the Derby accessibility baseline). Deferred so it runs
 * after the DOM is ready and the Elementor editor has hydrated the widget.
 */
(function () {
	'use strict';

	function initFaq(root) {
		var buttons = root.querySelectorAll('.vew-faq__question');
		buttons.forEach(function (button) {
			if (button.getAttribute('data-vew-bound') === '1') {
				return;
			}
			button.setAttribute('data-vew-bound', '1');
			button.addEventListener('click', function () {
				var open = button.getAttribute('aria-expanded') === 'true';
				var panelId = button.getAttribute('aria-controls');
				var panel = panelId ? root.querySelector('#' + panelId) : null;
				button.setAttribute('aria-expanded', String(!open));
				if (panel) {
					panel.hidden = open; // closing when it was open
				}
			});
		});
	}

	function onReady() {
		document.querySelectorAll('[data-vew-faq]').forEach(initFaq);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', onReady);
	} else {
		onReady();
	}
})();
