# Team Extensions

Route: `/professor/extensions`, named `professor.extensions.index`. Uses the shared page layout, CSS tokens, buttons, badges, and project icon component.

This frontend prototype includes sample milestone and team selection, an explicit scope for one whole team or all teams in the project, current deadline metadata, team member summary, date and time validation, +1/+3/+7 day presets, reason templates, email reference fields, local evidence-file validation, notification preferences, an impact preview, timeline, manually logged requests, searchable/filterable extension history, a detail dialog, and CSV export. Team, milestone, and scope choices update the preview. The project-wide scope displays all eight sample teams and 24 students.

Confirmation adds an illustrative active item only to sessionStorage. Revoking and declining also change the current browser session's sample data. No deadline, calendar, reminders, flags, audit database, uploaded file, email, or notification is changed. CSV export contains sample history. Backend integration needs authorization scoped to the professor's course, server-side deadline and extension-limit validation, database transactions preserving the original deadline and audit events, evidence upload/storage, and notification delivery with a clear post-save status. Make sure the actual policies about deadline changes, team submissions, TA notification, expiry and revocation are agreed before enabling those services.

The fixture dates show an example snapshot around October 15, 2026 to match the supplied design. Replace them with live course deadlines and the current clock during backend integration.
