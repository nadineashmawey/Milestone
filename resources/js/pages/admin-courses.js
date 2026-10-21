const tabs = document.querySelectorAll("[data-course-tab]");
tabs.forEach(
    (tab) =>
        (tab.onclick = () => {
            tabs.forEach((x) => x.classList.toggle("active", x === tab));
            semestersPanel.hidden = tab.dataset.courseTab !== "semesters";
            coursesPanel.hidden = tab.dataset.courseTab !== "courses";
        }),
);
const drawer = document.querySelector("#semesterDrawer"),
    overlay = document.querySelector("#semesterOverlay");
function openDrawer(title = "Add semester") {
    semesterDrawerTitle.textContent = title;
    drawer.classList.add("open");
    drawer.setAttribute("aria-hidden", "false");
    overlay.hidden = false;
}
function closeDrawer() {
    drawer.classList.remove("open");
    drawer.setAttribute("aria-hidden", "true");
    overlay.hidden = true;
    semesterForm.reset();
    document.querySelectorAll(".error").forEach((x) => (x.hidden = true));
}
addSemester.onclick = () => openDrawer();
function bindSemesterButton(button) {
    button.onclick = () => {
        openDrawer(`Manage ${button.dataset.manage}`);
        semesterName.value = button.dataset.manage;
    };
}
document.querySelectorAll("[data-manage]").forEach(bindSemesterButton);
closeSemester.onclick = cancelSemester.onclick = overlay.onclick = closeDrawer;
semesterForm.onsubmit = (e) => {
    e.preventDefault();
    const values = [
        [semesterName, semesterNameError, "Enter a semester name."],
        [semesterStart, semesterStartError, "Choose a start date."],
        [semesterEnd, semesterEndError, "Choose an end date."],
    ];
    let valid = true;
    values.forEach(([field, error, message]) => {
        const bad = !field.value;
        error.textContent = message;
        error.hidden = !bad;
        field.classList.toggle("invalid", bad);
        valid = valid && !bad;
    });
    if (valid && new Date(semesterEnd.value) <= new Date(semesterStart.value)) {
        semesterEndError.textContent = "End date must be after the start date.";
        semesterEndError.hidden = false;
        valid = false;
    }
    if (valid) {
        const addingSemester = semesterDrawerTitle.textContent === "Add semester";
        const name = semesterName.value.trim();
        const start = new Date(`${semesterStart.value}T00:00:00`);
        const end = new Date(`${semesterEnd.value}T00:00:00`);
        if (addingSemester) {
            const formatDate = (date) => date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
            const card = document.createElement("article");
            card.className = "card semester-card";
            card.innerHTML = `<span class="badge ${semesterActive.checked ? "success" : "review"}">${semesterActive.checked ? "Active" : "Scheduled"}</span><h2></h2><p>${formatDate(start)}–${formatDate(end)}</p><footer><div><strong>0 courses</strong><small>0 students</small></div><button data-manage="">Manage</button></footer>`;
            card.querySelector("h2").textContent = name;
            const manageButton = card.querySelector("[data-manage]");
            manageButton.dataset.manage = name;
            bindSemesterButton(manageButton);
            semestersPanel.prepend(card);
            document.querySelector("#semestersEmptyState").hidden = true;
            semestersPanel.hidden = false;
        }
        closeDrawer();
        document.querySelector("#semesterToast p").textContent = addingSemester
            ? `${name} was added successfully.`
            : `${name} was updated successfully.`;
        semesterToast.hidden = false;
        setTimeout(() => (semesterToast.hidden = true), 3000);
    }
};
document.querySelector("#emptyAddSemester").addEventListener("click", () => openDrawer());

const courseDrawer = document.querySelector("#courseDrawer");
const courseOverlay = document.querySelector("#courseOverlay");
const courseForm = document.querySelector("#courseForm");
let activeCourseButton = null;
function closeCourseDrawer() {
    courseDrawer.classList.remove("open");
    courseDrawer.setAttribute("aria-hidden", "true");
    courseOverlay.hidden = true;
}
function openCourseDrawer(button) {
    activeCourseButton = button;
    document.querySelector("#courseCode").readOnly = true;
    document.querySelector("#courseDrawerTitle").textContent =
        `Manage ${button.dataset.code}`;
    document.querySelector("#courseCode").value = button.dataset.code;
    document.querySelector("#courseName").value = button.dataset.name;
    document.querySelector("#courseProfessor").value = button.dataset.professor;
    document.querySelector("#courseStudents").value = button.dataset.students;
    document.querySelector("#courseProjects").value = button.dataset.projects;
    courseDrawer.classList.add("open");
    courseDrawer.setAttribute("aria-hidden", "false");
    courseOverlay.hidden = false;
}
function bindCourseButton(button) {
    button.addEventListener("click", () => openCourseDrawer(button));
}
document.querySelectorAll("[data-manage-course]").forEach(bindCourseButton);

tabs.forEach((tab) =>
    tab.addEventListener("click", () => {
        document.querySelector("#addSemester").textContent =
            tab.dataset.courseTab === "courses"
                ? "+ Add course"
                : "+ Add semester";
    }),
);
document.querySelector("#addSemester").onclick = () => {
    const coursesActive = [...tabs].some(
        (tab) =>
            tab.dataset.courseTab === "courses" &&
            tab.classList.contains("active"),
    );
    if (!coursesActive) {
        openDrawer();
        return;
    }
    activeCourseButton = null;
    courseForm.reset();
    document.querySelector("#courseDrawerTitle").textContent = "Add course";
    document.querySelector("#courseCode").readOnly = false;
    courseDrawer.classList.add("open");
    courseDrawer.setAttribute("aria-hidden", "false");
    courseOverlay.hidden = false;
};
document
    .querySelector("#closeCourse")
    .addEventListener("click", closeCourseDrawer);
document
    .querySelector("#cancelCourse")
    .addEventListener("click", closeCourseDrawer);
courseOverlay.addEventListener("click", closeCourseDrawer);
courseForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const name = document.querySelector("#courseName");
    const professor = document.querySelector("#courseProfessor");
    const nameError = document.querySelector("#courseNameError");
    const professorError = document.querySelector("#courseProfessorError");
    nameError.textContent = "Enter a course name.";
    professorError.textContent = "Enter the professor name.";
    nameError.hidden = Boolean(name.value.trim());
    professorError.hidden = Boolean(professor.value.trim());
    const code = document.querySelector("#courseCode");
    if (!code.value.trim() || !name.value.trim() || !professor.value.trim())
        return;
    const students = document.querySelector("#courseStudents").value || "0";
    const projects = document.querySelector("#courseProjects").value || "0";
    if (!activeCourseButton) {
        const row = document.createElement("tr");
        row.innerHTML = `<td><strong>${code.value.trim()}</strong><br><small>${name.value.trim()}</small></td><td>${professor.value.trim()}</td><td>${students}</td><td>${projects}</td><td><button type="button" data-manage-course data-code="${code.value.trim()}" data-name="${name.value.trim()}" data-professor="${professor.value.trim()}" data-students="${students}" data-projects="${projects}">Manage</button></td>`;
        document.querySelector("#coursesPanel tbody").append(row);
        activeCourseButton = row.querySelector("[data-manage-course]");
        bindCourseButton(activeCourseButton);
    }
    const row = activeCourseButton.closest("tr");
    const cells = row.querySelectorAll("td");
    cells[0].querySelector("small").textContent = name.value.trim();
    cells[1].textContent = professor.value.trim();
    cells[2].textContent = students;
    cells[3].textContent = projects;
    Object.assign(activeCourseButton.dataset, {
        name: name.value.trim(),
        professor: professor.value.trim(),
        students: cells[2].textContent,
        projects: cells[3].textContent,
    });
    closeCourseDrawer();
    document.querySelector("#semesterToast p").textContent =
        `${activeCourseButton.dataset.code} updated successfully.`;
    document.querySelector("#semesterToast").hidden = false;
    setTimeout(() => {
        document.querySelector("#semesterToast").hidden = true;
    }, 3000);
});

const academicState = new URLSearchParams(location.search).get("state");
if (academicState === "empty-semesters") {
    semestersPanel.hidden = true;
    coursesPanel.hidden = true;
    document.querySelector("#semestersEmptyState").hidden = false;
} else if (academicState === "empty-courses") {
    tabs.forEach((tab) => tab.classList.toggle("active", tab.dataset.courseTab === "courses"));
    semestersPanel.hidden = true;
    coursesPanel.hidden = false;
    document.querySelector("#coursesTable").hidden = true;
    document.querySelector("#coursesEmptyState").hidden = false;
    document.querySelector("#addSemester").textContent = "+ Add course";
} else if (academicState === "error") {
    semestersPanel.hidden = true;
    coursesPanel.hidden = true;
    document.querySelector("#academicErrorState").hidden = false;
}
