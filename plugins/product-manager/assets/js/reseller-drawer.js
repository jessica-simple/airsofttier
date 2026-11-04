(function () {
    'use strict';

    var body = document.body;
    var drawer = document.getElementById('airsoft-reseller-drawer');
    var toggle = document.querySelector('.airsoft-drawer-toggle');
    var closeButton = document.querySelector('.airsoft-drawer-close');
    var backdrop = document.querySelector('.airsoft-drawer-backdrop');
    var mobile = window.matchMedia('(max-width: 700px)');

    if (!body || !drawer || !toggle || !closeButton || !backdrop) {
        return;
    }

    var previousFocus = null;

    function setDialogMode() {
        if (mobile.matches) {
            drawer.setAttribute('role', 'dialog');
            drawer.setAttribute('aria-modal', 'true');
        } else {
            drawer.removeAttribute('role');
            drawer.removeAttribute('aria-modal');
            close();
        }
    }

    function open() {
        if (!mobile.matches) {
            return;
        }
        previousFocus = document.activeElement;
        body.classList.add('airsoft-drawer-open');
        drawer.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        closeButton.focus();
    }

    function close() {
        body.classList.remove('airsoft-drawer-open');
        drawer.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        if (previousFocus && typeof previousFocus.focus === 'function') {
            previousFocus.focus();
        }
        previousFocus = null;
    }

    toggle.addEventListener('click', open);
    closeButton.addEventListener('click', close);
    backdrop.addEventListener('click', close);
    drawer.addEventListener('click', function (event) {
        if (event.target.closest('a')) {
            close();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (!body.classList.contains('airsoft-drawer-open')) {
            return;
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }
        var focusable = drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])');
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (!first || !last) {
            return;
        }
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    if (typeof mobile.addEventListener === 'function') {
        mobile.addEventListener('change', setDialogMode);
    } else {
        mobile.addListener(setDialogMode);
    }

    body.classList.add('airsoft-drawer-ready');
    setDialogMode();
}());
