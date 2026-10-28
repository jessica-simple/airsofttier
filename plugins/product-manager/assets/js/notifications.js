(function () {
    'use strict';
    const config = window.airsoftNotifications;
    if (!config) return;
    const states = new WeakMap();
    let serial = 0;
    let generation = 0;
    let openState = null;

    function ensure(root) {
        if (states.has(root)) return states.get(root);
        const toggle = root.querySelector('.airsoft-notification-toggle');
        if (!toggle) return null;
        let panel = root.querySelector('.airsoft-notification-panel');
        // Some header builders clone the bell when creating their sticky header.
        if (!panel) {
            const original = document.getElementById(toggle.getAttribute('aria-controls'));
            if (original) panel = original.cloneNode(true);
        }
        if (!panel) return null;
        panel.id = 'airsoft-notification-live-' + (++serial);
        panel.hidden = true;
        toggle.setAttribute('aria-controls', panel.id);
        toggle.setAttribute('aria-expanded', 'false');
        document.body.appendChild(panel); // Avoid clipping by header overflow/transform rules.
        const state = { root, toggle, panel };
        states.set(root, state);
        return state;
    }

    function position(state) {
        if (!state || state.panel.hidden) return;
        const rect = state.toggle.getBoundingClientRect();
        const viewport = window.visualViewport;
        const leftEdge = viewport ? viewport.offsetLeft : 0;
        const topEdge = viewport ? viewport.offsetTop : 0;
        const width = viewport ? viewport.width : window.innerWidth;
        const height = viewport ? viewport.height : window.innerHeight;
        const panel = state.panel;
        panel.style.width = Math.min(380, width - 24) + 'px';
        const panelWidth = panel.getBoundingClientRect().width;
        panel.style.left = Math.max(leftEdge + 12, Math.min(rect.right - panelWidth, leftEdge + width - panelWidth - 12)) + 'px';
        const below = Math.max(0, topEdge + height - rect.bottom - 20);
        const above = Math.max(0, rect.top - topEdge - 20);
        const useAbove = below < 240 && above > below;
        panel.style.maxHeight = Math.max(80, useAbove ? above : below) + 'px';
        panel.style.top = (useAbove ? Math.max(topEdge + 12, rect.top - panel.getBoundingClientRect().height - 8) : rect.bottom + 8) + 'px';
    }

    function close(restoreFocus) {
        if (!openState) return;
        const state = openState;
        state.panel.hidden = true;
        state.toggle.setAttribute('aria-expanded', 'false');
        openState = null;
        if (restoreFocus) state.toggle.focus();
    }

    function draw(data) {
        document.querySelectorAll('.airsoft-notification-widget').forEach(function (root) {
            const state = ensure(root);
            if (!state) return;
            const badge = root.querySelector('.airsoft-notification-badge');
            badge.textContent = data.count > 99 ? '99+' : String(data.count);
            badge.hidden = !data.count;
            state.toggle.setAttribute('aria-label', root.dataset.label + (data.count ? ' (' + data.count + ')' : ''));
            const list = state.panel.querySelector('.airsoft-notification-preview');
            const focusedLink = list.contains(document.activeElement) ? document.activeElement.dataset.notificationId : null;
            list.replaceChildren();
            data.items.slice(0, Number(root.dataset.limit) || 5).forEach(function (item) {
                const li = document.createElement('li');
                li.classList.toggle('is-unread', item.unread);
                const link = document.createElement('a');
                link.href = item.url;
                link.dataset.notificationId = item.id;
                if (item.unread) {
                    const unread = document.createElement('span');
                    unread.className = 'airsoft-notification-unread';
                    unread.textContent = config.unread;
                    link.appendChild(unread);
                }
                const message = document.createElement('span');
                message.textContent = item.message;
                const time = document.createElement('time');
                time.dateTime = item.datetime;
                time.textContent = item.time;
                link.append(message, time);
                li.appendChild(link);
                list.appendChild(li);
            });
            state.panel.querySelector('.airsoft-notification-empty').hidden = data.items.length > 0;
            state.panel.querySelector('.airsoft-notification-read-all').disabled = !data.count;
            if (focusedLink) {
                const replacement = Array.from(list.querySelectorAll('a')).find(function (link) { return link.dataset.notificationId === focusedLink; });
                (replacement || state.panel.querySelector('.airsoft-notification-view-all')).focus();
            }
            if (document.activeElement === state.panel.querySelector('.airsoft-notification-read-all') && !data.count) {
                state.panel.querySelector('.airsoft-notification-view-all').focus();
            }
        });
        position(openState);
    }

    async function request(mode, id) {
        const version = ++generation;
        const body = new URLSearchParams({ action: 'airsoft_notifications', nonce: config.nonce, mode: mode });
        if (id) body.set('id', id);
        const controller = new AbortController();
        const timeout = window.setTimeout(function () { controller.abort(); }, 10000);
        try {
            const response = await fetch(config.url, { method: 'POST', credentials: 'same-origin', body: body, cache: 'no-store', signal: controller.signal });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(config.error);
            if (version === generation) draw(result.data);
        } finally { window.clearTimeout(timeout); }
    }

    function error(state) {
        if (state) state.panel.querySelector('.airsoft-notification-feedback').textContent = config.error;
    }

    document.addEventListener('click', async function (event) {
        const toggle = event.target.closest('.airsoft-notification-toggle');
        if (toggle) {
            const state = ensure(toggle.closest('.airsoft-notification-widget'));
            if (!state) return;
            const wasOpen = openState === state;
            close(false);
            if (wasOpen) return;
            openState = state;
            state.panel.hidden = false;
            toggle.setAttribute('aria-expanded', 'true');
            state.panel.querySelector('.airsoft-notification-feedback').textContent = '';
            position(state);
            state.panel.querySelector('a, button:not(:disabled)').focus();
            try { await request('list'); } catch (_) { error(state); }
            return;
        }
        const all = event.target.closest('.airsoft-notification-read-all');
        if (all) {
            const state = openState;
            all.disabled = true;
            try { await request('all'); } catch (_) { all.disabled = false; error(state); }
            return;
        }
        const link = event.target.closest('a[data-notification-id]');
        if (link && (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)) {
            request('read', link.dataset.notificationId).catch(function () {});
            return;
        }
        if (link) {
            event.preventDefault();
            // Follow the useful destination even if the session expires during the update.
            try { await request('read', link.dataset.notificationId); } catch (_) { /* Keep navigation available. */ }
            window.location.assign(link.href);
            return;
        }
        if (openState && !openState.panel.contains(event.target) && !openState.root.contains(event.target)) close(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && openState) { event.preventDefault(); close(true); }
    });
    document.addEventListener('focusin', function (event) {
        if (openState && !openState.root.contains(event.target) && !openState.panel.contains(event.target)) close(false);
    });
    window.addEventListener('resize', function () { position(openState); });
    window.addEventListener('scroll', function () { position(openState); }, true);
    if (window.visualViewport) {
        window.visualViewport.addEventListener('resize', function () { position(openState); });
        window.visualViewport.addEventListener('scroll', function () { position(openState); });
    }
    document.querySelectorAll('.airsoft-notification-widget').forEach(ensure);
    // Refresh once on load; opening the dropdown fetches another fresh snapshot.
    if (document.querySelector('.airsoft-notification-widget')) request('list').catch(function () {});
}());
