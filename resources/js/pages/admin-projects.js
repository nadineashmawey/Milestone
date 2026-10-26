const projects = [
    {
        name: "Software Engineering Capstone",
        meta: "Fall 2026 · CS course",
        mode: "Academic",
        owner: "Professor Lee",
        state: "Active",
    },
    {
        name: "Database Systems Project",
        meta: "Fall 2026 · CS course",
        mode: "Academic",
        owner: "Dr. Lina Haddad",
        state: "Transfer needed",
    },
    {
        name: "Campus Navigation App",
        meta: "Student-created · No grading",
        mode: "Independent",
        owner: "Aya Hassan",
        state: "Active",
    },
    {
        name: "Web Workshop 2025",
        meta: "Fall 2026 · CS course",
        mode: "Academic",
        owner: "Professor Omar Malik",
        state: "Completed",
    },
    {
        name: "Smart Campus Assistant",
        meta: "Fall 2026 · CS course",
        mode: "Academic",
        owner: "Professor Nabil Farouk",
        state: "Draft",
    },
    {
        name: "Library Queue Tracker",
        meta: "Spring 2026 · CS course",
        mode: "Academic",
        owner: "Professor Mona Adel",
        state: "Archived",
    },
];
let modeFilter = "all",
    stateFilter = "all",
    deleteIndex = null,
    transferIndex = null,
    pendingProjectAction = null,
    selectedProject = null;
const rows = document.querySelector("#projectRows");
const deleteOverlayElement = document.querySelector("#deleteOverlay");
const deleteDialogElement = document.querySelector("#deleteDialog");
const deleteProjectNameElement = document.querySelector("#deleteProjectName");
const deleteReasonElement = document.querySelector("#deleteReason");
const deleteConfirmationElement = document.querySelector("#deleteConfirmation");
const deleteReasonErrorElement = document.querySelector("#deleteReasonError");
const deleteNameErrorElement = document.querySelector("#deleteNameError");
const cancelDeleteButton = document.querySelector("#cancelDelete");
const confirmDeleteButton = document.querySelector("#confirmDelete");
function tone(value) {
    return value === "Active"
        ? "success"
        : value === "Transfer needed"
          ? "warning"
          : value === "Read only"
            ? "review"
            : value === "Draft"
              ? "info"
              : value === "Completed"
                ? "review"
                : "";
}
function toast(message) {
    projectToastMessage.textContent = message;
    projectToast.hidden = false;
    clearTimeout(toast.timer);
    toast.timer = setTimeout(() => (projectToast.hidden = true), 3000);
}
function render() {
    const visible = projects
        .map((project, index) => ({ project, index }))
        .filter(
            ({ project }) =>
                (modeFilter === "all" ||
                    project.mode.toLowerCase() === modeFilter) &&
                (stateFilter === "all" ||
                    project.state.toLowerCase().replace(/\s/g, "") ===
                        stateFilter),
        );
    function actions(project, index) {
        if (project.state === "Transfer needed")
            return `<button data-transfer="${index}">Transfer</button><button data-archive="${index}">Archive</button><button class="unavailable" type="button" disabled>Delete</button><small class="eligibility-note">Only draft projects can be deleted</small>`;
        if (project.state === "Draft")
            return `<button data-archive="${index}">Archive</button><button class="delete" data-delete="${index}">Delete</button>`;
        if (project.state === "Archived")
            return `<button data-archive="${index}">Restore</button><small class="eligibility-note">Archived records cannot be deleted</small>`;
        if (project.state === "Completed")
            return `<button data-archive="${index}">Archive</button><small class="eligibility-note">Completed projects are archive only</small>`;
        return `<button data-archive="${index}">Archive</button><button class="unavailable" type="button" disabled>Delete</button><small class="eligibility-note">Only draft projects can be deleted</small>`;
    }
    rows.innerHTML = visible
        .map(
            ({ project, index }) =>
                `<tr><td><span class="project-title">${project.name}</span><span class="project-meta">${project.meta}</span></td><td><span class="badge ${project.mode === "Independent" ? "success" : "info"}">● ${project.mode}</span></td><td>${project.owner}</td><td><span class="badge ${tone(project.state)}">● ${project.state}</span></td><td><div class="project-actions">${actions(project, index)}</div></td></tr>`,
        )
        .join("");
    projectEmpty.hidden = visible.length > 0;
    document.querySelectorAll("[data-archive]").forEach(
        (button) =>
            (button.onclick = () => openProjectAction(Number(button.dataset.archive))),
    );
    document.querySelectorAll("[data-transfer]").forEach(
        (button) =>
            (button.onclick = () => openTransfer(Number(button.dataset.transfer))),
    );
    document
        .querySelectorAll("[data-delete]")
        .forEach(
            (button) =>
                (button.onclick = () =>
                    openDelete(Number(button.dataset.delete))),
        );
}
document.querySelectorAll("[data-project-select]").forEach((select) => {
    const trigger = select.querySelector(".select-trigger"),
        menu = select.querySelector(".select-menu");
    trigger.onclick = () => (menu.hidden = !menu.hidden);
    menu.querySelectorAll("button").forEach(
        (option) =>
            (option.onclick = () => {
                trigger.querySelector("span").textContent = option.textContent;
                menu.hidden = true;
                if (select.dataset.projectSelect === "mode")
                    modeFilter = option.dataset.value;
                else stateFilter = option.dataset.value;
                render();
            }),
    );
});
const actionOverlayElement = document.querySelector("#actionOverlay");
const actionDialogElement = document.querySelector("#actionDialog");
function openProjectAction(index) {
    const project = projects[index];
    const restoring = project.state === "Archived";
    pendingProjectAction = { index, action: restoring ? "restore" : "archive" };
    document.querySelector("#actionDialogTitle").textContent = restoring
        ? "Restore project?"
        : "Archive project?";
    document.querySelector("#actionDialogMessage").textContent = restoring
        ? `${project.name} will return to the active project records.`
        : `${project.name} will be moved out of active project records.`;
    document.querySelector("#actionDialogNotice").textContent = restoring
        ? "Restoring makes the record available again. Existing history remains unchanged."
        : "Archiving preserves tasks, files, submissions, grades, and team history. The project can be restored later.";
    document.querySelector("#actionDialogIcon").textContent = restoring ? "↻" : "▣";
    document.querySelector("#confirmProjectAction").textContent = restoring
        ? "Confirm restore"
        : "Confirm archive";
    actionOverlayElement.hidden = false;
    actionDialogElement.hidden = false;
    document.querySelector("#cancelProjectAction").focus();
}
function closeProjectAction() {
    actionOverlayElement.hidden = true;
    actionDialogElement.hidden = true;
    pendingProjectAction = null;
}
document.querySelector("#cancelProjectAction").addEventListener("click", closeProjectAction);
actionOverlayElement.addEventListener("click", closeProjectAction);
document.querySelector("#confirmProjectAction").addEventListener("click", () => {
    if (!pendingProjectAction) return;
    const { index, action } = pendingProjectAction;
    const project = projects[index];
    project.state = action === "restore" ? "Active" : "Archived";
    closeProjectAction();
    render();
    renderPicker();
    toast(`${project.name} was ${action === "restore" ? "restored" : "archived"}.`);
});
const transferOverlayElement = document.querySelector("#transferOverlay");
const transferDialogElement = document.querySelector("#transferDialog");
const transferProfessorElement = document.querySelector("#transferProfessor");
const transferReasonElement = document.querySelector("#transferReason");
function openTransfer(index) {
    transferIndex = index;
    const project = projects[index];
    document.querySelector("#transferProjectName").textContent = project.name;
    document.querySelector("#transferCurrentOwner").textContent = project.owner;
    transferProfessorElement.value = "";
    transferReasonElement.value = "";
    transferProfessorElement.classList.remove("invalid");
    transferReasonElement.classList.remove("invalid");
    document.querySelector("#transferProfessorError").hidden = true;
    document.querySelector("#transferReasonError").hidden = true;
    transferOverlayElement.hidden = false;
    transferDialogElement.hidden = false;
    transferProfessorElement.focus();
}
function closeTransfer() {
    transferOverlayElement.hidden = true;
    transferDialogElement.hidden = true;
    transferIndex = null;
}
document.querySelector("#cancelTransfer").addEventListener("click", closeTransfer);
transferOverlayElement.addEventListener("click", closeTransfer);
document.querySelector("#confirmTransfer").addEventListener("click", () => {
    const professorError = document.querySelector("#transferProfessorError");
    const reasonError = document.querySelector("#transferReasonError");
    const missingProfessor = !transferProfessorElement.value;
    const missingReason = !transferReasonElement.value;
    professorError.textContent = "Choose the new professor.";
    reasonError.textContent = "Choose a transfer reason.";
    professorError.hidden = !missingProfessor;
    reasonError.hidden = !missingReason;
    transferProfessorElement.classList.toggle("invalid", missingProfessor);
    transferReasonElement.classList.toggle("invalid", missingReason);
    if (missingProfessor || missingReason || transferIndex === null) {
        (missingProfessor ? transferProfessorElement : transferReasonElement).focus();
        return;
    }
    const project = projects[transferIndex];
    const previousOwner = project.owner;
    project.owner = transferProfessorElement.value;
    project.state = "Active";
    closeTransfer();
    render();
    renderPicker();
    toast(`${project.name} transferred from ${previousOwner} to ${project.owner}.`);
});
transferProfessorElement.addEventListener("change", () => {
    document.querySelector("#transferProfessorError").hidden = Boolean(transferProfessorElement.value);
    transferProfessorElement.classList.remove("invalid");
});
transferReasonElement.addEventListener("change", () => {
    document.querySelector("#transferReasonError").hidden = Boolean(transferReasonElement.value);
    transferReasonElement.classList.remove("invalid");
});
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !transferDialogElement.hidden) closeTransfer();
    if (event.key === "Escape" && !actionDialogElement.hidden) closeProjectAction();
});
function openDelete(index) {
    if (projects[index].state !== "Draft") return;
    deleteIndex = index;
    deleteProjectNameElement.textContent = projects[index].name;
    deleteReasonElement.value = "";
    deleteConfirmationElement.value = "";
    confirmDeleteButton.disabled = true;
    deleteReasonErrorElement.hidden = deleteNameErrorElement.hidden = true;
    deleteOverlayElement.hidden = false;
    deleteDialogElement.hidden = false;
}
function closeDelete() {
    deleteOverlayElement.hidden = true;
    deleteDialogElement.hidden = true;
    deleteIndex = null;
}
function validateDelete() {
    if (deleteIndex === null) return;
    const hasText = deleteConfirmationElement.value.trim().length > 0;
    confirmDeleteButton.disabled = !hasText;
    deleteNameErrorElement.textContent = "Enter confirmation text.";
    deleteNameErrorElement.hidden =
        hasText || deleteConfirmationElement.value.length === 0;
    deleteReasonErrorElement.hidden = true;
}
deleteReasonElement.addEventListener("change", validateDelete);
deleteConfirmationElement.addEventListener("input", validateDelete);
cancelDeleteButton.addEventListener("click", closeDelete);
deleteOverlayElement.addEventListener("click", closeDelete);
confirmDeleteButton.addEventListener("click", () => {
    if (deleteIndex === null || confirmDeleteButton.disabled) return;
    if (!deleteReasonElement.value) {
        deleteReasonErrorElement.textContent = "Select a reason for deletion.";
        deleteReasonErrorElement.hidden = false;
        return;
    }
    const name = projects[deleteIndex].name;
    projects.splice(deleteIndex, 1);
    closeDelete();
    render();
    renderPicker();
    toast(`${name} was deleted.`);
});
document.querySelector("#closeProjectToast").addEventListener("click", () => {
    document.querySelector("#projectToast").hidden = true;
});
function renderPicker(query = "") {
    const search = query.toLowerCase();
    projectPicker.innerHTML = projects
        .map((project, index) => ({ project, index }))
        .filter(({ project }) =>
            `${project.name} ${project.meta} ${project.owner}`
                .toLowerCase()
                .includes(search),
        )
        .map(
            ({ project, index }) =>
                `<button type="button" class="picker-item${selectedProject === project.name ? " selected" : ""}" data-pick="${index}"><span><strong>${project.name}</strong><br><small>${project.meta.includes("Independent") ? "Independent project · " : project.meta.replace("Fall 2026 · ", "") + " · "}${project.owner}</small></span><span class="badge ${project.mode === "Independent" ? "success" : "info"}">● ${project.mode}</span></button>`,
        )
        .join("");
    document.querySelectorAll("[data-pick]").forEach(
        (button) =>
            (button.onclick = () => {
                selectedProject = projects[button.dataset.pick].name;
                renderPicker(projectSearch.value);
                supportProjectError.hidden = true;
            }),
    );
}
openSupport.onclick = () => {
    projectList.hidden = true;
    supportScreen.hidden = false;
    renderPicker();
    window.scrollTo(0, 0);
};
backProjects.onclick = () => {
    supportScreen.hidden = true;
    projectList.hidden = false;
    window.scrollTo(0, 0);
};
projectSearch.oninput = () => renderPicker(projectSearch.value);
supportForm.onsubmit = (event) => {
    event.preventDefault();
    const reason = supportReason.value,
        details = supportDetails.value.trim();
    supportProjectError.textContent = "Select a project.";
    supportReasonError.textContent = "Choose a support reason.";
    supportDetailsError.textContent = "Enter at least 10 characters.";
    supportProjectError.hidden = !!selectedProject;
    supportReasonError.hidden = !!reason;
    supportDetailsError.hidden = details.length >= 10;
    if (!selectedProject || !reason || details.length < 10) return;
    supportEmpty.hidden = true;
    supportSessions.hidden = false;
    supportSessions.innerHTML = `<article class="session-item"><strong>${selectedProject}</strong><p>${reason}</p><span class="badge success">Active · 30 minutes</span></article>`;
    toast("Read-only support session started.");
    supportForm.reset();
    selectedProject = null;
    renderPicker();
};
render();
const projectPageParams = new URLSearchParams(location.search);
if (projectPageParams.get("state") === "empty") {
    document.querySelector("#projectRecordsCard").hidden = true;
    document.querySelector("#projectsEmptyState").hidden = false;
} else if (projectPageParams.get("state") === "error") {
    document.querySelector("#projectRecordsCard").hidden = true;
    document.querySelector("#projectsErrorState").hidden = false;
} else if (projectPageParams.get("transfer") === "1") {
    const transferProjectIndex = projects.findIndex((project) => project.state === "Transfer needed");
    if (transferProjectIndex >= 0) openTransfer(transferProjectIndex);
}
