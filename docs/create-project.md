# Create Project

Open `/professor/projects/create` or select **Create academic project** on Courses.

The Blade view uses the existing professor layout and design tokens. The shared professor navigation is still pending in that layout. Feature styles and JavaScript are scoped to the project wizard. No React dependency is required.

All six steps retain entered values while navigating. Goals, milestones, and rubric criteria can be added or removed. Required fields, milestone weights totaling 100%, rubric points matching the grade (unless no criteria), and minimum/maximum team sizes are validated before review. The summary reflects current form values.

This is a frontend implementation: there is no database write, authentication, PDF analysis, CSV matching, invitation delivery, or publishing endpoint. The roster and Sara Ahmed invitation are explicitly sample data from the supplied design. File selections remain local. Publish opens a preview dialog and allows downloading draft fields as JSON. Reloading the page resets the form; download before leaving to preserve the draft.

Backend integration should replace the sample course/semester/TA/roster data with controller data, provide a FormRequest with server validation and authorization, and persist through a CSRF-protected POST route. Uploaded files and checked/unchecked policy values need explicit handling. Browser validation is only a usability aid. No submission POST exists in this phase.

Run `npm run dev` for live assets or `npm run build` for compiled assets. If Vite has stopped but `public/hot` remains, Laravel may reference stale development assets; restart Vite or remove the stale marker to use the build.
