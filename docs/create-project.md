# Create Project

Open `/professor/projects/create` (navbar **Create project**) or select **Create academic project** on Courses. Only professors can open it; anyone else gets the access-denied page.

The Blade view uses the professor layout and design tokens. Feature styles and JavaScript are scoped to the project wizard. No React dependency is required.

All six steps retain entered values while navigating. Goals, milestones, and rubric criteria can be added or removed. The browser checks required fields, milestone weights totaling 100%, rubric points matching the grade (unless no criteria), and minimum/maximum team sizes before review. The summary reflects current form values.

## Publishing (UC-02)

The wizard posts to `POST /professor/projects` (`professor.projects.store`, `App\Http\Controllers\Professor\ProjectController@store`) through `App\Http\Requests\StoreProjectRequest`. The server checks everything again:

- title, course (one of yours), description and total grade (more than 0) are required;
- team minimum at least 1, maximum at least the minimum;
- at least one milestone, each with a deliverable type; due dates in order, inside the course's semester and after the team-choosing deadline;
- weights total 100%, and rubric points equal the grade when there is a rubric.

On an error the wizard comes back on the step with the first problem, with every field (milestones included) kept and the rejected fields marked. On success the project, its milestones and rubric are saved in one transaction as `active` with `published_at`, an audit entry `project.published` is written, and you land on the project page with "Project published". Student invitations are only logged for now (TODO for Raghad's notifications).

- **Publish project** asks for confirmation, then shows a loading state. Each page load carries a `submission_key`, so a double-click or a resend opens the project the first click created instead of making a second one.
- **Save as draft** saves with status `draft` and only needs a title. Drafts show **Edit and publish** on My projects and the project page (`/professor/projects/{project}/edit`).
- **Cancel** goes back to My projects and saves nothing.

The wizard's single "Deliverable" choice per milestone is stored as deliverable types: PDF report and Written answer → file, PowerPoint → slides, GitHub link → github, Demo video link → link, Mixed → file, slides, images and link.

Still sample data: the roster CSV, the Sara Ahmed TA invitation and the PDF brief. File selections stay in the browser and aren't uploaded.

Run `npm run dev` for live assets or `npm run build` for compiled assets. If Vite has stopped but `public/hot` remains, Laravel may reference stale development assets; restart Vite or remove the stale marker to use the build.
