const app = document.querySelector('[data-issues-app]');

if (app) {
    const list = app.querySelector('.issues-list');
    const cards = () => [...list.querySelectorAll('.issue-card')];
    const statusNames = { reviewing: 'Professor reviewing', 'in-progress': 'In progress', resolved: 'Resolved' };
    const priorityNames = { high: 'High', medium: 'Medium', low: 'Low' };
    const priorityTones = { high: 'danger', medium: 'warning', low: 'neutral' };
    const statusTones = { reviewing: 'warning', 'in-progress': 'info', resolved: 'success' };
    const details = {
        'issue-1': { project: 'Software Engineering Capstone', team: 'Team 3', reporter: 'Sarah Ahmed · Teaching assistant', date: 'Today at 10:24 AM', description: 'Team 3’s previous leader left the project, and the team cannot assign a new leader. They are blocked from submitting their milestone work until the roster is updated.', comments: [{ author: 'Sarah Ahmed', initials: 'SA', time: 'Today, 10:24 AM', text: 'I checked the team roster and confirmed there is no active leader assigned.' }] },
        'issue-2': { project: 'Database Systems', team: 'Team 2', reporter: 'Sarah Ahmed · Teaching assistant', date: 'Yesterday at 3:40 PM', description: 'The exported M2 PDF will not open. The team has retried the export and can share the original document if needed.', comments: [{ author: 'Sarah Ahmed', initials: 'SA', time: 'Yesterday, 3:40 PM', text: 'The attached PDF appears to be corrupted when downloaded.' }, { author: 'Professor Lee', initials: 'PL', time: 'Today, 9:05 AM', text: 'Resolved after the team re-uploaded the document.' }] },
        'issue-3': { project: 'Human Computer Interaction', team: 'Team 2', reporter: 'Sarah Ahmed · Teaching assistant', date: 'Oct 22, 2026', description: 'Team 2 members receive a permission error when opening the shared project repository. They need access to continue their prototype work.', comments: [{ author: 'Sarah Ahmed', initials: 'SA', time: 'Oct 22, 2:12 PM', text: 'Sent the repository access request to the project owner.' }] },
        'issue-4': { project: 'Software Engineering Capstone', team: 'Team 1', reporter: 'Sarah Ahmed · Teaching assistant', date: 'Oct 21, 2026', description: 'The milestone page shows a deadline one week earlier than the date shared in class. Please confirm the correct submission date.', comments: [{ author: 'Sarah Ahmed', initials: 'SA', time: 'Oct 21, 11:30 AM', text: 'The team asked me to confirm which deadline is correct.' }] },
        'issue-5': { project: 'Database Systems', team: 'Team 4', reporter: 'Sarah Ahmed · Teaching assistant', date: 'Oct 18, 2026', description: 'The invitation link sent to a new teammate had expired before they could accept it.', comments: [{ author: 'Sarah Ahmed', initials: 'SA', time: 'Oct 18, 9:20 AM', text: 'The team needed a fresh invitation link.' }, { author: 'Professor Lee', initials: 'PL', time: 'Oct 18, 1:15 PM', text: 'Resolved. A new invitation was sent.' }] },
    };
    let activeId = 'issue-1';
    let nextId = 6;
    let toastTimer;
    const toast = app.querySelector('[data-issues-toast]');
    const escapeHtml = (value) => { const node = document.createElement('span'); node.textContent = value; return node.innerHTML; };
    const showToast = (message) => { toast.textContent = message; toast.hidden = false; clearTimeout(toastTimer); toastTimer = setTimeout(() => { toast.hidden = true; }, 3000); };
    const badge = (text, tone) => `<span class="ms-badge${tone && tone !== 'neutral' ? ` ms-badge--${tone}` : ''}">${escapeHtml(text)}</span>`;
    const selected = () => list.querySelector(`[data-issue-id="${activeId}"]`);

    function renderComments(issue) {
        const target = app.querySelector('[data-comment-list]');
        target.replaceChildren();
        issue.comments.forEach((comment) => {
            const article = document.createElement('article'); article.className = 'issue-comment';
            article.innerHTML = `<span class="comment-avatar">${escapeHtml(comment.initials)}</span><div><p><strong>${escapeHtml(comment.author)}</strong><time>${escapeHtml(comment.time)}</time></p><p>${escapeHtml(comment.text)}</p></div>`;
            target.append(article);
        });
        app.querySelector('[data-comment-count]').textContent = `${issue.comments.length} ${issue.comments.length === 1 ? 'update' : 'updates'}`;
    }

    function selectIssue(id) {
        const card = list.querySelector(`[data-issue-id="${id}"]`);
        const issue = details[id];
        if (!card || !issue) return;
        activeId = id;
        cards().forEach((item) => { const active = item === card; item.classList.toggle('is-selected', active); item.setAttribute('aria-pressed', String(active)); });
        app.querySelector('[data-detail-project]').textContent = issue.project.toUpperCase();
        app.querySelector('[data-detail-title]').textContent = card.querySelector('.issue-card__body strong').textContent;
        app.querySelector('[data-detail-reporter]').textContent = issue.reporter;
        app.querySelector('[data-detail-team]').textContent = issue.team;
        app.querySelector('[data-detail-date]').textContent = issue.date;
        app.querySelector('[data-detail-description]').textContent = issue.description;
        app.querySelector('[data-detail-status]').innerHTML = badge(statusNames[card.dataset.status], statusTones[card.dataset.status]);
        app.querySelector('[data-detail-priority]').innerHTML = badge(`${priorityNames[card.dataset.priority]} priority`, priorityTones[card.dataset.priority]);
        app.querySelector('#issue-status').value = card.dataset.status;
        renderComments(issue);
        app.querySelector('.issues-content').classList.add('has-detail');
    }

    function applyFilters() {
        const query = app.querySelector('#issue-search').value.trim().toLowerCase();
        const status = app.querySelector('#status-filter').value;
        const priority = app.querySelector('#priority-filter').value;
        let visible = 0;
        cards().forEach((card) => {
            const text = card.textContent.toLowerCase();
            const show = (!query || text.includes(query)) && (status === 'all' || card.dataset.status === status) && (priority === 'all' || card.dataset.priority === priority);
            card.hidden = !show;
            if (show) visible++;
        });
        app.querySelector('[data-empty-state]').hidden = visible !== 0;
        app.querySelector('[data-issue-count]').textContent = `${visible} ${visible === 1 ? 'issue' : 'issues'}`;
        if (selected()?.hidden) {
            const first = cards().find((card) => !card.hidden);
            if (first) selectIssue(first.dataset.issueId);
            else app.querySelector('.issues-content').classList.remove('has-detail');
        }
    }

    list.addEventListener('click', (event) => { const card = event.target.closest('.issue-card'); if (card) selectIssue(card.dataset.issueId); });
    ['#issue-search', '#status-filter', '#priority-filter'].forEach((selector) => app.querySelector(selector).addEventListener('input', applyFilters));
    app.querySelector('.issues-close-detail').addEventListener('click', () => app.querySelector('.issues-content').classList.remove('has-detail'));
    app.querySelector('#focus-report').addEventListener('click', () => {
        app.querySelector('#report-form').scrollIntoView({ behavior: 'smooth', block: 'center' });
        app.querySelector('#report-description').focus({ preventScroll: true });
    });

    app.querySelector('#issue-status').addEventListener('change', (event) => {
        const card = selected();
        if (!card) return;
        card.dataset.status = event.target.value;
        card.querySelector('.issue-card__meta .ms-badge:first-child').outerHTML = badge(statusNames[event.target.value], statusTones[event.target.value]);
        selectIssue(activeId); applyFilters(); showToast(`Issue status changed to ${statusNames[event.target.value]}.`);
    });

    app.querySelector('[data-comment-form]').addEventListener('submit', (event) => {
        event.preventDefault();
        const input = app.querySelector('#issue-comment');
        const text = input.value.trim();
        if (!text || !details[activeId]) return;
        details[activeId].comments.push({ author: 'Sarah Ahmed', initials: 'SA', time: 'Just now', text });
        input.value = ''; renderComments(details[activeId]); showToast('Mock comment added to this issue.');
    });

    app.querySelector('[data-report-form]').addEventListener('submit', (event) => {
        event.preventDefault();
        const project = app.querySelector('#report-project').value;
        const description = app.querySelector('#report-description').value.trim();
        const title = description.split(/[.!?\n]/)[0].trim().slice(0, 72) || 'New project issue';
        const priority = 'medium';
        const id = `issue-${nextId++}`;
        details[id] = { project, team: 'Not specified', reporter: 'Sarah Ahmed · Teaching assistant', date: 'Just now', description, comments: [{ author: 'Sarah Ahmed', initials: 'SA', time: 'Just now', text: 'Issue reported.' }] };
        const card = document.createElement('button');
        card.type = 'button'; card.className = 'issue-card'; card.setAttribute('role', 'listitem'); card.dataset.issueId = id; card.dataset.status = 'reviewing'; card.dataset.priority = priority; card.setAttribute('aria-pressed', 'false');
        card.innerHTML = `<span class="issue-priority-mark issue-priority-mark--${priority}" aria-hidden="true"></span><span class="issue-card__body"><strong>${escapeHtml(title)}</strong><span>${escapeHtml(project)} · Reported by you</span><small>Just now</small></span><span class="issue-card__meta">${badge('Professor reviewing', 'warning')}</span>`;
        list.prepend(card);
        event.currentTarget.reset();
        app.querySelector('#status-filter').value = 'all'; app.querySelector('#priority-filter').value = 'all'; app.querySelector('#issue-search').value = '';
        applyFilters(); selectIssue(id); showToast('Mock issue added to the list. Nothing was sent to a server.');
    });
}
