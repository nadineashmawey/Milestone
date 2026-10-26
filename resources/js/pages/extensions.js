const extensions = document.querySelector('[data-extensions]');
if (extensions) {
    const data = JSON.parse(extensions.querySelector('[data-extension-data]').textContent);
    const find = selector => extensions.querySelector(selector);
    const all = selector => [...extensions.querySelectorAll(selector)];
    const milestoneSelect = find('[data-milestone]');
    const teamSelect = find('[data-team]');
    const scopeSelect = find('[data-scope]');
    const form = find('[data-extension-form]');
    const status = message => { find('[data-status]').textContent = message; };
    const stateKey = 'milestone:extension-demo:v1';
    let saved = {};
    try { saved = JSON.parse(sessionStorage.getItem(stateKey) || '{}') || {}; } catch { /* Browser session storage may be unavailable. */ }
    let requests = Array.isArray(saved.requests) ? saved.requests : structuredClone(data.requests);
    let history = Array.isArray(saved.history) ? saved.history : structuredClone(data.history);
    const riskTone = { low: 'success', medium: 'warning', high: 'danger' };
    const mile = id => data.milestones.find(item => item.id === id) || data.milestones[0];
    const team = id => data.teams.find(item => item.id === id) || data.teams[0];
    const prettyDate = (date, options = { month: 'short', day: 'numeric' }) => new Date(`${date}T12:00:00`).toLocaleDateString('en-US', options);
    const node = (tag, text, className) => { const item = document.createElement(tag); if (text !== undefined) item.textContent = text; if (className) item.className = className; return item; };
    const button = (label, action, className = '') => { const item = node('button', label, className); item.type = 'button'; item.addEventListener('click', action); return item; };
    function persist() {
        try { sessionStorage.setItem(stateKey, JSON.stringify({ requests, history })); }
        catch { status('Browser session storage is unavailable. Changes will last only until this page closes.'); }
    }
    function selectedDeadline() { return mile(milestoneSelect.value); }
    function selectedTeam() { return team(teamSelect.value); }
    function getNewDate() { return find('[data-new-date]').value; }
    function getNewTime() { return find('[data-new-time]').value || '17:00'; }
    function updateSummary() {
        const seededActive = data.history.filter(item => item.status === 'Active');
        const changedSeeded = seededActive.filter(seed => history.some(item => item.id === seed.id && item.status !== 'Active')).length;
        find('[data-active-count]').textContent = Math.max(0, data.context.activeExtensionCount + history.filter(item => !data.history.some(seed => seed.id === item.id) && item.status === 'Active').length - changedSeeded);
        const activeTeams = new Set(data.teams.filter(item => item.extensions > 0).map(item => item.id));
        history.filter(item => item.status === 'Active').forEach(item => (item.affectedTeamIds || [item.teamId]).filter(Boolean).forEach(id => activeTeams.add(id)));
        history.filter(item => item.status !== 'Active').forEach(item => (item.affectedTeamIds || [item.teamId]).filter(Boolean).forEach(id => {
            if (!history.some(active => active.status === 'Active' && (active.affectedTeamIds || [active.teamId]).includes(id))) activeTeams.delete(id);
        }));
        find('[data-team-count]').textContent = activeTeams.size;
        find('[data-pending-count]').textContent = requests.filter(item => item.status === 'Pending').length;
    }
    function renderTeam() {
        const chosen = selectedTeam();
        const chosenMilestone = selectedDeadline();
        const allTeams = scopeSelect.value === 'all';
        find('[data-selected-team-fields]').hidden = allTeams;
        find('[data-all-team-summary]').hidden = !allTeams;
        const submittedTeams = data.teams.filter(item => item.submitted);
        find('[data-submitted-summary]').textContent = submittedTeams.length
            ? `${submittedTeams.length} team${submittedTeams.length === 1 ? '' : 's'} already submitted: ${submittedTeams.map(item => item.name).join(', ')}. This change will update the deadline for them too.`
            : 'No teams have submitted this milestone yet.';
        find('[data-team-label]').textContent = chosen.name;
        find('[data-team-details]').textContent = `${chosen.members.length} members · ${chosen.leader} is team leader${chosen.submitted ? ' · Already submitted' : ''}`;
        const badge = find('[data-team-risk]');
        badge.textContent = `${chosen.risk[0].toUpperCase()}${chosen.risk.slice(1)} risk`;
        badge.className = `ms-badge ms-badge--${riskTone[chosen.risk]}`;
        const avatars = find('[data-team-avatars]');
        avatars.replaceChildren(...chosen.members.map(({ name }, index) => {
            const avatar = node('span', name[0], 'extension-avatar');
            avatar.setAttribute('aria-label', name);
            if (name === chosen.leader) avatar.title = `${name}, team leader`;
            return avatar;
        }));
        find('[data-progress-label]').textContent = `${chosen.progress}%`;
        find('[data-progress-bar]').style.setProperty('--team-progress', `${chosen.progress}%`);
        const matching = history.filter(item => item.teamId === chosen.id && item.milestoneId === chosenMilestone.id && item.status !== 'Revoked');
        find('[data-current-deadline]').textContent = `${prettyDate(chosenMilestone.date)} · ${chosenMilestone.time === '17:00' ? '5:00 PM' : chosenMilestone.time}`;
        find('[data-earlier-extensions]').textContent = matching.length || chosen.extensions ? String(Math.max(matching.length, chosen.extensions)) : 'None';
        const meta = find('[data-milestone-meta]');
        const teamCount = data.context.teamCount;
        meta.replaceChildren(...[["Current deadline", `${prettyDate(chosenMilestone.date)} · ${chosenMilestone.time === '17:00' ? '5:00 PM' : chosenMilestone.time}`], ['Weight', `${chosenMilestone.weight}%`], ['Teams', String(teamCount)], ['Submissions', `${chosenMilestone.submissions} received`]].map(([label, value]) => {
            const wrapper = node('div'); wrapper.append(node('dt', label), node('dd', value)); return wrapper;
        }));
        find('[data-deadline-help]').textContent = `Choose a date and time after the current ${prettyDate(chosenMilestone.date, { month: 'long', day: 'numeric' })} deadline.`;
        find('[data-original-date]').textContent = prettyDate(chosenMilestone.date, { month: 'short', day: 'numeric' });
        const proposed = new Date(`${getNewDate()}T${getNewTime()}:00`);
        const original = new Date(`${chosenMilestone.date}T${chosenMilestone.time}:00`);
        const valid = Boolean(getNewDate() && !Number.isNaN(proposed.getTime()) && proposed > original);
        find('[data-date-error]').hidden = valid;
        find('[data-date-error]').textContent = `Choose a date and time later than the current deadline (${prettyDate(chosenMilestone.date)}).`;
        find('[data-comparison]').hidden = !valid;
        find('[data-confirm-extension]').disabled = !valid || !form.elements.reason.value.trim() || !form.elements.sender.value.trim() || !form.elements.subject.value.trim();
        if (valid) {
            find('[data-new-date-label]').textContent = prettyDate(getNewDate(), { month: 'short', day: 'numeric' }) + ` · ${getNewTime()}`;
            const days = Math.ceil((proposed - original) / 86400000);
            find('[data-day-difference]').textContent = `+${days} day${days === 1 ? '' : 's'}`;
            find('[data-warning]').hidden = !(chosenMilestone.id === 'm2' && getNewDate() >= '2026-11-03');
            find('[data-warning]').textContent = 'The new date is on or after the next milestone (M3 opens Nov 3). Check the schedule before confirming.';
        }
        const submittedWarning = allTeams ? submittedTeams.length > 0 : chosen.submitted;
        if (submittedWarning) {
            find('[data-warning]').hidden = false;
            find('[data-warning]').textContent = allTeams
                ? `${submittedTeams.map(item => item.name).join(', ')} already submitted. The project-wide change will update their deadline too; review this impact before confirming.`
                : 'This team has already submitted. Review the impact before preparing a deadline change.';
        }
        renderTimeline();
    }
    function renderTimeline() {
        const start = new Date('2026-10-11T12:00:00');
        const end = new Date('2026-11-08T12:00:00');
        const percent = date => Math.max(0, Math.min(100, (new Date(`${date}T12:00:00`) - start) / (end - start) * 100));
        const axis = find('[data-timeline-axis]');
        axis.replaceChildren(...['2026-10-11','2026-10-18','2026-10-25','2026-11-03','2026-11-08'].map(date => node('span', prettyDate(date))));
        const rows = find('[data-timeline-rows]');
        const today = document.createElement('i'); today.className = 'timeline-today'; today.style.setProperty('--today-x', `${percent(data.context.sampleAsOf)}%`); today.append(node('span', 'Today · sample')); rows.replaceChildren(today);
        const chosen = selectedDeadline();
        data.milestones.forEach(item => {
            const row = node('div', undefined, 'timeline-row');
            const track = node('div', undefined, 'timeline-track');
            track.style.setProperty('--milestone-x', `${percent(item.date)}%`);
            const dot = node('i', undefined, `timeline-milestone-dot${item.id === chosen.id ? ' timeline-milestone-dot--selected' : ''}`);
            dot.title = `${item.name}: ${prettyDate(item.date)}`; track.append(dot); row.append(node('strong', item.short), track); rows.append(row);
        });
        data.teams.forEach(item => {
            const row = node('div', undefined, 'timeline-row');
            const track = node('div', undefined, 'timeline-track');
            const existing = history.find(entry => (entry.teamId === item.id || entry.affectedTeamIds?.includes(item.id)) && entry.milestoneId === chosen.id && entry.status === 'Active');
            const currentDate = existing?.originalDate || chosen.date;
            track.style.setProperty('--original-x', `${percent(currentDate)}%`);
            if (existing) {
                const bar = node('i', undefined, 'timeline-extension-bar');
                bar.style.setProperty('--extension-width', `${Math.max(.5, percent(existing.newDate) - percent(existing.originalDate))}%`);
                bar.title = `${existing.team}: ${prettyDate(existing.originalDate)} to ${prettyDate(existing.newDate)}`;
                track.append(bar);
            } else {
                const marker = node('i', undefined, 'timeline-milestone-dot'); marker.title = `${item.name}: original deadline ${prettyDate(chosen.date)}`; track.append(marker);
            }
            if (scopeSelect.value === 'all' || item.id === selectedTeam().id) track.classList.add('is-selected');
            row.append(node('strong', item.name), track); rows.append(row);
        });
        find('[data-timeline-selected]').textContent = `${chosen.short} selected`;
    }
    function renderRequests() {
        const list = find('[data-request-list]');
        list.replaceChildren(...requests.map(item => {
            const article = node('article', undefined, 'extension-request-item');
            const heading = node('div', undefined, 'extension-request-heading'); heading.append(node('strong', item.team));
            const badge = node('span', item.status, `ms-badge ms-badge--${item.status === 'Pending' ? 'warning' : item.status === 'Approved' ? 'success' : 'danger'}`); heading.append(badge);
            article.append(heading, node('p', `${item.date} · ${item.note}`));
            if (item.status === 'Pending') {
                const actions = node('div', undefined, 'extension-request-actions');
                actions.append(button('Record extension from this request', () => {
                    const matchedTeam = data.teams.find(entry => entry.name === item.team);
                    const matchedMilestone = data.milestones.find(entry => entry.short === item.milestone);
                    if (matchedTeam) { teamSelect.value = matchedTeam.id; scopeSelect.value = 'team'; }
                    if (matchedMilestone) milestoneSelect.value = matchedMilestone.id;
                    form.elements.reason.value = `Approved by email request. ${item.note}`;
                    form.elements.sender.value = item.sender || '';
                    form.elements.subject.value = item.subject || '';
                    renderTeam(); updateReason();
                    document.querySelector('#record-extension')?.scrollIntoView({ behavior: 'smooth' });
                    find('[data-reason-input]').focus({ preventScroll: true });
                }));
                actions.append(button('Mark declined', () => { item.status = 'Declined'; item.tone = 'danger'; persist(); renderRequests(); updateSummary(); status(`${item.team} request marked declined in this sample.`); }, 'is-danger'));
                article.append(actions);
            }
            list.append(article); return article;
        }));
        updateSummary();
    }
    function details(entry) {
        const dialog = find('[data-detail-dialog]');
        find('[data-detail-title]').textContent = `${entry.scope === 'all' ? `All ${entry.affectedTeamIds.length} teams` : entry.team} · ${entry.milestone}`;
        const content = find('[data-detail-content]');
        const details = [['Status', entry.status], ['Scope', entry.scope === 'all' ? `All project teams (${entry.affectedTeamIds.length})` : entry.team], ...(entry.scope === 'all' ? [['Affected teams', entry.affectedTeams.join(', ')]] : []), ['Original deadline', `${prettyDate(entry.originalDate, { month: 'long', day: 'numeric', year: 'numeric' })} · ${entry.originalTime || '17:00'}`], ['New deadline', `${prettyDate(entry.newDate, { month: 'long', day: 'numeric', year: 'numeric' })} · ${entry.newTime || '17:00'}`], ['Reason', entry.reason], ['Approved by', entry.approvedBy], ['Email reference', `${entry.sender} · ${entry.received}`], ['Notification selection', `Team: ${entry.notifyTeam ? 'selected' : 'not selected'} · TA: ${entry.notifyTa ? 'selected' : 'not selected'}`]];
        content.replaceChildren(...details.map(([label, value]) => { const wrapper = node('div', undefined, 'extension-detail-row'); wrapper.append(node('strong', label), node('span', value)); return wrapper; }));
        dialog.showModal();
    }
    function filteredHistory() {
        const milestoneFilter = find('[data-history-milestone]').value;
        const teamFilter = find('[data-history-team]').value;
        const statusFilter = find('[data-history-status]').value;
        const search = find('[data-history-search]').value.trim().toLocaleLowerCase();
        return history.filter(item => (milestoneFilter === 'all' || item.milestone === milestoneFilter) && (teamFilter === 'all' || item.team === teamFilter || item.affectedTeams?.includes(teamFilter)) && (statusFilter === 'all' || item.status === statusFilter) && (!search || [item.team, ...(item.affectedTeams || []), item.milestone, item.reason, item.approvedBy, item.sender].some(value => value?.toLocaleLowerCase().includes(search))));
    }
    function renderHistory() {
        const list = find('[data-history-list]');
        const entries = filteredHistory();
        list.replaceChildren(...entries.map(item => {
            const article = node('article', undefined, 'extension-history-item');
            const header = node('div', undefined, 'extension-history-heading'); header.append(node('strong', `${item.scope === 'all' ? `All ${item.affectedTeamIds.length} teams` : item.team} · ${item.milestone}`));
            const badgeTone = item.status === 'Active' ? 'review' : item.status === 'Expired' ? 'neutral' : 'danger';
            header.append(node('span', item.status, `ms-badge ms-badge--${badgeTone}`));
            const days = Math.ceil((new Date(`${item.newDate}T12:00:00`) - new Date(`${item.originalDate}T12:00:00`)) / 86400000);
            article.append(header, node('p', `${prettyDate(item.originalDate)} → ${prettyDate(item.newDate)} · +${days} days`), node('small', `${item.reasonType} · ${item.approvedBy} · Recorded ${item.recorded}`));
            const actions = node('div', undefined, 'extension-history-actions');
            actions.append(button('View details', () => details(item)));
            if (item.status === 'Active') actions.append(button('Revoke', () => {
                const dialog = find('[data-confirm-dialog]');
                find('[data-confirm-summary]').textContent = `Revoke the ${item.team} ${item.milestone} extension? The original deadline would be restored after backend integration. In this preview, the entry will be marked Revoked in this browser session.`;
                dialog.dataset.mode = 'revoke'; dialog.dataset.entry = item.id; dialog.showModal();
            }, 'is-danger'));
            article.append(actions); return article;
        }));
        find('[data-history-empty]').hidden = entries.length > 0;
    }
    function updateReason() {
        const reason = form.elements.reason.value;
        find('[data-char-count]').textContent = `${reason.length}/500`;
        find('[data-confirm-extension]').disabled = !reason.trim() || !form.elements.sender.value.trim() || !form.elements.subject.value.trim() || find('[data-comparison]').hidden;
    }
    function resetForm() {
        form.reset(); milestoneSelect.value = data.milestones.find(item => item.id === 'm2')?.id || data.milestones[0].id;
        teamSelect.value = 'team-3'; scopeSelect.value = 'team'; find('[data-new-date]').value = '2026-10-21';
        find('[data-file-name]').textContent = 'Attach email evidence'; find('[data-file-help]').textContent = 'Screenshot or PDF · Optional · Up to 25 MB';
        renderTeam(); updateReason();
        allReasonButtons();
        status('Extension form reset.');
    }
    function allReasonButtons() { extensions.querySelectorAll('[data-reason]').forEach(item => item.setAttribute('aria-pressed', 'false')); }
    function quote(value) { return `"${String(value).replaceAll('"', '""')}"`; }
    function downloadCsv() {
        const entries = [['ID', 'Team', 'Milestone', 'Original deadline', 'New deadline', 'Reason type', 'Reason', 'Approved by', 'Email sender', 'Date received', 'Status'], ...history.map(item => [item.id, item.team, item.milestone, item.originalDate, item.newDate, item.reasonType, item.reason, item.approvedBy, item.sender, item.received, item.status])];
        const content = entries.map(row => row.map(quote).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob([content], { type: 'text/csv;charset=utf-8' }));
        const link = node('a'); link.href = url; link.download = 'extension-history-sample.csv'; link.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000); status('Sample extension history downloaded as CSV.');
    }
    milestoneSelect.addEventListener('change', () => { renderTeam(); status(`${selectedDeadline().name} selected.`); });
    teamSelect.addEventListener('change', () => { renderTeam(); status(`${selectedTeam().name} selected.`); });
    scopeSelect.addEventListener('change', () => { renderTeam(); status(scopeSelect.value === 'all' ? `Deadline change applies to all ${data.context.teamCount} project teams.` : `${selectedTeam().name} selected.`); });
    form.addEventListener('input', event => { if (event.target.matches('[data-new-date],[data-new-time]')) renderTeam(); if (event.target.matches('[data-reason-input], [name=sender], [name=subject]')) updateReason(); });
    find('[data-days]').closest('.extension-step-content').addEventListener('click', event => {
        const preset = event.target.closest('[data-days]');
        if (preset) {
            const date = new Date(`${selectedDeadline().date}T12:00:00`); date.setDate(date.getDate() + Number(preset.dataset.days));
            find('[data-new-date]').value = date.toISOString().slice(0, 10); renderTeam();
        }
        if (event.target.closest('[data-custom]')) { find('[data-new-date]').focus(); status('Choose a custom date and time after the original deadline.'); }
    });
    extensions.querySelectorAll('[data-reason]').forEach(item => item.addEventListener('click', () => {
        allReasonButtons(); item.setAttribute('aria-pressed', 'true');
        form.elements.reason.value = item.dataset.reason === 'Other' ? '' : `${item.dataset.reason}. Supporting details were reviewed by the professor.`;
        updateReason(); find('[data-reason-input]').focus();
    }));
    find('[data-evidence]').addEventListener('change', event => {
        const file = event.target.files[0]; if (!file) return;
        const accepted = ['application/pdf', 'image/png', 'image/jpeg'].includes(file.type) || /\.(pdf|png|jpe?g)$/i.test(file.name);
        if (!accepted || file.size > 25 * 1024 * 1024) {
            event.target.value = ''; find('[data-file-name]').textContent = 'Attach email evidence';
            find('[data-file-help]').textContent = !accepted ? 'Choose a PDF, PNG, or JPEG file.' : 'File is larger than 25 MB.';
            status('Evidence file was not accepted. Choose a PDF, PNG, or JPEG file up to 25 MB.'); return;
        }
        find('[data-file-name]').textContent = file.name; find('[data-file-help]').textContent = `${(file.size / 1024).toFixed(0)} KB · Selected locally; upload is not connected.`;
        status('Evidence selected locally. It has not been uploaded.');
    });
    form.addEventListener('submit', event => {
        event.preventDefault(); renderTeam(); updateReason();
        if (find('[data-confirm-extension]').disabled || !form.reportValidity()) { status('Complete the required fields and choose a deadline after the current date.'); return; }
        const dialog = find('[data-confirm-dialog]');
        const chosen = selectedTeam(); const chosenMilestone = selectedDeadline();
        const scopeDescription = scopeSelect.value === 'all' ? `All ${data.context.teamCount} teams (${data.context.studentCount} students)${data.teams.some(item => item.submitted) ? `, including ${data.teams.filter(item => item.submitted).map(item => item.name).join(', ')} who already submitted` : ''}` : chosen.name;
        find('[data-confirm-summary]').textContent = `${scopeDescription} · ${chosenMilestone.name} · ${prettyDate(chosenMilestone.date, { month: 'short', day: 'numeric' })} → ${prettyDate(getNewDate(), { month: 'short', day: 'numeric', year: 'numeric' })} at ${getNewTime()}. Reason: ${form.elements.reason.value}`;
        dialog.dataset.mode = 'record'; dialog.dataset.entry = ''; dialog.showModal();
    });
    find('[data-dialog-close]').addEventListener('click', () => find('[data-confirm-dialog]').close());
    find('[data-dialog-confirm]').addEventListener('click', () => {
        const dialog = find('[data-confirm-dialog]');
        if (dialog.dataset.mode === 'revoke') {
            const entry = history.find(item => item.id === dialog.dataset.entry);
            if (entry) { entry.status = 'Revoked'; persist(); renderHistory(); renderTimeline(); updateSummary(); status(`${entry.team} extension marked revoked in this browser session.`); }
        } else {
            const chosen = selectedTeam(); const chosenMilestone = selectedDeadline();
            const days = Math.ceil((new Date(`${getNewDate()}T${getNewTime()}:00`) - new Date(`${chosenMilestone.date}T${chosenMilestone.time}:00`)) / 86400000);
            const allTeams = scopeSelect.value === 'all';
            const affectedTeams = data.teams.map(item => item.name);
            const newEntry = { id: `sample-${Date.now()}`, team: allTeams ? `All ${affectedTeams.length} teams` : chosen.name, teamId: allTeams ? null : chosen.id, scope: allTeams ? 'all' : 'team', affectedTeamIds: allTeams ? data.teams.map(item => item.id) : [chosen.id], affectedTeams: allTeams ? affectedTeams : [chosen.name], milestone: chosenMilestone.short, milestoneId: chosenMilestone.id, originalDate: chosenMilestone.date, originalTime: chosenMilestone.time, newDate: getNewDate(), newTime: getNewTime(), reasonType: form.elements.reason.value.split(/[.:]/)[0], reason: form.elements.reason.value, approvedBy: data.context.professor, recorded: 'Today · sample', status: 'Active', tone: 'review', sender: form.elements.sender.value, received: form.elements.received.value || 'Not recorded', notifyTeam: form.elements.notify_team.checked, notifyTa: form.elements.notify_ta.checked };
            history.unshift(newEntry); persist(); renderHistory(); renderTimeline(); updateSummary();
            status(`Preview recorded for ${allTeams ? `all ${affectedTeams.length} teams` : chosen.name} · ${chosenMilestone.short} · +${days} days. No deadline changed and no notifications were sent.`);
            resetForm();
        }
        dialog.close();
    });
    all('[data-history-milestone],[data-history-team],[data-history-status]').forEach(select => select.addEventListener('change', renderHistory));
    find('[data-history-search]').addEventListener('input', renderHistory);
    find('[data-export]').addEventListener('click', downloadCsv);
    find('[data-scroll-form]').addEventListener('click', () => { document.querySelector('#record-extension')?.scrollIntoView({ behavior: 'smooth' }); find('[data-milestone]').focus({ preventScroll: true }); });
    find('[data-cancel]').addEventListener('click', resetForm);
    find('[data-log-request]').addEventListener('click', () => find('[data-request-dialog]').showModal());
    find('[data-request-close]').addEventListener('click', () => find('[data-request-dialog]').close());
    find('[data-request-save]').addEventListener('click', () => {
        const note = find('[data-request-note]').value.trim();
        if (!note) { find('[data-request-note]').setCustomValidity('Add a short request summary.'); find('[data-request-note]').reportValidity(); find('[data-request-note]').setCustomValidity(''); return; }
        requests.unshift({ id: `request-${Date.now()}`, team: find('[data-request-team]').value, milestone: find('[data-request-milestone]').value, date: 'Today · sample', note, status: 'Pending', tone: 'warning', sender: '', subject: '' });
        find('[data-request-note]').value = ''; persist(); renderRequests(); find('[data-request-dialog]').close(); status('Sample email request logged in this browser session.');
    });
    find('[data-detail-close]').addEventListener('click', () => find('[data-detail-dialog]').close());
    find('[data-confirm-dialog]').addEventListener('click', event => { if (event.target === event.currentTarget) event.currentTarget.close(); });
    find('[data-request-dialog]').addEventListener('click', event => { if (event.target === event.currentTarget) event.currentTarget.close(); });
    find('[data-detail-dialog]').addEventListener('click', event => { if (event.target === event.currentTarget) event.currentTarget.close(); });
    find('[data-new-date]').value = '2026-10-21';
    renderRequests(); renderHistory(); renderTeam(); updateReason();
}
