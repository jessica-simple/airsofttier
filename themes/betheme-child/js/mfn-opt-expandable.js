(function () {
    'use strict';

    var itemSelector = '.mfn-opt-expandable';
    var childSelector = ':scope > ul, :scope > ol, :scope > .children, :scope > .mfn-opt-children, :scope > .mfn-opt-expandable-children';
    var itemIndex = 0;
    var hiddenFilterLabels = {
        'best seller': true,
        'featured': true,
        'free shipping': true,
        'hot sale': true
    };

    function getChildList(item) {
        try {
            return item.querySelector(childSelector);
        } catch (error) {
            return Array.prototype.find.call(item.children, function (child) {
                return child.matches('ul, ol, .children, .mfn-opt-children, .mfn-opt-expandable-children');
            }) || null;
        }
    }

    function setupItem(item) {
        if (item.getAttribute('data-mfn-expandable-ready') === 'true') {
            return;
        }

        var childList = getChildList(item);

        if (!childList) {
            return;
        }

        item.setAttribute('data-mfn-expandable-ready', 'true');
        item.classList.add('mfn-opt-expandable--has-children');

        var toggle = item.querySelector(':scope > .mfn-opt-expandable-toggle');

        if (!toggle) {
            toggle = document.createElement('button');
            toggle.className = 'mfn-opt-expandable-toggle';
            toggle.type = 'button';
            toggle.setAttribute('aria-label', 'Show options');
            item.insertBefore(toggle, childList);
        }

        if (!childList.id) {
            itemIndex += 1;
            childList.id = 'mfn-opt-expandable-list-' + itemIndex;
        }

        toggle.setAttribute('aria-controls', childList.id);
        toggle.setAttribute('aria-expanded', 'false');

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            var isOpen = item.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Hide options' : 'Show options');
        });
    }

    function hideSelectedAdvancedFilters() {
        var selector = '.mfn-advanced-filters-checkbox .mfn-advanced-filters-label.mfn-advanced-filters-checkbox-label, ' +
            '.mfn-advanced-filters-checkbox.mfn-advanced-filters-label.mfn-advanced-filters-checkbox-label';

        Array.prototype.forEach.call(document.querySelectorAll(selector), function (label) {
            var labelText = label.textContent.replace(/\s+/g, ' ').trim().toLowerCase();

            if (!hiddenFilterLabels[labelText]) {
                return;
            }

            var option = label.closest('.mfn-advanced-filters-checkbox') || label;
            option.hidden = true;
            option.setAttribute('aria-hidden', 'true');
        });
    }

    function setupAdvancedFilterParents() {
        var labels = document.querySelectorAll(
            '.mfn-advanced-filters-label.mfn-advanced-filters-checkbox-label'
        );

        Array.prototype.forEach.call(labels, function (label) {
            var item = label.closest('li');

            if (!item || !getChildList(item)) {
                return;
            }

            item.classList.add('mfn-opt-expandable');
            setupItem(item);
        });
    }

    function initExpandableItems() {
        Array.prototype.forEach.call(document.querySelectorAll(itemSelector), setupItem);
        setupAdvancedFilterParents();
        hideSelectedAdvancedFilters();
    }

    function start() {
        initExpandableItems();

        if (typeof window.jQuery !== 'undefined') {
            window.jQuery(document).on('mfn:ajax:refresh', initExpandableItems);
        }

        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(initExpandableItems);
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}());
