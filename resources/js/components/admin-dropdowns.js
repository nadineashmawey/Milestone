const adminNativeSelects = [...document.querySelectorAll('.page select.control')];

function enhanceAdminSelect(select) {
    if (select.dataset.enhancedSelect === 'true') return;
    select.dataset.enhancedSelect = 'true';
    const dropdown = document.createElement('div');
    dropdown.className = 'select admin-enhanced-select';
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.innerHTML = '<span data-select-value></span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" /></svg>';
    const menu = document.createElement('div');
    menu.className = 'select-menu';
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;
    select.parentNode.insertBefore(dropdown, select);
    dropdown.append(select, trigger, menu);
    select.classList.add('admin-enhanced-select__native');
    const label = trigger.querySelector('[data-select-value]');
    const close = () => { menu.hidden = true; trigger.setAttribute('aria-expanded', 'false'); };
    const sync = () => {
        label.textContent = select.options[select.selectedIndex]?.textContent || 'Choose an option';
        [...menu.children].forEach((option, index) => option.setAttribute('aria-selected', String(index === select.selectedIndex)));
        trigger.classList.toggle('is-placeholder', !select.value);
        trigger.classList.toggle('invalid', select.classList.contains('invalid') || select.getAttribute('aria-invalid') === 'true');
    };
    const render = () => {
        menu.replaceChildren();
        [...select.options].forEach((nativeOption, index) => {
            const option = document.createElement('button');
            option.type = 'button'; option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', String(index === select.selectedIndex));
            option.textContent = nativeOption.textContent; option.disabled = nativeOption.disabled;
            option.addEventListener('click', () => {
                select.value = nativeOption.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                sync(); close(); trigger.focus();
            });
            option.addEventListener('keydown', (event) => {
                const options = [...menu.querySelectorAll('[role="option"]:not(:disabled)')];
                const current = options.indexOf(option);
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    const next = current + (event.key === 'ArrowDown' ? 1 : -1);
                    options[(next + options.length) % options.length]?.focus();
                }
                if (event.key === 'Escape') { close(); trigger.focus(); }
            });
            menu.append(option);
        });
        sync();
    };
    trigger.addEventListener('click', () => {
        const opening = menu.hidden;
        document.querySelectorAll('.admin-enhanced-select .select-menu').forEach((other) => { if (other !== menu) other.hidden = true; });
        document.querySelectorAll('.admin-enhanced-select .select-trigger').forEach((other) => { if (other !== trigger) other.setAttribute('aria-expanded', 'false'); });
        menu.hidden = !opening; trigger.setAttribute('aria-expanded', String(opening));
        if (opening) menu.querySelector('[aria-selected="true"]')?.focus();
    });
    select.addEventListener('change', sync);
    select.form?.addEventListener('reset', () => window.setTimeout(sync));
    new MutationObserver(render).observe(select, { childList: true, subtree: true });
    new MutationObserver(sync).observe(select, { attributes: true, attributeFilter: ['class', 'aria-invalid'] });
    render();
}

adminNativeSelects.forEach(enhanceAdminSelect);
document.querySelectorAll('.select .select-trigger').forEach((trigger) => {
    if (trigger.querySelector('svg')) return;
    [...trigger.childNodes].forEach((node) => {
        if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) node.remove();
    });
    trigger.insertAdjacentHTML('beforeend', '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" /></svg>');
});
document.querySelectorAll('.select:not(.admin-enhanced-select)').forEach((dropdown) => {
    const trigger = dropdown.querySelector('.select-trigger');
    const menu = dropdown.querySelector('.select-menu');
    if (!trigger || !menu) return;
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', String(!menu.hidden));
    trigger.addEventListener('click', () => window.setTimeout(() => trigger.setAttribute('aria-expanded', String(!menu.hidden))));
    menu.querySelectorAll('button').forEach((option) => option.addEventListener('click', () => window.setTimeout(() => trigger.setAttribute('aria-expanded', 'false'))));
});
document.addEventListener('click', (event) => {
    if (event.target.closest('.admin-enhanced-select')) return;
    document.querySelectorAll('.admin-enhanced-select .select-menu').forEach((menu) => { menu.hidden = true; });
    document.querySelectorAll('.admin-enhanced-select .select-trigger').forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
});
