// The one Milestone navbar (<x-navbar />). The navbar works without this file; it adds the menus, dropdowns and theme.
import { notificationStorageKey, previewNotifications } from '../notification-data.js';

const navbars = [...document.querySelectorAll('[data-navbar]')];

navbars.forEach((navbar) => {
    initFit(navbar);
    initMenu(navbar);
    initDropdowns(navbar);
    initRoleSwitch(navbar);
    if (navbar.hasAttribute('data-navbar-guest')) initScrolled(navbar);
});
initTheme();
initNotifications();

// Roles with many items (Professor has eight) switch to icons only when the labels don't fit.
function initFit(navbar) {
    const nav = navbar.querySelector('.ms-navbar__nav');
    const list = nav?.querySelector('.ms-navbar__list');
    if (!nav || !list) return;

    // First tighten the spacing, and only drop to icons if the labels still don't fit.
    const overflows = () => list.scrollWidth > nav.clientWidth + 1;
    const fit = () => {
        navbar.classList.remove('is-tight', 'is-compact');
        if (overflows()) navbar.classList.add('is-tight');
        if (overflows()) navbar.classList.add('is-compact');
    };
    fit();
    new ResizeObserver(fit).observe(navbar);
    // Plus Jakarta Sans loads after first paint and is wider than the fallback font.
    document.fonts?.ready.then(fit);
    window.addEventListener('load', fit);
}

// Mobile menu: opens the full item list, closes on Escape or a link click, and returns focus to the button.
function initMenu(navbar) {
    const toggle = navbar.querySelector('[data-navbar-menu-toggle]');
    const menu = toggle && document.getElementById(toggle.getAttribute('aria-controls'));
    if (!toggle || !menu) return;

    const setOpen = (open, { focus = false } = {}) => {
        toggle.setAttribute('aria-expanded', String(open));
        menu.hidden = !open;
        if (!open && focus) toggle.focus();
    };

    toggle.addEventListener('click', () => setOpen(toggle.getAttribute('aria-expanded') !== 'true'));
    menu.addEventListener('click', (event) => {
        if (event.target.closest('a[href]')) setOpen(false);
    });
    navbar.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') setOpen(false, { focus: true });
    });
    // Matches the 900px breakpoint in navbar.css, where the menu button disappears.
    window.matchMedia('(min-width: 901px)').addEventListener('change', (event) => {
        if (event.matches) setOpen(false);
    });
}

// Notifications and account dropdowns: one open at a time, Escape returns focus, a click outside closes.
function initDropdowns(navbar) {
    const dropdowns = [...navbar.querySelectorAll('[data-navbar-dropdown]')].map((root) => ({
        root,
        toggle: root.querySelector('[data-navbar-dropdown-toggle]'),
        panel: root.querySelector('[data-navbar-dropdown-panel]'),
    })).filter((dropdown) => dropdown.toggle && dropdown.panel);

    const setOpen = (dropdown, open, { focus = false } = {}) => {
        dropdown.toggle.setAttribute('aria-expanded', String(open));
        dropdown.panel.hidden = !open;
        if (open) dropdown.root.dispatchEvent(new CustomEvent('navbar:open', { bubbles: true }));
        if (!open && focus) dropdown.toggle.focus();
    };

    dropdowns.forEach((dropdown) => {
        dropdown.toggle.addEventListener('click', () => {
            const open = dropdown.toggle.getAttribute('aria-expanded') !== 'true';
            dropdowns.forEach((other) => other !== dropdown && setOpen(other, false));
            setOpen(dropdown, open);
        });
        dropdown.root.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !dropdown.panel.hidden) {
                event.stopPropagation();
                setOpen(dropdown, false, { focus: true });
            }
        });
        dropdown.root.addEventListener('focusout', (event) => {
            if (!dropdown.root.contains(event.relatedTarget) && event.relatedTarget) setOpen(dropdown, false);
        });
    });

    document.addEventListener('click', (event) => {
        dropdowns.forEach((dropdown) => {
            if (!dropdown.root.contains(event.target)) setOpen(dropdown, false);
        });
    });
}

// Dev-only role switcher (local environment): switching submits right away.
function initRoleSwitch(navbar) {
    navbar.querySelectorAll('[data-navbar-role-switch] select').forEach((select) => {
        select.addEventListener('change', () => select.form.submit());
    });
}

function initScrolled(navbar) {
    const update = () => navbar.classList.toggle('is-scrolled', window.scrollY > 8);
    update();
    window.addEventListener('scroll', update, { passive: true });
}

// Dark mode, remembered in localStorage. The navbar applies the saved theme before the page paints.
function initTheme() {
    const buttons = [...document.querySelectorAll('[data-navbar-theme]')];
    if (!buttons.length) return;
    const root = document.documentElement;
    const sync = () => buttons.forEach((button) => button.setAttribute('aria-pressed', String(root.dataset.theme === 'dark')));

    buttons.forEach((button) => button.addEventListener('click', () => {
        const dark = root.dataset.theme !== 'dark';
        if (dark) root.dataset.theme = 'dark';
        else delete root.dataset.theme;
        try { localStorage.setItem('milestone-theme', dark ? 'dark' : 'light'); } catch { /* Private mode: the choice lasts for this page only. */ }
        sync();
    }));
    sync();
}

// Notifications preview in the bell dropdown (Raghad's data and read state, shared with the Notifications page).
function initNotifications() {
    const toggle = document.querySelector('[data-notifications-toggle]');
    const popover = document.querySelector('[data-notifications-popover]');
    const preview = document.querySelector('[data-notification-preview]');
    if (!toggle || !popover || !preview) return;

    const readStore = () => {
        try { return JSON.parse(localStorage.getItem(notificationStorageKey) || '{}'); }
        catch { return {}; }
    };
    // The navbar's one current role, from the server. Never the address bar or what another page remembered.
    const currentRole = () => {
        const role = toggle.dataset.notificationsRole;
        return previewNotifications[role] ? role : 'student';
    };
    const renderPreview = () => {
        const store = readStore();
        const role = currentRole();
        const roleState = store.roles?.[role] || { read: {}, dismissed: [] };
        const items = (previewNotifications[role] || []).filter((item) => !roleState.dismissed?.includes(item.id));
        const unread = items.filter((item) => roleState.read?.[item.id] !== true).length;
        toggle.setAttribute('aria-label', unread ? `Notifications, ${unread} unread` : 'Notifications');
        toggle.classList.toggle('has-unread', unread > 0);
        const viewAll = popover.querySelector('[data-notifications-view-all]');
        const base = viewAll?.href.split('?')[0];
        if (viewAll) viewAll.href = `${base}?showNotifications=1`;
        preview.replaceChildren();
        items.slice(0, 3).forEach((item) => {
            const link = document.createElement(base ? 'a' : 'div');
            const isRead = roleState.read?.[item.id] === true;
            link.className = `ms-notification-preview__item${isRead ? ' is-read' : ''}`;
            if (base) link.href = `${base}?notification=${encodeURIComponent(item.id)}`;
            link.innerHTML = '<span class="ms-notification-preview__dot" aria-hidden="true"></span><span><strong></strong><small></small></span>';
            link.querySelector('strong').textContent = item.title;
            link.querySelector('small').textContent = item.time;
            preview.append(link);
        });
        if (!items.length) preview.innerHTML = '<p class="ms-notification-preview__empty">You’re all caught up.</p>';
    };

    popover.closest('[data-navbar-dropdown]')?.addEventListener('navbar:open', renderPreview);
    window.addEventListener('storage', renderPreview);
    window.addEventListener('notifications-state-change', renderPreview);
    renderPreview();
    if (new URLSearchParams(window.location.search).get('showNotifications') === '1') toggle.click();
}
