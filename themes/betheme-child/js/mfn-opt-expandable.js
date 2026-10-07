(function () {
    'use strict';

    var itemSelector = '.mfn-opt-expandable';
    var childSelector = ':scope > ul, :scope > ol, :scope > .children, :scope > .mfn-opt-children, :scope > .mfn-opt-expandable-children';
    var expanderSelector = '.mfn-advanced-filters .mfn-advanced-filters-expand';
    var itemIndex = 0;
    var expandableState = Object.create(null);
    var initialCategoryState = Object.create(null);
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
        try {
            return item.querySelector(childSelector);
        } catch (error) {
            return Array.prototype.find.call(item.children, function (child) {
                return child.matches(
                    'ul, ol, .children, .mfn-opt-children, .mfn-opt-expandable-children'
                );
            }) || null;
        }
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

        if (!wrapper) {
            return null;
        }

        return wrapper.querySelector('ul.mfn-advanced-filters-options');
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

    function getCategoryKey(option) {
        var checkbox = option.querySelector(
            ':scope > input[type="checkbox"][name^="tax_"][value]'
        );

        if (!checkbox) {
            checkbox = option.querySelector(
                'input[type="checkbox"][name^="tax_"][value]'
            );
        }

        return checkbox ? checkbox.name + ':' + checkbox.value : null;
    }

    function captureInitialCategories(expander) {
        var filterKey = getFilterKey(expander);

        if (
            filterKey === null ||
            Object.prototype.hasOwnProperty.call(initialCategoryState, filterKey)
        ) {
            return;
        }

        var keys = [];
        var options = getTopLevelOptions(expander);

        options.forEach(function (option) {
            var key = getCategoryKey(option);

            if (
                key !== null &&
                !option.hidden &&
                !option.classList.contains('mfn-opt-hidden')
            ) {
                keys.push(key);
            }
        });

        initialCategoryState[filterKey] = keys;
    }

    function captureAllInitialCategories() {
        Array.prototype.forEach.call(
            document.querySelectorAll(expanderSelector),
            captureInitialCategories
        );
    }

    function restoreInitialCategories(expander) {
        var filterKey = getFilterKey(expander);
        var initialKeys = filterKey !== null
            ? initialCategoryState[filterKey]
            : null;
        var list = getFilterList(expander);

        if (!initialKeys || !list) {
            return;
        }

        var initialSet = Object.create(null);

        initialKeys.forEach(function (key) {
            initialSet[key] = true;
        });

        var options = getTopLevelOptions(expander);

        /*
         * Only touch direct children of BeTheme's top-level filter list.
         * Nested child <ul>s are never inspected or modified here.
         */
        options.forEach(function (option) {
            var key = getCategoryKey(option);

            if (key === null) {
                return;
            }

            var shouldShow = !!initialSet[key];

            option.classList.toggle('mfn-opt-hidden', !shouldShow);

            if (shouldShow) {
                option.hidden = false;
                option.removeAttribute('aria-hidden');
            } else {
                option.hidden = true;
                option.setAttribute('aria-hidden', 'true');
            }
        });

        /*
         * Restore only the original top-level order.
         * Child <li> elements remain inside their original parent.
         */
        initialKeys.forEach(function (key) {
            var option = options.find(function (candidate) {
                return getCategoryKey(candidate) === key;
            });

            if (option) {
                list.appendChild(option);
            }
        });
    }

    function collapseAllChildCategories() {
        /*
         * Show Less resets the custom nested UI completely.
         * Do not preserve any child expansion state through this action.
         */
        expandableState = Object.create(null);

        Array.prototype.forEach.call(
            document.querySelectorAll(itemSelector),
            function (item) {
                item.classList.remove('is-open');

                var toggle = item.querySelector(
                    ':scope > .mfn-opt-expandable-toggle'
                );

                if (toggle) {
                    updateToggleState(item, toggle);
                }
            }
        );
    }

    function updateToggleState(item, toggle) {
        var isOpen = item.classList.contains('is-open');

        toggle.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );
        toggle.setAttribute(
            'aria-label',
            isOpen ? 'Hide options' : 'Show options'
        );
    }

    function setupItem(item) {
        var childList = getChildList(item);

        if (!childList) {
            return;
        }

        var stateKey = getStateKey(item);
        var toggle = item.querySelector(
            ':scope > .mfn-opt-expandable-toggle'
        );

        item.classList.add('mfn-opt-expandable--has-children');
        item.setAttribute('data-mfn-expandable-ready', 'true');

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

        if (
            !resetting &&
            stateKey !== null &&
            Object.prototype.hasOwnProperty.call(expandableState, stateKey)
        ) {
            item.classList.toggle(
                'is-open',
                expandableState[stateKey]
            );
        }

        updateToggleState(item, toggle);
    }

    function rememberShowMoreState(expander) {
        var key = getFilterKey(expander);

        if (key !== null) {
            showMoreState[key] =
                expander.classList.contains('mfn-expanded');
        }
    }

    function restoreShowMoreState(expander) {
        var key = getFilterKey(expander);

        if (
            key === null ||
            !Object.prototype.hasOwnProperty.call(showMoreState, key)
        ) {
            return;
        }

        var expanded = showMoreState[key];
        var label = expanded
            ? expander.getAttribute('data-less')
            : expander.getAttribute('data-more');

        expander.classList.toggle('mfn-expanded', expanded);

        if (label !== null) {
            expander.textContent = label;
        }
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

                var option =
                    label.closest('.mfn-advanced-filters-checkbox') ||
                    label;

                option.hidden = true;
                option.setAttribute('aria-hidden', 'true');
            }
        );
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

                item.classList.add('mfn-opt-expandable');
                setupItem(item);
            }
        );
    }

    function init() {
        if (resetting) {
            return;
        }

        hideSelectedAdvancedFilters();
        captureAllInitialCategories();

        Array.prototype.forEach.call(
            document.querySelectorAll(itemSelector),
            setupItem
        );

        setupAdvancedFilterParents();

        Array.prototype.forEach.call(
            document.querySelectorAll(expanderSelector),
            restoreShowMoreState
        );
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

        /*
         * One delegated handler for custom child toggles.
         */
        document.addEventListener('click', function (event) {
            var target = event.target;
            var toggle = target && target.closest
                ? target.closest('.mfn-opt-expandable-toggle')
                : null;

            if (!toggle || resetting) {
                return;
            }

            var item = toggle.closest(itemSelector);

            if (!item) {
                return;
            }

            var childList = getChildList(item);

            if (!childList) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var key = getStateKey(item);
            var isOpen = item.classList.toggle('is-open');

            if (key !== null) {
                expandableState[key] = isOpen;
            }

            updateToggleState(item, toggle);
        }, true);

        /*
         * Let BeTheme own Show More/Show Less.
         */
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
                var expanded =
                    expander.classList.contains('mfn-expanded');

                rememberShowMoreState(expander);

                if (!expanded) {
                    /*
                     * This is the critical boundary:
                     * Show Less restores ONLY top-level categories and
                     * resets ALL custom child expansion.
                     */
                    restoreInitialCategories(expander);
                    collapseAllChildCategories();
                }
            }, 0);
        }, false);

        /*
         * Preserve See More after a category checkbox triggers AJAX.
         */
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

        /*
         * Reset All: never interfere with BeTheme's native handler.
         */
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