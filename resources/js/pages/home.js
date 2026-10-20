// Home page and Help center. Every page works without this file; it only adds the interactive layer.

const root = document.querySelector('.hp');

if (root) {
    initNav(root);
    initMenu(root);
    initFaq(root);
    initTypeTabs(root);
    initHelpSearch(root);
    initReportForm(root);
}

function initNav(root) {
    const nav = root.querySelector('[data-hp-nav]');
    if (!nav) return;
    const update = () => nav.classList.toggle('is-scrolled', window.scrollY > 8);
    update();
    window.addEventListener('scroll', update, { passive: true });
}

function initMenu(root) {
    const toggle = root.querySelector('[data-hp-menu-toggle]');
    const menu = root.querySelector('[data-hp-menu]');
    if (!toggle || !menu) return;

    const isOpen = () => toggle.getAttribute('aria-expanded') === 'true';
    const setOpen = (open) => {
        toggle.setAttribute('aria-expanded', String(open));
        menu.hidden = !open;
    };

    toggle.addEventListener('click', () => setOpen(!isOpen()));
    menu.addEventListener('click', (event) => {
        if (event.target.closest('a')) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && isOpen()) {
            setOpen(false);
            toggle.focus();
        }
    });
    // Matches the 900px breakpoint in home.css, where the menu button disappears.
    window.matchMedia('(min-width: 901px)').addEventListener('change', (event) => {
        if (event.matches) setOpen(false);
    });
}

function initFaq(root) {
    const items = [...root.querySelectorAll('[data-hp-faq-item]')];

    const setItem = (item, open) => {
        item.classList.toggle('open', open);
        item.querySelector('button').setAttribute('aria-expanded', String(open));
        item.querySelector('.hp-faq-answer').hidden = !open;
    };

    items.forEach((item) => {
        item.querySelector('button').addEventListener('click', (event) => {
            const willOpen = event.currentTarget.getAttribute('aria-expanded') !== 'true';
            items.forEach((other) => setItem(other, other === item && willOpen));
        });
    });
}

function initTypeTabs(root) {
    const list = root.querySelector('[data-hp-type-tabs]');
    if (!list) return;
    const tabs = [...list.querySelectorAll('[role="tab"]')];

    const select = (tab, focus = false) => {
        tabs.forEach((other) => {
            const selected = other === tab;
            other.setAttribute('aria-selected', String(selected));
            other.tabIndex = selected ? 0 : -1;
            document.getElementById(other.getAttribute('aria-controls'))?.classList.toggle('active', selected);
        });
        if (focus) tab.focus();
    };

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => select(tab));
        tab.addEventListener('keydown', (event) => {
            const next = {
                ArrowRight: tabs[(index + 1) % tabs.length],
                ArrowLeft: tabs[(index - 1 + tabs.length) % tabs.length],
                Home: tabs[0],
                End: tabs[tabs.length - 1],
            }[event.key];
            if (!next) return;
            event.preventDefault();
            select(next, true);
        });
    });
}

function initHelpSearch(root) {
    const form = root.querySelector('[data-hp-search-form]');
    if (!form) return;
    const input = form.querySelector('input[type="search"]');
    const topics = [...root.querySelectorAll('[data-hp-topic]')];
    const empty = root.querySelector('[data-hp-search-empty]');
    const status = root.querySelector('[data-hp-search-status]');
    // Same normalising as HomeController::normalise(), so live results match ?q= results.
    const normalise = (text) => text.replace(/[‘’]/g, "'").toLowerCase();

    const apply = () => {
        const query = input.value.trim();
        const needle = normalise(query);
        let count = 0;

        topics.forEach((topic) => {
            let matches = 0;
            topic.querySelectorAll('[data-hp-article]').forEach((article) => {
                const match = needle === '' || normalise(article.textContent.replace(/\s+/g, ' ')).includes(needle);
                article.hidden = !match;
                matches += match ? 1 : 0;
            });
            topic.hidden = matches === 0;
            count += matches;
        });

        empty.hidden = count > 0;
        empty.querySelector('.ms-empty__title').textContent = `No articles match “${query}”.`;
        status.textContent = needle === '' ? '' : `${count} ${count === 1 ? 'article' : 'articles'} found.`;

        const url = new URL(window.location.href);
        if (query) url.searchParams.set('q', query);
        else url.searchParams.delete('q');
        window.history.replaceState(null, '', url);
    };

    input.addEventListener('input', apply);
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        apply();
    });
    root.querySelector('[data-hp-search-clear]')?.addEventListener('click', (event) => {
        event.preventDefault();
        input.value = '';
        apply();
        input.focus();
    });
}

function initReportForm(root) {
    const form = root.querySelector('[data-hp-report-form]');
    if (!form) return;
    const button = form.querySelector('[type="submit"]');
    const original = [...button.childNodes].map((node) => node.cloneNode(true));

    const reset = () => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.classList.remove('ms-button--loading');
        button.replaceChildren(...original.map((node) => node.cloneNode(true)));
    };

    form.addEventListener('submit', () => {
        const spinner = document.createElement('span');
        spinner.className = 'ms-spinner';
        spinner.setAttribute('aria-hidden', 'true');
        button.replaceChildren(spinner, document.createTextNode(button.dataset.loadingText || 'Sending…'));
        button.classList.add('ms-button--loading');
        button.setAttribute('aria-busy', 'true');
        // Disable after this tick so the browser still submits the form.
        setTimeout(() => { button.disabled = true; });
    });

    // Coming back with the browser's Back button restores the page from cache with the button still busy.
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) reset();
    });
}
