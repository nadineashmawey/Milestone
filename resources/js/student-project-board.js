const board = document.querySelector('[data-corkboard]');

if (board) {
    const columns = [
        { id: 'todo', label: 'To do' },
        { id: 'in-progress', label: 'In progress' },
        { id: 'review', label: 'Review' },
        { id: 'done', label: 'Done' },
    ];

    // This local array is the single source of truth for the board's task data.
    const sampleTasks = [
        { id: 'research-summary', column: 'todo', title: 'Research summary', priority: 'High', description: 'Document research findings and confirm the proposed API resources.', assignee: 'Aya Hassan', role: 'Team Leader', due: 'Oct 12', tag: 'Overdue' },
        { id: 'api-contract', column: 'in-progress', title: 'API contract', priority: 'Medium', description: 'Define the endpoints, payloads, and ownership for the implementation.', assignee: 'Malek Mansour', role: 'Team Member', due: 'Oct 14', tag: null },
        { id: 'wireframes', column: 'review', title: 'Wireframes', priority: 'Medium', description: 'Complete the annotated workflow screens for the M2 review.', assignee: 'Nour Saleh', role: 'Team Member', due: 'Oct 15', tag: 'Blocked' },
        { id: 'project-brief', column: 'done', title: 'Project brief', priority: 'Low', description: 'Confirm the agreed project scope and success measures.', assignee: 'Aya Hassan', role: 'Team Leader', due: 'Oct 07', tag: null },
    ];
    let tasks = sampleTasks;
    try {
        const savedTasks = JSON.parse(localStorage.getItem('milestone:board-tasks'));
        if (Array.isArray(savedTasks) && savedTasks.every((task) => task.id && task.column && task.title !== undefined)) tasks = savedTasks;
    } catch { /* Use the sample task list when local storage is unavailable. */ }

    const columnsRoot = board.querySelector('[data-board-columns]');
    const status = board.querySelector('[data-board-status]');
    let dragState = null;

    const initialsFor = (name) => name.split(/\s+/).map((part) => part[0]).slice(0, 2).join('').toUpperCase();
    const taskById = (id) => tasks.find((task) => task.id === id);
    const make = (tag, className, text) => {
        const element = document.createElement(tag);
        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;
        return element;
    };

    const createTaskCard = (task) => {
        const card = make('article', 'corkboard__task-card');
        card.dataset.taskCard = task.id;
        card.setAttribute('aria-label', `${task.title}, ${task.priority} priority`);

        const top = make('div', 'corkboard__task-top');
        const title = make('h4', 'corkboard__task-title', task.title);
        title.contentEditable = 'true';
        title.tabIndex = 0;
        title.setAttribute('role', 'textbox');
        title.setAttribute('aria-label', 'Task title');
        title.dataset.editField = 'title';
        const priority = make('span', `corkboard__priority corkboard__priority--${task.priority.toLowerCase()}`, task.priority);
        top.append(title, priority);

        const description = make('p', 'corkboard__task-description', task.description);
        description.contentEditable = 'true';
        description.tabIndex = 0;
        description.setAttribute('role', 'textbox');
        description.setAttribute('aria-label', 'Task description');
        description.dataset.editField = 'description';

        const owner = make('div', 'corkboard__owner');
        owner.append(make('span', 'corkboard__avatar', initialsFor(task.assignee)));
        const ownerText = make('span', 'corkboard__owner-text');
        ownerText.append(make('strong', 'corkboard__owner-name', task.assignee), make('small', 'corkboard__owner-role', task.role));
        owner.append(ownerText);

        const footer = make('div', 'corkboard__task-footer');
        footer.append(make('span', 'corkboard__due', `Due ${task.due}`));
        if (task.tag) {
            footer.append(make('span', `corkboard__tag${task.tag === 'Blocked' ? ' corkboard__tag--blocked' : ''}`, task.tag));
        }

        const actions = make('div', 'corkboard__task-actions');
        const currentColumn = columns.findIndex((column) => column.id === task.column);
        const back = make('button', null, '‹ Move back');
        back.type = 'button';
        back.dataset.move = 'back';
        back.disabled = currentColumn === 0;
        const forward = make('button', null, 'Move forward ›');
        forward.type = 'button';
        forward.dataset.move = 'forward';
        forward.disabled = currentColumn === columns.length - 1;
        const remove = make('button', 'corkboard__task-delete', 'Delete');
        remove.type = 'button';
        remove.dataset.deleteTask = '';
        actions.append(back, forward, remove);
        card.append(top, description, owner, footer, actions);
        return card;
    };

    const createColumn = (column) => {
        const section = make('section', 'corkboard__column');
        section.dataset.column = column.id;
        section.setAttribute('aria-label', `${column.label} tasks`);

        const paper = make('div', 'corkboard__paper');
        const shape = make('div', 'corkboard__paper-shape');
        const pin = make('span', 'corkboard__column-pin');
        pin.setAttribute('aria-hidden', 'true');

        const header = make('header', 'corkboard__column-header');
        header.append(make('h3', null, column.label));
        const count = make('span', 'corkboard__count', '0');
        count.dataset.columnCount = '';
        count.setAttribute('aria-label', '0 tasks');
        header.append(count);

        const taskList = make('div', 'corkboard__task-list');
        taskList.dataset.taskList = '';
        tasks.filter((task) => task.column === column.id).forEach((task) => taskList.append(createTaskCard(task)));

        const addButton = make('button', 'corkboard__add-task', '＋ Add task');
        addButton.type = 'button';
        addButton.dataset.addTask = '';
        shape.append(pin, header, taskList, addButton);
        paper.append(shape);
        section.append(paper);
        return section;
    };

    const syncTaskOrderFromDom = () => {
        const orderedTasks = columns.flatMap((column) => [...columnsRoot.querySelectorAll(`[data-column="${column.id}"] [data-task-list] > [data-task-card]`)]
            .map((card) => taskById(card.dataset.taskCard))
            .filter(Boolean));
        tasks.splice(0, tasks.length, ...orderedTasks);
    };

    const updateCounts = () => {
        let completed = 0;
        let blocked = 0;
        columns.forEach((column) => {
            const columnTasks = tasks.filter((task) => task.column === column.id);
            const count = columnsRoot.querySelector(`[data-column="${column.id}"] [data-column-count]`);
            count.textContent = columnTasks.length;
            count.setAttribute('aria-label', `${columnTasks.length} ${columnTasks.length === 1 ? 'task' : 'tasks'}`);
            completed += column.id === 'done' ? columnTasks.length : 0;
            blocked += columnTasks.filter((task) => task.tag === 'Blocked').length;
        });
        const percent = tasks.length ? Math.round((completed / tasks.length) * 100) : 0;
        board.querySelector('[data-complete-count]').textContent = completed;
        board.querySelector('[data-total-count]').textContent = tasks.length;
        board.querySelector('[data-summary-percent]').textContent = percent;
        board.querySelector('[data-blocked-count]').textContent = blocked;
        board.querySelector('[data-summary-progress]').style.width = `${percent}%`;
    };

    const renderBoard = (focusTaskId = null) => {
        columnsRoot.replaceChildren(...columns.map(createColumn));
        updateCounts();
        try { localStorage.setItem('milestone:board-tasks', JSON.stringify(tasks)); } catch { /* Board remains editable for this session. */ }
        if (focusTaskId) {
            const title = columnsRoot.querySelector(`[data-task-card="${focusTaskId}"] [data-edit-field="title"]`);
            title?.focus();
            if (title) document.getSelection()?.selectAllChildren(title);
        }
    };

    const announce = (message) => { status.textContent = message; };

    board.addEventListener('click', (event) => {
        const deleteButton = event.target.closest('[data-delete-task]');
        if (deleteButton) {
            const card = deleteButton.closest('[data-task-card]');
            const task = taskById(card.dataset.taskCard);
            if (task && window.confirm(`Delete “${task.title}”? This removes it from this local prototype board.`)) {
                tasks = tasks.filter((item) => item.id !== task.id);
                renderBoard();
                announce(`${task.title} deleted.`);
                board.dispatchEvent(new CustomEvent('portal:activity', { bubbles: true, detail: { text: 'deleted a task', related: task.title } }));
            }
            return;
        }

        const moveButton = event.target.closest('[data-move]');
        if (moveButton && !moveButton.disabled) {
            const card = moveButton.closest('[data-task-card]');
            const task = taskById(card.dataset.taskCard);
            const currentIndex = columns.findIndex((column) => column.id === task.column);
            const nextColumn = columns[currentIndex + (moveButton.dataset.move === 'forward' ? 1 : -1)];
            task.column = nextColumn.id;
            syncTaskOrderFromDom();
            const withoutTask = tasks.filter((item) => item.id !== task.id);
            const lastInColumn = withoutTask.reduce((lastIndex, item, index) => item.column === nextColumn.id ? index : lastIndex, -1);
            withoutTask.splice(lastInColumn + 1, 0, task);
            tasks.splice(0, tasks.length, ...withoutTask);
            renderBoard();
            announce(`${task.title} moved to ${nextColumn.label}.`);
            board.dispatchEvent(new CustomEvent('portal:activity', { bubbles: true, detail: { text: 'moved a task', related: task.title } }));
            return;
        }

        if (event.target.closest('[data-add-task]')) {
            const id = `task-${Date.now()}`;
            const task = { id, column: event.target.closest('[data-column]')?.dataset.column || 'todo', title: 'New task', priority: 'Low', description: 'Add a description', assignee: 'Aya Hassan', role: 'Team Leader', due: 'Not set', tag: null };
            tasks.push(task);
            renderBoard(id);
            announce('New task added. Edit its title and description.');
            board.dispatchEvent(new CustomEvent('portal:activity', { bubbles: true, detail: { text: 'created a task', related: task.title } }));
        }
    });

    board.addEventListener('input', (event) => {
        const field = event.target.closest('[data-edit-field]');
        if (!field) return;
        const task = taskById(field.closest('[data-task-card]').dataset.taskCard);
        task[field.dataset.editField] = field.textContent;
    });

    board.addEventListener('keydown', (event) => {
        const field = event.target.closest('[data-edit-field]');
        if (!field || event.key !== 'Enter') return;
        event.preventDefault();
        field.blur();
    });

    board.addEventListener('focusout', (event) => {
        const field = event.target.closest('[data-edit-field]');
        if (!field) return;
        const task = taskById(field.closest('[data-task-card]').dataset.taskCard);
        task[field.dataset.editField] = field.textContent.trim();
        if (field.dataset.editField === 'title' && !task.title) task.title = 'Untitled task';
        field.textContent = task[field.dataset.editField];
        field.closest('[data-task-card]').setAttribute('aria-label', `${task.title}, ${task.priority} priority`);
        try { localStorage.setItem('milestone:board-tasks', JSON.stringify(tasks)); } catch { /* Board remains editable for this session. */ }
    });

    const clearDropTargets = () => board.querySelectorAll('.is-drop-target').forEach((column) => column.classList.remove('is-drop-target'));

    const positionGhost = (event) => {
        if (!dragState || event.pointerId !== dragState.pointerId) return;
        const now = performance.now();
        const elapsed = Math.max(now - dragState.lastTime, 1);
        const speed = (event.clientX - dragState.lastX) / elapsed;
        const tilt = Math.max(-9, Math.min(9, speed * 7));
        dragState.ghost.style.left = `${event.clientX - dragState.offsetX}px`;
        dragState.ghost.style.top = `${event.clientY - dragState.offsetY}px`;
        dragState.ghost.style.setProperty('--ghost-tilt', `${tilt}deg`);
        dragState.lastX = event.clientX;
        dragState.lastTime = now;

        const column = document.elementFromPoint(event.clientX, event.clientY)?.closest('.corkboard__column');
        clearDropTargets();
        if (column) {
            column.classList.add('is-drop-target');
            dragState.targetColumn = column;
        } else {
            dragState.targetColumn = null;
        }
    };

    const finishDrag = (event, canceled = false) => {
        if (!dragState || (event && event.pointerId !== dragState.pointerId)) return;
        const current = dragState;
        const dropColumn = !canceled && event
            ? document.elementFromPoint(event.clientX, event.clientY)?.closest('.corkboard__column') || current.targetColumn
            : null;

        if (dropColumn) {
            const list = dropColumn.querySelector('[data-task-list]');
            const otherCards = [...list.querySelectorAll(':scope > [data-task-card]:not(.is-placeholder)')];
            const insertBefore = otherCards.find((card) => event.clientY < card.getBoundingClientRect().top + card.getBoundingClientRect().height / 2);
            list.insertBefore(current.card, insertBefore || null);
            taskById(current.id).column = dropColumn.dataset.column;
        }

        current.ghost.remove();
        current.card.classList.remove('is-placeholder');
        current.card.removeAttribute('aria-grabbed');
        clearDropTargets();
        dragState = null;

        if (dropColumn) {
            syncTaskOrderFromDom();
            renderBoard();
            announce(`Task moved to ${columns.find((column) => column.id === dropColumn.dataset.column).label}.`);
            board.dispatchEvent(new CustomEvent('portal:activity', { bubbles: true, detail: { text: 'moved a task', related: taskById(current.id)?.title || 'Task' } }));
        }
    };

    board.addEventListener('pointerdown', (event) => {
        if (!event.isPrimary || event.button !== 0 || event.target.closest('button, [contenteditable="true"], a, input, select')) return;
        const card = event.target.closest('[data-task-card]');
        if (!card) return;

        const bounds = card.getBoundingClientRect();
        const ghost = card.cloneNode(true);
        ghost.classList.add('corkboard__ghost');
        ghost.removeAttribute('aria-grabbed');
        ghost.setAttribute('aria-hidden', 'true');
        ghost.querySelectorAll('[contenteditable], button').forEach((item) => {
            item.removeAttribute('contenteditable');
            item.removeAttribute('tabindex');
            if (item.matches('button')) item.disabled = true;
        });
        ghost.style.width = `${bounds.width}px`;
        board.append(ghost);
        card.classList.add('is-placeholder');
        card.setAttribute('aria-grabbed', 'true');
        dragState = {
            id: card.dataset.taskCard,
            card,
            ghost,
            pointerId: event.pointerId,
            offsetX: event.clientX - bounds.left,
            offsetY: event.clientY - bounds.top,
            lastX: event.clientX,
            lastTime: performance.now(),
            targetColumn: card.closest('.corkboard__column'),
        };
        event.preventDefault();
        positionGhost(event);
    });

    window.addEventListener('pointermove', positionGhost);
    window.addEventListener('pointerup', (event) => finishDrag(event));
    window.addEventListener('pointercancel', (event) => finishDrag(event, true));
    renderBoard();

    const permissionSelect = board.querySelector('#board-view');
    const permissionHint = board.querySelector('.corkboard__summary-hint');
    const settingsDialog = board.querySelector('[data-board-settings]');
    const membersList = board.querySelector('[data-permission-members]');
    const permissionInputs = [...settingsDialog.querySelectorAll('[name="task-creation-permission"]')];
    const memberInputs = [...membersList.querySelectorAll('input[type="checkbox"]')];
    const permissionKey = `milestone:task-creation:${board.closest('[data-project-board]')?.dataset.projectId || 'default'}`;
    let permission = { mode: 'leader', members: ['Malek Mansour'] };
    try {
        const saved = JSON.parse(localStorage.getItem(permissionKey));
        if (saved && ['leader', 'selected', 'all'].includes(saved.mode) && Array.isArray(saved.members)) permission = saved;
    } catch { /* Keep the default leader-only permission. */ }

    const updatePermissions = () => {
        const actor = permissionSelect.value.startsWith('Aya') ? 'Aya Hassan' : permissionSelect.value.startsWith('Nour') ? 'Nour Saleh' : 'Malek Mansour';
        const allowed = actor === 'Aya Hassan' || permission.mode === 'all' || (permission.mode === 'selected' && permission.members.includes(actor));
        board.querySelectorAll('[data-add-task]').forEach((button) => {
            button.disabled = !allowed;
            button.title = allowed ? '' : 'You do not have permission to create tasks.';
        });
        permissionHint.textContent = permission.mode === 'leader' ? 'Only the team leader can create tasks' : permission.mode === 'all' ? 'All team members can create tasks' : `${permission.members.length} selected member${permission.members.length === 1 ? '' : 's'} can create tasks`;
    };
    const syncSettings = () => {
        permissionInputs.forEach((input) => { input.checked = input.value === permission.mode; });
        memberInputs.forEach((input) => { input.checked = permission.members.includes(input.value); });
        membersList.hidden = permission.mode !== 'selected';
    };
    const savePermission = () => {
        permission.mode = permissionInputs.find((input) => input.checked)?.value || 'leader';
        permission.members = memberInputs.filter((input) => input.checked).map((input) => input.value);
        try { localStorage.setItem(permissionKey, JSON.stringify(permission)); } catch { /* The current page still honors the selected setting. */ }
        syncSettings();
        updatePermissions();
    };
    board.querySelector('[data-open-board-settings]').addEventListener('click', () => settingsDialog.showModal());
    board.querySelector('[data-close-board-settings]').addEventListener('click', () => settingsDialog.close());
    settingsDialog.addEventListener('change', savePermission);
    permissionSelect.addEventListener('change', updatePermissions);
    syncSettings();
    updatePermissions();
}

const projectTabs = document.querySelector('[data-project-board]');

if (projectTabs) {
    const tabs = [...projectTabs.querySelectorAll('[data-tab]')];
    const panels = [...projectTabs.querySelectorAll('[data-panel]')];
    tabs.forEach((tab) => tab.addEventListener('click', () => {
        tabs.forEach((item) => {
            const active = item === tab;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-selected', String(active));
            item.tabIndex = active ? 0 : -1;
        });
        panels.forEach((panel) => { panel.hidden = panel.dataset.panel !== tab.dataset.tab; });
    }));
    tabs.forEach((tab, index) => { tab.tabIndex = index === 0 ? 0 : -1; });
    projectTabs.querySelector('[role="tablist"]').addEventListener('keydown', (event) => {
        const current = tabs.indexOf(document.activeElement);
        let next = current;
        if (event.key === 'ArrowRight') next = (current + 1) % tabs.length;
        if (event.key === 'ArrowLeft') next = (current - 1 + tabs.length) % tabs.length;
        if (event.key === 'Home') next = 0;
        if (event.key === 'End') next = tabs.length - 1;
        if (next === current) return;
        event.preventDefault(); tabs[next].focus(); tabs[next].click();
    });
}
