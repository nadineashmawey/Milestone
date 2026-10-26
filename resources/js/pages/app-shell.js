// Zina's signed-in pages. Everything works without this file; it only adds the interactive layer.

const root = document.querySelector('.as');

if (root) {
    initDialogs(root);
    initUnsavedWarning(root);
    initCopyButtons(root);
    initBusyForms(root);
}

// Confirm dialogs. Trigger links point at ?confirm=<id> so they still work without JavaScript.
function initDialogs(root) {
    const dialogs = new Map([...root.querySelectorAll('[data-as-dialog]')].map((dialog) => [dialog.id, dialog]));
    let opener = null;

    const close = (dialog) => {
        dialog.close();
        if (dialog.hasAttribute('data-as-dialog-static')) {
            dialog.removeAttribute('data-as-dialog-static');
            const url = new URL(window.location.href);
            url.searchParams.delete('confirm');
            window.history.replaceState(null, '', url);
        }
        opener?.focus();
        opener = null;
    };

    root.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-as-dialog-open]');
        if (trigger) {
            const dialog = dialogs.get(trigger.dataset.asDialogOpen);
            if (!dialog) return;
            event.preventDefault();
            opener = trigger;
            dialog.showModal();
            return;
        }
        const closer = event.target.closest('[data-as-dialog-close]');
        if (closer) {
            event.preventDefault();
            close(closer.closest('dialog'));
        }
    });

    dialogs.forEach((dialog) => {
        // The server rendered this one open (no-JS fallback). Reopen it as a real modal for focus trapping.
        if (dialog.hasAttribute('data-as-dialog-static') && dialog.open) {
            dialog.close();
            dialog.showModal();
        }
        dialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            close(dialog);
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) close(dialog);
        });
    });
}

// Forms marked data-as-unsaved warn before leaving with changes and show an inline "Unsaved changes" note.
function initUnsavedWarning(root) {
    root.querySelectorAll('form[data-as-unsaved]').forEach((form) => {
        const status = form.querySelector('[data-as-unsaved-status]');
        const snapshot = () => new URLSearchParams(new FormData(form)).toString();
        const initial = snapshot();
        let dirty = false;
        let submitting = false;

        const update = () => {
            dirty = snapshot() !== initial;
            if (status) status.hidden = !dirty;
        };

        form.addEventListener('input', update);
        form.addEventListener('change', update);
        form.addEventListener('reset', () => setTimeout(update));
        form.addEventListener('submit', () => { submitting = true; });
        window.addEventListener('beforeunload', (event) => {
            if (!dirty || submitting) return;
            event.preventDefault();
            event.returnValue = '';
        });
    });
}

function initCopyButtons(root) {
    const live = document.createElement('p');
    live.className = 'as-sr';
    live.setAttribute('aria-live', 'polite');
    root.append(live);

    root.querySelectorAll('[data-as-copy]').forEach((button) => {
        button.hidden = false;
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.asCopy);
                live.textContent = 'Copied to the clipboard.';
            } catch {
                live.textContent = 'Couldn’t copy. Select the text and copy it instead.';
            }
        });
    });
}

// Submit buttons with data-as-busy show a spinner and stop double submits.
function initBusyForms(root) {
    root.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const button = event.submitter;
            if (!button || !button.hasAttribute('data-as-busy')) return;
            const spinner = document.createElement('span');
            spinner.className = 'ms-spinner';
            spinner.setAttribute('aria-hidden', 'true');
            button.replaceChildren(spinner, document.createTextNode(button.dataset.asBusy || 'Saving…'));
            button.classList.add('ms-button--loading');
            button.setAttribute('aria-busy', 'true');
            // Disable after this tick so the browser still sends the button's name and value.
            setTimeout(() => { button.disabled = true; });
        });
    });

    window.addEventListener('pageshow', (event) => {
        if (event.persisted) window.location.reload();
    });
}
