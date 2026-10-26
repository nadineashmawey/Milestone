const reviews = document.querySelector('[data-reviews]');
if (reviews) {
    const submissions = JSON.parse(reviews.querySelector('[data-submissions-json]').textContent);
    const find = selector => reviews.querySelector(selector);
    const all = selector => [...reviews.querySelectorAll(selector)];
    let selected = submissions[0];
    let version = selected.versions.at(-1);
    let filter = 'All';
    const maximums = all('[data-score]').map(input => Number(input.max));
    const storageKey = 'milestone:review-demo-drafts:v2';
    let saved = {};
    try {
        const current = sessionStorage.getItem(storageKey);
        saved = JSON.parse(current || sessionStorage.getItem('milestone:review-demo-drafts:v1') || '{}') || {};
        if (!current) {
            Object.values(saved).forEach(item => {
                if (Array.isArray(item?.scores)) item.scores = item.scores.map((score, i) => score * maximums[i] / 10);
            });
        }
    } catch { /* Draft persistence may be unavailable. */ }
    const drafts = Object.fromEntries(submissions.map(item => {
        const draft = saved[item.id];
        const valid = draft && Array.isArray(draft.scores) && draft.scores.length === maximums.length && draft.scores.every((score, i) => Number.isFinite(score) && Number.isInteger(score * 2) && score >= 0 && score <= maximums[i]) && typeof draft.feedback === 'string' && typeof draft.adjustment === 'string';
        return [item.id, valid ? draft : { scores: [...item.scores], feedback: item.feedback, adjustment: '' }];
    }));
    const draft = () => drafts[selected.id];
    const status = text => { find('[data-status]').textContent = text; };
    function node(tag, text, className) {
        const element = document.createElement(tag);
        if (text !== undefined) element.textContent = text;
        if (className) element.className = className;
        return element;
    }
    function updateGrade() {
        const scores = draft().scores;
        find('[data-grade]').textContent = scores.reduce((total, score) => total + score, 0);
        all('[data-score]').forEach((input, i) => {
            input.value = scores[i];
            const ratio = scores[i] / maximums[i];
            const description = ratio >= .9 ? 'Excellent' : ratio >= .7 ? 'Strong' : 'Developing';
            find(`[data-score-label="${i}"]`).textContent = `${scores[i]}/${maximums[i]} · ${description}`;
            input.setAttribute('aria-valuetext', `${scores[i]} out of ${maximums[i]}, ${description}`);
        });
    }
    function renderQueue() {
        let count = 0;
        all('[data-submission]').forEach(button => {
            const item = submissions.find(submission => submission.id === button.dataset.submission);
            const matches = filter === 'All' || (filter === 'Needs review' && ['Submitted', 'Late'].includes(item.status)) || (filter === 'TA reviewed' && item.status === 'TA reviewed');
            button.hidden = !matches;
            button.setAttribute('aria-pressed', item.id === selected.id ? 'true' : 'false');
            if (matches) count++;
        });
        all('[data-filter]').forEach(button => button.setAttribute('aria-pressed', button.dataset.filter === filter ? 'true' : 'false'));
        find('[data-count]').textContent = `${count} submission${count === 1 ? '' : 's'}`;
        find('[data-empty]').hidden = count !== 0;
        find('[data-reader]').hidden = count === 0;
        find('[data-grading]').hidden = count === 0;
    }
    function render() {
        renderQueue();
        find('[data-team]').textContent = selected.team;
        find('[data-milestone]').textContent = selected.milestone;
        find('[data-document-title]').textContent = selected.document;
        find('[data-document-version]').textContent = `${selected.team} · Version ${version}`;
        const versions = find('[data-versions]');
        versions.replaceChildren(...selected.versions.map(number => {
            const button = node('button', `v${number}`);
            button.type = 'button'; button.dataset.version = number;
            button.setAttribute('aria-pressed', String(number === version));
            button.setAttribute('aria-label', `Version ${number}${number === selected.versions.at(-1) ? ', latest' : ''}`);
            if (number === selected.versions.at(-1)) button.append(node('small', 'Latest'));
            return button;
        }));
        const title = selected.milestone.startsWith('M1') ? 'proposal' : 'design';
        const files = [[`${selected.id}-${title}-v${version}.pdf`, '2.8 MB · PDF'], [`github.com/${selected.id}/capstone`, 'Repository · main'], ['prototype.fig', 'Figma design link']];
        find('[data-files]').replaceChildren(...files.map(([name, description], i) => {
            const button = node('button', undefined, 'reviews-file');
            button.type = 'button'; button.dataset.file = name;
            const span = node('span'); span.append(node('strong', name), node('small', description));
            const svg = find(i === 1 ? '[data-folder-icon]' : '[data-file-icon]').content.firstElementChild.cloneNode(true);
            button.append(svg, span, node('span', '›'));
            return button;
        }));
        find('[data-evidence]').replaceChildren(...selected.members.map(([name, tasks, uploads, commits, percentage], i) => {
            const row = node('div', undefined, 'reviews-evidence');
            const avatar = node('span', name[0], `reviews-avatar reviews-avatar--${['blue', 'yellow', 'violet'][i % 3]}`);
            const bar = node('span', undefined, 'reviews-bar'); bar.setAttribute('aria-label', `${name}: illustrative contribution activity ${percentage}%`);
            const fill = node('i'); bar.style.setProperty('--evidence', `${percentage}%`); bar.append(fill);
            row.append(avatar, node('strong', name), node('small', `${tasks} tasks`), node('small', `${uploads} uploads`), node('small', `${commits} commits`), bar);
            return row;
        }));
        find('[data-activity]').replaceChildren(...selected.activity.map(([time, text]) => {
            const row = node('div', undefined, 'reviews-event'); row.append(node('time', time), node('p', text)); return row;
        }));
        find('[data-ta]').hidden = !selected.taFeedback;
        find('[data-ta-feedback]').textContent = selected.taFeedback || '';
        find('[data-feedback]').value = draft().feedback;
        const adjustment = find('[data-adjustment]');
        adjustment.replaceChildren(node('option', 'No adjustment'), ...selected.members.flatMap(([name]) => [-1, 1].map(change => {
            const option = node('option', `${name} ${change > 0 ? '+' : ''}${change} · Evidence reviewed`);
            option.value = `${name}:${change}`; return option;
        })));
        adjustment.firstElementChild.value = '';
        adjustment.value = draft().adjustment;
        updateGrade();
    }
    all('[data-filter]').forEach(button => button.addEventListener('click', () => {
        filter = button.dataset.filter;
        const matches = submissions.filter(item => filter === 'All' || (filter === 'Needs review' && ['Submitted', 'Late'].includes(item.status)) || item.status === filter);
        if (matches.length && !matches.some(item => item.id === selected.id)) { selected = matches[0]; version = selected.versions.at(-1); }
        render();
        status(matches.length ? `${matches.length} submissions shown.` : 'No graded submissions in this sample queue.');
    }));
    all('[data-submission]').forEach(button => button.addEventListener('click', () => {
        selected = submissions.find(item => item.id === button.dataset.submission); version = selected.versions.at(-1); render(); status(`${selected.team} selected.`);
    }));
    find('[data-versions]').addEventListener('click', event => {
        const button = event.target.closest('[data-version]');
        if (!button) return;
        version = Number(button.dataset.version); render();
        status(`Viewing version ${version}. The grading draft remains for the latest submission.`);
    });
    all('[data-score]').forEach((input, index) => input.addEventListener('input', () => { draft().scores[index] = Number(input.value); updateGrade(); }));
    find('[data-feedback]').addEventListener('input', event => { draft().feedback = event.target.value; });
    find('[data-adjustment]').addEventListener('change', event => { draft().adjustment = event.target.value; status('Student adjustment selected for the draft. The team grade is unchanged.'); });
    find('[data-use-ta]').addEventListener('click', () => { draft().feedback = selected.taFeedback; find('[data-feedback]').value = draft().feedback; status('TA feedback copied into professor feedback.'); });
    all('[data-comment]').forEach(button => button.addEventListener('click', () => {
        draft().feedback = [draft().feedback.trim(), `${button.dataset.comment}.`].filter(Boolean).join('\n'); find('[data-feedback]').value = draft().feedback;
    }));
    find('[data-save]').addEventListener('click', () => {
        saved[selected.id] = structuredClone(draft());
        try { sessionStorage.setItem(storageKey, JSON.stringify(saved)); status(`${selected.team} draft saved in this browser tab. Backend saving is not connected.`); }
        catch { status('Browser storage is unavailable. Your draft remains in memory until this page closes.'); }
    });
    const dialog = find('dialog');
    let pending = '';
    function openDialog(title, body, confirm = false) {
        find('[data-dialog-title]').textContent = title; find('[data-dialog-body]').textContent = body;
        find('[data-dialog-confirm]').hidden = !confirm;
        find('[data-dialog-close]').textContent = confirm ? 'Keep reviewing' : 'Close'; dialog.showModal();
    }
    find('[data-open-document]').addEventListener('click', () => openDialog('Document preview', `${selected.team} · Version ${version}. This is an illustrative preview. The full document will open here after real submission files are connected.`));
    find('[data-files]').addEventListener('click', event => {
        const button = event.target.closest('[data-file]');
        if (button) openDialog('Sample attachment', `${button.dataset.file} is sample data. Real file and repository links will be supplied by the backend.`);
    });
    all('[data-action]').forEach(button => button.addEventListener('click', () => {
        pending = button.dataset.action;
        openDialog(pending === 'finalize' ? 'Approve and finalize?' : 'Request resubmission?', `${selected.team}: this is a frontend preview. ${pending === 'finalize' ? 'The backend will lock grades and feedback after confirmation.' : 'The backend will notify the team and manage its resubmission deadline.'} No grade will be saved and no notification will be sent from this preview.`, true);
    }));
    find('[data-dialog-close]').addEventListener('click', () => dialog.close());
    find('[data-dialog-confirm]').addEventListener('click', () => { dialog.close(); status(`${pending === 'finalize' ? 'Finalization' : 'Resubmission'} preview confirmed for ${selected.team}. Backend integration is pending; no changes were sent.`); });
    render();
}
