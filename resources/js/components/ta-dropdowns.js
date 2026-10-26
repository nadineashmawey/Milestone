const taDropdownSelects = [...document.querySelectorAll('.ta-page select.control')];

function enhanceTaDropdown(select) {
    if (select.dataset.enhancedSelect === 'true') return;
    select.dataset.enhancedSelect = 'true';

    const dropdown = document.createElement('div');
    dropdown.className = 'ta-dropdown';
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'ta-dropdown__trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.innerHTML = '<span data-dropdown-value></span><svg aria-hidden="true" viewBox="0 0 24 24"><path d="m7 10 5 5 5-5" /></svg>';
    const menu = document.createElement('div');
    menu.className = 'ta-dropdown__menu';
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;

    select.parentNode.insertBefore(dropdown, select);
    dropdown.append(select, trigger, menu);
    select.classList.add('ta-dropdown__native');
    const valueLabel = trigger.querySelector('[data-dropdown-value]');

    const close = () => {
        menu.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
    };

    const sync = () => {
        const selected = select.options[select.selectedIndex];
        valueLabel.textContent = selected?.textContent || 'Choose an option';
        [...menu.children].forEach((option, index) => option.setAttribute('aria-selected', String(index === select.selectedIndex)));
        trigger.classList.toggle('is-placeholder', !select.value);
        trigger.disabled = select.disabled;
        trigger.setAttribute('aria-invalid', String(select.classList.contains('invalid') || select.getAttribute('aria-invalid') === 'true'));
    };

    const render = () => {
        menu.replaceChildren();
        [...select.options].forEach((nativeOption, index) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', String(index === select.selectedIndex));
            option.textContent = nativeOption.textContent;
            option.disabled = nativeOption.disabled;
            option.addEventListener('click', () => {
                select.value = nativeOption.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                sync();
                close();
                trigger.focus();
            });
            option.addEventListener('keydown', (event) => {
                const options = [...menu.querySelectorAll('[role="option"]:not(:disabled)')];
                const current = options.indexOf(option);
                if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                    event.preventDefault();
                    const next = event.key === 'Home' ? 0 : event.key === 'End' ? options.length - 1 : current + (event.key === 'ArrowDown' ? 1 : -1);
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
        document.querySelectorAll('.ta-dropdown__menu').forEach((other) => { if (other !== menu) other.hidden = true; });
        document.querySelectorAll('.ta-dropdown__trigger').forEach((other) => { if (other !== trigger) other.setAttribute('aria-expanded', 'false'); });
        menu.hidden = !opening;
        trigger.setAttribute('aria-expanded', String(opening));
        if (opening) menu.querySelector('[aria-selected="true"]')?.focus();
    });
    select.addEventListener('change', sync);
    select.form?.addEventListener('reset', () => window.setTimeout(sync));
    new MutationObserver(render).observe(select, { childList: true, subtree: true });
    new MutationObserver(sync).observe(select, { attributes: true, attributeFilter: ['class', 'aria-invalid', 'disabled'] });
    render();
}

taDropdownSelects.forEach(enhanceTaDropdown);
document.addEventListener('click', (event) => {
    if (event.target.closest('.ta-dropdown')) return;
    document.querySelectorAll('.ta-dropdown__menu').forEach((menu) => { menu.hidden = true; });
    document.querySelectorAll('.ta-dropdown__trigger').forEach((trigger) => trigger.setAttribute('aria-expanded', 'false'));
});
