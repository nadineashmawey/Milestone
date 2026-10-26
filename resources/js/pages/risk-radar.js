const radar = document.querySelector('[data-risk-radar]');
if (radar) {
    const { teams, context } = JSON.parse(radar.querySelector('[data-risk-data]').textContent);
    const find = selector => radar.querySelector(selector);
    let selected = teams.find(team => team.id === '3') || teams[0];
    const status = message => { find('[data-risk-status]').textContent = message; };
    function selectTeam(id) {
        const team = teams.find(item => item.id === id);
        if (!team) return;
        selected = team;
        radar.querySelectorAll('[data-team]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.team === id)));
        find('[data-team-select]').value = id;
        find('[data-team-name]').textContent = team.name;
        find('[data-contribution-team]').textContent = team.name;
        find('[data-simulator-team]').textContent = team.name;
        find('[data-risk-badge]').textContent = `${team.risk[0].toUpperCase()}${team.risk.slice(1)} risk`;
        find('[data-risk-badge]').className = `ms-badge ms-badge--${team.risk === 'high' ? 'danger' : team.risk === 'medium' ? 'warning' : 'success'}`;
        const forecast = team.delayDays ? `May miss ${context.milestone.split(' · ')[0]} by about ${team.delayDays} day${team.delayDays === 1 ? '' : 's'}` : 'Currently on track for this milestone';
        find('[data-forecast]').textContent = forecast;
        const reasons = [
            `${team.progress}% project progress`,
            team.inactiveDays ? `No recorded activity for ${team.inactiveDays} day${team.inactiveDays === 1 ? '' : 's'}` : 'Recent project activity recorded',
            `${team.openTasks} open tasks across ${team.members.length} members`,
        ];
        find('[data-reasons]').replaceChildren(...reasons.map(text => {
            const item = document.createElement('li'); item.textContent = text; return item;
        }));
        find('[data-suggestion]').textContent = team.action;
        const list = find('[data-members]');
        list.replaceChildren(...team.members.map(([name, score], index) => {
            const row = document.createElement('div'); row.className = 'risk-member';
            const initials = document.createElement('span'); initials.className = `risk-avatar risk-avatar--${['blue', 'yellow', 'violet'][index % 3]}`; initials.textContent = name[0];
            const memberName = document.createElement('strong'); memberName.textContent = name;
            const track = document.createElement('span'); track.className = 'risk-member-track'; track.setAttribute('role', 'img'); track.setAttribute('aria-label', `${name}: activity score ${score} out of 100`); track.style.setProperty('--member-score', `${score}%`);
            track.append(document.createElement('span'));
            const output = document.createElement('output'); output.textContent = `${score}%`;
            row.append(initials, memberName, track, output); return row;
        }));
        updateScenario();
    }
    function updateScenario() {
        const extra = Number(find('[data-extra-days]').value);
        const forecastDelay = Math.max(0, selected.delayDays - extra);
        const date = new Date(`${context.deadline}T12:00:00`);
        date.setDate(date.getDate() + extra);
        const newDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        find('[data-days]').textContent = `+${extra} day${extra === 1 ? '' : 's'}`;
        find('[data-proposed-deadline]').textContent = `Proposed: ${newDate}`;
        const impact = find('[data-impact]');
        impact.classList.toggle('risk-impact--reduced', forecastDelay === 0);
        find('[data-scenario-result]').textContent = forecastDelay === 0 ? 'Potentially on track' : `May still be ${forecastDelay} day${forecastDelay === 1 ? '' : 's'} late`;
        find('[data-scenario-detail]').textContent = extra === 0 ? 'No extra time in this scenario.' : `Adds ${extra} day${extra === 1 ? '' : 's'} to the sample deadline; estimate uses sample delay only.`;
    }
    radar.querySelectorAll('[data-team]').forEach(button => button.addEventListener('click', () => {
        selectTeam(button.dataset.team); status(`${selected.name} selected. Its sample prediction and activity are shown.`);
    }));
    find('[data-team-select]').addEventListener('change', event => {
        selectTeam(event.target.value); status(`${selected.name} selected. Contribution activity and prediction updated.`);
    });
    find('[data-extra-days]').addEventListener('input', updateScenario);
    find('[data-export]').addEventListener('click', () => {
        const rows = [['Team', 'Risk level', 'Progress (%)', 'Activity score / 100', 'Open tasks', 'Inactive days', 'Estimated delay (days)', 'Suggested action'], ...teams.map(team => [team.name, team.risk, team.progress, team.activity, team.openTasks, team.inactiveDays, team.delayDays, team.action])];
        const csv = rows.map(row => row.map(value => `"${String(value).replaceAll('"', '""')}"`).join(',')).join('\r\n');
        const url = URL.createObjectURL(new Blob([csv], { type: 'text/csv;charset=utf-8' }));
        const link = document.createElement('a'); link.href = url; link.download = 'risk-radar-sample-report.csv'; link.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
        status('Sample risk report downloaded as CSV.');
    });
    selectTeam(selected.id);
}
