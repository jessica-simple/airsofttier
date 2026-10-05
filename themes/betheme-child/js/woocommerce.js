(function ($) {
    'use strict';

    var itemSelector = '.wc-block-product-categories-list li.wc-block-product-categories-list-item, ' +
        '.widget_product_categories ul.product-categories li.cat-item';

    function addExpanders(scope) {
        scope.closest(itemSelector)
            .add(scope.filter(itemSelector))
            .add(scope.find(itemSelector))
            .each(function () {
                var item = this;
                var $item = $(item);
                var $children = $item.children('ul');

                if (!$children.length || $item.children('span.cat-expander').length) {
                    return;
                }

                $('<span class="cat-expander"></span>')
                    .on('click', function () {
                        if ($children.is(':visible')) {
                            $children.slideUp(300);
                            $item.removeClass('li-expanded');
                        } else {
                            $children.slideDown(300);
                            $item.addClass('li-expanded');
                        }
                    })
                    .appendTo(item);
            });
    }

    $(function () {
        addExpanders($(document));

        if (typeof MutationObserver === 'undefined' || !document.body) {
            return;
        }

        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                addExpanders($(mutation.target));

                Array.prototype.forEach.call(mutation.addedNodes, function (node) {
                    if (node.nodeType === 1) {
                        addExpanders($(node));
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    });
}(jQuery));
