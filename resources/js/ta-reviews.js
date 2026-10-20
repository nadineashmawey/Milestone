const app = document.querySelector('[data-review-app]');

if (app) {
    const submissions = {
        'team-3': { team: 'Team 3', title: 'M2 · Design document', doc: 'System Design Document', version: 'v3', file: 'team-3-design-v3.pdf' },
        'team-1': { team: 'Team 1', title: 'M2 · Design document', doc: 'System Design Document', version: 'v2', file: 'team-1-design-v2.pdf' },
        'team-2': { team: 'Team 2', title: 'M1 · Proposal', doc: 'Project Proposal', version: 'v1', file: 'team-2-proposal-v1.pdf' },
    };
    const cards = [...app.querySelectorAll('[data-submission]')];
    const toast = app.querySelector('[data-toast]');
    const dialog = app.querySelector('[data-dialog]');
    let toastTimer;

    function announce(message) {
        toast.textContent = message;
        toast.hidden = false;
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => { toast.hidden = true; }, 2800);
    }

    app.querySelectorAll('[data-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const filter = button.dataset.filter;
            app.querySelectorAll('[data-filter]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });
            let visibleCount = 0;
            cards.forEach((card) => {
                const visible = filter === 'all' || card.dataset.status === filter;
                card.hidden = !visible;
                if (visible) visibleCount += 1;
            });
            app.querySelector('.review-empty').hidden = visibleCount > 0;
        });
    });

    cards.forEach((card) => {
        card.addEventListener('click', () => {
            cards.forEach((item) => {
                const selected = item === card;
                item.classList.toggle('is-selected', selected);
                item.setAttribute('aria-pressed', String(selected));
            });
            const submission = submissions[card.dataset.submission];
            app.querySelector('[data-team-title]').textContent = submission.team;
            app.querySelector('[data-document-kicker]').textContent = submission.title;
            app.querySelector('[data-paper-title]').textContent = submission.doc;
            app.querySelector('[data-paper-subtitle]').textContent = `${submission.team} · Version ${submission.version.slice(1)}`;
            app.querySelector('[data-file-name]').textContent = submission.file;
            app.querySelectorAll('[data-version]').forEach((button) => {
                const active = button.dataset.version === submission.version;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', String(active));
            });
        });
    });

    app.querySelectorAll('[data-version]').forEach((button) => {
        button.addEventListener('click', () => {
            app.querySelectorAll('[data-version]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-pressed', String(active));
            });
            const selected = app.querySelector('.submission-card.is-selected');
            const item = submissions[selected.dataset.submission];
            app.querySelector('[data-paper-subtitle]').textContent = `${item.team} · Version ${button.dataset.version.slice(1)}`;
            app.querySelector('[data-file-name]').textContent = item.file.replace(/v\d+/, button.dataset.version);
            announce(`${item.team} ${button.dataset.version} selected (mock preview).`);
        });
    });

    app.querySelectorAll('.rubric-item input').forEach((slider) => {
        slider.addEventListener('input', () => {
            const outputs = slider.closest('.rubric-item').querySelectorAll('output');
            outputs[0].value = slider.value;
            outputs[0].textContent = slider.value;
        });
    });

    app.querySelectorAll('[data-suggestion]').forEach((button) => {
        button.addEventListener('click', () => {
            const field = app.querySelector('[data-feedback]');
            const suggestion = button.dataset.suggestion;
            field.value = `${field.value.trim()}${field.value.trim() ? ' ' : ''}${suggestion}`;
            field.focus();
        });
    });

    app.querySelector('[data-action="save"]').addEventListener('click', () => {
        const key = `milestone-ta-review-${app.querySelector('.submission-card.is-selected').dataset.submission}`;
        localStorage.setItem(key, JSON.stringify({ feedback: app.querySelector('[data-feedback]').value, savedAt: new Date().toISOString() }));
        announce('Draft saved in this browser.');
    });

    app.querySelectorAll('[data-action="request"], [data-action="send"]').forEach((button) => {
        button.addEventListener('click', () => {
            const requesting = button.dataset.action === 'request';
            app.querySelector('[data-dialog-title]').textContent = requesting ? 'Resubmission requested' : 'Review sent';
            app.querySelector('[data-dialog-message]').textContent = requesting
                ? 'The team has been asked to revise and resubmit this work. (Mock action)'
                : 'Your feedback is ready for the team. (Mock action)';
            if (typeof dialog.showModal === 'function') dialog.showModal();
            else announce(requesting ? 'Resubmission requested (mock action).' : 'Review sent (mock action).');
        });
    });

    app.querySelector('[data-dialog-close]').addEventListener('click', () => dialog.close());
    app.querySelector('[data-open-document]').addEventListener('click', (event) => {
        event.preventDefault();
        announce('Document preview is mock data; no file is attached yet.');
    });
}
