@extends('layouts.admin')

@section('title', 'Users and Roles')
@section('page-class', 'page users')

@push('styles')
    <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'pages/admin-users.css']) }}">
@endpush

@section('content')
<x-admin.page-header eyebrow="Admin · Access control" title="Users and roles" description="One role per account. Historical records remain after deactivation.">
                    <div>
                        <button class="btn" id="addUser">+ Add user</button>
                        <a class="btn secondary" href="{{ route('admin.imports') }}"
                            >Import CSV</a
                        >
                    </div>
</x-admin.page-header>
                <nav class="tabs" role="tablist">
                    <button data-tab="all" aria-selected="true">
                        All users</button
                    ><button data-tab="pending" aria-selected="false">
                        Pending approval
                        <span id="pendingCount">1</span></button
                    ><button data-tab="requests" aria-selected="false">
                        Data requests <span>1</span>
                    </button>
                </nav>
                <section class="users-panel" data-panel="all">
                    <div class="toolbar" id="usersToolbar">
                        <div class="select" data-select>
                            <button
                                class="select-trigger"
                                aria-expanded="false"
                            >
                                <span>All roles</span
                                ><svg viewBox="0 0 24 24">
                                    <path d="m7 10 5 5 5-5" />
                                </svg>
                            </button>
                            <div class="select-menu" hidden>
                                <button data-value="all" aria-selected="true">
                                    All roles</button
                                ><button data-value="student">Student</button
                                ><button data-value="professor">
                                    Professor</button
                                ><button data-value="ta">TA</button>
                            </div>
                        </div>
                        <div class="bulk">
                            <span id="selectionCount">0 selected</span
                            ><button
                                class="btn secondary"
                                id="assignProfessor"
                                disabled
                            >
                                Assign professor role</button
                            ><button
                                class="btn secondary"
                                id="assignTa"
                                disabled
                            >
                                Assign TA role
                            </button>
                        </div>
                    </div>
                    <div class="table-wrap" id="usersTableWrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last active</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="usersBody"></tbody>
                        </table>
                    </div>
                    <div class="card page-state" id="usersEmptyState" hidden>
                        <span class="state-icon">0</span>
                        <h2>No users found</h2>
                        <p>
                            No users match the selected role. Choose another
                            role or add a user.
                        </p>
                        <button
                            class="btn secondary"
                            type="button"
                            id="clearUserFilter"
                        >
                            Show all users
                        </button>
                    </div>
                    <div
                        class="card page-state error-state"
                        id="usersErrorState"
                        hidden
                    >
                        <span class="state-icon">!</span>
                        <h2>Users could not be loaded</h2>
                        <p>
                            Something went wrong while loading university
                            accounts. Try again.
                        </p>
                        <button
                            class="btn"
                            type="button"
                            onclick="location.href = '/admin/users'"
                        >
                            Retry
                        </button>
                    </div>
                </section>
                <section class="users-panel" data-panel="pending" hidden>
                    <div class="toolbar">
                        <strong id="pendingSummary"
                            >1 account awaiting approval</strong
                        >
                    </div>
                    <div id="pendingTable">
                        <table>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="person">
                                            <span class="avatar">DL</span>
                                            <div>
                                                <strong>Dr. Lina Haddad</strong
                                                ><span
                                                    >lina.haddad@miuegypt.edu.eg</span
                                                >
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge info"
                                            >Professor</span
                                        >
                                    </td>
                                    <td>Oct 16, 2026</td>
                                    <td>
                                        <button
                                            type="button"
                                            class="row-action"
                                            id="reviewPending"
                                        >
                                            Review
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="empty" id="pendingEmpty" hidden>
                        No accounts are awaiting approval.
                    </div>
                </section>
                <section class="users-panel" data-panel="requests" hidden>
                    <div class="toolbar request-toolbar">
                        <div>
                            <strong>Data-change requests</strong>
                            <p>
                                Review corrections submitted by university
                                users.
                            </p>
                        </div>
                        <div class="field request-status-field">
                            <span>Status</span>
                            <div class="select" id="requestStatusSelect">
                                <button
                                    type="button"
                                    class="select-trigger"
                                    aria-expanded="false"
                                >
                                    <span>All statuses</span
                                    ><svg viewBox="0 0 24 24">
                                        <path d="m7 10 5 5 5-5" />
                                    </svg>
                                </button>
                                <div class="select-menu" hidden>
                                    <button
                                        type="button"
                                        data-request-status="all"
                                        aria-selected="true"
                                    >
                                        All statuses</button
                                    ><button
                                        type="button"
                                        data-request-status="open"
                                    >
                                        Open</button
                                    ><button
                                        type="button"
                                        data-request-status="approved"
                                    >
                                        Approved</button
                                    ><button
                                        type="button"
                                        data-request-status="rejected"
                                    >
                                        Rejected
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table class="request-table">
                            <thead>
                                <tr>
                                    <th>Requester</th>
                                    <th>Role</th>
                                    <th>Field</th>
                                    <th>Current value</th>
                                    <th>Requested value</th>
                                    <th>Submitted</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="requestsBody">
                                <tr data-request-row data-status="open">
                                    <td>
                                        <div class="person">
                                            <span class="avatar">AH</span>
                                            <div>
                                                <strong>Aya Hassan</strong
                                                ><span
                                                    >aya.hassan@miuegypt.edu.eg</span
                                                >
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge info">Student</span>
                                    </td>
                                    <td><strong>Student ID</strong></td>
                                    <td>20230012</td>
                                    <td><strong>20230021</strong></td>
                                    <td>Oct 12, 2026</td>
                                    <td>
                                        <span
                                            class="badge warning"
                                            data-request-badge
                                            >Open</span
                                        >
                                    </td>
                                    <td>
                                        <button
                                            type="button"
                                            class="row-action"
                                            id="reviewDataRequest"
                                        >
                                            Review
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="empty" id="requestsEmpty" hidden>
                            No data requests match this filter.
                        </div>
                    </div>
                </section>
@endsection

@push('overlays')
            <div class="overlay" id="overlay" hidden></div>
            <aside class="drawer" id="drawer" aria-hidden="true">
                <button class="drawer-close" id="closeDrawer">×</button>
                <p class="eyebrow">Admin · Account creation</p>
                <h2>Add user</h2>
                <form id="userForm" novalidate>
                    <div class="name-grid">
                        <div class="field">
                            <label for="first">First name</label
                            ><input
                                class="control"
                                id="first"
                                maxlength="50"
                                autocomplete="given-name"
                                required
                            />
                            <p class="error" data-error="first" hidden></p>
                        </div>
                        <div class="field">
                            <label for="last">Last name</label
                            ><input
                                class="control"
                                id="last"
                                maxlength="50"
                                autocomplete="family-name"
                                required
                            />
                            <p class="error" data-error="last" hidden></p>
                        </div>
                    </div>
                    <div class="field">
                        <label for="email">University email</label
                        ><input
                            class="control"
                            id="email"
                            type="email"
                            placeholder="name@miuegypt.edu.eg"
                            autocomplete="email"
                            required
                        />
                        <p class="error" data-error="email" hidden></p>
                    </div>
                    <div class="field">
                        <span>Role</span>
                        <div class="select" data-select>
                            <input
                                type="hidden"
                                id="role"
                                value="professor"
                            /><button type="button" class="select-trigger">
                                <span>Professor</span
                                ><svg viewBox="0 0 24 24">
                                    <path d="m7 10 5 5 5-5" />
                                </svg>
                            </button>
                            <div class="select-menu" hidden>
                                <button
                                    data-value="professor"
                                    aria-selected="true"
                                >
                                    Professor</button
                                ><button data-value="ta">TA</button>
                            </div>
                        </div>
                    </div>
                    <label class="invite"
                        ><input type="checkbox" checked /> Send an invitation
                        email</label
                    >
                    <div class="drawer-actions">
                        <button
                            type="button"
                            class="btn secondary"
                            id="cancelDrawer"
                        >
                            Cancel</button
                        ><button class="btn">Add user</button>
                    </div>
                </form>
            </aside>
            <div class="overlay" id="reviewOverlay" hidden></div>
            <aside
                class="drawer review-drawer"
                id="reviewDrawer"
                aria-hidden="true"
            >
                <button type="button" class="drawer-close" id="closeReview">
                    ×
                </button>
                <p class="eyebrow">Admin · Role approval</p>
                <h2>Review role request</h2>
                <div class="review-person">
                    <span class="avatar">DL</span>
                    <div>
                        <strong>Dr. Lina Haddad</strong>
                        <p>lina.haddad@miuegypt.edu.eg</p>
                    </div>
                </div>
                <dl class="review-details">
                    <div>
                        <dt>Requested role</dt>
                        <dd><span class="badge info">Professor</span></dd>
                    </div>
                    <div>
                        <dt>Submitted</dt>
                        <dd>Oct 16, 2026</dd>
                    </div>
                    <div>
                        <dt>Verification</dt>
                        <dd>
                            <span class="badge success">Verified faculty</span>
                        </dd>
                    </div>
                </dl>
                <p class="review-note">
                    Approving assigns the Professor role and activates the
                    account. Historical records remain retained.
                </p>
                <div class="drawer-actions">
                    <button
                        type="button"
                        class="btn secondary"
                        id="rejectPending"
                    >
                        Reject request</button
                    ><button type="button" class="btn" id="approvePending">
                        Approve professor role
                    </button>
                </div>
            </aside>
            <div class="overlay" id="dataRequestOverlay" hidden></div>
            <aside
                class="drawer review-drawer"
                id="dataRequestDrawer"
                aria-hidden="true"
            >
                <button
                    type="button"
                    class="drawer-close"
                    id="closeDataRequest"
                >
                    ×
                </button>
                <p class="eyebrow">Admin · Data request</p>
                <h2>Review data correction</h2>
                <div class="review-person">
                    <span class="avatar">AH</span>
                    <div>
                        <strong>Aya Hassan</strong>
                        <p>aya.hassan@miuegypt.edu.eg</p>
                    </div>
                </div>
                <dl class="review-details">
                    <div>
                        <dt>Field</dt>
                        <dd>Student ID</dd>
                    </div>
                    <div>
                        <dt>Current value</dt>
                        <dd>20230012</dd>
                    </div>
                    <div>
                        <dt>Requested value</dt>
                        <dd>20230021</dd>
                    </div>
                    <div>
                        <dt>Submitted</dt>
                        <dd>Oct 12, 2026</dd>
                    </div>
                </dl>
                <p class="review-note">
                    Approve only after confirming the requested value against
                    the university record.
                </p>
                <div class="drawer-actions">
                    <button
                        type="button"
                        class="btn secondary"
                        id="rejectDataRequest"
                    >
                        Reject request</button
                    ><button type="button" class="btn" id="approveDataRequest">
                        Approve correction
                    </button>
                </div>
            </aside>
            <div class="overlay" id="deactivateOverlay" hidden></div>
            <section
                class="deactivate-dialog"
                id="deactivateDialog"
                role="dialog"
                aria-modal="true"
                aria-labelledby="deactivateTitle"
                hidden
            >
                <span
                    class="deactivate-icon"
                    id="statusDialogIcon"
                    aria-hidden="true"
                    >!</span
                >
                <p class="eyebrow">Admin · Account access</p>
                <h2 id="deactivateTitle">Deactivate account?</h2>
                <p id="statusDialogMessage"></p>
                <aside class="history-notice" id="statusDialogNotice">
                    Historical project records, submissions, activity, and role
                    history will remain available.
                </aside>
                <div class="deactivate-actions">
                    <button
                        type="button"
                        class="btn secondary"
                        id="cancelDeactivate"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        class="btn danger-button"
                        id="confirmDeactivate"
                    >
                        Confirm deactivation
                    </button>
                </div>
            </section>
            <div class="toast" id="toast" hidden>
                <div>
                    <strong>Done</strong>
                    <p id="toastMessage">User added successfully.</p>
                </div>
                <button type="button" id="closeToast">×</button>
            </div>
@endpush

@push('scripts')
    <script src="{{ route('admin.assets.js', ['path' => 'pages/admin-users.js']) }}"></script>
@endpush
