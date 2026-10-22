@extends('layouts.admin')

@section('title', 'Audit Log')

@push('styles')
    <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'pages/admin-audit.css']) }}">
@endpush

@section('content')
<x-admin.page-header eyebrow="Admin · Governance" title="Audit log" description="Role, account, course, and project administration history.">
                    <button class="btn secondary" id="exportCsv">
                        ↓ Export CSV
                    </button>
</x-admin.page-header>
                <section class="card audit-card" id="auditCard">
                    <div class="audit-toolbar">
                        <div class="select" id="auditSelect">
                            <button class="select-trigger">
                                <span>All actions</span>⌄
                            </button>
                            <div class="select-menu" hidden>
                                <button data-value="all" aria-selected="true">
                                    All actions</button
                                ><button data-value="role">Role changes</button
                                ><button data-value="account">
                                    Account changes</button
                                ><button data-value="course">
                                    Course changes</button
                                ><button data-value="project">
                                    Project changes
                                </button>
                            </div>
                        </div>
                        <p>
                            <span id="visibleCount">5</span> records · Showing
                            the last 30 days
                        </p>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Administrator</th>
                                    <th>Action</th>
                                    <th>Target</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody id="auditBody"></tbody>
                        </table>
                        <div class="audit-empty" id="auditEmpty" hidden>
                            No audit records match this filter.
                        </div>
                    </div>
                </section>
                <section class="card page-state" id="auditNoActivity" hidden>
                    <span class="state-icon">0</span>
                    <h2>No audit activity</h2>
                    <p>
                        Sensitive administrative actions will appear here when
                        they occur.
                    </p>
                </section>
                <section
                    class="card page-state error-state"
                    id="auditErrorState"
                    hidden
                >
                    <span class="state-icon">!</span>
                    <h2>Audit log could not be loaded</h2>
                    <p>
                        Something went wrong while loading administrative
                        activity.
                    </p>
                    <button
                        class="btn"
                        type="button"
                        onclick="location.href = '/admin/audit'"
                    >
                        Retry
                    </button>
                </section>
@endsection

@push('overlays')
            <div class="toast" id="auditToast" hidden>
                <div>
                    <strong>Export ready</strong>
                    <p id="toastCopy"></p>
                </div>
                <button>×</button>
            </div>
@endpush

@push('scripts')
    <script src="{{ route('admin.assets.js', ['path' => 'pages/admin-audit.js']) }}"></script>
@endpush
