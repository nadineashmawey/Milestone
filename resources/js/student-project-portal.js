const root = document.querySelector('[data-project-board]');

if (root) {
    const read = (key, fallback) => { try { return JSON.parse(localStorage.getItem(`milestone:${key}`)) ?? fallback; } catch { return fallback; } };
    const save = (key, value) => { try { localStorage.setItem(`milestone:${key}`, JSON.stringify(value)); return true; } catch { return false; } };
    const escape = (value = '') => String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
    const dialog = root.querySelector('[data-portal-dialog]');
    const dialogContent = root.querySelector('[data-dialog-content]');
    const initials = (name) => name.split(/\s+/).map((part) => part[0]).slice(0, 2).join('').toUpperCase();
    const sessionFiles = new Map();
    let activity = read('activity', [
        { category: 'Tasks', person: 'Aya Hassan', initials: 'AH', text: 'created the project brief', related: 'Project brief', time: 'Oct 7 · 9:14 AM' },
        { category: 'Files', person: 'Nour Saleh', initials: 'NS', text: 'uploaded the revised ERD', related: 'database-schema.pkt', time: 'Oct 14 · 10:18 AM' },
        { category: 'Tasks', person: 'Malek Mansour', initials: 'MM', text: 'updated the API contract', related: 'API contract', time: 'Oct 14 · 10:31 AM' },
        { category: 'Submissions', person: 'Aya Hassan', initials: 'AH', text: 'edited the M2 Design Document draft', related: 'design-document-v3.pdf', time: 'Today · 10:42 AM' },
        { category: 'Tasks', person: 'Aya Hassan', initials: 'AH', text: 'moved a task to Review', related: 'Wireframes', time: 'Today · 10:47 AM' },
        { category: 'Risks', person: 'Malek Mansour', initials: 'MM', text: 'marked a risk as being mitigated', related: 'Delayed API contract', time: 'Today · 11:05 AM' },
    ]);
    const addActivity = (category, text, related = '') => {
        activity.unshift({ category, person: 'Aya Hassan', initials: 'AH', text, related, time: `Today · ${new Intl.DateTimeFormat('en', { hour: 'numeric', minute: '2-digit' }).format(new Date())}` });
        activity = activity.slice(0, 60); save('activity', activity); renderActivity();
    };
    root.addEventListener('portal:activity', (event) => addActivity('Tasks', event.detail?.text || 'updated a task', event.detail?.related || ''));

    const members = [
        ['Aya Hassan', 'Leader', 'Assigned by Professor Lee'],
        ['Malek Mansour', 'Member', ''],
        ['Nour Saleh', 'Member', ''],
    ];
    root.querySelector('[data-members]').innerHTML = members.map(([name, role, label]) => `<div class="portal-member"><span class="portal-avatar">${initials(name)}</span><span class="portal-member-info"><strong>${escape(name)}</strong>${label ? `<small>${escape(label)}</small>` : ''}</span><span class="portal-role ${role === 'Leader' ? 'portal-role--leader' : ''}">${role}</span></div>`).join('');

    const grades = [
        { name: 'M1 Proposal', score: '88%', status: 'Published', feedback: 'Clear problem statement and a thoughtful research direction.' },
        { name: 'M2 Design Document', score: '—', status: 'Pending assessment', feedback: 'Submitted work will appear here after supervisor review.' },
        { name: 'M3 Implementation', score: '—', status: 'Not submitted', feedback: 'Assessment is not available yet.' },
        { name: 'Final Presentation', score: '—', status: 'Not submitted', feedback: 'Assessment is not available yet.' },
    ];
    root.querySelector('[data-grade-cards]').innerHTML = grades.map((grade) => `<article class="portal-card portal-grade-card"><div><h3>${escape(grade.name)}</h3><span class="portal-badge ${grade.score === '—' ? 'portal-badge--muted' : 'portal-badge--green'}">${escape(grade.status)}</span></div><strong class="portal-grade-score">${grade.score}</strong><p>${escape(grade.feedback)}</p><small>Illustrative sample data</small></article>`).join('');

    const initialMessages = [
        { person: 'Nour Saleh', initials: 'NS', time: '10:18 AM', text: 'I uploaded the revised ERD and added the missing cardinality labels.', own: false },
        { person: 'Malek Mansour', initials: 'MM', time: '10:31 AM', text: "Great. I'll update the API contract after lunch.", own: false },
        { person: 'Aya Hassan', initials: 'AH', time: '10:42 AM', text: "Thanks. Let's have the final PDF ready by 4 PM.", own: true },
    ];
    let messages = read('chat', initialMessages);
    const renderChat = () => {
        const feed = root.querySelector('[data-chat-feed]');
        feed.innerHTML = messages.length ? messages.map((message) => `<article class="portal-message ${message.own ? 'portal-message--own' : ''}"><span class="portal-avatar">${escape(message.initials)}</span><div class="portal-message-body"><div><strong>${escape(message.person)}</strong><time>${escape(message.time)}</time></div><p>${escape(message.text).replace(/\n/g, '<br>')}</p></div></article>`).join('') : '<p class="portal-empty">No messages yet. Start the conversation with your team.</p>';
        feed.scrollTop = feed.scrollHeight;
    };
    renderChat();
    root.querySelector('[data-chat-form]').addEventListener('submit', (event) => {
        event.preventDefault(); const input = event.currentTarget.elements.message; const text = input.value.trim(); if (!text) return;
        const time = new Intl.DateTimeFormat('en', { hour: 'numeric', minute: '2-digit' }).format(new Date());
        messages.push({ person: 'Aya Hassan', initials: 'AH', time, text, own: true }); save('chat', messages); renderChat(); input.value = ''; addActivity('Tasks', 'sent a team chat message', 'Project chat');
    });
    root.querySelector('[data-chat-form] textarea').addEventListener('keydown', (event) => { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); event.currentTarget.form.requestSubmit(); } });

    let sharedFiles = read('files', [
        { id: 'seed-1', name: 'design-document-v3.pdf', size: 2.8, uploader: 'Aya', version: 3, date: 'Today', type: 'document', sample: true },
        { id: 'seed-2', name: 'database-schema.pkt', size: 8.4, uploader: 'Nour', version: 2, date: 'Yesterday', type: 'document', sample: true },
        { id: 'seed-3', name: 'prototype-assets.zip', size: 14.2, uploader: 'Malek', version: 4, date: 'Oct 5', type: 'archive', sample: true },
        { id: 'seed-4', name: 'research-notes.docx', size: 1.1, uploader: 'Aya', version: 1, date: 'Oct 3', type: 'document', sample: true },
    ]);
    const fileType = (name) => /\.(zip|rar|7z)$/i.test(name) ? 'archive' : /\.(png|jpe?g|gif|svg|webp)$/i.test(name) ? 'image' : 'document';
    const renderFiles = () => {
        const term = (root.querySelector('[data-file-search]')?.value || '').toLowerCase(); const filter = root.querySelector('[data-file-filter]')?.value || 'all';
        const filtered = sharedFiles.filter((file) => file.name.toLowerCase().includes(term) && (filter === 'all' || file.type === filter));
        root.querySelector('[data-shared-file-list]').innerHTML = filtered.length ? filtered.map((file) => `<article class="portal-file-row"><span class="portal-file-icon" aria-hidden="true">${file.type === 'archive' ? 'ZIP' : file.type === 'image' ? 'IMG' : 'DOC'}</span><span class="portal-file-name"><strong>${escape(file.name)}</strong><small>${file.size.toFixed(1)} MB · Uploaded by ${escape(file.uploader)} · ${escape(file.date)}${file.sample ? ' · sample' : ' · local'}</small></span><span class="portal-badge portal-badge--muted">v${file.version}</span><button class="portal-text-button" type="button" data-download-file="${escape(file.id)}">Download</button></article>`).join('') : '<p class="portal-empty">No files match these filters.</p>';
    };
    renderFiles();
    root.querySelector('[data-file-search]').addEventListener('input', renderFiles); root.querySelector('[data-file-filter]').addEventListener('change', renderFiles);
    const acceptFiles = (list, destination) => {
        const allowed = /\.(pdf|docx?|pptx?|xlsx?|txt|md|png|jpe?g|gif|svg|webp|zip|rar|7z)$/i;
        [...list].forEach((file) => {
            if (file.size > 25 * 1024 * 1024) { setMessage(destination === 'submission' ? '[data-submit-message]' : '[data-file-upload-message]', `${file.name} exceeds the 25 MB per-file limit.`); return; }
            if (!allowed.test(file.name)) { setMessage(destination === 'submission' ? '[data-submit-message]' : '[data-file-upload-message]', `${file.name} is not a supported file type.`); return; }
            const id = `local-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`; sessionFiles.set(id, file);
            const item = { id, name: file.name, size: file.size / 1024 / 1024, uploader: 'Aya', version: 1, date: 'Just now', type: fileType(file.name), sample: false, ...(destination === 'submission' ? { session: true } : {}) };
            if (destination === 'submission') submissionFiles.push(item); else sharedFiles.unshift(item);
            addActivity(destination === 'submission' ? 'Submissions' : 'Files', destination === 'submission' ? 'added a file to the submission draft' : 'added a file to shared files', file.name);
        });
        save('files', sharedFiles); save('submission-files', submissionFiles); renderFiles(); renderSubmissionFiles();
        if (destination === 'shared') setMessage('[data-file-upload-message]', 'File metadata was added locally. No server upload occurred.');
    };
    root.querySelector('[data-shared-files]').addEventListener('change', (event) => { acceptFiles(event.target.files, 'shared'); event.target.value = ''; });
    const sharedDrop = root.querySelector('[data-shared-drop]');
    ['dragenter', 'dragover'].forEach((name) => sharedDrop.addEventListener(name, (event) => { event.preventDefault(); sharedDrop.classList.add('is-dragging'); }));
    ['dragleave', 'drop'].forEach((name) => sharedDrop.addEventListener(name, (event) => { event.preventDefault(); sharedDrop.classList.remove('is-dragging'); if (name === 'drop') acceptFiles(event.dataTransfer.files, 'shared'); }));
    root.querySelector('[data-shared-file-list]').addEventListener('click', (event) => {
        const button = event.target.closest('[data-download-file]'); if (!button) return;
        const file = sharedFiles.find((item) => item.id === button.dataset.downloadFile); if (!file) return;
        const blob = sessionFiles.has(file.id) ? sessionFiles.get(file.id) : new Blob([`Sample prototype file record: ${file.name}\nNo original file contents are available.`], { type: 'text/plain' });
        const url = URL.createObjectURL(blob); const link = document.createElement('a'); link.href = url; link.download = sessionFiles.has(file.id) ? file.name : `${file.name}.sample.txt`; link.click(); URL.revokeObjectURL(url);
    });

    const validTypes = '.pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.md,.png,.jpg,.jpeg,.gif,.svg,.zip,.rar,.7z';
    let submissionFiles = read('submission-files', []);
    const renderSubmissionFiles = () => { root.querySelector('[data-submit-file-list]').innerHTML = submissionFiles.map((file) => `<li><span>${escape(file.name)} <small>${file.size.toFixed(1)} MB${file.session ? sessionFiles.has(file.id) ? ' · file available this session' : ' · local metadata only; reselect to attach file contents' : ''}</small></span><button class="portal-text-button" type="button" data-remove-submit-file="${escape(file.id)}">Remove</button></li>`).join(''); };
    renderSubmissionFiles();
    const submitInput = root.querySelector('[data-submit-files]');
    root.querySelector('[data-choose-submit-files]').addEventListener('click', () => submitInput.click());
    submitInput.addEventListener('change', (event) => { acceptFiles(event.target.files, 'submission'); event.target.value = ''; });
    const uploadZone = root.querySelector('[data-upload-zone]');
    ['dragenter', 'dragover'].forEach((name) => uploadZone.addEventListener(name, (event) => { event.preventDefault(); uploadZone.classList.add('is-dragging'); }));
    ['dragleave', 'drop'].forEach((name) => uploadZone.addEventListener(name, (event) => { event.preventDefault(); uploadZone.classList.remove('is-dragging'); if (name === 'drop') acceptFiles(event.dataTransfer.files, 'submission'); }));
    const setMessage = (selector, message) => { const element = root.querySelector(selector); if (element) element.textContent = message; };
    root.querySelector('[data-submit-file-list]').addEventListener('click', (event) => { const button = event.target.closest('[data-remove-submit-file]'); if (!button) return; submissionFiles = submissionFiles.filter((file) => file.id !== button.dataset.removeSubmitFile); sessionFiles.delete(button.dataset.removeSubmitFile); save('submission-files', submissionFiles); renderSubmissionFiles(); });
    const submissionForm = root.querySelector('[data-submission-form]');
    const saveDraft = () => {
        const link = submissionForm.elements.link.value.trim();
        if (link && !/^https?:\/\//i.test(link)) { submissionForm.elements.link.setCustomValidity('Enter a complete URL starting with http:// or https://'); submissionForm.elements.link.reportValidity(); return false; }
        submissionForm.elements.link.setCustomValidity('');
        const ok = save('submission-draft', { link, note: submissionForm.elements.note.value, savedAt: new Date().toISOString(), metadata: { fileName: 'design-document-v3.pdf', edited: 'Today at 10:42 AM' } });
        setMessage('[data-draft-status]', ok ? `Draft saved locally at ${new Intl.DateTimeFormat('en', { hour: 'numeric', minute: '2-digit' }).format(new Date())}.` : 'Draft could not be saved in this browser.');
        setMessage('[data-submit-message]', 'This is a local draft; no files or data were uploaded to a server.');
        return ok;
    };
    const savedDraft = read('submission-draft', null); if (savedDraft) { submissionForm.elements.link.value = savedDraft.link || ''; submissionForm.elements.note.value = savedDraft.note || ''; }
    root.querySelector('[data-save-draft]').addEventListener('click', saveDraft);
    submissionForm.addEventListener('submit', (event) => {
        event.preventDefault(); if (!saveDraft()) return;
        showDialog('<p class="projects-eyebrow">Confirm milestone</p><h2 id="dialog-title">Submit this milestone?</h2><p>Your selected file details will be marked submitted in this browser only. This prototype does not upload files or notify your supervisor.</p><div class="portal-actions"><button class="ms-button ms-button--secondary" type="button" data-dialog-cancel>Cancel</button><button class="ms-button" type="button" data-confirm-submit>Confirm submission</button></div>');
    });

    const renderActivity = () => {
        const filter = root.querySelector('[data-activity-filter]').value; const items = activity.filter((item) => filter === 'all' || item.category === filter);
        root.querySelector('[data-activity]').innerHTML = items.length ? items.map((item) => `<article class="portal-activity-item"><span class="portal-avatar">${escape(item.initials)}</span><div><p><strong>${escape(item.person)}</strong> ${escape(item.text)}${item.related ? ` <span class="portal-activity-related">${escape(item.related)}</span>` : ''}</p><time>${escape(item.time)}</time></div><span class="portal-badge portal-badge--muted">${escape(item.category)}</span></article>`).join('') : '<p class="portal-empty">No activity in this category yet.</p>';
    };
    renderActivity(); root.querySelector('[data-activity-filter]').addEventListener('change', renderActivity);

    const sampleRisks = [
        { id: 'risk-api', title: 'Delayed API contract', description: 'Unresolved endpoint ownership could delay integration work.', likelihood: 'Possible', impact: 'Major', severity: 'High', owner: 'Malek Mansour', mitigation: 'Agree on endpoint owners and review the contract at the next team check-in.', status: 'Mitigating' },
        { id: 'risk-db', title: 'Incomplete database schema', description: 'Missing relationships may cause rework during implementation.', likelihood: 'Possible', impact: 'Moderate', severity: 'Medium', owner: 'Nour Saleh', mitigation: 'Validate the ERD against the approved use cases.', status: 'Open' },
        { id: 'risk-test', title: 'Insufficient usability testing', description: 'A small test sample could leave navigation issues undiscovered.', likelihood: 'Unlikely', impact: 'Moderate', severity: 'Medium', owner: 'Aya Hassan', mitigation: 'Recruit two additional student testers.', status: 'Open' },
        { id: 'risk-deadline', title: 'Milestone submission deadline', description: 'Final document review time is limited before the due date.', likelihood: 'Possible', impact: 'Major', severity: 'High', owner: 'Aya Hassan', mitigation: 'Set an internal handoff one day before the deadline.', status: 'Resolved' },
    ];
    let risks = read('risks', sampleRisks);
    const renderRisks = () => {
        const term = root.querySelector('[data-risk-search]').value.toLowerCase(); const filter = root.querySelector('[data-risk-filter]').value;
        const filtered = risks.filter((risk) => `${risk.title} ${risk.description} ${risk.owner}`.toLowerCase().includes(term) && (filter === 'all' || risk.status === filter));
        root.querySelector('[data-risk-stats]').innerHTML = [['Total risks', risks.length], ['High priority', risks.filter((risk) => risk.severity === 'High' && risk.status !== 'Resolved').length], ['Active mitigations', risks.filter((risk) => risk.status === 'Mitigating').length], ['Resolved', risks.filter((risk) => risk.status === 'Resolved').length]].map(([label, value]) => `<article class="portal-card"><span>${label}</span><strong>${value}</strong></article>`).join('');
        root.querySelector('[data-risk-list]').innerHTML = filtered.length ? filtered.map((risk) => `<article class="portal-risk-row"><div class="portal-risk-main"><div><h3>${escape(risk.title)}</h3><span class="portal-badge portal-badge--${risk.status === 'Resolved' ? 'green' : risk.severity === 'High' ? 'coral' : 'yellow'}">${escape(risk.severity)} · ${escape(risk.status)}</span></div><p>${escape(risk.description)}</p><small>Likelihood: ${escape(risk.likelihood)} · Impact: ${escape(risk.impact)} · Owner: ${escape(risk.owner)}</small><p><strong>Mitigation:</strong> ${escape(risk.mitigation)}</p></div><label class="portal-status-control">Status<select class="ms-input" data-risk-status="${escape(risk.id)}"><option ${risk.status === 'Open' ? 'selected' : ''}>Open</option><option ${risk.status === 'Mitigating' ? 'selected' : ''}>Mitigating</option><option ${risk.status === 'Resolved' ? 'selected' : ''}>Resolved</option></select></label><button class="portal-text-button" type="button" data-edit-risk="${escape(risk.id)}">Edit</button></article>`).join('') : '<p class="portal-empty">No risks match your search.</p>';
    };
    renderRisks(); root.querySelector('[data-risk-search]').addEventListener('input', renderRisks); root.querySelector('[data-risk-filter]').addEventListener('change', renderRisks);

    const schedule = [
        { title: 'API contract', row: 'Malek Mansour', start: 0, span: 3, kind: 'development' }, { title: 'M2 Design Document', row: 'Whole team', start: 0, span: 6, kind: 'milestone' }, { title: 'M2 due', row: 'Whole team', start: 6, span: 1, kind: 'deadline' }, { title: 'Database schema', row: 'Nour Saleh', start: 1, span: 4, kind: 'development' }, { title: 'Auth endpoints', row: 'Omar Khaled', start: 3, span: 5, kind: 'development' }, { title: 'UI mockups', row: 'Salma Nabil', start: 2, span: 4, kind: 'development' }, { title: 'Usability test', row: 'Aya Hassan', start: 7, span: 3, kind: 'development' }, { title: 'Write test plan', row: 'Unassigned', start: 9, span: 3, kind: 'development' },
    ];
    let periodOffset = 0; let calendarView = 'timeline';
    const renderCalendar = () => {
        const date = new Date(2026, 9 + periodOffset, 1); const monthName = new Intl.DateTimeFormat('en', { month: 'long', year: 'numeric' }).format(date); root.querySelector('[data-calendar-title]').textContent = monthName;
        const first = new Date(date.getFullYear(), date.getMonth(), 12); const days = Array.from({ length: 13 }, (_, i) => new Date(first.getFullYear(), first.getMonth(), first.getDate() + i));
        const rows = ['Aya Hassan', 'Omar Khaled', 'Salma Nabil', 'Whole team', 'Unassigned'];
        const dates = days.map((day) => `<span class="portal-day"><small>${new Intl.DateTimeFormat('en', { weekday: 'short' }).format(day)}</small><strong>${day.getDate()}</strong></span>`).join('');
        const rowHtml = rows.map((row) => { const items = schedule.filter((item) => item.row === row); const bars = items.map((item) => `<button class="portal-timeline-bar portal-timeline-bar--${item.kind}" style="--start:${item.start + 1};--span:${item.span}" type="button" data-schedule-detail="${escape(item.title)}">${escape(item.title)}</button>`).join(''); return `<div class="portal-timeline-row"><div class="portal-timeline-person"><span class="portal-avatar">${row === 'Whole team' || row === 'Unassigned' ? '·' : initials(row)}</span><strong>${escape(row)}</strong><small>${items.length} scheduled</small></div><div class="portal-date-grid" style="--day-count:${days.length}">${bars}</div></div>`; }).join('');
        const timeline = `<div class="portal-timeline"><div class="portal-timeline-axis"><div class="portal-timeline-person"><strong>Team member</strong></div><div class="portal-date-grid portal-date-head" style="--day-count:${days.length}">${dates}</div></div>${rowHtml}</div>`;
        const list = `<div class="portal-schedule-list">${schedule.map((item) => { const itemDate = new Date(first.getFullYear(), first.getMonth(), first.getDate() + item.start); return `<button type="button" class="portal-schedule-item portal-schedule-item--${item.kind}" data-schedule-detail="${escape(item.title)}"><strong>${escape(item.title)}</strong><span>${escape(item.row)}</span><small>${new Intl.DateTimeFormat('en', { month: 'short', day: 'numeric', year: 'numeric' }).format(itemDate)}</small></button>`; }).join('')}</div>`;
        root.querySelector('[data-calendar]').innerHTML = calendarView === 'timeline' ? timeline : list;
        root.querySelector('[data-calendar-summary]').textContent = `${schedule.length} scheduled items across ${rows.length} team rows · Illustrative internal schedule only.`;
    };
    renderCalendar();

    function showDialog(markup) { dialogContent.innerHTML = markup; if (!dialog.open) dialog.showModal(); }
    root.querySelector('[data-leave-request]').addEventListener('click', () => showDialog('<p class="projects-eyebrow">Team formation</p><h2 id="dialog-title">Request to leave Team 1?</h2><p>Leaving a team requires academic approval. Submitting this request does not remove you from the team; it creates a pending request in this local prototype.</p><div class="portal-actions"><button class="ms-button ms-button--secondary" type="button" data-dialog-cancel>Cancel</button><button class="ms-button portal-button--coral" type="button" data-confirm-leave>Submit request</button></div>'));
    root.querySelector('[data-add-risk]').addEventListener('click', () => showRiskForm());
    const showRiskForm = (risk = {}) => showDialog(`<p class="projects-eyebrow">Risk register</p><h2 id="dialog-title">${risk.id ? 'Edit risk' : 'Add a risk'}</h2><form class="portal-form" data-risk-form><input type="hidden" name="id" value="${escape(risk.id || '')}"><label>Risk title<input required class="ms-input" name="title" value="${escape(risk.title || '')}" placeholder="Describe the risk"></label><label>Description<textarea class="ms-input" name="description" required rows="2">${escape(risk.description || '')}</textarea></label><div class="portal-form-row"><label>Likelihood<select class="ms-input" name="likelihood"><option>Unlikely</option><option ${risk.likelihood === 'Possible' ? 'selected' : ''}>Possible</option><option ${risk.likelihood === 'Likely' ? 'selected' : ''}>Likely</option></select></label><label>Impact<select class="ms-input" name="impact"><option>Minor</option><option ${risk.impact === 'Moderate' ? 'selected' : ''}>Moderate</option><option ${risk.impact === 'Major' ? 'selected' : ''}>Major</option></select></label></div><label>Severity<select class="ms-input" name="severity"><option>Low</option><option ${risk.severity === 'Medium' ? 'selected' : ''}>Medium</option><option ${risk.severity === 'High' ? 'selected' : ''}>High</option></select></label><label>Owner<input class="ms-input" name="owner" value="${escape(risk.owner || 'Aya Hassan')}"></label><label>Mitigation plan<textarea class="ms-input" name="mitigation" rows="2">${escape(risk.mitigation || '')}</textarea></label><label>Status<select class="ms-input" name="status"><option>Open</option><option ${risk.status === 'Mitigating' ? 'selected' : ''}>Mitigating</option><option ${risk.status === 'Resolved' ? 'selected' : ''}>Resolved</option></select></label><div class="portal-actions"><button class="ms-button ms-button--secondary" type="button" data-dialog-cancel>Cancel</button><button class="ms-button" type="submit">Save risk</button></div></form>`);
    dialog.addEventListener('click', (event) => { if (event.target === dialog || event.target.closest('[data-dialog-cancel]')) dialog.close(); });
    dialog.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-risk-form]'); if (!form) return; event.preventDefault(); const data = new FormData(form); const existing = data.get('id'); const risk = Object.fromEntries(data.entries()); risk.id = existing || `risk-${Date.now()}`; risk.severity ||= 'Medium';
        const index = risks.findIndex((item) => item.id === risk.id); if (index >= 0) risks[index] = { ...risks[index], ...risk }; else risks.unshift(risk); save('risks', risks); renderRisks(); dialog.close(); addActivity('Risks', existing ? 'updated a risk' : 'added a risk', risk.title);
    });
    dialog.addEventListener('click', (event) => {
        if (event.target.closest('[data-confirm-leave]')) { save('leave-request', { status: 'Pending academic approval', requestedAt: new Date().toISOString() }); dialog.close(); root.querySelector('[data-leave-request]').textContent = 'Request pending academic approval'; root.querySelector('[data-leave-request]').disabled = true; addActivity('Submissions', 'submitted a team-leaving request', 'Pending academic approval'); }
        if (event.target.closest('[data-confirm-submit]')) { root.querySelector('[data-submit-status]').textContent = 'Submitted · local prototype'; save('submission-status', 'Submitted · local prototype'); dialog.close(); setMessage('[data-draft-status]', 'Submission marked in this browser only. No server upload occurred.'); addActivity('Submissions', 'marked the M2 milestone submitted locally', 'M2 Design Document'); }
    });
    const leaveState = read('leave-request', null); if (leaveState) { root.querySelector('[data-leave-request]').textContent = 'Request pending academic approval'; root.querySelector('[data-leave-request]').disabled = true; }
    const submissionState = read('submission-status', null); if (submissionState) root.querySelector('[data-submit-status]').textContent = submissionState;
    root.querySelector('[data-risk-list]').addEventListener('change', (event) => { const select = event.target.closest('[data-risk-status]'); if (!select) return; const risk = risks.find((item) => item.id === select.dataset.riskStatus); if (!risk) return; risk.status = select.value; save('risks', risks); renderRisks(); addActivity('Risks', `changed risk status to ${risk.status}`, risk.title); });
    root.querySelector('[data-risk-list]').addEventListener('click', (event) => { const button = event.target.closest('[data-edit-risk]'); if (button) showRiskForm(risks.find((risk) => risk.id === button.dataset.editRisk)); });
    root.querySelector('[data-calendar-prev]').addEventListener('click', () => { periodOffset--; renderCalendar(); }); root.querySelector('[data-calendar-next]').addEventListener('click', () => { periodOffset++; renderCalendar(); });
    root.querySelectorAll('[data-calendar-view]').forEach((button) => button.addEventListener('click', () => { calendarView = button.dataset.calendarView; root.querySelectorAll('[data-calendar-view]').forEach((item) => item.classList.toggle('is-active', item === button)); renderCalendar(); }));
    root.querySelector('[data-calendar]').addEventListener('click', (event) => { const button = event.target.closest('[data-schedule-detail]'); if (button) showDialog(`<p class="projects-eyebrow">Scheduled item</p><h2 id="dialog-title">${escape(button.dataset.scheduleDetail)}</h2><p>This is an illustrative internal schedule item. External calendar sync is not connected.</p><button class="ms-button" type="button" data-dialog-cancel>Close</button>`); });
    root.querySelector('[data-shared-file-list]').insertAdjacentHTML('afterend', '<p class="portal-inline-status" data-file-upload-message aria-live="polite"></p>');
    if (submissionState) setMessage('[data-submit-message]', 'This submission status is stored locally in this prototype; no files were uploaded to a server.');
}
