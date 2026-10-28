(function () {
	'use strict';

	var registered = false;

	function escapeAttribute(value) {
		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
	}

	function timerMarkup(deal, productId) {
		if (!deal.expiry) {
			return '';
		}

		return '<span class="gb-timer gb-timer-compact" data-product-id="' + productId +
			'" data-expiry="' + escapeAttribute(deal.expiry) + '" data-expiry-ts="' +
			(parseInt(deal.expiry_ts, 10) || 0) + '">' +
			'<span class="gb-time-unit"><span class="gb-unit-value gb-days">00</span><span class="gb-unit-label">Days</span></span>' +
			'<span class="gb-time-separator">:</span>' +
			'<span class="gb-time-unit"><span class="gb-unit-value gb-hours">00</span><span class="gb-unit-label">Hrs</span></span>' +
			'<span class="gb-time-separator">:</span>' +
			'<span class="gb-time-unit"><span class="gb-unit-value gb-mins">00</span><span class="gb-unit-label">Mins</span></span>' +
			'<span class="gb-time-separator">:</span>' +
			'<span class="gb-time-unit"><span class="gb-unit-value gb-secs">00</span><span class="gb-unit-label">Secs</span></span>' +
			'</span>';
	}

	function dealMarker(deal, productId) {
		return ' [[GBDEAL|' + productId + '|' + encodeURIComponent(deal.expiry || '') + '|' +
			(parseInt(deal.expiry_ts, 10) || 0) + ']]';
	}

	function createDealMarkup(deal, productId) {
		var dealMarkup = document.createElement('span');
		dealMarkup.className = 'gb-cart-deal';
		dealMarkup.dataset.productId = productId;
		dealMarkup.innerHTML = '<span class="gb-cart-deal-badge">LIMITED DEALS</span>' +
			timerMarkup(deal, productId);

		return dealMarkup;
	}

	function appendDealMarkup(name, deal, productId) {
		if (!deal || !deal.enabled || name.querySelector('.gb-cart-deal')) {
			return;
		}

		name.appendChild(createDealMarkup(deal, productId));
	}

	function replaceDealMarkers() {
		var cart = document.querySelector('.wp-block-woocommerce-cart');

		if (!cart) {
			return;
		}

		var markerPattern = /\[\[GBDEAL\|(\d+)\|([^|]*)\|(\d+)\]\]/;

		cart.querySelectorAll('.wc-block-components-product-name').forEach(function (name) {
			var walker = document.createTreeWalker(name, NodeFilter.SHOW_TEXT);
			var textNode;

			while ((textNode = walker.nextNode())) {
				var match = textNode.nodeValue.match(markerPattern);

				if (!match) {
					continue;
				}

				var expiry = '';
				try {
					expiry = decodeURIComponent(match[2]);
				} catch (error) {
					return;
				}

				var deal = {
					expiry: expiry,
					expiry_ts: parseInt(match[3], 10) || 0,
				};
				var dealMarkup = createDealMarkup(deal, match[1]);

				var fragment = document.createDocumentFragment();
				var before = textNode.nodeValue.slice(0, match.index);
				var after = textNode.nodeValue.slice(match.index + match[0].length);

				if (before) {
					fragment.appendChild(document.createTextNode(before));
				}
				fragment.appendChild(dealMarkup);
				if (after) {
					fragment.appendChild(document.createTextNode(after));
				}

				textNode.parentNode.replaceChild(fragment, textNode);
				break;
			}
		});

		var fallbackDeals = Array.isArray(window.gbCartDeals) ? window.gbCartDeals : [];
		cart.querySelectorAll('table.wc-block-cart-items:not(.wc-block-mini-cart-items) .wc-block-cart-items__row').forEach(function (row, index) {
			var deal = fallbackDeals[index];
			var names = row.querySelectorAll('.wc-block-cart-item__product .wc-block-components-product-name');
			var name = Array.prototype.find.call(names, function (item) {
				return !item.hidden;
			}) || names[0];

			if (name && deal) {
				appendDealMarkup(name, deal, deal.product_id || '');
			}
		});
	}

	function registerFilter() {
		if (registered || !window.wc || !window.wc.blocksCheckout ||
			typeof window.wc.blocksCheckout.registerCheckoutFilters !== 'function') {
			return;
		}

		window.wc.blocksCheckout.registerCheckoutFilters('gb-deal-cart', {
			itemName: function (defaultValue, extensions, args) {
				var deal = extensions && extensions['gb-deal'];
				var cartItem = args && args.cartItem;
				var productId = cartItem && cartItem.id ? parseInt(cartItem.id, 10) : 0;

				if (!deal || !deal.enabled || !productId) {
					return defaultValue;
				}

				return defaultValue + dealMarker(deal, productId);
			},
		});

		registered = true;
	}

	registerFilter();
	window.setTimeout(registerFilter, 500);

	function initDealMarkup() {
		replaceDealMarkers();

		if (typeof MutationObserver === 'undefined' || !document.body) {
			return;
		}

		var observer = new MutationObserver(replaceDealMarkers);
		observer.observe(document.body, { childList: true, subtree: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initDealMarkup);
	} else {
		initDealMarkup();
	}
}());
