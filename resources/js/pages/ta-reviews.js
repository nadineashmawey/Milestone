const submissions = [
    {
        team: "Team 3",
        milestone: "M2",
        title: "M2 Design Document",
        status: "submitted",
        deadline: "Today · 11:59 PM",
        late: false,
    },
    {
        team: "Team 1",
        milestone: "M2",
        title: "M2 Design Document",
        status: "reviewed",
        deadline: "Oct 23 · 11:59 PM",
        late: false,
    },
    {
        team: "Team 5",
        milestone: "M2",
        title: "M2 Design Document",
        status: "resubmission",
        deadline: "Overdue · Oct 21",
        late: true,
    },
    {
        team: "Team 2",
        milestone: "M1",
        title: "M1 Project Proposal",
        status: "graded",
        deadline: "Closed · Oct 15",
        late: false,
    },
    {
        team: "Team 1",
        milestone: "M3",
        title: "M3 Prototype",
        status: "not-started",
        deadline: "Oct 27 · 11:59 PM",
        late: false,
    },
    {
        team: "Team 2",
        milestone: "M2",
        title: "M2 Design Document",
        status: "approved",
        deadline: "Oct 24 · 11:59 PM",
        late: false,
    },
];
const labels = {
        "not-started": "Not started",
        submitted: "Submitted",
        reviewed: "TA reviewed",
        resubmission: "Needs resubmission",
        approved: "Approved",
        graded: "Graded",
    },
    params = new URLSearchParams(location.search),
    access = params.get("access") === "comment" ? "comment" : "full",
    list = document.querySelector("#submissionList"),
    toast = document.querySelector("#toast");
let selected = submissions[0];
function render() {
    const status = statusFilter.value,
        team = teamFilter.value,
        milestone = milestoneFilter.value,
        visible = submissions.filter(
            (x) =>
                (status === "all" || x.status === status) &&
                (team === "all" || x.team === team) &&
                (milestone === "all" || x.milestone === milestone),
        );
    queueCount.textContent = visible.length;
    queueEmpty.hidden = visible.length !== 0;
    list.innerHTML = visible
        .map(
            (x, i) =>
                `<button class="submission${x === selected ? " active" : ""}" data-team="${x.team}" data-milestone="${x.milestone}"><strong>${x.team}</strong><small>${x.title}</small><span class="badge ${x.status === "resubmission" ? "warning" : x.status === "reviewed" || x.status === "approved" ? "success" : "info"}">${labels[x.status]}</span>${x.late ? '<span class="badge danger">Late</span>' : ""}<small>${x.deadline}</small></button>`,
        )
        .join("");
    list.querySelectorAll("button").forEach(
        (btn) =>
            (btn.onclick = () => {
                selected = submissions.find(
                    (x) =>
                        x.team === btn.dataset.team &&
                        x.milestone === btn.dataset.milestone,
                );
                teamTitle.textContent = selected.team;
                milestoneLabel.textContent = `${selected.milestone} · ${selected.title.replace(/^M\d\s/, "")}`;
                deadlineLabel.textContent = selected.deadline;
                render();
            }),
    );
}
[statusFilter, teamFilter, milestoneFilter].forEach(
    (x) => (x.onchange = render),
);
document
    .querySelectorAll(".file-row button:not(:disabled)")
    .forEach((button) => {
        button.onclick = () => {
            if (button.textContent === "View") {
                showToast(
                    "File preview opened",
                    "design-document-v3.pdf is available in the prototype preview.",
                );
                return;
            }
            const blob = new Blob(
                ["Prototype attachment for Team 3 - M2 Design Document"],
                { type: "text/plain" },
            );
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "design-document-v3.txt";
            link.click();
            URL.revokeObjectURL(link.href);
            showToast("Download ready", "The prototype attachment was downloaded.");
        };
    });
document.querySelectorAll(".versions button").forEach(
    (btn) =>
        (btn.onclick = () => {
            document
                .querySelectorAll(".versions button")
                .forEach((x) => x.classList.toggle("active", x === btn));
        }),
);
document.querySelectorAll(".chips button").forEach(
    (btn) =>
        (btn.onclick = () => {
            feedback.value +=
                (feedback.value ? " " : "") + btn.textContent + ".";
            feedback.focus();
        }),
);
accessBadge.textContent =
    access === "comment"
        ? "Comment only · No review outcome"
        : "Full review · No final grade";
outcomeField.hidden = access === "comment";
permissionNote.textContent =
    access === "comment"
        ? "Comment-only access: you may send feedback, but cannot choose an outcome or request resubmission."
        : "Full-review access: you may record a review outcome, but the professor controls rubric scores and the final grade.";
function showToast(title, message) {
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    toast.hidden = false;
    setTimeout(() => (toast.hidden = true), 3200);
}
saveDraft.onclick = () =>
    showToast("Draft saved", "Your review draft is saved for this prototype.");
sendReview.onclick = () => {
    const noFeedback = !feedback.value.trim(),
        noOutcome = access === "full" && !reviewOutcome.value;
    feedbackError.hidden = !noFeedback;
    outcomeError.hidden = !noOutcome;
    feedback.classList.toggle("invalid", noFeedback);
    reviewOutcome.classList.toggle("invalid", noOutcome);
    if (noFeedback || noOutcome) return;
    selected.status = access === "full" ? reviewOutcome.value : "reviewed";
    render();
    showToast(
        access === "full" && selected.status === "resubmission"
            ? "Revision requested"
            : "Review sent",
        "Professor Lee and the team were notified.",
    );
};
feedback.oninput = () => {
    if (feedback.value.trim()) {
        feedbackError.hidden = true;
        feedback.classList.remove("invalid");
    }
};
toast.querySelector("button").onclick = () => (toast.hidden = true);
render();
const state = params.get("state"),
    content = document.querySelector("#reviewContent"),
    box = document.querySelector("#reviewState");
if (state) {
    content.hidden = true;
    box.hidden = false;
    const states = {
        empty: [
            "0",
            "No submissions to review",
            "New submissions from assigned teams will appear here.",
        ],
        error: [
            "!",
            "Reviews could not be loaded",
            "Something went wrong while loading the review queue.",
        ],
        loading: [
            "",
            "Loading reviews",
            '<div class="skeleton"><i></i><i></i><i></i></div>',
        ],
        revoked: [
            "!",
            "Project access removed",
            "Your TA access to this project was revoked. No private project details are available.",
        ],
    };
    const s = states[state] || states.error;
    box.classList.add(state);
    box.innerHTML = `<span class="state-icon">${s[0]}</span><h2>${s[1]}</h2>${state === "loading" ? s[2] : `<p>${s[2]}</p><a class="btn" href="/ta/dashboard">Return to dashboard</a>`}`;
}
