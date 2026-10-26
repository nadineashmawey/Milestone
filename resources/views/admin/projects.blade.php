@extends('layouts.admin')

@section('title', 'Project Administration')
@section('page-class', 'page projects')

@push('styles')
    <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'pages/admin-projects.css']) }}">
@endpush

@section('content')
                <section id="projectList">
<x-admin.page-header eyebrow="Admin · Historical records" title="Project administration" description="Transfer academic ownership, archive records, and apply approved retention rules.">
                        <button class="btn" id="openSupport">
                            ♧ Support access
                        </button>
</x-admin.page-header>
                    <section class="card project-card" id="projectRecordsCard">
                        <div class="project-toolbar">
                            <div class="project-filters">
                                <div class="select" data-project-select="mode">
                                    <button
                                        class="select-trigger"
                                        type="button"
                                    >
                                        <span>All project modes</span>⌄
                                    </button>
                                    <div class="select-menu" hidden>
                                        <button data-value="all">
                                            All project modes</button
                                        ><button data-value="academic">
                                            Academic</button
                                        ><button data-value="independent">
                                            Independent
                                        </button>
                                    </div>
                                </div>
                                <div class="select" data-project-select="state">
                                    <button
                                        class="select-trigger"
                                        type="button"
                                    >
                                        <span>All record states</span>⌄
                                    </button>
                                    <div class="select-menu" hidden>
                                        <button data-value="all">
                                            All record states</button
                                        ><button data-value="active">
                                            Active</button
                                        ><button data-value="draft">
                                            Draft</button
                                        ><button data-value="completed">
                                            Completed</button
                                        ><button data-value="transferneeded">
                                            Transfer needed</button
                                        ><button data-value="readonly">
                                            Read only</button
                                        ><button data-value="archived">
                                            Archived
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p>
                                Academic: retain 1 year · Independent: retained
                                indefinitely
                            </p>
                        </div>
                        <div class="table-wrap">
                            <table class="project-table">
                                <thead>
                                    <tr>
                                        <th>Project</th>
                                        <th>Mode</th>
                                        <th>Owner</th>
                                        <th>Record state</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="projectRows"></tbody>
                            </table>
                            <div class="project-empty" id="projectEmpty" hidden>
                                No projects match these filters.
                            </div>
                        </div>
                    </section>
                    <section
                        class="card page-state"
                        id="projectsEmptyState"
                        hidden
                    >
                        <span class="state-icon">0</span>
                        <h2>No project records</h2>
                        <p>
                            Project records will appear here after projects are
                            created.
                        </p>
                    </section>
                    <section
                        class="card page-state error-state"
                        id="projectsErrorState"
                        hidden
                    >
                        <span class="state-icon">!</span>
                        <h2>Project records could not be loaded</h2>
                        <p>
                            Something went wrong while loading retained project
                            records.
                        </p>
                        <button
                            class="btn"
                            type="button"
                            onclick="location.href = '/admin/projects'"
                        >
                            Retry
                        </button>
                    </section>
                </section>
                <section id="supportScreen" hidden>
                    <button type="button" class="back" id="backProjects">
                        ← Back to project administration
                    </button>
                    <header class="support-heading">
                        <p class="eyebrow">Admin · Temporary access</p>
                        <h1>Support access</h1>
                        <p>
                            Open a logged, read-only project session for
                            approved administrative support.
                        </p>
                    </header>
                    <div class="support-layout">
                        <form
                            class="card support-form"
                            id="supportForm"
                            novalidate
                        >
                            <section>
                                <header class="step-heading">
                                    <b>1</b>
                                    <div>
                                        <h2>Find a project</h2>
                                        <p>
                                            Search by project name, course, or
                                            owner.
                                        </p>
                                    </div>
                                </header>
                                <label class="field"
                                    ><span>Search projects</span
                                    ><input
                                        class="control"
                                        id="projectSearch"
                                        placeholder="Project, course, or owner"
                                /></label>
                                <div
                                    class="project-picker"
                                    id="projectPicker"
                                ></div>
                                <small
                                    class="error"
                                    id="supportProjectError"
                                    hidden
                                ></small>
                            </section>
                            <section>
                                <header class="step-heading">
                                    <b>2</b>
                                    <div>
                                        <h2>Support reason</h2>
                                        <p>
                                            Explain why temporary administrator
                                            access is required.
                                        </p>
                                    </div>
                                </header>
                                <label class="field"
                                    ><span>Reason</span
                                    ><select class="control" id="supportReason">
                                        <option value="">
                                            Choose a reason
                                        </option>
                                        <option>Resolve account issue</option>
                                        <option>
                                            Investigate reported content
                                        </option>
                                        <option>Recover retained record</option>
                                        <option>
                                            Other approved support
                                        </option></select
                                    ><small
                                        class="error"
                                        id="supportReasonError"
                                        hidden
                                    ></small></label
                                ><label class="field"
                                    ><span>Details</span
                                    ><textarea
                                        class="control"
                                        id="supportDetails"
                                        rows="5"
                                        placeholder="Describe the support request in at least 10 characters."
                                    ></textarea
                                    ><small
                                        class="error"
                                        id="supportDetailsError"
                                        hidden
                                    ></small
                                ></label>
                                <aside class="support-warning">
                                    <strong>This access will be logged.</strong>
                                    <p>
                                        The session is read-only and ends
                                        automatically after 30 minutes.
                                    </p>
                                </aside>
                            </section>
                            <footer>
                                <span></span
                                ><button class="btn" type="submit">
                                    Start support session
                                </button>
                            </footer>
                        </form>
                        <aside class="card support-history">
                            <h2>Recent support sessions</h2>
                            <p>Temporary administrator access history</p>
                            <div class="support-empty" id="supportEmpty">
                                <span>□</span
                                ><strong>No support sessions yet</strong>
                                <p>Logged support access will appear here.</p>
                            </div>
                            <div id="supportSessions" hidden></div>
                        </aside>
                    </div>
                </section>
@endsection

@push('overlays')
            <div class="project-overlay" id="actionOverlay" hidden></div>
            <section
                class="action-dialog"
                id="actionDialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="actionDialogTitle"
                hidden
            >
                <span
                    class="action-icon"
                    id="actionDialogIcon"
                    aria-hidden="true"
                    >&#128451;</span
                >
                <p class="eyebrow">Admin · Project records</p>
                <h2 id="actionDialogTitle">Archive project?</h2>
                <p id="actionDialogMessage"></p>
                <aside class="action-notice" id="actionDialogNotice"></aside>
                <footer>
                    <button
                        class="btn secondary"
                        type="button"
                        id="cancelProjectAction"
                    >
                        Cancel
                    </button>
                    <button class="btn" type="button" id="confirmProjectAction">
                        Confirm archive
                    </button>
                </footer>
            </section>
            <div class="project-overlay" id="transferOverlay" hidden></div>
            <section
                class="transfer-dialog"
                id="transferDialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="transferDialogTitle"
                hidden
            >
                <p class="eyebrow">Admin · Ownership transfer</p>
                <h2 id="transferDialogTitle">Transfer project ownership</h2>
                <p class="dialog-copy">
                    Assign this academic project to another professor. No
                    project records will be changed.
                </p>
                <dl class="transfer-summary">
                    <div>
                        <dt>Project</dt>
                        <dd id="transferProjectName"></dd>
                    </div>
                    <div>
                        <dt>Current professor</dt>
                        <dd id="transferCurrentOwner"></dd>
                    </div>
                </dl>
                <div class="field">
                    <label for="transferProfessor">New professor</label>
                    <select class="control" id="transferProfessor">
                        <option value="">Choose a professor</option>
                        <option>Professor Omar Malik</option>
                        <option>Professor Nabil Farouk</option>
                        <option>Professor Mona Adel</option>
                    </select>
                    <small
                        class="error"
                        id="transferProfessorError"
                        hidden
                    ></small>
                </div>
                <div class="field">
                    <label for="transferReason">Transfer reason</label>
                    <select class="control" id="transferReason">
                        <option value="">Choose a reason</option>
                        <option>Professor departure</option>
                        <option>Teaching assignment changed</option>
                        <option>Administrative correction</option>
                        <option>Other approved reason</option>
                    </select>
                    <small
                        class="error"
                        id="transferReasonError"
                        hidden
                    ></small>
                </div>
                <aside class="transfer-notice">
                    This administrative change will be recorded in the audit
                    log.
                </aside>
                <footer>
                    <button
                        class="btn secondary"
                        type="button"
                        id="cancelTransfer"
                    >
                        Cancel
                    </button>
                    <button class="btn" type="button" id="confirmTransfer">
                        Confirm transfer
                    </button>
                </footer>
            </section>
            <div class="project-overlay" id="deleteOverlay" hidden></div>
            <section
                class="delete-dialog"
                id="deleteDialog"
                role="dialog"
                aria-modal="true"
                hidden
            >
                <span class="delete-icon">♜</span>
                <h2>Delete <span id="deleteProjectName"></span>?</h2>
                <p>
                    Deleting this project removes its tasks, files, submissions,
                    grades and team history. This can’t be undone. Archiving
                    keeps the history.
                </p>
                <aside>
                    ♧ This project has an active team. Consider archiving
                    instead.
                </aside>
                <label class="field"
                    ><span>Reason for deletion</span
                    ><select class="control" id="deleteReason">
                        <option value="">Select a reason</option>
                        <option>Duplicate project</option>
                        <option>Created in error</option>
                        <option>No longer required</option>
                        <option>Requested by course owner</option>
                        <option>Other</option></select
                    ><small
                        class="error"
                        id="deleteReasonError"
                        hidden
                    ></small></label
                ><label class="field"
                    ><span>Type anything to confirm deletion</span
                    ><input
                        class="control"
                        id="deleteConfirmation"
                        placeholder="Enter confirmation text" /><small
                        class="error"
                        id="deleteNameError"
                        hidden
                    ></small
                ></label>
                <footer>
                    <button
                        class="btn secondary"
                        type="button"
                        id="cancelDelete"
                    >
                        Cancel</button
                    ><button
                        class="btn delete-button"
                        type="button"
                        id="confirmDelete"
                        disabled
                    >
                        Delete project
                    </button>
                </footer>
            </section>
            <div class="toast" id="projectToast" hidden>
                <div>
                    <strong>Done</strong>
                    <p id="projectToastMessage"></p>
                </div>
                <button type="button" id="closeProjectToast">×</button>
            </div>
@endpush

@push('scripts')
    <script src="{{ route('admin.assets.js', ['path' => 'pages/admin-projects.js']) }}"></script>
@endpush
