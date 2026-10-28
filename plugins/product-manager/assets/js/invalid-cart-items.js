(function () {
	'use strict';

	var registered = false;
	var marker = ' [[AIRSOFT_SOLD_OUT_ITEM]]';

	function registerSoldOutMarker() {
		if (registered || !window.wc || !window.wc.blocksCheckout ||
			typeof window.wc.blocksCheckout.registerCheckoutFilters !== 'function') {
			return;
		}

		window.wc.blocksCheckout.registerCheckoutFilters('airsoft-invalid-cart-items', {
			itemName: function (defaultValue, extensions) {
				var cartData = extensions && extensions['airsoft-cart'];
				return cartData && cartData.sold_out ? defaultValue + marker : defaultValue;
			},
		});

		registered = true;
	}

	function getOrCreateInvalidSection(cart, table) {
		var section = cart.querySelector('.wc-block-cart-invalid-items');
		if (section) {
			return section;
		}

		section = document.createElement('section');
		section.className = 'wc-block-cart-invalid-items';
		section.setAttribute('aria-labelledby', 'wc-block-cart-invalid-items-title');
		section.innerHTML = '<h2 id="wc-block-cart-invalid-items-title">Invalid items</h2>' +
			'<table class="wc-block-cart-items wc-block-cart-invalid-items__table"><tbody></tbody></table>';
		table.insertAdjacentElement('afterend', section);
		return section;
	}

	function removeMarker(element) {
		var walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);
		var textNode;
		while ((textNode = walker.nextNode())) {
			if (textNode.nodeValue.includes(marker)) {
				textNode.nodeValue = textNode.nodeValue.replace(marker, '').trim();
			}
		}
	}

	function moveSoldOutItems() {
		var cart = document.querySelector('.wp-block-woocommerce-cart');
		if (!cart) {
			return;
		}

		var table = cart.querySelector('table.wc-block-cart-items:not(.wc-block-mini-cart-items):not(.wc-block-cart-invalid-items__table)');
		if (!table) {
			return;
		}

		var rows = table.querySelectorAll('.wc-block-cart-items__row');
		var invalidRows = [];
		rows.forEach(function (row) {
			var productName = row.querySelector('.wc-block-components-product-name');
			if (!productName || !productName.textContent.includes(marker)) {
				return;
			}

			removeMarker(productName);
			row.classList.add('wc-block-cart-invalid-item');
			invalidRows.push(row);
		});

		if (!invalidRows.length) {
			return;
		}

		var section = getOrCreateInvalidSection(cart, table);
		var body = section.querySelector('tbody');
		invalidRows.forEach(function (row) {
			body.appendChild(row);
		});
	}

	function setUpBulkRemove(cart, section) {
		if (!section || section.querySelector('.cart-remove-invalid-items')) {
			return;
		}

		var actions = document.createElement('p');
		actions.className = 'wc-block-cart-invalid-items__actions';
		var note = document.createElement('span');
		note.textContent = airsoftInvalidCart.soldOutCheckoutNote;
		var removeButton = document.createElement('a');
		removeButton.className = 'button cart-remove-invalid-items';
		removeButton.href = airsoftInvalidCart.removeAllUrl;
		removeButton.textContent = airsoftInvalidCart.removeAllInvalid;
		actions.appendChild(note);
		actions.appendChild(document.createTextNode(' '));
		actions.appendChild(removeButton);
		section.insertBefore(actions, section.querySelector('table'));
	}

	function updateCartActions() {
		if (!window.airsoftInvalidCart) {
			return;
		}

		var classicCart = document.querySelector('form.woocommerce-cart-form');
		var blockCart = document.querySelector('.wp-block-woocommerce-cart');
		var cart = classicCart || blockCart;
		if (!cart) {
			return;
		}

		var invalidRows = cart.querySelectorAll('.cart-invalid-item, .wc-block-cart-invalid-item');
		var hasInvalidItems = invalidRows.length > 0;
		var invalidSection = cart.querySelector('.wc-block-cart-invalid-items');
		if (invalidSection) {
			invalidSection.hidden = !hasInvalidItems;
		}
		cart.querySelectorAll('.cart-invalid-items-heading, .cart-invalid-items-actions').forEach(function (row) {
			row.hidden = !hasInvalidItems;
		});
		var validRows = classicCart
			? classicCart.querySelectorAll('.woocommerce-cart-form__cart-item.cart_item:not(.cart-invalid-item)')
			: blockCart.querySelectorAll('table.wc-block-cart-items:not(.wc-block-mini-cart-items):not(.wc-block-cart-invalid-items__table) .wc-block-cart-items__row:not(.wc-block-cart-invalid-item)');
		var hasValidItems = validRows.length > 0;

		if (blockCart && hasInvalidItems) {
			setUpBulkRemove(blockCart, blockCart.querySelector('.wc-block-cart-invalid-items'));
		}

		var checkoutButtons = document.querySelectorAll(
			'.wc-proceed-to-checkout .checkout-button, .wc-block-cart__submit-button, .wp-block-woocommerce-proceed-to-checkout a'
		);
		checkoutButtons.forEach(function (button) {
			button.hidden = hasInvalidItems && !hasValidItems;
			button.setAttribute('aria-hidden', button.hidden ? 'true' : 'false');
			if (button.tagName === 'A') {
				button.href = hasInvalidItems ? airsoftInvalidCart.checkoutUrl : airsoftInvalidCart.normalCheckoutUrl;
			}

			var parent = button.parentElement;
			if (parent && hasInvalidItems && !hasValidItems && !parent.querySelector('.cart-continue-shopping')) {
				var continueLink = document.createElement('a');
				continueLink.className = 'button cart-continue-shopping';
				continueLink.href = airsoftInvalidCart.shopUrl;
				continueLink.textContent = airsoftInvalidCart.continueShopping;
				parent.appendChild(continueLink);
			}
			if (parent && (hasValidItems || !hasInvalidItems)) {
				var existingContinueLink = parent.querySelector('.cart-continue-shopping');
				if (existingContinueLink) {
					existingContinueLink.remove();
				}
			}
		});
	}

	function handleCartAction(event) {
		var bulkRemove = event.target.closest('.cart-remove-invalid-items');
		if (bulkRemove && window.airsoftInvalidCart) {
			event.preventDefault();
			window.location.assign(airsoftInvalidCart.removeAllUrl);
			return;
		}

		var checkoutButton = event.target.closest(
			'.wc-proceed-to-checkout .checkout-button, .wc-block-cart__submit-button, .wp-block-woocommerce-proceed-to-checkout a'
		);
		if (checkoutButton && !checkoutButton.hidden && window.airsoftInvalidCart &&
			(document.querySelector('.cart-invalid-item, .wc-block-cart-invalid-item'))) {
			event.preventDefault();
			window.location.assign(airsoftInvalidCart.checkoutUrl);
		}
	}

	function scheduleUpdate() {
		if (scheduleUpdate.frame) {
			return;
		}
		scheduleUpdate.frame = window.requestAnimationFrame(function () {
			scheduleUpdate.frame = null;
			registerSoldOutMarker();
			moveSoldOutItems();
			updateCartActions();
		});
	}

	function init() {
		scheduleUpdate();
		window.setTimeout(registerSoldOutMarker, 500);
		document.addEventListener('click', handleCartAction, true);
		if (typeof MutationObserver === 'undefined' || !document.body) {
			return;
		}

		var observer = new MutationObserver(scheduleUpdate);
		observer.observe(document.body, { childList: true, subtree: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
}());
