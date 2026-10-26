@extends('layouts.ta')

@section('title', 'TA Assignments')

@push('styles')
    <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'pages/ta-invitations.css']) }}">
@endpush

@section('content')
<x-ta.page-header title="Project assignments" description="Professor assignments appear automatically. No acceptance is required.">
    <span class="badge info">2 active assignments</span>
</x-ta.page-header>
                <section id="assignmentContent">
                    <aside class="assignment-note">
                        Assignment access is controlled by the supervising
                        professor. Access changes take effect immediately and
                        never grant final-grade authority.
                    </aside>
                    <section class="assignment-list">
                        <article class="card assignment-card">
                            <header>
                                <div>
                                    <span class="badge success">Active</span>
                                    <h2>Software Engineering Capstone</h2>
                                    <p>
                                        CS 490 · Fall 2026 · Assigned by
                                        Professor Lee
                                    </p>
                                </div>
                                <span class="assignment-icon">TA</span>
                            </header>
                            <div class="assignment-details">
                                <div>
                                    <small>ACCESS</small
                                    ><strong>Full review</strong>
                                    <p>
                                        Comment, save drafts, and record review
                                        outcomes.
                                    </p>
                                </div>
                                <div>
                                    <small>ASSIGNED TEAMS</small
                                    ><strong>Teams 1, 2, and 3</strong>
                                    <p>
                                        Access limited to these assigned teams.
                                    </p>
                                </div>
                                <div>
                                    <small>ASSIGNED</small
                                    ><strong>October 7, 2026</strong>
                                    <p>
                                        Project access remains active until
                                        removed.
                                    </p>
                                </div>
                            </div>
                            <footer>
                                <span>Final grades remain professor-only.</span
                                ><a class="btn" href="{{ route('ta.reviews', ['access' => 'full']) }}"
                                    >Open reviews</a
                                >
                            </footer>
                        </article>
                        <article class="card assignment-card">
                            <header>
                                <div>
                                    <span class="badge success">Active</span>
                                    <h2>Database Systems</h2>
                                    <p>
                                        CS 342 · Fall 2026 · Assigned by Dr.
                                        Lina Haddad
                                    </p>
                                </div>
                                <span class="assignment-icon">TA</span>
                            </header>
                            <div class="assignment-details">
                                <div>
                                    <small>ACCESS</small
                                    ><strong>Comment only</strong>
                                    <p>
                                        Leave feedback without recording an
                                        outcome.
                                    </p>
                                </div>
                                <div>
                                    <small>ASSIGNED TEAMS</small
                                    ><strong>Teams 4 and 5</strong>
                                    <p>
                                        Access limited to these assigned teams.
                                    </p>
                                </div>
                                <div>
                                    <small>ASSIGNED</small
                                    ><strong>October 14, 2026</strong>
                                    <p>
                                        Project access remains active until
                                        removed.
                                    </p>
                                </div>
                            </div>
                            <footer>
                                <span
                                    >Review outcomes and grades are
                                    unavailable.</span
                                ><a
                                    class="btn"
                                    href="{{ route('ta.reviews', ['access' => 'comment']) }}"
                                    >Open comments</a
                                >
                            </footer>
                        </article>
                    </section>
                    <section class="card access-history">
                        <h2>Recent access changes</h2>
                        <div class="history-row">
                            <span
                                ><strong>Mobile Computing Project</strong
                                ><small
                                    >Access removed October 18, 2026 by
                                    Professor Mona</small
                                ></span
                            ><span class="badge danger">Revoked</span>
                        </div>
                    </section>
                </section>
                <section
                    class="card state-card"
                    id="assignmentState"
                    hidden
                ></section>
@endsection

@push('scripts')
    <script src="{{ route('ta.assets.js', ['path' => 'pages/ta-invitations.js']) }}"></script>
@endpush
