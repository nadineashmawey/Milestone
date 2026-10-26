const records = [
    [
        "2026-10-18 10:42",
        "Today · 10:42",
        "University Admin",
        "Assigned TA role",
        "Sara Ahmed",
        "Success",
        "role",
    ],
    [
        "2026-10-17 15:18",
        "Yesterday · 3:18",
        "University Admin",
        "Imported course roster",
        "CS 490 · 24 students",
        "Success",
        "course",
    ],
    [
        "2026-10-05 09:12",
        "Oct 5 · 9:12",
        "University Admin",
        "Deactivated account",
        "Nadia Karim",
        "History retained",
        "account",
    ],
    [
        "2026-10-03 13:05",
        "Oct 3 · 1:05",
        "University Admin",
        "Transferred ownership",
        "Database Systems",
        "Success",
        "project",
    ],
    [
        "2026-10-01 08:30",
        "Oct 1 · 8:30",
        "University Admin",
        "Activated account",
        "Omar Said",
        "Success",
        "account",
    ],
];
let active = "all",
    visible = [];
function draw() {
    visible = records.filter((r) => active === "all" || r[6] === active);
    auditBody.innerHTML = visible
        .map(
            (r) =>
                `<tr><td>${r[1]}</td><td>${r[2]}</td><td>${r[3]}</td><td>${r[4]}</td><td><span class="badge success">${r[5]}</span></td></tr>`,
        )
        .join("");
    visibleCount.textContent = visible.length;
    auditEmpty.hidden = !!visible.length;
}
const trigger = auditSelect.querySelector(".select-trigger"),
    menu = auditSelect.querySelector(".select-menu");
trigger.onclick = () => (menu.hidden = !menu.hidden);
menu.querySelectorAll("button").forEach(
    (b) =>
        (b.onclick = () => {
            active = b.dataset.value;
            trigger.querySelector("span").textContent = b.textContent;
            menu.hidden = true;
            menu.querySelectorAll("button").forEach((x) =>
                x.setAttribute("aria-selected", x === b),
            );
            draw();
        }),
);
exportCsv.onclick = () => {
    const esc = (v) => `"${String(v).replaceAll('"', '""')}"`;
    const data = [
        ["Time", "Administrator", "Action", "Target", "Result"],
        ...visible.map((r) => [r[0], r[2], r[3], r[4], r[5]]),
    ]
        .map((row) => row.map(esc).join(","))
        .join("\r\n");
    const a = document.createElement("a");
    a.href = URL.createObjectURL(
        new Blob([data], { type: "text/csv;charset=utf-8" }),
    );
    a.download = "admin-audit.csv";
    a.click();
    URL.revokeObjectURL(a.href);
    toastCopy.textContent = `${visible.length} record${visible.length === 1 ? "" : "s"} exported.`;
    auditToast.hidden = false;
    setTimeout(() => (auditToast.hidden = true), 3000);
};
draw();
const auditState = new URLSearchParams(location.search).get("state");
if (auditState === "empty") {
    document.querySelector("#auditCard").hidden = true;
    document.querySelector("#exportCsv").disabled = true;
    document.querySelector("#auditNoActivity").hidden = false;
} else if (auditState === "error") {
    document.querySelector("#auditCard").hidden = true;
    document.querySelector("#exportCsv").disabled = true;
    document.querySelector("#auditErrorState").hidden = false;
}
