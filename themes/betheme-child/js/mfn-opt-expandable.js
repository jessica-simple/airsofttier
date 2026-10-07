(function () {
    'use strict';

    var itemSelector = '.mfn-opt-expandable';
    var childSelector = ':scope > ul, :scope > ol, :scope > .children, :scope > .mfn-opt-children, :scope > .mfn-opt-expandable-children';
    var itemIndex = 0;
    var expandableState = Object.create(null);
    var advancedFilterState = Object.create(null);
    var advancedFilterInitialVisibleState = Object.create(null);
    var advancedFilterShowMoreState = Object.create(null);
    var advancedFilterExpanderSelector = '.mfn-advanced-filters .mfn-advanced-filters-expand';
    var mutationTimer = null;
    var isResettingAdvancedFilter = false;
    var restoringAdvancedFilterState = false;
    var toggleEventsInitialized = false;
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

    function getAdvancedFilterOptionList(wrapper) {
        if (!wrapper) {
            return null;
        }

        return wrapper.querySelector('ul.mfn-advanced-filters-options') || wrapper.querySelector('ul');
    }

    function getAdvancedFilterOptions(expander) {
        var wrapper = getAdvancedFilterWrapper(expander);
        var optionList = wrapper && getAdvancedFilterOptionList(wrapper);

        return optionList ? Array.prototype.filter.call(optionList.children, function (option) {
            return option.matches('li');
        }) : [];
    }

    function getCategoryStateKey(option) {
        var checkbox = option.querySelector(':scope > input[type="checkbox"][name^="tax_"][value]');

        if (!checkbox) {
            checkbox = option.querySelector('input[type="checkbox"][name^="tax_"][value]');
        }

        return checkbox ? checkbox.name + ':' + checkbox.value : null;
    }

    function getAdvancedFilterStateKey(expander) {
        var wrapper = getAdvancedFilterWrapper(expander);

        if (!wrapper) {
            return null;
        }

        var optionList = getAdvancedFilterOptionList(wrapper);
        var checkbox = optionList && optionList.querySelector(
            'li > input[type="checkbox"][name^="tax_"][value]'
        );

        if (!checkbox) {
            checkbox = wrapper.querySelector('input[type="checkbox"][name^="tax_"][value]');
        }

        return checkbox ? 'advanced-filter:' + checkbox.name : null;
    }

    function rememberInitialVisibleCategories(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);

        if (
            stateKey === null ||
            Object.prototype.hasOwnProperty.call(advancedFilterInitialVisibleState, stateKey)
        ) {
            return;
        }

        var initialVisibleCategories = Object.create(null);

        getAdvancedFilterOptions(expander).forEach(function (option) {
            var categoryKey = getCategoryStateKey(option);

            if (
                categoryKey !== null &&
                !option.classList.contains('mfn-opt-hidden') &&
                !option.hidden
            ) {
                initialVisibleCategories[categoryKey] = true;
            }
        });

        advancedFilterInitialVisibleState[stateKey] = initialVisibleCategories;

        if (!Object.prototype.hasOwnProperty.call(advancedFilterShowMoreState, stateKey)) {
            advancedFilterShowMoreState[stateKey] = expander.classList.contains('mfn-expanded');
        }
    }

    function rememberInitialVisibleCategoriesForAllFilters() {
        Array.prototype.forEach.call(
            document.querySelectorAll(advancedFilterExpanderSelector),
            rememberInitialVisibleCategories
        );
    }

    function applyAdvancedFilterVisibility(expander, isExpanded) {
        var stateKey = getAdvancedFilterStateKey(expander);
        var initialVisibleCategories = stateKey !== null
            ? advancedFilterInitialVisibleState[stateKey]
            : null;

        if (!initialVisibleCategories) {
            return;
        }

        getAdvancedFilterOptions(expander).forEach(function (option) {
            var categoryKey = getCategoryStateKey(option);
            var wasInitiallyVisible = categoryKey !== null &&
                Object.prototype.hasOwnProperty.call(initialVisibleCategories, categoryKey);

            if (isExpanded || wasInitiallyVisible) {
                option.classList.remove('mfn-opt-hidden');

                if (!wasInitiallyVisible) {
                    option.classList.add('mfn-opt-expandable');
                }
            } else {
                option.classList.add('mfn-opt-hidden');
                option.classList.remove('mfn-opt-expandable');
            }
        });
    }

    function rememberAdvancedFilterState(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);
        var isExpanded = expander.classList.contains('mfn-expanded');

        if (stateKey !== null) {
            advancedFilterState[stateKey] = isExpanded;
            advancedFilterShowMoreState[stateKey] = isExpanded;
        }
    }

    function rememberAllAdvancedFilterStates() {
        Array.prototype.forEach.call(
            document.querySelectorAll(advancedFilterExpanderSelector),
            rememberAdvancedFilterState
        );
    }

    function clearAdvancedFilterMemory() {
        advancedFilterState = Object.create(null);

        Object.keys(advancedFilterShowMoreState).forEach(function (stateKey) {
            advancedFilterShowMoreState[stateKey] = false;
        });

        Array.prototype.forEach.call(
            document.querySelectorAll('.mfn-advanced-filters ' + itemSelector),
            function (item) {
                var stateKey = getStateKey(item);

                if (stateKey !== null) {
                    delete expandableState[stateKey];
                }
            }
        );
    }

    function restoreAdvancedFilterState(expander) {
        var stateKey = getAdvancedFilterStateKey(expander);

        if (stateKey === null || !Object.prototype.hasOwnProperty.call(advancedFilterState, stateKey)) {
            return;
        }

        var wrapper = getAdvancedFilterWrapper(expander);
        var isExpanded = advancedFilterState[stateKey];
        var label = isExpanded ? expander.getAttribute('data-less') : expander.getAttribute('data-more');

        restoringAdvancedFilterState = true;
        expander.classList.toggle('mfn-expanded', isExpanded);

        if (label !== null && expander.textContent !== label) {
            expander.textContent = label;
        }

        if (!wrapper) {
            window.setTimeout(function () {
                restoringAdvancedFilterState = false;
            }, 0);
            return;
        }

        applyAdvancedFilterVisibility(expander, isExpanded);

        window.setTimeout(function () {
            restoringAdvancedFilterState = false;
        }, 0);
    }

    function restoreAdvancedFilterStates() {
        if (isResettingAdvancedFilter) {
            return;
        }

        Array.prototype.forEach.call(
            document.querySelectorAll(advancedFilterExpanderSelector),
            function (expander) {
                var stateKey = getAdvancedFilterStateKey(expander);

                if (
                    stateKey !== null &&
                    Object.prototype.hasOwnProperty.call(advancedFilterState, stateKey)
                ) {
                    restoreAdvancedFilterState(expander);
                } else if (
                    stateKey !== null &&
                    Object.prototype.hasOwnProperty.call(advancedFilterShowMoreState, stateKey)
                ) {
                    applyAdvancedFilterVisibility(
                        expander,
                        advancedFilterShowMoreState[stateKey]
                    );
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
        var hasSavedState = stateKey !== null &&
            Object.prototype.hasOwnProperty.call(expandableState, stateKey);

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

        if (hasSavedState) {
            item.classList.toggle('is-open', expandableState[stateKey]);
            childList.style.display = '';
        }

        var isOpen = item.classList.contains('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', isOpen ? 'Hide options' : 'Show options');

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

            if (!toggle) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            var item = toggle.closest(itemSelector);

            if (!item) {
                return;
            }

            var childList = getChildList(item);

            if (!childList) {
                return;
            }

            var stateKey = getStateKey(item);
            var isOpen = item.classList.toggle('is-open');

            if (stateKey !== null) {
                expandableState[stateKey] = isOpen;
            }

            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Hide options' : 'Show options');
        }, true);
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
        if (isResettingAdvancedFilter) {
            return;
        }

        hideSelectedAdvancedFilters();
        rememberInitialVisibleCategoriesForAllFilters();
        Array.prototype.forEach.call(document.querySelectorAll(itemSelector), setupItem);
        setupAdvancedFilterParents();
        restoreAdvancedFilterStates();
    }

    function scheduleInit() {
        if (mutationTimer !== null) {
            window.clearTimeout(mutationTimer);
        }

        mutationTimer = window.setTimeout(function () {
            mutationTimer = null;

            if (isResettingAdvancedFilter) {
                return;
            }

            initExpandableItems();
        }, 50);
    }

    function nodeContainsAdvancedFilters(node) {
        return node && node.nodeType === 1 && (
            node.matches('.mfn-advanced-filters') ||
            node.querySelector('.mfn-advanced-filters') ||
            node.closest('.mfn-advanced-filters')
        );
    }

    function advancedFilterMutationsDetected(mutations) {
        return mutations.some(function (mutation) {
            if (nodeContainsAdvancedFilters(mutation.target)) {
                return true;
            }

            return Array.prototype.some.call(mutation.addedNodes, nodeContainsAdvancedFilters) ||
                Array.prototype.some.call(mutation.removedNodes, nodeContainsAdvancedFilters);
        });
    }

    function handleAdvancedFilterMutations(mutations) {
        if (!isResettingAdvancedFilter && advancedFilterMutationsDetected(mutations)) {
            scheduleInit();
        }
    }

    function startAdvancedFilterStateTracking() {
        document.addEventListener('click', function (event) {
            var target = event.target;
            var resetControl = target && target.closest
                ? target.closest('.mfn-active-filters .mfn-reset-filters')
                : null;

            if (!resetControl) {
                return;
            }

            isResettingAdvancedFilter = true;
            clearAdvancedFilterMemory();

            if (mutationTimer !== null) {
                window.clearTimeout(mutationTimer);
                mutationTimer = null;
            }
        }, true);

        document.addEventListener('click', function (event) {
            var target = event.target;
            var expander = target && target.closest ? target.closest(advancedFilterExpanderSelector) : null;

            if (!expander) {
                return;
            }

            window.setTimeout(function () {
                if (isResettingAdvancedFilter) {
                    return;
                }

                rememberAdvancedFilterState(expander);
                applyAdvancedFilterVisibility(
                    expander,
                    expander.classList.contains('mfn-expanded')
                );
            }, 0);
        }, true);

        document.addEventListener('change', function (event) {
            var target = event.target;

            if (
                target &&
                target.closest &&
                target.closest('.mfn-advanced-filters') &&
                !restoringAdvancedFilterState
            ) {
                rememberAllAdvancedFilterStates();
            }
        }, true);

        document.addEventListener('submit', function (event) {
            var form = event.target;

            if (form && form.matches && form.matches('.mfn-advanced-filters')) {
                if (!restoringAdvancedFilterState) {
                    rememberAllAdvancedFilterStates();
                }
            }
        }, true);
    }

    function start() {
        startAdvancedFilterStateTracking();
        startExpandableToggleEvents();
        initExpandableItems();

        if (typeof window.jQuery !== 'undefined') {
            window.jQuery(document).on('mfn:ajax:refresh', function () {
                window.setTimeout(function () {
                    isResettingAdvancedFilter = false;
                    initExpandableItems();
                }, 0);
            });
        }

        if (typeof MutationObserver !== 'undefined') {
            var observerTarget = document.querySelector('#Content') || document.body;
            var observer = new MutationObserver(handleAdvancedFilterMutations);
            observer.observe(observerTarget, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
}());
