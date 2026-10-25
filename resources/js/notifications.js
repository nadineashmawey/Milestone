import { notificationStorageKey as STORAGE_KEY } from './notification-data.js';

const app = document.querySelector('[data-notifications-app]');

if (app) {
    const roleSelect = app.querySelector('#notification-role');
    const list = app.querySelector('[data-notification-list]');
    const detail = app.querySelector('[data-notification-detail]');
    const filterButtons = [...app.querySelectorAll('[data-filter]')];
    const channelButtons = [...app.querySelectorAll('[data-channel]')];
    const iconSvgs = {
        calendar: '<svg viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="14" rx="2"/><path d="M8 3v5m8-5v5M4 10h16"/></svg>',
        folder: '<svg viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg>',
        comment: '<svg viewBox="0 0 24 24"><path d="M5 5h14a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H9l-5 3V7a2 2 0 0 1 1-2Z"/><path d="M8 10h8m-8 4h5"/></svg>',
        users: '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-1a6 6 0 0 1 12 0v1ZM16 5a3 3 0 0 1 0 6m2 3a5 5 0 0 1 3 5v1h-3"/></svg>',
        chart: '<svg viewBox="0 0 24 24"><path d="M4 19V5m0 14h16M7 15l4-4 3 2 5-6"/><path d="M16 7h3v3"/></svg>',
        bell: '<svg viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>',
        shield: '<svg viewBox="0 0 24 24"><path d="M12 3 20 6v5c0 5-3.4 8.2-8 10-4.6-1.8-8-5-8-10V6Z"/><path d="m9 12 2 2 4-4"/></svg>',
    };
    const mockNotifications = {
        student: [
            { id: 'student-deadline', icon: 'calendar', tone: 'blue', category: 'Submission deadline', title: 'M2 Design Document is due in 3 days', description: 'Your team’s M2 Design Document for Software Engineering Capstone is due Friday at 11:59 PM. Submit the final document from your project workspace.', time: '12 minutes ago', email: true, unread: true },
            { id: 'student-update', icon: 'folder', tone: 'violet', category: 'Project update', title: 'Professor Lee posted a project update', description: 'The project brief has been updated with the latest milestone requirements. Review the new acceptance criteria before your next team meeting.', time: 'Yesterday', unread: true },
            { id: 'student-feedback', icon: 'comment', tone: 'green', category: 'Feedback received', title: 'New feedback on your M1 Proposal', description: 'Dr. Lina Haddad left feedback on your team’s M1 Proposal. Open your submission to read the comments.', time: 'Yesterday', unread: true },
            { id: 'student-invite', icon: 'users', tone: 'violet', category: 'Team invitation', title: 'You were invited to join Team 4', description: 'Your teammate invited you to join Team 4 in Database Systems. Review the project invitation and respond when you are ready.', time: 'Monday', unread: false },
            { id: 'student-risk', icon: 'chart', tone: 'coral', category: 'Project update', title: 'Risk status changed to Medium', description: 'Team 3 risk status changed to Medium in the project activity overview.', time: 'Yesterday', unread: true },
        ],
        ta: [
            { id: 'ta-submission', icon: 'calendar', tone: 'blue', category: 'New submission', title: 'Team 3 submitted an M2 Design Document', description: 'Team 3 submitted their M2 Design Document for Software Engineering Capstone. Review the submission and leave feedback for Professor Lee.', time: '12 minutes ago', unread: true },
            { id: 'ta-review-deadline', icon: 'bell', tone: 'yellow', category: 'Review deadline', title: 'Two reviews are due today', description: 'You have two assigned submissions waiting for review. The oldest submission was received yesterday.', time: '1 hour ago', unread: true },
            { id: 'ta-issue', icon: 'shield', tone: 'coral', category: 'Reported issue', title: 'Team 3 reported a project issue', description: 'Team 3 reported that their project has no active team leader. The professor has been notified.', time: 'Yesterday', unread: true },
            { id: 'ta-invitation', icon: 'users', tone: 'violet', category: 'TA invitation', title: 'Invitation to Database Systems', description: 'Dr. Lina Haddad invited you to support Database Systems as a teaching assistant with comment-only project access.', time: 'Monday', unread: false },
        ],
        professor: [
            { id: 'prof-submission', icon: 'calendar', tone: 'blue', category: 'New submission', title: 'Team 3 submitted an M2 Design Document', description: 'Team 3 submitted their M2 Design Document for Software Engineering Capstone. The submission is ready for review.', time: '12 minutes ago', unread: true },
            { id: 'prof-review', icon: 'comment', tone: 'green', category: 'Review completed', title: 'Sarah Ahmed completed a submission review', description: 'Your teaching assistant finished the review for Team 1’s M2 Design Document and left feedback for the team.', time: '2 hours ago', unread: true },
            { id: 'prof-progress', icon: 'chart', tone: 'violet', category: 'Project progress', title: 'Software Engineering Capstone reached 70%', description: 'Most teams have completed the current milestone. Three teams still have outstanding submissions.', time: 'Yesterday', unread: true },
            { id: 'prof-activity', icon: 'users', tone: 'yellow', category: 'Student activity', title: 'Team 4 added a new project member', description: 'A student accepted the invitation to join Team 4 in Database Systems.', time: 'Monday', unread: false },
        ],
        admin: [
            { id: 'admin-registration', icon: 'users', tone: 'blue', category: 'New registration', title: '12 new student registrations', description: 'Twelve students registered for courses today. Review the registration queue for any accounts that need attention.', time: '18 minutes ago', unread: true },
            { id: 'admin-alert', icon: 'shield', tone: 'coral', category: 'System alert', title: 'Storage usage is above 80%', description: 'Project file storage has reached 83% of its current capacity. Review storage usage and plan the next maintenance step.', time: '1 hour ago', unread: true },
            { id: 'admin-course', icon: 'folder', tone: 'violet', category: 'Course update', title: 'CS 342 course details were updated', description: 'The Database Systems course schedule and project term dates were updated by the course coordinator.', time: 'Yesterday', unread: true },
            { id: 'admin-task', icon: 'bell', tone: 'yellow', category: 'Administrative task', title: 'Review pending TA access requests', description: 'There are three teaching assistant access requests waiting for administrative review.', time: 'Monday', unread: false },
        ],
    };

    const roleNames = { student: 'Student', ta: 'Teaching Assistant', professor: 'Professor', admin: 'Admin' };
    Object.values(mockNotifications).forEach((items) => items.forEach((item) => { item.email = true; }));
    let store = { activeRole: 'student', roles: {} };
    let activeFilter = 'all';
    let activeChannel = 'in-app';
    let activeNotificationId = null;
    let toastTimer;

    try {
        const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
        if (saved && typeof saved === 'object') store = { ...store, ...saved, roles: saved.roles || {} };
    } catch { /* Use the defaults if localStorage is unavailable or malformed. */ }
    if (!mockNotifications[store.activeRole]) store.activeRole = 'student';
    roleSelect.value = store.activeRole;
    const requestedRole = new URLSearchParams(window.location.search).get('role');
    if (requestedRole && mockNotifications[requestedRole]) {
        store.activeRole = requestedRole;
        roleSelect.value = requestedRole;
    }

    const roleState = (role = store.activeRole) => {
        if (!store.roles[role]) store.roles[role] = { read: {}, dismissed: [] };
        if (!store.roles[role].read) store.roles[role].read = {};
        if (!Array.isArray(store.roles[role].dismissed)) store.roles[role].dismissed = [];
        return store.roles[role];
    };
    const isRead = (item) => Object.hasOwn(roleState().read, item.id) ? roleState().read[item.id] : !item.unread;
    const escapeHtml = (value) => { const node = document.createElement('span'); node.textContent = value; return node.innerHTML; };
    const saveStore = () => { try { localStorage.setItem(STORAGE_KEY, JSON.stringify(store)); } catch { /* The page still works for this session without storage. */ } };
    const iconMarkup = (item) => `<span class="notification-icon notification-icon--${item.tone}" aria-hidden="true">${iconSvgs[item.icon] || iconSvgs.bell}</span>`;

    function updateCounts() {
        const notifications = mockNotifications[store.activeRole];
        const state = roleState();
        const channelItems = notifications.filter((item) => activeChannel === 'in-app' || item.email === true);
        const visibleItems = channelItems.filter((item) => !state.dismissed.includes(item.id));
        const unread = visibleItems.filter((item) => !isRead(item)).length;
        app.querySelector('[data-unread-count]').textContent = `${unread} unread`;
        app.querySelector('[data-filter-count="all"]').textContent = visibleItems.length;
        app.querySelector('[data-filter-count="unread"]').textContent = unread;
        app.querySelector('[data-filter-count="read"]').textContent = visibleItems.length - unread;
        app.querySelector('[data-mark-all]').disabled = unread === 0;
        window.dispatchEvent(new Event('notifications-state-change'));
    }

    function renderList() {
        const notifications = mockNotifications[store.activeRole].filter((item) => activeChannel === 'in-app' || item.email === true);
        const state = roleState();
        list.replaceChildren();
        const items = notifications.filter((item) => {
            if (state.dismissed.includes(item.id)) return false;
            if (activeFilter === 'unread') return !isRead(item);
            if (activeFilter === 'read') return isRead(item);
            return true;
        });

        items.forEach((item) => {
            const read = isRead(item);
            const article = document.createElement('article');
            article.className = `notification-row${read ? ' is-read' : ' is-unread'}`;
            article.dataset.notificationId = item.id;
            article.innerHTML = `<button type="button" class="notification-main" data-open-notification="${escapeHtml(item.id)}"><span class="notification-leading">${iconMarkup(item)}<span class="notification-unread-dot" aria-hidden="true"></span></span><span class="notification-copy"><strong>${escapeHtml(item.title)}</strong><span>${escapeHtml(item.description)}</span><small>${escapeHtml(item.time)}</small></span></button><div class="notification-actions"><button type="button" class="notification-action notification-action--read" data-mark-read="${escapeHtml(item.id)}" aria-label="${read ? 'Already read' : `Mark ${escapeHtml(item.title)} as read`}" title="${read ? 'Already read' : 'Mark as read'}" ${read ? 'disabled' : ''}><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 5 5L20 7"/></svg></button><button type="button" class="notification-action" data-dismiss-notification="${escapeHtml(item.id)}" aria-label="Dismiss ${escapeHtml(item.title)}" title="Dismiss"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg></button></div>`;
            list.append(article);
        });
        app.querySelector('[data-notification-empty]').hidden = items.length > 0;
        updateCounts();
    }

    function showToast(message) {
        const toast = app.querySelector('[data-notification-toast]');
        toast.textContent = message;
        toast.hidden = false;
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => { toast.hidden = true; }, 2500);
    }

    function markRead(id) {
        roleState().read[id] = true;
        saveStore();
        renderList();
        const item = mockNotifications[store.activeRole].find((notification) => notification.id === id);
        const markButton = app.querySelector('[data-detail-mark-read]');
        if (activeNotificationId === id && markButton) {
            markButton.disabled = true;
            markButton.textContent = 'Read';
        }
        if (item) showToast('Notification marked as read.');
    }

    function dismissNotification(id) {
        const dismissed = roleState().dismissed;
        if (!dismissed.includes(id)) dismissed.push(id);
        saveStore();
        if (activeNotificationId === id && detail.open) detail.close();
        activeNotificationId = null;
        renderList();
        showToast('Notification dismissed.');
    }

    function openDetails(id) {
        const item = mockNotifications[store.activeRole].find((notification) => notification.id === id);
        if (!item) return;
        activeNotificationId = id;
        app.querySelector('[data-detail-icon]').className = `notification-detail__icon notification-icon--${item.tone}`;
        app.querySelector('[data-detail-icon]').innerHTML = iconSvgs[item.icon] || iconSvgs.bell;
        app.querySelector('[data-detail-category]').textContent = item.category;
        app.querySelector('[data-detail-title]').textContent = item.title;
        app.querySelector('[data-detail-description]').textContent = item.description;
        app.querySelector('[data-detail-time]').textContent = item.time;
        if (!isRead(item)) markRead(id);
        const markButton = app.querySelector('[data-detail-mark-read]');
        markButton.disabled = isRead(item);
        markButton.textContent = isRead(item) ? 'Read' : 'Mark as read';
        if (typeof detail.showModal === 'function') detail.showModal();
    }

    filterButtons.forEach((button) => button.addEventListener('click', () => {
        activeFilter = button.dataset.filter;
        filterButtons.forEach((filter) => filter.setAttribute('aria-pressed', String(filter === button)));
        renderList();
    }));

    channelButtons.forEach((button) => button.addEventListener('click', () => {
        activeChannel = button.dataset.channel;
        channelButtons.forEach((channel) => channel.setAttribute('aria-pressed', String(channel === button)));
        activeFilter = 'all';
        filterButtons.forEach((filter) => filter.setAttribute('aria-pressed', String(filter.dataset.filter === 'all')));
        renderList();
    }));

    roleSelect.addEventListener('change', () => {
        store.activeRole = roleSelect.value;
        activeChannel = 'in-app';
        channelButtons.forEach((channel) => channel.setAttribute('aria-pressed', String(channel.dataset.channel === 'in-app')));
        activeFilter = 'all';
        filterButtons.forEach((button) => button.setAttribute('aria-pressed', String(button.dataset.filter === 'all')));
        if (detail.open) detail.close();
        activeNotificationId = null;
        saveStore();
        renderList();
    });

    app.querySelector('[data-mark-all]').addEventListener('click', () => {
        mockNotifications[store.activeRole].forEach((item) => { roleState().read[item.id] = true; });
        saveStore();
        renderList();
        if (detail.open && activeNotificationId) {
            app.querySelector('[data-detail-mark-read]').disabled = true;
            app.querySelector('[data-detail-mark-read]').textContent = 'Read';
        }
        showToast('All notifications marked as read.');
    });

    list.addEventListener('click', (event) => {
        const openButton = event.target.closest('[data-open-notification]');
        const readButton = event.target.closest('[data-mark-read]');
        const dismissButton = event.target.closest('[data-dismiss-notification]');
        if (openButton) openDetails(openButton.dataset.openNotification);
        if (readButton && !readButton.disabled) markRead(readButton.dataset.markRead);
        if (dismissButton) dismissNotification(dismissButton.dataset.dismissNotification);
    });

    app.querySelector('[data-close-detail]').addEventListener('click', () => detail.close());
    app.querySelector('[data-detail-mark-read]').addEventListener('click', () => {
        if (activeNotificationId) markRead(activeNotificationId);
    });
    app.querySelector('[data-detail-dismiss]').addEventListener('click', () => {
        if (activeNotificationId) dismissNotification(activeNotificationId);
    });
    detail.addEventListener('click', (event) => { if (event.target === detail) detail.close(); });

    saveStore();
    renderList();
    const requestedId = new URLSearchParams(window.location.search).get('notification');
    if (requestedId) openDetails(requestedId);
}
