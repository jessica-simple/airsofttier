(function () {
	'use strict';

	function normalize(value) {
		var normalized = String(value || '').toLowerCase();

		if (normalized.normalize) {
			normalized = normalized.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
		}

		return normalized;
	}

	function initBrandsGrid(container) {
		var input = container.querySelector('.wc-brands-grid__search');
		var items = Array.prototype.slice.call(container.querySelectorAll('.wc-brands-grid__item'));
		var noResults = container.querySelector('.wc-brands-grid__no-results');

		if (!input || !items.length) {
			return;
		}

		function filterBrands() {
			var query = normalize(input.value.trim());
			var visibleCount = 0;

			items.forEach(function (item) {
				var brandName = normalize(item.getAttribute('data-brand-name'));
				var isMatch = !query || brandName.indexOf(query) !== -1;

				item.hidden = !isMatch;

				if (isMatch) {
					visibleCount += 1;
				}
			});

			if (noResults) {
				noResults.hidden = visibleCount !== 0;
			}
		}

		input.addEventListener('input', filterBrands);
		filterBrands();
	}

	function init() {
		document.querySelectorAll('[data-wc-brands-grid]').forEach(initBrandsGrid);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
