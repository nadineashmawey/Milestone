@extends('layouts.base')


@section('title', $project['title'])

@push('styles')
    @vite('resources/css/student-projects.css')
@endpush


@push('scripts')
    @vite('resources/js/student-project-board.js')
    @vite('resources/js/student-project-portal.js')
@endpush


@section('body')
    <main class="detail-page" data-project-board>
        <div class="detail-container">
            <a class="back-link" href="{{ route('student.projects') }}"><span aria-hidden="true">←</span> My Projects</a>
            <header class="detail-heading">
                <div>
                    <p class="projects-eyebrow">{{ $project['course'] }}@if($project['team']) · {{ $project['team'] }}@endif</p>
                    <h1>{{ $project['title'] }}</h1>
                    <p class="projects-intro">{{ $project['due'] }}</p>
                </div>
            </header>


            <nav class="project-tabs" aria-label="Project sections" role="tablist">
                <button class="project-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="board-panel" data-tab="board">Board</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="formation-panel" data-tab="formation">Formation</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="submit-panel" data-tab="submit">Submit</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="grades-panel" data-tab="grades">Grades</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="chat-panel" data-tab="chat">Chat</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="files-panel" data-tab="files">Files</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="calendar-panel" data-tab="calendar">Calendar</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="risk-panel" data-tab="risk">Risk</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="activity-panel" data-tab="activity">Activity</button>
            </nav>


            <section class="detail-panel" id="board-panel" role="tabpanel" data-panel="board">
                <div class="corkboard" data-corkboard aria-label="Project kanban board">
                    <div class="corkboard__summary">
                        <div class="corkboard__summary-info">
                            <p class="corkboard__eyebrow">Project board</p>
                            <h2><span data-complete-count>0</span> of <span data-total-count>0</span> tasks complete</h2>
                            <div class="corkboard__progress"><span data-summary-progress></span></div>
                            <p class="corkboard__meta"><span data-summary-percent>0</span>% complete · 0 unassigned · <span data-blocked-count>0</span> blocked</p>
                        </div>
                        <div class="corkboard__summary-actions">
                            <label for="board-view">Prototype permission view</label>
                            <select id="board-view" class="ms-input"><option>Aya · Team Leader</option><option>Malek · Team Member</option></select>
                            <span class="corkboard__summary-hint">Task creation permissions</span>
                        </div>
                    </div>
                    <div class="corkboard__frame">
                        <div class="corkboard__surface">
                            <span class="corkboard__hero-pin" aria-hidden="true"></span>
                            <div class="corkboard__columns" data-board-columns aria-label="Task columns"></div>
                        </div>
                    </div>
                    <p class="corkboard__live-status" data-board-status aria-live="polite"></p>
                </div>
            </section>


            <section class="detail-panel portal-panel" id="formation-panel" role="tabpanel" data-panel="formation" hidden>
                <header class="portal-heading"><div><p class="projects-eyebrow">Team workspace</p><h2>Team formation</h2><p>Formation closed October 4. Changes now require a professor override.</p></div><span class="portal-badge portal-badge--lavender">🔒 Locked</span></header>
                <div class="portal-grid portal-grid--formation"><article class="portal-card"><h3>Team 1</h3><div class="member-list" data-members></div><button class="ms-button portal-button--coral" type="button" data-leave-request>Request to leave team</button></article><article class="portal-card"><h3>Formation rules</h3><ul class="portal-rules"><li>Team size: <strong>2 minimum · 4 maximum</strong></li><li>Pending join requests allowed: <strong>up to 5</strong></li><li>Students cannot leave a team independently.</li></ul><p class="portal-note">Membership changes are locked. A professor must approve any team changes.</p></article></div>
            </section>
            <section class="detail-panel portal-panel" id="submit-panel" role="tabpanel" data-panel="submit" hidden>
                <header class="portal-heading"><div><p class="projects-eyebrow">Milestone 2</p><h2>Submit M2 Design Document</h2><p>Aya is the team leader. Submissions can be edited until October 18 at 5:00 PM.</p></div><span class="portal-badge portal-badge--blue" data-submit-status>Draft</span></header>
                <div class="portal-card portal-submit-card"><div class="portal-upload" data-upload-zone><input type="file" data-submit-files multiple accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.md,.png,.jpg,.jpeg,.gif,.svg,.zip,.rar" hidden><span class="portal-upload-icon" aria-hidden="true">⇧</span><strong>Upload official milestone files</strong><span>Presentations, images, documents, ZIP files... max 25 MB each</span><button class="ms-button ms-button--secondary" type="button" data-choose-submit-files>Choose files</button></div><p class="portal-inline-status" data-submit-message aria-live="polite">Files and drafts are stored locally in this prototype; nothing is uploaded to a server.</p><ul class="portal-file-list" data-submit-file-list></ul>
                    <form class="portal-form" data-submission-form novalidate><label>GitHub or supporting link<input class="ms-input" type="url" name="link" placeholder="https://github.com/team-alpha/project"></label><label>Submission note<textarea class="ms-input" name="note" rows="4" placeholder="What changed since your last checkpoint?"></textarea></label><div class="portal-draft"><div><span class="portal-badge portal-badge--lavender">Current draft</span><strong>design-document-v3.pdf</strong><small>Edited today at 10:42 AM</small></div><p>Late submissions may require academic approval. Please confirm the deadline and requirements with your supervisor.</p></div><div class="portal-actions"><button class="ms-button ms-button--secondary" type="button" data-save-draft>Save draft</button><button class="ms-button" type="submit">Submit milestone</button></div><p class="portal-inline-status" data-draft-status aria-live="polite"></p></form>
                </div>
            </section>
            <section class="detail-panel portal-panel" id="grades-panel" role="tabpanel" data-panel="grades" hidden><header class="portal-heading"><div><p class="projects-eyebrow">Assessment overview</p><h2>Capstone grades & feedback</h2><p>Illustrative sample data for this frontend prototype. Grades shown here are not official.</p></div><span class="portal-badge portal-badge--lavender">Sample data</span></header><div class="portal-grade-summary portal-card"><span>Overall sample grade</span><strong>88%</strong><small>Published assessments only · M2 assessment pending</small></div><div class="portal-grid portal-grid--grades" data-grade-cards></div></section>
            <section class="detail-panel portal-panel" id="chat-panel" role="tabpanel" data-panel="chat" hidden><header class="portal-heading"><div><p class="projects-eyebrow">Team workspace</p><h2>Project chat</h2><p>Visible to Team 1 and authorized academic supervisors · No private messages</p></div><span class="portal-badge portal-badge--green">● 3 members online · sample</span></header><div class="portal-card portal-chat"><div class="portal-chat-feed" data-chat-feed aria-live="polite"></div><form class="portal-chat-form" data-chat-form><textarea class="ms-input" name="message" rows="1" placeholder="Write to the project team..." aria-label="Write to the project team"></textarea><button class="ms-button" type="submit">Send</button></form><p class="portal-note">Chat history is saved in this browser for this prototype.</p></div></section>
            <section class="detail-panel portal-panel" id="files-panel" role="tabpanel" data-panel="files" hidden><header class="portal-heading"><div><p class="projects-eyebrow">Team repository</p><h2>Shared files</h2><p>25 MB per file · Every version is retained</p></div><label class="ms-button portal-upload-button">＋ Upload file<input type="file" data-shared-files multiple hidden></label></header><div class="portal-card"><p class="portal-note">File records are local prototype data. Files are not uploaded to a server; downloads work for sample entries and files selected in this session.</p><div class="portal-upload portal-upload--compact" data-shared-drop><strong>Drop files here to add them locally</strong><span>Max 25 MB each · Nothing is uploaded to a server</span></div><div class="portal-file-tools"><input class="ms-input" type="search" placeholder="Search files..." data-file-search aria-label="Search files"><select class="ms-input" data-file-filter aria-label="Filter file type"><option value="all">All file types</option><option value="document">Documents</option><option value="image">Images</option><option value="archive">Archives</option></select></div><div class="portal-shared-files" data-shared-file-list></div></div></section>
            <section class="detail-panel portal-panel" id="calendar-panel" role="tabpanel" data-panel="calendar" hidden><header class="portal-heading"><div><p class="projects-eyebrow">Team schedule</p><h2 data-calendar-title>October 2026</h2><p>Internal project calendar · Tasks and academic milestones</p></div><span class="portal-badge portal-badge--lavender">External sync planned later</span></header><div class="portal-card"><div class="portal-calendar-controls"><button class="ms-button ms-button--secondary" type="button" data-calendar-prev aria-label="Previous period">←</button><button class="ms-button ms-button--secondary" type="button" data-calendar-next aria-label="Next period">→</button><div class="portal-segmented"><button type="button" class="is-active" data-calendar-view="timeline">Timeline</button><button type="button" data-calendar-view="list">List</button></div></div><div data-calendar></div><p class="portal-calendar-summary" data-calendar-summary></p></div></section>
            <section class="detail-panel portal-panel" id="risk-panel" role="tabpanel" data-panel="risk" hidden><header class="portal-heading"><div><p class="projects-eyebrow">Delivery oversight</p><h2>Risk register</h2><p>Track blockers and potential delivery risks while there is time to respond.</p></div><button class="ms-button" type="button" data-add-risk>＋ Add risk</button></header><div class="portal-risk-stats" data-risk-stats></div><div class="portal-card"><div class="portal-file-tools"><input class="ms-input" type="search" placeholder="Search risks..." data-risk-search aria-label="Search risks"><select class="ms-input" data-risk-filter aria-label="Filter risk status"><option value="all">All statuses</option><option>Open</option><option>Mitigating</option><option>Resolved</option></select></div><div data-risk-list></div></div></section>
            <section class="detail-panel portal-panel" id="activity-panel" role="tabpanel" data-panel="activity" hidden><header class="portal-heading"><div><p class="projects-eyebrow">Project history</p><h2>Recent activity</h2><p>Changes across tasks, files, submissions, and risks.</p></div><select class="ms-input" data-activity-filter aria-label="Filter activity"><option value="all">All activity</option><option>Tasks</option><option>Files</option><option>Submissions</option><option>Risks</option></select></header><div class="portal-card"><div class="portal-activity" data-activity></div></div></section>
        </div>
        <dialog class="portal-dialog" data-portal-dialog aria-labelledby="dialog-title"><form method="dialog"><button class="portal-dialog-close" value="cancel" aria-label="Close dialog">×</button></form><div data-dialog-content></div></dialog>
    </main>
@endsection
