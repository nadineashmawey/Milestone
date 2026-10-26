@extends('layouts.ta')

@section('title', 'TA Issues')

@push('styles')
    <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'pages/ta-issues.css']) }}">
@endpush

@section('content')
<x-ta.page-header title="Issues" description="Report project or team problems directly to the supervising professor.">
    <button class="btn" id="focusIssue">+ Report issue</button>
</x-ta.page-header>
                <section id="issuesContent">
                    <div class="issue-filters card">
                        <label
                            >Project<select class="control" id="projectFilter">
                                <option value="all">All projects</option>
                                <option>Software Engineering Capstone</option>
                                <option>Database Systems</option>
                            </select></label
                        ><label
                            >Status<select class="control" id="statusFilter">
                                <option value="all">All statuses</option>
                                <option>Open</option>
                                <option>Acknowledged</option>
                                <option>Resolved</option>
                            </select></label
                        >
                    </div>
                    <section class="issues-grid">
                        <article class="card issues-list">
                            <div>
                                <h2>Reported issues</h2>
                                <p>Open, acknowledged, and resolved reports</p>
                            </div>
                            <div id="issueRows"></div>
                            <div class="list-empty" id="issueEmpty" hidden>
                                No issues match these filters.
                            </div>
                        </article>
                        <article class="card issue-form">
                            <h2>Report a problem</h2>
                            <p>The supervising professor will be notified.</p>
                            <form id="issueForm" novalidate>
                                <label class="field"
                                    >Project<select
                                        class="control"
                                        id="project"
                                    >
                                        <option value="">
                                            Choose a project
                                        </option>
                                        <option>
                                            Software Engineering Capstone
                                        </option>
                                        <option>
                                            Database Systems
                                        </option></select
                                    ><small
                                        class="error"
                                        id="projectError"
                                        hidden
                                        >Choose a project.</small
                                    ></label
                                ><label class="field"
                                    >Team<select class="control" id="team">
                                        <option value="">Choose a team</option>
                                        <option>Team 1</option>
                                        <option>Team 2</option>
                                        <option>Team 3</option>
                                        <option>Team 4</option>
                                        <option>Team 5</option></select
                                    ><small class="error" id="teamError" hidden
                                        >Choose a team.</small
                                    ></label
                                ><label class="field"
                                    >Description<textarea
                                        class="control"
                                        id="description"
                                        maxlength="500"
                                        placeholder="Describe the problem and what you already tried"
                                    ></textarea
                                    ><small
                                        ><span id="descriptionCount">0</span
                                        >/500</small
                                    ><small
                                        class="error"
                                        id="descriptionError"
                                        hidden
                                        >Enter at least 10 characters.</small
                                    ></label
                                ><button class="btn" type="submit">
                                    Send to professor
                                </button>
                            </form>
                        </article>
                    </section>
                </section>
                <section
                    class="card state-card"
                    id="issuesState"
                    hidden
                ></section>
@endsection

@push('overlays')
        <div class="toast" id="toast" hidden>
            <div>
                <strong>Issue reported</strong>
                <p id="toastMessage"></p>
            </div>
            <button>×</button>
        </div>
@endpush

@push('scripts')
    <script src="{{ route('ta.assets.js', ['path' => 'pages/ta-issues.js']) }}"></script>
@endpush
