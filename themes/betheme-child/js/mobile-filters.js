(function ($) {
    'use strict';
    const mobile = window.matchMedia('(max-width: 767px)');
    let sequence = 0;
    let active = null;

    function button(text, className, action) {
        const element = document.createElement('button');
        element.type = 'button';
        element.className = className;
        element.textContent = text;
        element.addEventListener('click', action);
        return element;
    }

    function close() {
        if (active) active.close();
        active = null;
        document.documentElement.classList.remove('child-filters-open');
    }

    function initialize() {
        if (active && !active.isConnected) close();
        document.querySelectorAll('.mcb-item-filters-inner').forEach(function (host) {
            const form = host.querySelector('form.mfn-advanced-filters');
            if (!form || host.querySelector('.child-filter-trigger') || !window.HTMLDialogElement) return;
            const id = 'child-filters-' + (++sequence);
            const dialog = document.createElement('dialog');
            dialog.className = 'child-filter-sheet';
            dialog.id = id;
            dialog.setAttribute('aria-labelledby', id + '-title');
            const header = document.createElement('div');
            header.className = 'child-filter-header';
            const title = document.createElement('h2');
            title.id = id + '-title';
            title.textContent = 'Filters';
            const dismiss = button('×', 'child-filter-close', close);
            dismiss.setAttribute('aria-label', 'Close filters');
            header.append(title, dismiss);
            const content = document.createElement('div');
            content.className = 'child-filter-content';
            const footer = document.createElement('div');
            footer.className = 'child-filter-footer';
            const reset = button('Reset all', 'child-filter-reset', function () {
                const nativeReset = document.querySelector('.mfn-active-filters .mfn-reset-filters');
                if (nativeReset) nativeReset.click();
            });
            reset.hidden = !document.querySelector('.mfn-active-filters .mfn-reset-filters');
            footer.append(reset, button('View results', 'child-filter-results', close));
            dialog.append(header, content, footer);
            const trigger = button('Filters', 'child-filter-trigger', function () {
                active = dialog;
                dialog.showModal();
                document.documentElement.classList.add('child-filters-open');
                trigger.setAttribute('aria-expanded', 'true');
            });
            trigger.setAttribute('aria-haspopup', 'dialog');
            trigger.setAttribute('aria-controls', id);
            trigger.setAttribute('aria-expanded', 'false');
            dialog.addEventListener('close', function () {
                trigger.setAttribute('aria-expanded', 'false');
                document.documentElement.classList.remove('child-filters-open');
                if (active === dialog) active = null;
                if (mobile.matches && trigger.isConnected) trigger.focus();
            });
            dialog.addEventListener('click', function (event) {
                if (event.target !== dialog) return;
                const rect = dialog.getBoundingClientRect();
                if (event.clientY < rect.top || event.clientY > rect.bottom || event.clientX < rect.left || event.clientX > rect.right) close();
            });
            const placeholder = document.createComment('Mobile filter form position');
            form.before(placeholder);
            host.prepend(trigger);
            host.append(dialog);

            // Keep the same form, inputs, listeners and taxonomy hierarchy.
            function layout() {
                if (mobile.matches) content.append(form);
                else {
                    close();
                    placeholder.after(form);
                }
            }
            form.querySelectorAll('.mfn-form-row').forEach(function (row, index) {
                const label = row.querySelector('.mfn-advanced-filters-group-label');
                const body = row.querySelector('.mfn-form-row-wrapper');
                if (!label || !body) return;
                const toggle = button(label.textContent.trim(), 'child-filter-group-toggle', function () {
                    const expanded = toggle.getAttribute('aria-expanded') !== 'true';
                    toggle.setAttribute('aria-expanded', String(expanded));
                    row.classList.toggle('child-filter-collapsed', !expanded);
                });
                if (!body.id) body.id = id + '-group-' + index;
                toggle.setAttribute('aria-controls', body.id);
                toggle.setAttribute('aria-expanded', 'true');
                label.before(toggle);
                row.classList.add('child-filter-group');
            });
            function count() {
                const selected = form.querySelectorAll('input[type="checkbox"]:checked, input[type="radio"]:checked').length;
                trigger.textContent = selected ? 'Filters (' + selected + ')' : 'Filters';
            }
            form.addEventListener('change', count);
            count();
            layout();
            // A single shared resize handler avoids retaining AJAX-replaced forms.
            host._childFilterLayout = layout;
        });
    }

    mobile.addEventListener('change', function () {
        document.querySelectorAll('.mcb-item-filters-inner').forEach(function (host) {
            if (host._childFilterLayout) host._childFilterLayout();
        });
    });
    $(initialize);
    $(document).on('mfn:ajax:refresh', initialize);
})(jQuery);
