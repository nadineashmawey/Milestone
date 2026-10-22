@extends('layouts.ta')

@section('title', 'TA Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'pages/ta-dashboard.css']) }}">
@endpush

@section('content')
<x-ta.page-header title="Good morning, Sara" description="Assigned projects, teams, deadlines, notifications, and reported issues.">
    <a class="btn" href="{{ route('ta.reviews') }}">Open review queue →</a>
</x-ta.page-header>
                <section id="dashboardContent">
                    <section class="metrics">
                        <article class="card metric">
                            <span>Assigned projects</span><strong>2</strong
                            ><small>Across 2 courses</small>
                        </article>
                        <article class="card metric">
                            <span>Assigned teams</span><strong>5</strong
                            ><small>Only teams you support</small>
                        </article>
                        <article class="card metric">
                            <span>Upcoming deadlines</span><strong>3</strong
                            ><small>1 overdue</small>
                        </article>
                        <article class="card metric">
                            <span>Open issues</span><strong>2</strong
                            ><small>1 acknowledged</small>
                        </article>
                    </section>
                    <section class="dashboard-grid">
                        <article class="card panel">
                            <header>
                                <div>
                                    <h2>Assigned projects and teams</h2>
                                    <p>Permissions are set per project</p>
                                </div>
                                <a href="{{ route('ta.assignments') }}">View access</a>
                            </header>
                            <a
                                class="project-row"
                                href="{{ route('ta.reviews', ['access' => 'full']) }}"
                                ><span class="initial">SE</span
                                ><span class="copy"
                                    ><strong
                                        >Software Engineering Capstone</strong
                                    ><small
                                        >CS 490 · Professor Lee · Teams 1, 2,
                                        3</small
                                    ></span
                                ><span class="badge info">Full review</span
                                ><b>›</b></a
                            ><a
                                class="project-row"
                                href="{{ route('ta.reviews', ['access' => 'comment']) }}"
                                ><span class="initial">DB</span
                                ><span class="copy"
                                    ><strong>Database Systems</strong
                                    ><small
                                        >CS 342 · Dr. Lina Haddad · Teams 4,
                                        5</small
                                    ></span
                                ><span class="badge">Comment only</span
                                ><b>›</b></a
                            >
                        </article>
                        <article class="card panel">
                            <header>
                                <div>
                                    <h2>Deadlines</h2>
                                    <p>Upcoming and overdue work</p>
                                </div>
                            </header>
                            <div class="deadline-row overdue">
                                <span
                                    ><strong>M2 Design Document</strong
                                    ><small
                                        >Team 5 · Database Systems</small
                                    ></span
                                ><span
                                    ><b>Overdue</b><small>Oct 21</small></span
                                >
                            </div>
                            <div class="deadline-row">
                                <span
                                    ><strong>M2 Design Document</strong
                                    ><small
                                        >Team 3 · Software Engineering</small
                                    ></span
                                ><span
                                    ><b>Today</b><small>11:59 PM</small></span
                                >
                            </div>
                            <div class="deadline-row">
                                <span
                                    ><strong>M3 Prototype</strong
                                    ><small
                                        >Teams 1–3 · Software Engineering</small
                                    ></span
                                ><span><b>Oct 27</b><small>5 days</small></span>
                            </div>
                        </article>
                        <article class="card panel">
                            <header>
                                <div>
                                    <h2>Notifications</h2>
                                    <p>Recent activity in assigned projects</p>
                                </div>
                                <a href="{{ route('ta.reviews') }}">View reviews</a>
                            </header>
                            <a class="notice-row unread" href="{{ route('ta.reviews') }}"
                                ><i></i
                                ><span
                                    ><strong>Team 3 submitted M2 Design</strong
                                    ><small>12 minutes ago</small></span
                                ></a
                            ><a class="notice-row unread" href="{{ route('ta.issues') }}"
                                ><i></i
                                ><span
                                    ><strong
                                        >Professor Lee acknowledged your
                                        issue</strong
                                    ><small>1 hour ago</small></span
                                ></a
                            >
                        </article>
                        <article class="card panel">
                            <header>
                                <div>
                                    <h2>Reported issues</h2>
                                    <p>Problems shared with professors</p>
                                </div>
                                <a href="{{ route('ta.issues') }}">View all</a>
                            </header>
                            <a class="issue-row" href="{{ route('ta.issues') }}"
                                ><span
                                    ><strong>Team 3 sharing is disabled</strong
                                    ><small
                                        >Software Engineering · Today</small
                                    ></span
                                ><span class="badge status-acknowledged"
                                    >Acknowledged</span
                                ></a
                            ><a class="issue-row" href="{{ route('ta.issues') }}"
                                ><span
                                    ><strong
                                        >Milestone file cannot be opened</strong
                                    ><small
                                        >Database Systems · Oct 21</small
                                    ></span
                                ><span class="badge status-open">Open</span></a
                            >
                        </article>
                    </section>
                </section>
                <section
                    class="card state-card"
                    id="dashboardState"
                    hidden
                ></section>
@endsection

@push('scripts')
    <script src="{{ route('ta.assets.js', ['path' => 'pages/ta-dashboard.js']) }}"></script>
@endpush
