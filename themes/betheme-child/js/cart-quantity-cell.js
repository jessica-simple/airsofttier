(function () {
    'use strict';

    function moveQuantityControls() {
        document.querySelectorAll(
            '.wp-block-woocommerce-cart table.wc-block-cart-items:not(.wc-block-mini-cart-items)'
        ).forEach(function (table) {
            var headerRow = table.querySelector('thead .wc-block-cart-items__header');
            var totalHeader = headerRow && Array.prototype.find.call(headerRow.children, function (cell) {
                return cell.classList.contains('wc-block-cart-items__header-total');
            });

            if (headerRow && totalHeader && !headerRow.querySelector('.wc-block-cart-items__header-quantity')) {
                var quantityHeader = document.createElement('th');
                quantityHeader.className = 'wc-block-cart-items__header-quantity';
                quantityHeader.scope = 'col';
                quantityHeader.textContent = 'Quantity';
                headerRow.insertBefore(quantityHeader, totalHeader);
            }

            table.querySelectorAll('.wc-block-cart-items__row').forEach(function (row) {
                var productCell = Array.prototype.find.call(row.children, function (cell) {
                    return cell.classList.contains('wc-block-cart-item__product');
                });
                var totalCell = Array.prototype.find.call(row.children, function (cell) {
                    return cell.classList.contains('wc-block-cart-item__total');
                });
                var quantityCell = Array.prototype.find.call(row.children, function (cell) {
                    return cell.classList.contains('wc-block-cart-item__quantity-cell');
                });

                if (!productCell) {
                    return;
                }

                var quantity = productCell.querySelector(
                    ':scope > .wc-block-cart-item__wrap > .wc-block-cart-item__quantity'
                );

                if (!quantity) {
                    return;
                }

                if (!quantityCell) {
                    quantityCell = document.createElement('td');
                    quantityCell.className = 'wc-block-cart-item__quantity-cell';
                    quantityCell.dataset.title = 'Quantity';

                    if (totalCell) {
                        row.insertBefore(quantityCell, totalCell);
                    } else {
                        row.appendChild(quantityCell);
                    }
                }

                quantityCell.appendChild(quantity);
            });
        });
    }

    function scheduleMove() {
        if (scheduleMove.frame) {
            return;
        }

        scheduleMove.frame = window.requestAnimationFrame(function () {
            scheduleMove.frame = null;
            moveQuantityControls();
        });
    }

    function init() {
        scheduleMove();

        if (typeof MutationObserver === 'undefined' || !document.body) {
            return;
        }

        var observer = new MutationObserver(scheduleMove);
        observer.observe(document.body, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
