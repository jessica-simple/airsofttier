(function () {
    'use strict';

    /*
     * BeTheme uses .mfn-opt-expandable as its own Show More/Show Less
     * bookkeeping class. Keep the custom child marker separate so native
     * handlers never hide or unmark nested child items.
     */
    var itemSelector = '.mfn-opt-expandable--has-children';

    var expanderSelector = '.mfn-advanced-filters .mfn-advanced-filters-expand';
    var itemIndex = 0;
    var expandableState = Object.create(null);
    var showMoreState = Object.create(null);
    var mutationTimer = null;
    var eventsInitialized = false;
    var resetting = false;

    var hiddenFilterLabels = {
        'best seller': true,
        'featured': true,
        'free shipping': true,
        'hot sale': true
    };

    function getChildList(item) {
        return Array.prototype.find.call(item.children, function (child) {
            return child.matches(
                'ul, ol, .children, .mfn-opt-children, .mfn-opt-expandable-children'
            );
        }) || null;
    }

    function getDirectToggle(item) {
        return Array.prototype.find.call(item.children, function (child) {
            return child.matches('.mfn-opt-expandable-toggle');
        }) || null;
    }

    function ensureChildListId(childList) {
        var id = childList.id;

        if (!id || document.getElementById(id) !== childList) {
            do {
                itemIndex += 1;
                id = 'mfn-opt-expandable-list-' + itemIndex;
            } while (document.getElementById(id));

            childList.id = id;
        }

        childList.classList.add('mfn-opt-expandable-list');

        return childList;
    }

    function getToggleTarget(toggle, item) {
        var controlsId = toggle.getAttribute('aria-controls');
        var childList = controlsId
            ? document.getElementById(controlsId)
            : null;

        if (childList && childList.parentElement === item) {
            return childList;
        }

        return getChildList(item);
    }

    function getStateKey(item) {
        var checkbox = item.querySelector(
            ':scope > input[type="checkbox"][name^="tax_"][value]'
        );

        if (!checkbox) {
            checkbox = item.querySelector(
                'input[type="checkbox"][name^="tax_"][value]'
            );
        }

        return checkbox ? checkbox.name + ':' + checkbox.value : null;
    }

    function getFilterWrapper(expander) {
        return expander.closest('.mfn-form-row-wrapper') ||
            expander.closest('.mfn-form-row');
    }

    function getFilterList(expander) {
        var wrapper = getFilterWrapper(expander);

        return wrapper
            ? wrapper.querySelector('ul.mfn-advanced-filters-options')
            : null;
    }

    function getFilterKey(expander) {
        var list = getFilterList(expander);
        var checkbox = list
            ? list.querySelector('input[type="checkbox"][name^="tax_"][value]')
            : null;

        if (!checkbox) {
            var wrapper = getFilterWrapper(expander);
            checkbox = wrapper
                ? wrapper.querySelector('input[type="checkbox"][name^="tax_"][value]')
                : null;
        }

        return checkbox ? 'advanced-filter:' + checkbox.name : null;
    }

    function getTopLevelOptions(expander) {
        var list = getFilterList(expander);

        if (!list) {
            return [];
        }

        return Array.prototype.filter.call(list.children, function (node) {
            return node.matches('li');
        });
    }

    function updateToggleState(item, toggle) {
        var isOpen = item.classList.contains('is-open');

        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute(
            'aria-label',
            isOpen ? 'Hide options' : 'Show options'
        );
    }
    function closeOtherExpandableItems(currentItem) {
        Array.prototype.forEach.call(
            document.querySelectorAll(itemSelector),
            function (item) {
                if (
                    item === currentItem ||
                    !item.classList.contains('is-open')
                ) {
                    return;
                }

                item.classList.remove('is-open');

                var key = getStateKey(item);

                if (key !== null) {
                    expandableState[key] = false;
                }

                var toggle = getDirectToggle(item);

                if (toggle) {
                    updateToggleState(item, toggle);
                }
            }
        );
    }

    function setupItem(item) {
        var childList = getChildList(item);

        if (!childList) {
            return;
        }

        var stateKey = getStateKey(item);
        var toggle = getDirectToggle(item);

        item.classList.add('mfn-opt-expandable--has-children');
        item.setAttribute('data-mfn-expandable-ready', 'true');

        if (!toggle) {
            toggle = document.createElement('button');
            toggle.className = 'mfn-opt-expandable-toggle';
            toggle.type = 'button';
            toggle.setAttribute('aria-label', 'Show options');
            item.insertBefore(toggle, childList);
        }

        ensureChildListId(childList);
        toggle.setAttribute('aria-controls', childList.id);

        if (
            !resetting &&
            stateKey !== null &&
            Object.prototype.hasOwnProperty.call(expandableState, stateKey)
        ) {
            item.classList.toggle('is-open', expandableState[stateKey]);
        }

        updateToggleState(item, toggle);
    }

    function setupAdvancedFilterParents() {
        Array.prototype.forEach.call(
            document.querySelectorAll(
                '.mfn-advanced-filters-label.mfn-advanced-filters-checkbox-label'
            ),
            function (label) {
                var item = label.closest('li');

                if (!item || !getChildList(item)) {
                    return;
                }

                setupItem(item);
            }
        );
    }

    function rememberShowMoreState(expander) {
        var key = getFilterKey(expander);

        if (key !== null) {
            showMoreState[key] = expander.classList.contains('mfn-expanded');
        }
    }

    /*
     * BeTheme's native handler works on all descendants. On an AJAX refresh,
     * only restore the direct top-level options, using the same class changes
     * as BeTheme, so nested child options are never affected.
     */
    function syncNativeShowMoreState(expander, expanded) {
        var options = getTopLevelOptions(expander);
        var label = expanded
            ? expander.getAttribute('data-less')
            : expander.getAttribute('data-more');

        expander.classList.toggle('mfn-expanded', expanded);

        if (label !== null) {
            expander.textContent = label;
        }

        options.forEach(function (option) {
            if (expanded) {
                if (!option.classList.contains('mfn-opt-hidden')) {
                    return;
                }

                option.classList.remove('mfn-opt-hidden');
                option.classList.add('mfn-opt-expandable');
                option.hidden = false;
                option.removeAttribute('aria-hidden');
                return;
            }

            if (!option.classList.contains('mfn-opt-expandable')) {
                return;
            }

            option.classList.add('mfn-opt-hidden');
            option.classList.remove('mfn-opt-expandable');
            option.hidden = true;
            option.setAttribute('aria-hidden', 'true');
        });
    }

    function restoreShowMoreState(expander) {
        var key = getFilterKey(expander);

        if (
            key === null ||
            !Object.prototype.hasOwnProperty.call(showMoreState, key)
        ) {
            return;
        }

        syncNativeShowMoreState(expander, showMoreState[key]);
    }

    function rememberAllShowMoreStates() {
        Array.prototype.forEach.call(
            document.querySelectorAll(expanderSelector),
            rememberShowMoreState
        );
    }

    function hideSelectedAdvancedFilters() {
        var selector =
            '.mfn-advanced-filters-checkbox .mfn-advanced-filters-label.mfn-advanced-filters-checkbox-label, ' +
            '.mfn-advanced-filters-checkbox.mfn-advanced-filters-label.mfn-advanced-filters-checkbox-label';

        Array.prototype.forEach.call(
            document.querySelectorAll(selector),
            function (label) {
                var text = label.textContent
                    .replace(/\s+/g, ' ')
                    .trim()
                    .toLowerCase();

                if (!hiddenFilterLabels[text]) {
                    return;
                }

                var option = label.closest('li') || label;

                option.hidden = true;
                option.setAttribute('aria-hidden', 'true');
            }
        );
    }

    function collapseAllChildCategories() {
        expandableState = Object.create(null);

        Array.prototype.forEach.call(
            document.querySelectorAll(itemSelector),
            function (item) {
                item.classList.remove('is-open');

                var toggle = getDirectToggle(item);

                if (toggle) {
                    updateToggleState(item, toggle);
                }
            }
        );
    }

    function init() {
        if (resetting) {
            return;
        }

        Array.prototype.forEach.call(
            document.querySelectorAll(itemSelector),
            setupItem
        );

        setupAdvancedFilterParents();

        Array.prototype.forEach.call(
            document.querySelectorAll(expanderSelector),
            restoreShowMoreState
        );

        hideSelectedAdvancedFilters();
    }

    function scheduleInit() {
        if (resetting) {
            return;
        }

        if (mutationTimer !== null) {
            window.clearTimeout(mutationTimer);
        }

        mutationTimer = window.setTimeout(function () {
            mutationTimer = null;
            init();
        }, 50);
    }

    function isAdvancedFilterNode(node) {
        return node &&
            node.nodeType === 1 &&
            (
                node.matches('.mfn-advanced-filters') ||
                node.querySelector('.mfn-advanced-filters') ||
                node.closest('.mfn-advanced-filters')
            );
    }

    function startEvents() {
        if (eventsInitialized) {
            return;
        }

        eventsInitialized = true;

        document.addEventListener('click', function (event) {
            var target = event.target;
            var toggle = target && target.closest
                ? target.closest('.mfn-opt-expandable-toggle')
                : null;

            if (!toggle || resetting) {
                return;
            }

            var item = toggle.closest(itemSelector);
            var childList = item ? getToggleTarget(toggle, item) : null;

            if (!item || !childList) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var key = getStateKey(item);
            var isOpen = !item.classList.contains('is-open');

            if (isOpen) {
                closeOtherExpandableItems(item);
            }

            item.classList.toggle('is-open', isOpen);

            if (key !== null) {
                expandableState[key] = isOpen;
            }

            updateToggleState(item, toggle);
        }, true);

        /* Let BeTheme own Show More/Show Less, then reset only child state. */
        document.addEventListener('click', function (event) {
            if (resetting) {
                return;
            }

            var expander = event.target && event.target.closest
                ? event.target.closest(expanderSelector)
                : null;

            if (!expander) {
                return;
            }

            window.setTimeout(function () {
                var expanded = expander.classList.contains('mfn-expanded');

                rememberShowMoreState(expander);

                if (!expanded) {
                    collapseAllChildCategories();
                }
            }, 0);
        }, false);

        document.addEventListener('change', function (event) {
            if (resetting) {
                return;
            }

            var target = event.target;

            if (
                target &&
                target.closest &&
                target.closest('.mfn-advanced-filters')
            ) {
                rememberAllShowMoreStates();
            }
        }, false);

        /* Do not prevent BeTheme's native Reset All handler. */
        document.addEventListener('click', function (event) {
            var target = event.target;
            var resetControl = target && target.closest
                ? target.closest(
                    '.mfn-active-filters .mfn-reset-filters, ' +
                    '.mfn-advanced-filters .mfn-reset-filters, ' +
                    '.mfn-advanced-filters-reset'
                )
                : null;

            if (!resetControl) {
                return;
            }

            resetting = true;
            expandableState = Object.create(null);
            showMoreState = Object.create(null);
            collapseAllChildCategories();

            if (mutationTimer !== null) {
                window.clearTimeout(mutationTimer);
                mutationTimer = null;
            }

            window.setTimeout(function () {
                resetting = false;
                init();
            }, 1000);
        }, true);
    }

    function start() {
        startEvents();
        init();

        if (typeof window.jQuery !== 'undefined') {
            window.jQuery(document).on(
                'mfn:ajax:refresh',
                function () {
                    if (resetting) {
                        resetting = false;
                        expandableState = Object.create(null);
                        showMoreState = Object.create(null);
                    }

                    window.setTimeout(init, 0);
                }
            );
        }

        if (typeof MutationObserver !== 'undefined') {
            var observerTarget =
                document.querySelector('#Content') ||
                document.body;

            var observer = new MutationObserver(function (mutations) {
                if (resetting) {
                    return;
                }

                var relevant = mutations.some(function (mutation) {
                    if (isAdvancedFilterNode(mutation.target)) {
                        return true;
                    }

                    return Array.prototype.some.call(
                        mutation.addedNodes,
                        isAdvancedFilterNode
                    ) || Array.prototype.some.call(
                        mutation.removedNodes,
                        isAdvancedFilterNode
                    );
                });

                if (relevant) {
                    scheduleInit();
                }
            });

            observer.observe(observerTarget, {
                childList: true,
                subtree: true
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}());
