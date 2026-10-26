const users = [
    {
        initials: "AH",
        name: "Aya Hassan",
        email: "aya.hassan@miuegypt.edu.eg",
        role: "Student",
        status: "Active",
        active: "Today",
    },
    {
        initials: "PL",
        name: "Professor Lee",
        email: "lee@miuegypt.edu.eg",
        role: "Professor",
        status: "Active",
        active: "Today",
    },
    {
        initials: "SA",
        name: "Sara Ahmed",
        email: "sara.ahmed@miuegypt.edu.eg",
        role: "TA",
        status: "Active",
        active: "Today",
    },
    {
        initials: "DL",
        name: "Dr. Lina Haddad",
        email: "lina.haddad@miuegypt.edu.eg",
        role: "Student",
        status: "Pending role",
        active: "Never",
    },
    {
        initials: "NK",
        name: "Nadia Karim",
        email: "nadia.karim@miuegypt.edu.eg",
        role: "Student",
        status: "Deactivated",
        active: "Sep 28",
    },
];
const body = document.querySelector("#usersBody");
const toast = document.querySelector("#toast");
const toastMessage = document.querySelector("#toastMessage");
let filter = "all";
let pendingDeactivationIndex = null;
let pendingStatusAction = null;

function visibleUsers() {
    return users
        .map((user, index) => ({ user, index }))
        .filter(
            ({ user }) =>
                filter === "all" || user.role.toLowerCase() === filter,
        );
}
function showToast(message) {
    toastMessage.textContent = message;
    toast.hidden = false;
    clearTimeout(showToast.timer);
    showToast.timer = setTimeout(() => {
        toast.hidden = true;
    }, 3000);
}
function selectedIndexes() {
    return [...document.querySelectorAll(".user-check:checked")].map((box) =>
        Number(box.value),
    );
}
function updateSelection() {
    const count = selectedIndexes().length;
    document.querySelector("#selectionCount").textContent = `${count} selected`;
    document.querySelector("#assignProfessor").disabled = count === 0;
    document.querySelector("#assignTa").disabled = count === 0;
}
function draw() {
    const visible = visibleUsers();
    body.innerHTML = visible
        .map(
            ({ user, index }) => `<tr data-user-index="${index}">
    <td><input type="checkbox" class="user-check" value="${index}" aria-label="Select ${user.name}"></td>
    <td><div class="person"><span class="avatar">${user.initials}</span><div><strong>${user.name}</strong><span>${user.email}</span></div></div></td>
    <td><span class="badge info">${user.role}</span></td>
    <td><span class="badge ${user.status === "Active" ? "success" : "warning"}">${user.status}</span></td>
    <td>${user.active}</td>
    <td><button type="button" class="row-action" data-toggle-status="${index}">${user.status === "Deactivated" ? "Activate" : "Deactivate"}</button></td>
  </tr>`,
        )
        .join("");
    document.querySelector("#usersTableWrap").hidden = visible.length === 0;
    document.querySelector("#usersEmptyState").hidden = visible.length !== 0;
    document
        .querySelectorAll(".user-check")
        .forEach((box) => box.addEventListener("change", updateSelection));
    document
        .querySelectorAll("[data-toggle-status]")
        .forEach((button) =>
            button.addEventListener("click", () =>
                toggleStatus(Number(button.dataset.toggleStatus)),
            ),
        );
    updateSelection();
}
function assignRole(role) {
    const selected = selectedIndexes();
    if (!selected.length) return;
    selected.forEach((index) => {
        users[index].role = role;
        if (users[index].status === "Pending role")
            users[index].status = "Active";
    });
    draw();
    showToast(
        `${selected.length} user${selected.length === 1 ? "" : "s"} assigned the ${role} role.`,
    );
}
function toggleStatus(index) {
    const user = users[index];
    const activating = user.status === "Deactivated";
    openDeactivateDialog(index, activating);
}

const deactivateDialog = document.querySelector("#deactivateDialog");
const deactivateOverlay = document.querySelector("#deactivateOverlay");
function openDeactivateDialog(index, activating) {
    pendingDeactivationIndex = index;
    pendingStatusAction = activating ? "activate" : "deactivate";
    const user = users[index];
    const title = document.querySelector("#deactivateTitle");
    const message = document.querySelector("#statusDialogMessage");
    const notice = document.querySelector("#statusDialogNotice");
    const icon = document.querySelector("#statusDialogIcon");
    const confirmButton = document.querySelector("#confirmDeactivate");
    title.textContent = activating ? "Activate account?" : "Deactivate account?";
    message.innerHTML = activating
        ? `<strong>${user.name}</strong> will be able to sign in and access the workspace again.`
        : `<strong>${user.name}</strong> will no longer be able to sign in or access the workspace.`;
    notice.textContent = activating
        ? "The user’s existing role and retained historical records will be restored without creating a new account."
        : "Historical project records, submissions, activity, and role history will remain available.";
    icon.textContent = activating ? "✓" : "!";
    icon.classList.toggle("activation", activating);
    confirmButton.textContent = activating ? "Confirm activation" : "Confirm deactivation";
    confirmButton.classList.toggle("danger-button", !activating);
    confirmButton.classList.toggle("success-button", activating);
    deactivateDialog.hidden = false;
    deactivateOverlay.hidden = false;
    document.querySelector("#cancelDeactivate").focus();
}
function closeDeactivateDialog() {
    deactivateDialog.hidden = true;
    deactivateOverlay.hidden = true;
    pendingDeactivationIndex = null;
    pendingStatusAction = null;
}
document.querySelector("#cancelDeactivate").addEventListener("click", closeDeactivateDialog);
deactivateOverlay.addEventListener("click", closeDeactivateDialog);
document.querySelector("#confirmDeactivate").addEventListener("click", () => {
    if (pendingDeactivationIndex === null) return;
    const user = users[pendingDeactivationIndex];
    const activating = pendingStatusAction === "activate";
    user.status = activating ? "Active" : "Deactivated";
    if (activating) user.active = "Today";
    closeDeactivateDialog();
    draw();
    showToast(
        activating
            ? `${user.name} was activated and can access the workspace again.`
            : `${user.name} was deactivated. Historical records were retained.`,
    );
});
document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && !deactivateDialog.hidden) closeDeactivateDialog();
});

document
    .querySelector("#assignProfessor")
    .addEventListener("click", () => assignRole("Professor"));
document
    .querySelector("#assignTa")
    .addEventListener("click", () => assignRole("TA"));
document.querySelector("#closeToast").addEventListener("click", () => {
    toast.hidden = true;
});
document.querySelectorAll("[data-tab]").forEach((button) =>
    button.addEventListener("click", () => {
        document
            .querySelectorAll("[data-tab]")
            .forEach((tab) =>
                tab.setAttribute("aria-selected", String(tab === button)),
            );
        document.querySelectorAll("[data-panel]").forEach((panel) => {
            panel.hidden = panel.dataset.panel !== button.dataset.tab;
        });
    }),
);
document.querySelectorAll("[data-select]").forEach((select) => {
    const trigger = select.querySelector(".select-trigger");
    const menu = select.querySelector(".select-menu");
    trigger.addEventListener("click", () => {
        menu.hidden = !menu.hidden;
        trigger.setAttribute("aria-expanded", String(!menu.hidden));
    });
    menu.querySelectorAll("button").forEach((option) =>
        option.addEventListener("click", () => {
            trigger.querySelector("span").textContent = option.textContent;
            menu.hidden = true;
            trigger.setAttribute("aria-expanded", "false");
            menu.querySelectorAll("button").forEach((item) =>
                item.setAttribute("aria-selected", String(item === option)),
            );
            const input = select.querySelector("input");
            if (input) input.value = option.dataset.value;
            else {
                filter = option.dataset.value;
                draw();
            }
        }),
    );
});
const drawer = document.querySelector("#drawer");
const overlay = document.querySelector("#overlay");
function toggleDrawer(open) {
    drawer.classList.toggle("open", open);
    drawer.setAttribute("aria-hidden", String(!open));
    overlay.hidden = !open;
}
document
    .querySelector("#addUser")
    .addEventListener("click", () => toggleDrawer(true));
document
    .querySelector("#closeDrawer")
    .addEventListener("click", () => toggleDrawer(false));
document
    .querySelector("#cancelDrawer")
    .addEventListener("click", () => toggleDrawer(false));
overlay.addEventListener("click", () => toggleDrawer(false));

const reviewDrawer = document.querySelector("#reviewDrawer");
const reviewOverlay = document.querySelector("#reviewOverlay");
function toggleReview(open) {
    reviewDrawer.classList.toggle("open", open);
    reviewDrawer.setAttribute("aria-hidden", String(!open));
    reviewOverlay.hidden = !open;
}
function resolvePending(approved) {
    if (approved) {
        const user = users.find(
            (item) => item.email === "lina.haddad@miuegypt.edu.eg",
        );
        user.role = "Professor";
        user.status = "Active";
        user.active = "Today";
    }
    document.querySelector("#pendingCount").textContent = "0";
    document.querySelector("#pendingSummary").textContent =
        "No accounts awaiting approval";
    document.querySelector("#pendingTable").hidden = true;
    document.querySelector("#pendingEmpty").hidden = false;
    toggleReview(false);
    draw();
    showToast(
        approved
            ? "Dr. Lina Haddad was approved as Professor."
            : "Dr. Lina Haddad’s role request was rejected.",
    );
}
document
    .querySelector("#reviewPending")
    .addEventListener("click", () => toggleReview(true));
document
    .querySelector("#closeReview")
    .addEventListener("click", () => toggleReview(false));
reviewOverlay.addEventListener("click", () => toggleReview(false));
document
    .querySelector("#approvePending")
    .addEventListener("click", () => resolvePending(true));
document
    .querySelector("#rejectPending")
    .addEventListener("click", () => resolvePending(false));

const dataRequestDrawer = document.querySelector("#dataRequestDrawer");
const dataRequestOverlay = document.querySelector("#dataRequestOverlay");
const requestRow = document.querySelector("[data-request-row]");
function toggleDataRequest(open) {
    dataRequestDrawer.classList.toggle("open", open);
    dataRequestDrawer.setAttribute("aria-hidden", String(!open));
    dataRequestOverlay.hidden = !open;
}
function resolveDataRequest(status) {
    requestRow.dataset.status = status.toLowerCase();
    const badge = requestRow.querySelector("[data-request-badge]");
    badge.textContent = status;
    badge.className = `badge ${status === "Approved" ? "success" : "danger"}`;
    const action = requestRow.querySelector("#reviewDataRequest");
    action.textContent = "Reviewed";
    action.disabled = true;
    document.querySelector('[data-tab="requests"] span').textContent = "0";
    toggleDataRequest(false);
    showToast(`Aya Hassan’s data request was ${status.toLowerCase()}.`);
}
document
    .querySelector("#reviewDataRequest")
    .addEventListener("click", () => toggleDataRequest(true));
document
    .querySelector("#closeDataRequest")
    .addEventListener("click", () => toggleDataRequest(false));
dataRequestOverlay.addEventListener("click", () => toggleDataRequest(false));
document
    .querySelector("#approveDataRequest")
    .addEventListener("click", () => resolveDataRequest("Approved"));
document
    .querySelector("#rejectDataRequest")
    .addEventListener("click", () => resolveDataRequest("Rejected"));

const requestStatusSelect = document.querySelector("#requestStatusSelect");
const requestStatusTrigger =
    requestStatusSelect.querySelector(".select-trigger");
const requestStatusMenu = requestStatusSelect.querySelector(".select-menu");
requestStatusTrigger.addEventListener("click", () => {
    requestStatusMenu.hidden = !requestStatusMenu.hidden;
    requestStatusTrigger.setAttribute(
        "aria-expanded",
        String(!requestStatusMenu.hidden),
    );
});
requestStatusMenu.querySelectorAll("button").forEach((option) =>
    option.addEventListener("click", () => {
        const status = option.dataset.requestStatus;
        requestStatusTrigger.querySelector("span").textContent =
            option.textContent;
        requestStatusMenu.hidden = true;
        requestStatusTrigger.setAttribute("aria-expanded", "false");
        requestStatusMenu
            .querySelectorAll("button")
            .forEach((item) =>
                item.setAttribute("aria-selected", String(item === option)),
            );
        const visible =
            status === "all" || requestRow.dataset.status === status;
        requestRow.hidden = !visible;
        document.querySelector("#requestsEmpty").hidden = visible;
    }),
);

const userFormRules = {
    first: (value) => {
        const name = value.trim();
        if (!name) return "First name is required.";
        if (name.length < 2) return "First name must contain at least 2 characters.";
        if (!/^[\p{L}][\p{L}\s'-]*$/u.test(name))
            return "Use letters, spaces, apostrophes, or hyphens only.";
        return "";
    },
    last: (value) => {
        const name = value.trim();
        if (!name) return "Last name is required.";
        if (name.length < 2) return "Last name must contain at least 2 characters.";
        if (!/^[\p{L}][\p{L}\s'-]*$/u.test(name))
            return "Use letters, spaces, apostrophes, or hyphens only.";
        return "";
    },
    email: (value) => {
        const email = value.trim().toLowerCase();
        if (!email) return "University email is required.";
        if (!/^[^\s@]+@miuegypt\.edu\.eg$/i.test(email))
            return "Enter a valid email ending in @miuegypt.edu.eg.";
        if (users.some((user) => user.email.toLowerCase() === email))
            return "A user with this email already exists.";
        return "";
    },
};
function validateUserField(id) {
    const input = document.querySelector(`#${id}`);
    const error = document.querySelector(`[data-error="${id}"]`);
    const message = userFormRules[id](input.value);
    error.textContent = message;
    error.hidden = !message;
    input.classList.toggle("invalid", Boolean(message));
    input.setAttribute("aria-invalid", String(Boolean(message)));
    return !message;
}
Object.keys(userFormRules).forEach((id) => {
    const input = document.querySelector(`#${id}`);
    input.addEventListener("blur", () => validateUserField(id));
    input.addEventListener("input", () => {
        if (input.classList.contains("invalid")) validateUserField(id);
    });
});

document.querySelector("#userForm").addEventListener("submit", (event) => {
    event.preventDefault();
    let valid = true;
    Object.keys(userFormRules).forEach((id) => {
        if (!validateUserField(id)) valid = false;
    });
    if (!valid) {
        document.querySelector("#userForm .invalid")?.focus();
        return;
    }
    const firstName = document.querySelector("#first").value.trim();
    const lastName = document.querySelector("#last").value.trim();
    const role = document.querySelector("#role").value;
    users.unshift({
        initials: `${firstName[0]}${lastName[0]}`.toUpperCase(),
        name: `${firstName} ${lastName}`,
        email: document.querySelector("#email").value.trim().toLowerCase(),
        role: role === "ta" ? "TA" : "Professor",
        status: "Active",
        active: "Just added",
    });
    toggleDrawer(false);
    document.querySelector("#userForm").reset();
    filter = "all";
    draw();
    showToast(`${firstName} ${lastName} was added successfully.`);
});
document.querySelector("#clearUserFilter").addEventListener("click", () => {
    filter = "all";
    document.querySelector('[data-panel="all"] [data-select] .select-trigger span').textContent = "All roles";
    draw();
});
draw();
const usersPageState = new URLSearchParams(location.search).get("state");
if (usersPageState === "error") {
    document.querySelector("#usersToolbar").hidden = true;
    document.querySelector("#usersTableWrap").hidden = true;
    document.querySelector("#usersEmptyState").hidden = true;
    document.querySelector("#usersErrorState").hidden = false;
} else if (usersPageState === "empty") {
    document.querySelector("#usersTableWrap").hidden = true;
    document.querySelector("#usersEmptyState").hidden = false;
}
