const board = document.querySelector('[data-project-board]');


if (board) {
    const statuses = ['To do', 'In progress', 'Review', 'Done'];
    const tabs = [...board.querySelectorAll('[data-tab]')];
    const panels = [...board.querySelectorAll('[data-panel]')];


    tabs.forEach((tab) => tab.addEventListener('click', () => {
        tabs.forEach((item) => {
            const active = item === tab;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-selected', String(active));
            item.tabIndex = active ? 0 : -1;
        });
        panels.forEach((panel) => { panel.hidden = panel.dataset.panel !== tab.dataset.tab; });
    }));


    const updateCounts = () => {
        let total = 0;
        let complete = 0;
        board.querySelectorAll('[data-column]').forEach((column) => {
            const count = column.querySelectorAll('[data-task]').length;
            total += count;
            if (column.dataset.column === 'Done') complete = count;
            column.querySelector('[data-column-count]').textContent = count;
        });
        const percent = total ? Math.round((complete / total) * 100) : 0;
        board.querySelector('[data-complete-count]').textContent = complete;
        board.querySelector('[data-summary-percent]').textContent = percent;
        board.querySelector('[data-summary-progress]').style.width = `${percent}%`;
    };


    board.addEventListener('click', (event) => {
        const move = event.target.closest('[data-move]');
        if (move && !move.disabled) {
            const card = move.closest('[data-task]');
            const column = card.closest('[data-column]');
            const index = statuses.indexOf(column.dataset.column) + (move.dataset.move === 'forward' ? 1 : -1);
            board.querySelector(`[data-column="${statuses[index]}"] .task-list`).append(card);
            updateCounts();
            return;
        }


        if (event.target.closest('[data-add-task]')) {
            const title = window.prompt('What is the task called?');
            if (!title?.trim()) return;


            const card = document.createElement('article');
            card.className = 'task-card';
            card.dataset.task = '';
            const heading = document.createElement('div');
            heading.className = 'task-card__top';
            const taskTitle = document.createElement('h4');
            taskTitle.textContent = title.trim();
            const priority = document.createElement('span');
            priority.className = 'priority priority--low';
            priority.textContent = 'Low';
            heading.append(taskTitle, priority);
            const description = document.createElement('p');
            description.className = 'task-description';
            description.textContent = 'New project task';
            const actions = document.createElement('div');
            actions.className = 'task-actions';
            actions.innerHTML = '<button type="button" data-move="back" disabled>‹ <span>Move Back</span></button><button type="button" data-move="forward"><span>Move Forward</span> ›</button>';
            card.append(heading, description, actions);
            board.querySelector('[data-column="To do"] .task-list').append(card);
            updateCounts();
        }
    });


    updateCounts();
}
