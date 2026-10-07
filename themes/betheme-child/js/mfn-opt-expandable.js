(function () {
    'use strict';

    var itemSelector = '.mfn-opt-expandable';
    var childSelector = ':scope > ul, :scope > ol, :scope > .children, :scope > .mfn-opt-children, :scope > .mfn-opt-expandable-children';
    var itemIndex = 0;
    var expandableState = Object.create(null);
    var nativeShowMoreState = Object.create(null);
        initialVisibleCategoryState = Object.create(null);
    var initialVisibleCategoryState = Object.create(null);
        initialVisibleCategoryState = Object.create(null);
    var initialVisibleCategoryState = Object.create(null);
    var mutationTimer = null;
    var toggleEventsInitialized = false;
    var isResetting = false;
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

    function getStateKey(item) {
        var checkbox = item.querySelector(':scope > input[type="checkbox"][name^="tax_"][value]');

        if (!checkbox) {
            checkbox = item.querySelector('input[type="checkbox"][name^="tax_"][value]');
        }

        return checkbox ? checkbox.name + ':' + checkbox.value : null;
    }

    function getAdvancedFilterWrapper(expander) {
        return expander.closest('.mfn-form-row-wrapper') || expander.closest('.mfn-form-row');
    }

    function getAdvancedFilterStateKey(expander) {
        var wrapper = getAdvancedFilterWrapper(expander);

        if (!wrapper) {
            return null;
        }

        var checkbox = wrapper.querySelector('input[type="checkbox"][name^="tax_"][value]');

        return checkbox ? 'advanced-filter:' + checkbox.name : null;
    }

    function getAdvancedFilterExpanders() {
        return document.querySelectorAll('.mfn-advanced-filters .mfn-advanced-filters-expand');
    }

    function clearCustomState() {
        expandableState = Object.create(null);
        nativeShowMoreState = Object.create(null);
    }

    function updateToggleState(item, toggle) {
        var isOpen = item.classList.contains('is-open');

        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Hide options' : 'Show options');
    }

    function restoreExpandableState(item) {
        var stateKey = getStateKey(item);

        if (
            isResetting ||
            stateKey === null ||
            !Object.prototype.hasOwnProperty.call(expandableState, stateKey)
        ) {
            return;
        }

        item.classList.toggle('is-open', expandableState[stateKey]);
    }

    function getAdvancedFilterOptions(expander) {
        var wrapper = getAdvancedFilterWrapper(expander);
        var list = wrapper
            ? wrapper.querySelector('ul.mfn-advanced-filters-options') || wrapper.querySelector('ul')
            : null;

        return list
            ? Array.prototype.filter.call(list.children, function (option) {
                return option.matches('li');
            })
            : [];
    }

    function getCategoryStateKey(option) {
        var checkbox = option.querySelector(':scope > input[type="checkbox"][name^="tax_"][value]');

        if (!checkbox) {
            checkbox = option.querySelector('input[type="checkbox"][name^="tax_"][value]');
        }

        return checkbox ? checkbox.name + ':' + checkbox.value : null;
    }

    function rememberInitialVisibleCategories(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);

        if (
            stateKey === null ||
            Object.prototype.hasOwnProperty.call(initialVisibleCategoryState, stateKey)
        ) {
            return;
        }

        var visible = Object.create(null);

        getAdvancedFilterOptions(expander).forEach(function (option) {
            var categoryKey = getCategoryStateKey(option);

            if (
                categoryKey !== null &&
                !option.hidden &&
                !option.classList.contains('mfn-opt-hidden')
            ) {
                visible[categoryKey] = true;
            }
        });

        initialVisibleCategoryState[stateKey] = visible;
    }

    function rememberInitialVisibleCategoriesForAllFilters() {
        Array.prototype.forEach.call(
            getAdvancedFilterExpanders(),
            rememberInitialVisibleCategories
        );
    }

    function restoreInitialVisibleCategories(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);
        var initialVisible = stateKey !== null
            ? initialVisibleCategoryState[stateKey]
            : null;

        if (!initialVisible) {
            return;
        }

        getAdvancedFilterOptions(expander).forEach(function (option) {
            var categoryKey = getCategoryStateKey(option);

            if (categoryKey === null) {
                return;
            }

            if (Object.prototype.hasOwnProperty.call(initialVisible, categoryKey)) {
                option.hidden = false;
                option.removeAttribute('aria-hidden');
                option.classList.remove('mfn-opt-hidden');
            } else {
                option.hidden = true;
                option.setAttribute('aria-hidden', 'true');
                option.classList.add('mfn-opt-hidden');
            }
        });
    }

    function restoreInitialVisibleCategoryOrder(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);
        var initialVisible = stateKey !== null
            ? initialVisibleCategoryState[stateKey]
            : null;
        var wrapper = getAdvancedFilterWrapper(expander);
        var list = wrapper
            ? wrapper.querySelector('ul.mfn-advanced-filters-options') || wrapper.querySelector('ul')
            : null;

        if (!initialVisible || !list) {
            return;
        }

        var options = Array.prototype.filter.call(list.children, function (option) {
            return option.matches('li');
        });

        var initialOptions = options.filter(function (option) {
            var key = getCategoryStateKey(option);
            return key !== null && Object.prototype.hasOwnProperty.call(initialVisible, key);
        });

        initialOptions.forEach(function (option) {
            list.appendChild(option);
        });
    }

    function getAdvancedFilterOptions(expander) {
        var wrapper = getAdvancedFilterWrapper(expander);
        var list = wrapper
            ? wrapper.querySelector('ul.mfn-advanced-filters-options') || wrapper.querySelector('ul')
            : null;

        return list
            ? Array.prototype.filter.call(list.children, function (option) {
                return option.matches('li');
            })
            : [];
    }

    function getCategoryStateKey(option) {
        var checkbox = option.querySelector(':scope > input[type="checkbox"][name^="tax_"][value]');

        if (!checkbox) {
            checkbox = option.querySelector('input[type="checkbox"][name^="tax_"][value]');
        }

        return checkbox ? checkbox.name + ':' + checkbox.value : null;
    }

    function rememberInitialVisibleCategories(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);

        if (
            stateKey === null ||
            Object.prototype.hasOwnProperty.call(initialVisibleCategoryState, stateKey)
        ) {
            return;
        }

        var visible = Object.create(null);

        getAdvancedFilterOptions(expander).forEach(function (option) {
            var key = getCategoryStateKey(option);

            if (key !== null && !option.hidden && !option.classList.contains('mfn-opt-hidden')) {
                visible[key] = true;
            }
        });

        initialVisibleCategoryState[stateKey] = visible;
    }

    function rememberInitialVisibleCategoriesForAllFilters() {
        Array.prototype.forEach.call(
            getAdvancedFilterExpanders(),
            rememberInitialVisibleCategories
        );
    }

    function restoreInitialVisibleCategories(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);
        var visible = stateKey !== null ? initialVisibleCategoryState[stateKey] : null;

        if (!visible) {
            return;
        }

        getAdvancedFilterOptions(expander).forEach(function (option) {
            var key = getCategoryStateKey(option);

            if (key === null) {
                return;
            }

            var isInitial = Object.prototype.hasOwnProperty.call(visible, key);

            option.hidden = !isInitial;
            option.classList.toggle('mfn-opt-hidden', !isInitial);

            if (isInitial) {
                option.removeAttribute('aria-hidden');
            } else {
                option.setAttribute('aria-hidden', 'true');
            }
        });
    }

    function restoreInitialVisibleCategoryOrder(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);
        var visible = stateKey !== null ? initialVisibleCategoryState[stateKey] : null;
        var wrapper = getAdvancedFilterWrapper(expander);
        var list = wrapper
            ? wrapper.querySelector('ul.mfn-advanced-filters-options') || wrapper.querySelector('ul')
            : null;

        if (!visible || !list) {
            return;
        }

        Array.prototype.filter.call(list.children, function (option) {
            return option.matches('li');
        }).filter(function (option) {
            var key = getCategoryStateKey(option);
            return key !== null && Object.prototype.hasOwnProperty.call(visible, key);
        }).forEach(function (option) {
            list.appendChild(option);
        });
    }

    function collapseExpandableChildren() {
        /*
         * Show Less is a reset point for the custom child-category UI.
         * Clear the entire remembered state first, otherwise a later
         * AJAX refresh can restore a previously opened parent category.
         */
        expandableState = Object.create(null);

        Array.prototype.forEach.call(
            document.querySelectorAll(itemSelector),
            function (item) {
                item.classList.remove('is-open');

                var toggle = item.querySelector(':scope > .mfn-opt-expandable-toggle');

                if (toggle) {
                    updateToggleState(item, toggle);
                }
            }
        );
    }

    function rememberNativeShowMoreState(expander) {
        if (isResetting) {
            return;
        }

        var stateKey = getAdvancedFilterStateKey(expander);

        if (stateKey === null) {
            return;
        }

        nativeShowMoreState[stateKey] = expander.classList.contains('mfn-expanded');
    }

    function rememberAllNativeShowMoreStates() {
        if (isResetting) {
            return;
        }

        Array.prototype.forEach.call(
            getAdvancedFilterExpanders(),
            rememberNativeShowMoreState
        );
    }

    function restoreNativeShowMoreState(expander) {
        if (isResetting) {
            return;
        }

        var stateKey = getAdvancedFilterStateKey(expander);

        if (
            stateKey === null ||
            !Object.prototype.hasOwnProperty.call(nativeShowMoreState, stateKey)
        ) {
            return;
        }

        var isExpanded = nativeShowMoreState[stateKey];
        var label = isExpanded
            ? expander.getAttribute('data-less')
            : expander.getAttribute('data-more');

        /*
         * Only restore BeTheme's own expander state.
         * Do not add/remove mfn-opt-hidden, reorder categories,
         * or calculate the initial visible category count.
         */
        expander.classList.toggle('mfn-expanded', isExpanded);

        if (label !== null) {
            expander.textContent = label;
        }
    }

    function restoreAllNativeShowMoreStates() {
        if (isResetting) {
            return;
        }

        Array.prototype.forEach.call(
            getAdvancedFilterExpanders(),
            restoreNativeShowMoreState
        );
    }

    function setupItem(item) {
        var childList = getChildList(item);

        if (!childList) {
            return;
        }

        var stateKey = getStateKey(item);
        var toggle = item.querySelector(':scope > .mfn-opt-expandable-toggle');

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

        restoreExpandableState(item);
        updateToggleState(item, toggle);
    }

    function startExpandableToggleEvents() {
        if (toggleEventsInitialized) {
            return;
        }

        toggleEventsInitialized = true;

        document.addEventListener('click', function (event) {
            var target = event.target;
            var toggle = target && target.closest
                ? target.closest('.mfn-opt-expandable-toggle')
                : null;

            if (!toggle || isResetting) {
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

            var stateKey = getStateKey(item);
            var isOpen = item.classList.toggle('is-open');

            if (stateKey !== null) {
                expandableState[stateKey] = isOpen;
            }

            updateToggleState(item, toggle);
        }, true);
    }

    function startResetTracking() {
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

            /*
             * Reset All belongs entirely to BeTheme.
             * Clear our remembered states immediately and do not try to
             * restore Show More or child-toggle state during the reset.
             */
            isResetting = true;
            clearCustomState();

            if (mutationTimer !== null) {
                window.clearTimeout(mutationTimer);
                mutationTimer = null;
            }

            /*
             * Allow BeTheme's native click handler and AJAX reset to run.
             * We deliberately do not preventDefault() or stopPropagation().
             */
            window.setTimeout(function () {
                /*
                 * If BeTheme does not emit mfn:ajax:refresh for this reset,
                 * release the guard after its normal synchronous work.
                 */
                if (isResetting) {
                    isResetting = false;
                    initExpandableItems();
                }
            }, 1000);
        }, true);
    }

    function startNativeShowMoreStateTracking() {
        document.addEventListener('click', function (event) {
            if (isResetting) {
                return;
            }

            var target = event.target;
            var expander = target && target.closest
                ? target.closest('.mfn-advanced-filters .mfn-advanced-filters-expand')
                : null;

            if (!expander) {
                return;
            }

            /*
             * Let BeTheme handle Show More/Show Less first.
             * We only remember the resulting state.
             */
            window.setTimeout(function () {
                var wasExpanded = expander.classList.contains('mfn-expanded');

                rememberNativeShowMoreState(expander);

                if (!wasExpanded) {
                    restoreInitialVisibleCategories(expander);
                    restoreInitialVisibleCategoryOrder(expander);
                }
            }, 0);
        }, false);

        document.addEventListener('change', function (event) {
            if (isResetting) {
                return;
            }

            var target = event.target;

            if (
                !target ||
                !target.closest ||
                !target.closest('.mfn-advanced-filters')
            ) {
                return;
            }

            /*
             * If the user checked a category while Show More was active,
             * remember that expanded state before BeTheme refreshes the filter.
             */
            rememberAllNativeShowMoreStates();
        }, false);
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
        if (isResetting) {
            return;
        }

        hideSelectedAdvancedFilters();
        rememberInitialVisibleCategoriesForAllFilters();

        Array.prototype.forEach.call(
            document.querySelectorAll(itemSelector),
            setupItem
        );

        setupAdvancedFilterParents();
        restoreAllNativeShowMoreStates();
    }

    function scheduleInit() {
        if (isResetting) {
            return;
        }

        if (mutationTimer !== null) {
            window.clearTimeout(mutationTimer);
        }

        mutationTimer = window.setTimeout(function () {
            mutationTimer = null;

            if (!isResetting) {
                initExpandableItems();
            }
        }, 50);
    }

    function nodeContainsAdvancedFilters(node) {
        return node && node.nodeType === 1 && (
            node.matches('.mfn-advanced-filters') ||
            node.querySelector('.mfn-advanced-filters') ||
            node.closest('.mfn-advanced-filters')
        );
    }

    function handleAdvancedFilterMutations(mutations) {
        if (isResetting) {
            return;
        }

        var relevant = mutations.some(function (mutation) {
            if (nodeContainsAdvancedFilters(mutation.target)) {
                return true;
            }

            return Array.prototype.some.call(
                mutation.addedNodes,
                nodeContainsAdvancedFilters
            ) || Array.prototype.some.call(
                mutation.removedNodes,
                nodeContainsAdvancedFilters
            );
        });

        if (relevant) {
            scheduleInit();
        }
    }

    function start() {
        startExpandableToggleEvents();
        startResetTracking();
        startNativeShowMoreStateTracking();
        initExpandableItems();

        if (typeof window.jQuery !== 'undefined') {
            window.jQuery(document).on('mfn:ajax:refresh', function () {
                /*
                 * During Reset All, allow BeTheme to finish rebuilding the
                 * filter first, then start fresh without restoring old state.
                 */
                if (isResetting) {
                    isResetting = false;
                    clearCustomState();
                    window.setTimeout(function () {
                    initExpandableItems();
                    Array.prototype.forEach.call(getAdvancedFilterExpanders(), function (expander) {
                        if (!expander.classList.contains('mfn-expanded')) {
                            restoreInitialVisibleCategories(expander);
                            restoreInitialVisibleCategoryOrder(expander);
                        }
                    });
                }, 0);
                    return;
                }

                window.setTimeout(function () {
                    initExpandableItems();

                    Array.prototype.forEach.call(getAdvancedFilterExpanders(), function (expander) {
                        if (!expander.classList.contains('mfn-expanded')) {
                            restoreInitialVisibleCategories(expander);
                            restoreInitialVisibleCategoryOrder(expander);
                            collapseExpandableChildren();
                        }
                    });
                }, 0);
            });
        }

        if (typeof MutationObserver !== 'undefined') {
            var observerTarget = document.querySelector('#Content') || document.body;
            var observer = new MutationObserver(handleAdvancedFilterMutations);

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
