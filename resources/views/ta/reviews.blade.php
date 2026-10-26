@extends('layouts.ta')

@section('title', 'TA Reviews')

@push('styles')
    <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'pages/ta-reviews.css']) }}">
@endpush

@section('content')
<x-ta.page-header title="Reviews" description="Review assigned submissions within the permission granted by the professor.">
    <span class="badge info" id="accessBadge">Full review · No final grade</span>
</x-ta.page-header>
                <section id="reviewContent">
                    <div class="review-filters card">
                        <label
                            >Status<select class="control" id="statusFilter">
                                <option value="all">All statuses</option>
                                <option value="not-started">Not started</option>
                                <option value="submitted">Submitted</option>
                                <option value="reviewed">TA reviewed</option>
                                <option value="resubmission">
                                    Needs resubmission
                                </option>
                                <option value="approved">Approved</option>
                                <option value="graded">Graded</option>
                            </select></label
                        ><label
                            >Team<select class="control" id="teamFilter">
                                <option value="all">All teams</option>
                                <option>Team 1</option>
                                <option>Team 2</option>
                                <option>Team 3</option>
                                <option>Team 5</option>
                            </select></label
                        ><label
                            >Milestone<select
                                class="control"
                                id="milestoneFilter"
                            >
                                <option value="all">All milestones</option>
                                <option value="M1">M1 Project Proposal</option>
                                <option value="M2">M2 Design Document</option>
                                <option value="M3">M3 Prototype</option>
                            </select></label
                        ><span>Sorted by nearest deadline</span>
                    </div>
                    <section class="card review-shell">
                        <aside class="queue">
                            <h2>Review queue</h2>
                            <p><span id="queueCount">0</span> submissions</p>
                            <div id="submissionList"></div>
                            <div class="queue-empty" id="queueEmpty" hidden>
                                No submissions match these filters.
                            </div>
                        </aside>
                        <article class="document">
                            <div class="doc-title">
                                <div>
                                    <p class="eyebrow" id="milestoneLabel">
                                        M2 · Design document
                                    </p>
                                    <h2 id="teamTitle">Team 3</h2>
                                    <p id="deadlineLabel">
                                        Due today · 11:59 PM
                                    </p>
                                </div>
                                <div class="versions">
                                    <button>v1</button><button>v2</button
                                    ><button class="active">
                                        v3 <small>Latest</small>
                                    </button>
                                </div>
                            </div>
                            <div class="paper">
                                <h3>System Design Document</h3>
                                <p>
                                    Software Engineering Capstone · Selected
                                    submission preview
                                </p>
                                <i></i><i></i><i></i><i></i>
                                <h4>Architecture overview</h4>
                                <i></i><i></i><i></i>
                            </div>
                            <section class="attachments">
                                <h3>Submission files</h3>
                                <div class="file-row">
                                    <span>PDF</span>
                                    <div>
                                        <strong>design-document-v3.pdf</strong
                                        ><small>2.4 MB · Version 3</small>
                                    </div>
                                    <button>View</button
                                    ><button>Download</button>
                                </div>
                                <div class="file-row unavailable">
                                    <span>ZIP</span>
                                    <div>
                                        <strong>prototype-source.zip</strong
                                        ><small
                                            >Unavailable · Upload processing
                                            failed</small
                                        >
                                    </div>
                                    <button disabled>Unavailable</button>
                                </div>
                            </section>
                        </article>
                        <aside class="review-form">
                            <div class="grade-view">
                                <span
                                    ><small>Professor grade</small
                                    ><strong>43/50</strong></span
                                ><span class="badge">View only</span>
                            </div>
                            <div class="rubric">
                                <h2>Rubric</h2>
                                <p>Professor scoring is read-only for TAs.</p>
                                <div><span>Research</span><b>9/10</b></div>
                                <div>
                                    <span>Technical design</span><b>12/15</b>
                                </div>
                                <div>
                                    <span>Collaboration</span><b>13/15</b>
                                </div>
                                <div><span>Communication</span><b>9/10</b></div>
                            </div>
                            <label class="field"
                                >TA feedback<textarea
                                    class="control"
                                    id="feedback"
                                    placeholder="Write feedback for the team and professor"
                                ></textarea
                                ><small class="error" id="feedbackError" hidden
                                    >Please add feedback before sending.</small
                                ></label
                            >
                            <div class="chips">
                                <button>Clear structure</button
                                ><button>Add evidence</button
                                ><button>Check formatting</button>
                            </div>
                            <label class="field" id="outcomeField"
                                >Review outcome<select
                                    class="control"
                                    id="reviewOutcome"
                                >
                                    <option value="">Choose an outcome</option>
                                    <option value="reviewed">
                                        TA reviewed
                                    </option>
                                    <option value="resubmission">
                                        Needs resubmission
                                    </option>
                                    <option value="approved">
                                        Recommend approval
                                    </option></select
                                ><small class="error" id="outcomeError" hidden
                                    >Choose a review outcome.</small
                                ></label
                            >
                            <div
                                class="permission-note"
                                id="permissionNote"
                            ></div>
                            <div class="actions">
                                <button class="btn secondary" id="saveDraft">
                                    Save draft</button
                                ><button class="btn" id="sendReview">
                                    Send review
                                </button>
                            </div>
                        </aside>
                    </section>
                </section>
                <section
                    class="card state-card"
                    id="reviewState"
                    hidden
                ></section>
@endsection

@push('overlays')
        <div class="toast" id="toast" hidden>
            <div>
                <strong id="toastTitle">Done</strong>
                <p id="toastMessage"></p>
            </div>
            <button>×</button>
        </div>
@endpush

@push('scripts')
    <script src="{{ route('ta.assets.js', ['path' => 'pages/ta-reviews.js']) }}"></script>
@endpush
