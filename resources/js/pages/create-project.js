const wizard = document.querySelector('[data-project-wizard]');
if (wizard) {
    const form = wizard.querySelector('form');
    const panels = [...wizard.querySelectorAll('[data-panel]')];
    const steps = [...wizard.querySelectorAll('[data-step]')];
    const error = wizard.querySelector('[data-error]');
    let step = 0;
    const value = name => form.elements.namedItem(name)?.value ?? '';
    const rows = selector => [...form.querySelectorAll(`${selector} [data-row]`)];
    const weight = () => rows('[data-milestones]').reduce((sum, row) => sum + Number(row.querySelector('[data-weight-input]').value), 0);
    const status = message => { wizard.querySelector('[data-status]').textContent = message; };
    function validate(index) {
        const invalid = [...panels[index].querySelectorAll('input,select,textarea')].find(input => !input.checkValidity());
        if (invalid) { invalid.reportValidity(); return 'Please complete the required fields.'; }
        if (index === 1 && (!rows('[data-milestones]').length || weight() !== 100)) return 'Milestone weights must total 100%, with at least one milestone.';
        if (index === 2) {
            const criteria = rows('[data-criteria]');
            const total = criteria.reduce((sum, row) => sum + Number(row.querySelector('input[type=number]').value), 0);
            if (criteria.length && total !== Number(value('grade'))) return 'Rubric points must equal the total project grade, or remove all criteria to use no rubric.';
        }
        if (index === 3 && Number(value('team_min')) > Number(value('team_max'))) return 'Minimum team size cannot exceed maximum team size.';
        return '';
    }
    function updateSummary() {
        const milestones = rows('[data-milestones]');
        const dates = milestones.map(row => row.querySelector('input[type=date]').value).filter(Boolean).sort();
        const dateLabel = date => new Date(`${date}T12:00:00`).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const summary = {
            course: value('course'), project: value('title') || 'Untitled project',
            milestones: `${milestones.length} · ${dates.length ? `${dateLabel(dates[0])} to ${dateLabel(dates.at(-1))}` : 'No deadlines'}`,
            teams: `${value('team_min')}–${value('team_max')} students · ${value('leftover')}`,
            ta: `Sara Ahmed · ${value('permission')}`, grading: `${value('grade')} points · ${rows('[data-criteria]').length ? 'Optional rubric' : 'No rubric'}`,
        };
        Object.entries(summary).forEach(([key, text]) => { wizard.querySelector(`[data-summary=${key}]`).textContent = text; });
        const badge = wizard.querySelector('[data-weight]');
        badge.textContent = `${weight()}% total`;
        badge.classList.toggle('ms-badge--success', weight() === 100);
        badge.classList.toggle('ms-badge--danger', weight() !== 100);
        const readiness = wizard.querySelectorAll('[data-readiness]');
        readiness[1].lastElementChild.textContent = `${milestones.length} milestones · ${weight()}%`;
        readiness[2].lastElementChild.textContent = `Rubric · ${value('grade')} points`;
        readiness[3].lastElementChild.textContent = `Teams · ${value('team_min')}–${value('team_max')} members`;
    }
    function show(next, focus = true) {
        if (next > step) {
            for (let i = 0; i < next; i++) {
                const message = validate(i);
                if (message) { show(i); error.textContent = message; error.hidden = false; return; }
            }
        }
        step = next;
        error.hidden = true;
        panels.forEach((panel, i) => { panel.hidden = i !== step; });
        steps.forEach((button, i) => {
            button.toggleAttribute('aria-current', i === step);
            if (i === step) button.setAttribute('aria-current', 'step');
            button.classList.toggle('is-done', i < step);
            button.firstElementChild.textContent = i < step ? '✓' : i + 1;
        });
        wizard.querySelector('[data-back]').disabled = step === 0;
        wizard.querySelector('[data-next]').hidden = step === 5;
        wizard.querySelector('[data-publish]').hidden = step !== 5;
        const percentage = Math.round((step + 1) / 6 * 100);
        wizard.querySelector('[data-progress]').textContent = `${percentage}%`;
        const ring = wizard.querySelector('.cp-ring');
        ring.style.setProperty('--progress', `${percentage}%`);
        ring.setAttribute('aria-valuenow', percentage);
        wizard.querySelectorAll('[data-readiness]').forEach((item, i) => {
            item.classList.toggle('is-ready', i <= step);
            item.firstElementChild.textContent = i <= step ? '✓' : '◷';
        });
        updateSummary();
        if (focus) panels[step].querySelector('h2').focus({ preventScroll: true });
    }
    steps.forEach(button => button.addEventListener('click', () => show(Number(button.dataset.step))));
    wizard.querySelector('[data-next]').addEventListener('click', () => show(Math.min(5, step + 1)));
    wizard.querySelector('[data-back]').addEventListener('click', () => show(Math.max(0, step - 1)));
    form.addEventListener('submit', event => { event.preventDefault(); if (step < 5) show(step + 1); });
    form.addEventListener('input', updateSummary);
    function renumber() {
        [['[data-milestones]', 'milestones'], ['[data-criteria]', 'criteria']].forEach(([selector, name]) => {
            rows(selector).forEach((row, index) => {
                row.querySelectorAll('[name]').forEach(input => { input.name = input.name.replace(new RegExp(`${name}\\[\\d+\\]`), `${name}[${index}]`); });
                const number = row.querySelector('[data-milestone-number]');
                if (number) number.textContent = `M${index + 1}`;
            });
        });
        updateSummary();
    }
    // Keep a template even when every row is removed.
    const templates = Object.fromEntries([['goal', '[data-goals]'], ['milestone', '[data-milestones]'], ['criterion', '[data-criteria]']].map(([name, selector]) => [name, { selector, row: form.querySelector(`${selector} [data-row]`).cloneNode(true) }]));
    form.addEventListener('click', event => {
        const remove = event.target.closest('[data-remove]');
        if (remove) { remove.closest('[data-row]').remove(); renumber(); }
        const add = event.target.closest('[data-add]');
        if (!add) return;
        const template = templates[add.dataset.add];
        const row = template.row.cloneNode(true);
        row.querySelectorAll('input').forEach(input => { input.value = input.type === 'number' ? '1' : ''; });
        form.querySelector(template.selector).append(row);
        renumber();
        row.querySelector('input').focus();
    });
    wizard.querySelector('[data-brief]').addEventListener('change', event => {
        const file = event.target.files[0];
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.pdf')) { event.target.value = ''; status('Please choose a PDF file.'); return; }
        wizard.querySelector('[data-brief-label]').textContent = `${file.name} selected · analysis will be available when the backend is connected`;
        status('PDF selected locally. No content has been uploaded or analyzed.');
    });
    wizard.querySelector('[data-roster]').addEventListener('change', event => {
        const file = event.target.files[0];
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.csv')) { event.target.value = ''; status('Please choose a CSV file.'); return; }
        wizard.querySelector('[data-roster-name]').textContent = file.name;
        wizard.querySelector('[data-roster-label]').textContent = 'Selected locally · roster matching requires backend integration';
        wizard.querySelectorAll('[data-readiness]')[4].lastElementChild.textContent = 'Roster selected · 1 TA';
        status('CSV selected. Student matching and invitations are not connected yet.');
    });
    const dialog = wizard.querySelector('dialog');
    wizard.querySelector('[data-publish]').addEventListener('click', () => {
        for (let i = 0; i < 5; i++) {
            const message = validate(i);
            if (message) { show(i); error.textContent = message; error.hidden = false; return; }
        }
        dialog.showModal();
    });
    wizard.querySelector('[data-close]').addEventListener('click', () => dialog.close());
    wizard.querySelector('[data-download]').addEventListener('click', () => {
        const entries = [...new FormData(form)].filter(([, entry]) => !(entry instanceof File));
        const draft = { status: 'draft', fields: entries, teaching_assistants: [{ name: 'Sara Ahmed', email: 'sara.ahmed@university.edu' }] };
        const url = URL.createObjectURL(new Blob([JSON.stringify(draft, null, 2)], { type: 'application/json' }));
        const link = document.createElement('a');
        link.href = url; link.download = 'milestone-project-draft.json'; link.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
        dialog.close(); status('Project draft downloaded. No project was published and no invitations were sent.');
    });
    show(0, false);
}
