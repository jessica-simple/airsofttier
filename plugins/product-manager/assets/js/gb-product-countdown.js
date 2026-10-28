(function () {
    'use strict';

    function placeLoopCountdowns(scope) {
        var selector = '.mfn-child-product-countdown, .mfn-child-loop-countdown';
        var countdowns = Array.prototype.slice.call(scope.querySelectorAll(selector));
        if (scope.matches && scope.matches(selector)) {
            countdowns.unshift(scope);
        }

        countdowns.forEach(function (countdown) {
            var card = countdown.closest('li.product, .mfn-product-li-item');
            if (!card) return;

            // Hooks and builder layouts can supply the same timer twice per card.
            // Keep one per card, even when the same product appears in other lists.
            var cardCountdowns = Array.prototype.filter.call(card.querySelectorAll(selector), function (item) {
                return item.closest('li.product, .mfn-product-li-item') === card;
            });
            if (cardCountdowns[0] !== countdown) return;
            cardCountdowns.slice(1).forEach(function (duplicate) {
                duplicate.remove();
            });

            var price = card.querySelector('.mfn-li-product-row.mfn-li-product-row-price') || card.querySelector('.price');
            if (price && price.nextElementSibling !== countdown) {
                price.insertAdjacentElement('afterend', countdown);
            }
        });
    }

    placeLoopCountdowns(document);

    // Position cards added by filters, pagination, or load more as well.
    if (typeof MutationObserver !== 'undefined' && document.body) {
        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                Array.prototype.forEach.call(mutation.addedNodes, function (node) {
                    if (node.nodeType === 1) placeLoopCountdowns(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    }

    var template = document.getElementById('mfn-child-gb-countdown');
    if (!template) return;

    var productId = template.dataset.productId;
    var content = document.querySelector('#Content .entry-content') || document.querySelector('main');
    if (!content) return;

    // Keep the timer supplied by the native summary or an explicit deal block.
    var existingTimers = content.querySelectorAll('.gb-timer[data-product-id="' + productId + '"]');
    var hasProductTimer = Array.prototype.some.call(existingTimers, function (timer) {
        return !timer.closest('li.product, .gb-deal-item, .gb-deal-grid');
    });
    if (hasProductTimer) {
        template.remove();
        return;
    }

    var countdown = template.content.querySelector('.mfn-child-product-countdown');
    if (!countdown || !countdown.querySelector('.gb-timer')) {
        template.remove();
        return;
    }

    var price = content.querySelector('.column_product_price .price, .entry-summary .price, .summary .price');
    var summary = content.querySelector('.entry-summary, .summary, .column_product_cart_button .mcb-column-inner');
    if (price) {
        price.insertAdjacentElement('afterend', countdown);
    } else {
        (summary || content).prepend(countdown);
    }
    template.remove();
}());
