const toast = document.querySelector("#taskToast");
const taskForm = document.querySelector("#taskForm");
const preview = document.querySelector("#taskPreview");
const teamOptions = { "Software Engineering Capstone": ["Team 1", "Team 2", "Team 3"], "Database Systems": ["Team 5"] };
let pendingTask = null;
let createdCount = 1;

function notify(title, message) {
    taskToastTitle.textContent = title;
    taskToastMessage.textContent = message;
    toast.hidden = false;
    setTimeout(() => (toast.hidden = true), 3000);
}
function formatDate(value) {
    return new Date(`${value}T00:00:00`).toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}
function setError(input, error, invalid) {
    input.classList.toggle("invalid", invalid);
    error.hidden = !invalid;
    return !invalid;
}

openTaskForm.onclick = () => {
    taskFormCard.scrollIntoView({ behavior: "smooth", block: "start" });
    taskProject.focus();
};
taskProject.onchange = () => {
    const teams = teamOptions[taskProject.value] || [];
    taskTeam.innerHTML = '<option value="">Choose a team</option>' + teams.map((team) => `<option>${team}</option>`).join("");
};
taskDescription.oninput = () => (descriptionCount.textContent = taskDescription.value.length);

document.querySelectorAll(".blocker [data-action]").forEach((btn) => {
    btn.onclick = () => {
        const blocker = btn.closest(".blocker");
        const status = btn.dataset.action === "ack" ? "Acknowledged" : "Resolved";
        const badge = blocker.querySelector(".badge");
        blocker.dataset.status = status;
        badge.textContent = status;
        badge.className = `badge status-${status.toLowerCase()}`;
        if (status === "Resolved") blocker.querySelector("div:last-child").innerHTML = '<span class="badge status-resolved">Response complete</span>';
        else btn.remove();
        notify(`Blocker ${status.toLowerCase()}`, status === "Acknowledged" ? "The team can see that you are reviewing it." : "The blocker was marked resolved.");
    };
});

taskForm.onsubmit = (event) => {
    event.preventDefault();
    const evidenceValid = !taskEvidence.value || /^https?:\/\/.+/i.test(taskEvidence.value);
    const datesValid = taskStart.value && taskDue.value && taskDue.value >= taskStart.value;
    const checks = [
        setError(taskProject, projectError, !taskProject.value), setError(taskTeam, teamError, !taskTeam.value),
        setError(taskTitle, titleError, !taskTitle.value.trim()), setError(taskAssignee, assigneeError, !taskAssignee.value),
        setError(taskPriority, priorityError, !taskPriority.value), setError(taskMilestone, milestoneError, !taskMilestone.value),
        setError(taskStart, startError, !taskStart.value), setError(taskDue, dueError, !datesValid),
        setError(taskDescription, descriptionError, taskDescription.value.trim().length < 10), setError(taskEvidence, evidenceError, !evidenceValid),
    ];
    if (checks.includes(false)) { taskForm.querySelector(".invalid").focus(); return; }
    pendingTask = {
        project: taskProject.value, team: taskTeam.value, title: taskTitle.value.trim(), assignee: taskAssignee.value,
        priority: taskPriority.value, milestone: taskMilestone.value, start: taskStart.value, due: taskDue.value,
        description: taskDescription.value.trim(), evidence: taskEvidence.value.trim(),
        checklist: [...document.querySelectorAll('[name="task-check"]:checked')].map((item) => item.value),
    };
    const checklist = pendingTask.checklist.length ? `<ul class="preview-checks">${pendingTask.checklist.map((item) => `<li>${item}</li>`).join("")}</ul>` : "No checklist items selected";
    previewBody.innerHTML = `<div class="preview-item full"><small>Task</small><p><strong>${pendingTask.title}</strong></p></div><div class="preview-item"><small>Project and team</small><p>${pendingTask.project}<br>${pendingTask.team}</p></div><div class="preview-item"><small>Assignee</small><p>${pendingTask.assignee}</p></div><div class="preview-item"><small>Priority</small><p><span class="badge priority-${pendingTask.priority.toLowerCase()}">${pendingTask.priority}</span></p></div><div class="preview-item"><small>Milestone</small><p>${pendingTask.milestone}</p></div><div class="preview-item"><small>Schedule</small><p>${formatDate(pendingTask.start)} – ${formatDate(pendingTask.due)}</p></div><div class="preview-item"><small>Evidence</small><p>${pendingTask.evidence ? `<a href="${pendingTask.evidence}" target="_blank" rel="noopener">Open reference link</a>` : "Not required"}</p></div><div class="preview-item full"><small>Description</small><p>${pendingTask.description}</p></div><div class="preview-item full"><small>Completion checklist</small>${checklist}</div>`;
    preview.showModal();
};

confirmTask.onclick = (event) => {
    event.preventDefault();
    if (!pendingTask) return;
    const row = document.createElement("div");
    row.className = "task-row";
    row.innerHTML = '<div><div class="task-row-title"><strong></strong><span class="badge"></span></div><small></small></div><span class="badge info">Official TA task</span>';
    row.querySelector("strong").textContent = pendingTask.title;
    const priority = row.querySelector(".task-row-title .badge");
    priority.textContent = pendingTask.priority;
    priority.classList.add(`priority-${pendingTask.priority.toLowerCase()}`);
    row.querySelector("small").textContent = `${pendingTask.team} · ${pendingTask.assignee} · ${pendingTask.milestone.replace(" · ", " ")} · Due ${formatDate(pendingTask.due)}`;
    taskRows.prepend(row);
    taskCount.textContent = `${++createdCount} tasks`;
    preview.close();
    taskForm.reset();
    pendingTask = null;
    notify("Official task published", "The selected team can now see this task.");
};

taskForm.onreset = () => setTimeout(() => {
    taskForm.querySelectorAll(".invalid").forEach((item) => item.classList.remove("invalid"));
    taskForm.querySelectorAll(".field-error").forEach((item) => (item.hidden = true));
    taskTeam.innerHTML = '<option value="">Choose a team</option>';
    descriptionCount.textContent = "0";
});
toast.querySelector("button").onclick = () => (toast.hidden = true);

const taskPageState = new URLSearchParams(location.search).get("state");
if (taskPageState) {
    tasksContent.hidden = true; tasksState.hidden = false;
    const states = { empty: ["0", "No tasks or blockers", "Official TA tasks and student blockers will appear here."], error: ["!", "Tasks could not be loaded", "Something went wrong while loading tasks and blockers."], loading: ["", "Loading tasks and blockers", '<div class="skeleton"><i></i><i></i><i></i></div>'] };
    const state = states[taskPageState] || states.error;
    tasksState.classList.add(taskPageState);
    tasksState.innerHTML = `<span class="state-icon">${state[0]}</span><h2>${state[1]}</h2>${taskPageState === "loading" ? state[2] : `<p>${state[2]}</p><a class="btn" href="/ta/tasks">${taskPageState === "error" ? "Retry" : "View tasks"}</a>`}`;
}
