# Submissions / Reviews

Route: `/professor/submissions`, named `professor.submissions.index`.

Uses the shared professor layout, button/badge components, design tokens, and page-scoped CSS/JavaScript. Controller fixtures isolate sample data for replacement with authorized backend records.

Working frontend interactions: queue filters with empty state, team-specific data, available versions and filenames, weighted rubric grade (10/15/15/10 points), TA feedback copying, comment bank, optional individual adjustments, per-team draft state, and confirmation dialogs. Team grade remains separate from individual adjustments. Save draft uses sessionStorage in the current browser tab and reports storage failures. Switching document versions does not replace the latest-submission grading draft.

Sample document illustration, attachments, GitHub activity, rosters, TA feedback, and timestamps are placeholders. No real files were supplied. File controls explain the missing integration. Approval and resubmission are explicitly preview actions; they do not lock grades, send notifications, or persist to a server. Authentication, authorization, final-grade locking, actual upload URLs, version-specific evidence, server validation, and backend storage remain pending.

See `page-checklist.md` for remaining pages and shared navbar work.
