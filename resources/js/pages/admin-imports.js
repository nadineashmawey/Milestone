let rows = [];
let validationCounts = { accepted: 0, duplicate: 0, rejected: 0 };
let selectedFile = null;
const requiredColumns = ["first_name", "last_name", "email", "university_id"];
const existingEmails = new Set([
    "aya.hassan@miuegypt.edu.eg",
    "lee@miuegypt.edu.eg",
    "sara.ahmed@miuegypt.edu.eg",
]);
const csvInput = document.querySelector("#csv");
const dropzone = document.querySelector("#dropzone");
const filePrompt = document.querySelector("#filePrompt");
const fileSummary = document.querySelector("#fileSummary");
const fileName = document.querySelector("#fileName");
const fileSize = document.querySelector("#fileSize");
const removeFileButton = document.querySelector("#removeFile");
const uploadError = document.querySelector("#uploadError");
const validateButton = document.querySelector("#validate");
const backButton = document.querySelector("#back");
const confirmButton = document.querySelector("#confirm");
const importAgainButton = document.querySelector("#again");
const previewBody = document.querySelector("#previewBody");
const validationSummary = document.querySelector("#summary");
const completeCopy = document.querySelector("#completeCopy");

document.querySelectorAll("[data-select]").forEach((select) => {
    const trigger = select.querySelector(".select-trigger");
    const menu = select.querySelector(".select-menu");
    trigger.addEventListener("click", () => {
        menu.hidden = !menu.hidden;
        trigger.setAttribute("aria-expanded", String(!menu.hidden));
    });
    menu.querySelectorAll("button").forEach((option) =>
        option.addEventListener("click", () => {
            select.querySelector("input").value = option.dataset.value;
            trigger.querySelector("span").textContent = option.textContent;
            menu.hidden = true;
            trigger.setAttribute("aria-expanded", "false");
        }),
    );
});

function showError(message) {
    uploadError.textContent = message;
    uploadError.hidden = false;
}
function acceptFile(file) {
    uploadError.hidden = true;
    if (!file || !file.name.toLowerCase().endsWith(".csv")) {
        showError("Choose a CSV file.");
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        showError("The CSV must be smaller than 5 MB.");
        return;
    }
    selectedFile = file;
    fileName.textContent = file.name;
    fileSize.textContent = `${Math.max(1, Math.round(file.size / 1024))} KB`;
    filePrompt.hidden = true;
    fileSummary.hidden = false;
    validateButton.disabled = false;
}
function removeFile() {
    selectedFile = null;
    csvInput.value = "";
    filePrompt.hidden = false;
    fileSummary.hidden = true;
    validateButton.disabled = true;
    uploadError.hidden = true;
}
function showStep(number) {
    document.querySelectorAll("[data-step]").forEach((step) => {
        step.hidden = Number(step.dataset.step) !== number;
    });
    document.querySelectorAll("[data-indicator]").forEach((indicator) => {
        const value = Number(indicator.dataset.indicator);
        indicator.classList.toggle("active", value === number);
        indicator.classList.toggle("done", value < number);
    });
    window.scrollTo({ top: 0, behavior: "smooth" });
}
function parseCsvLine(line) {
    const values = [];
    let value = "";
    let quoted = false;
    for (let index = 0; index < line.length; index += 1) {
        const character = line[index];
        if (character === '"' && line[index + 1] === '"' && quoted) {
            value += '"';
            index += 1;
        } else if (character === '"') quoted = !quoted;
        else if (character === "," && !quoted) {
            values.push(value.trim());
            value = "";
        } else value += character;
    }
    values.push(value.trim());
    return values;
}
function escapeHtml(value) {
    const element = document.createElement("span");
    element.textContent = value;
    return element.innerHTML;
}
function validateRows(importRows) {
    const seenEmails = new Set();
    const seenIds = new Set();
    return importRows.map((row) => {
        const email = row.email.trim().toLowerCase();
        const universityId = row.university_id.trim().toLowerCase();
        const missingFields = requiredColumns.filter((field) => !row[field]?.trim());
        let result = "accepted";
        let reason = "All required fields are valid.";
        if (missingFields.length) {
            result = "rejected";
            reason = `Missing: ${missingFields.join(", ")}.`;
        } else if (!/^[^\s@]+@miuegypt\.edu\.eg$/i.test(email)) {
            result = "rejected";
            reason = "Email must end with @miuegypt.edu.eg.";
        } else if (existingEmails.has(email)) {
            result = "duplicate";
            reason = "An account with this email already exists.";
        } else if (seenEmails.has(email)) {
            result = "duplicate";
            reason = "Duplicate email appears earlier in this CSV.";
        } else if (seenIds.has(universityId)) {
            result = "duplicate";
            reason = "Duplicate university ID appears earlier in this CSV.";
        }
        seenEmails.add(email);
        seenIds.add(universityId);
        return { ...row, result, reason };
    });
}
function updateValidationTotals() {
    validationCounts = rows.reduce(
        (counts, row) => {
            counts[row.result] += 1;
            return counts;
        },
        { accepted: 0, duplicate: 0, rejected: 0 },
    );
    document.querySelector("#totalRows").textContent = rows.length;
    document.querySelector("#acceptedRows").textContent = validationCounts.accepted;
    document.querySelector("#duplicateRows").textContent = validationCounts.duplicate;
    document.querySelector("#rejectedRows").textContent = validationCounts.rejected;
}

csvInput.addEventListener("change", () => acceptFile(csvInput.files[0]));
removeFileButton.addEventListener("click", removeFile);
["dragenter", "dragover"].forEach((eventName) =>
    dropzone.addEventListener(eventName, (event) => {
        event.preventDefault();
        dropzone.classList.add("drag");
    }),
);
["dragleave", "drop"].forEach((eventName) =>
    dropzone.addEventListener(eventName, (event) => {
        event.preventDefault();
        dropzone.classList.remove("drag");
    }),
);
dropzone.addEventListener("drop", (event) =>
    acceptFile(event.dataTransfer.files[0]),
);

validateButton.addEventListener("click", () => {
    if (!selectedFile) {
        showError("Choose a CSV file first.");
        return;
    }
    const reader = new FileReader();
    reader.addEventListener("load", () => {
        const lines = String(reader.result).trim().split(/\r?\n/);
        const headers = parseCsvLine(lines.shift()).map((header) =>
            header.toLowerCase(),
        );
        const missing = requiredColumns.filter(
            (column) => !headers.includes(column),
        );
        if (missing.length) {
            showError(`Missing required columns: ${missing.join(", ")}.`);
            return;
        }
        const parsedRows = lines.filter(Boolean).map((line) => {
            const values = parseCsvLine(line);
            return Object.fromEntries(
                headers.map((header, index) => [header, values[index] || ""]),
            );
        });
        if (!parsedRows.length) {
            showError("The CSV contains no user rows.");
            return;
        }
        rows = validateRows(parsedRows);
        updateValidationTotals();
        previewBody.innerHTML = rows
            .map((row, index) => {
                const labels = { accepted: "Ready", duplicate: "Duplicate", rejected: "Invalid" };
                const tones = { accepted: "success", duplicate: "warning", rejected: "danger" };
                return `<tr class="row-${row.result}"><td>${index + 2}</td><td>${escapeHtml(`${row.first_name} ${row.last_name}`.trim() || "—")}</td><td>${escapeHtml(row.email || "—")}</td><td><span class="badge ${tones[row.result]}">${labels[row.result]}</span></td><td>${escapeHtml(row.reason)}</td></tr>`;
            })
            .join("");
        validationSummary.textContent = `${validationCounts.accepted} accepted, ${validationCounts.duplicate} duplicate, and ${validationCounts.rejected} rejected. Only accepted rows will be confirmed.`;
        confirmButton.disabled = validationCounts.accepted === 0;
        document.querySelector("#noValidRowsState").hidden = validationCounts.accepted !== 0;
        showStep(2);
    });
    reader.readAsText(selectedFile);
});

backButton.addEventListener("click", () => showStep(1));
confirmButton.addEventListener("click", () => {
    completeCopy.textContent = `${validationCounts.accepted} valid account${validationCounts.accepted === 1 ? " is" : "s are"} ready. Duplicate and invalid rows were skipped.`;
    document.querySelector("#createdCount").textContent = validationCounts.accepted;
    document.querySelector("#skippedCount").textContent = validationCounts.duplicate;
    document.querySelector("#failedCount").textContent = validationCounts.rejected;
    showStep(3);
});
importAgainButton.addEventListener("click", () => {
    removeFile();
    rows = [];
    validationCounts = { accepted: 0, duplicate: 0, rejected: 0 };
    previewBody.innerHTML = "";
    showStep(1);
});
document.querySelector("#returnToUpload").addEventListener("click", () => showStep(1));
if (new URLSearchParams(location.search).get("state") === "error") {
    document.querySelectorAll("[data-step]").forEach((step) => { step.hidden = true; });
    document.querySelector("#importErrorState").hidden = false;
}
