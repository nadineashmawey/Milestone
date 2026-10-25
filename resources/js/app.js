import { notificationStorageKey, previewNotifications } from './notification-data.js';

const toggle = document.querySelector('[data-notifications-toggle]');
const popover = document.querySelector('[data-notifications-popover]');
const preview = document.querySelector('[data-notification-preview]');

if (toggle && popover && preview) {
    const readStore = () => {
        try { return JSON.parse(localStorage.getItem(notificationStorageKey) || '{}'); }
        catch { return {}; }
    };
    const currentRole = () => {
        const params = new URLSearchParams(window.location.search);
        const selectedRole = document.querySelector('#notification-role')?.value;
        const role = selectedRole || params.get('role') || readStore().activeRole || 'student';
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
        const base = document.querySelector('[data-notifications-view-all]').href;
        document.querySelector('[data-notifications-view-all]').href = `${base.split('?')[0]}?role=${encodeURIComponent(role)}&showNotifications=1`;
        preview.replaceChildren();
        items.slice(0, 3).forEach((item) => {
            const link = document.createElement('a');
            const isRead = roleState.read?.[item.id] === true;
            link.className = `ms-notification-preview__item${isRead ? ' is-read' : ''}`;
            link.href = `${base.split('?')[0]}?role=${encodeURIComponent(role)}&notification=${encodeURIComponent(item.id)}`;
            link.innerHTML = `<span class="ms-notification-preview__dot" aria-hidden="true"></span><span><strong></strong><small></small></span>`;
            link.querySelector('strong').textContent = item.title;
            link.querySelector('small').textContent = item.time;
            preview.append(link);
        });
        if (!items.length) preview.innerHTML = '<p class="ms-notification-preview__empty">You’re all caught up.</p>';
    };
    const setOpen = (open) => {
        popover.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
    };
    toggle.addEventListener('click', () => {
        renderPreview();
        setOpen(popover.hidden);
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.ms-notification-menu')) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
    window.addEventListener('storage', renderPreview);
    window.addEventListener('notifications-state-change', renderPreview);
    renderPreview();
    if (new URLSearchParams(window.location.search).get('showNotifications') === '1') setOpen(true);
}
