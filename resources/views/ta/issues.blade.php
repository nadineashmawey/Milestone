@extends('layouts.app')

@section('title', 'TA Issues')

@push('styles')
    @vite('resources/css/pages/ta-issues.css')
@endpush

@section('content')
    <div class="ta-issues" data-issues-app>
        <div class="issues-heading">
            <p class="issues-eyebrow">Teaching assistant workspace</p>
            <x-page-header title="Issues" description="Report project problems directly to the supervising professor.">
                <x-button type="button" id="focus-report">＋ Report issue</x-button>
            </x-page-header>
        </div>

        <div class="issues-layout">
            <section class="issues-list-panel" id="reported-issues" aria-labelledby="issues-list-title">
                <div class="issues-panel-heading">
                    <div>
                        <h2 id="issues-list-title">Reported issues</h2>
                        <p>Status is updated by the professor.</p>
                    </div>
                    <span class="issues-total" data-issue-count>2 issues</span>
                </div>

                <details class="issues-filter-disclosure">
                    <summary>Search and filter issues</summary>
                    <div class="issues-toolbar" role="search">
                    <label class="issues-search">
                        <span class="sr-only">Search issues</span>
                        <span aria-hidden="true">⌕</span>
                        <input type="search" id="issue-search" placeholder="Search issues…" autocomplete="off">
                    </label>
                    <label class="issues-filter"><span class="sr-only">Filter by status</span>
                        <select id="status-filter" aria-label="Filter by status">
                            <option value="all">All statuses</option><option value="reviewing">Professor reviewing</option><option value="in-progress">In progress</option><option value="resolved">Resolved</option>
                        </select>
                    </label>
                    <label class="issues-filter"><span class="sr-only">Filter by priority</span>
                        <select id="priority-filter" aria-label="Filter by priority">
                            <option value="all">All priorities</option><option value="high">High priority</option><option value="medium">Medium priority</option><option value="low">Low priority</option>
                        </select>
                    </label>
                    </div>
                </details>

                <div class="issues-content">
                    <div class="issues-list" role="list" aria-label="Reported issues">
                        <button class="issue-card is-selected" type="button" role="listitem" data-issue-id="issue-1" data-status="reviewing" data-priority="high" aria-pressed="true">
                            <span class="issue-priority-mark issue-priority-mark--high" aria-hidden="true"></span>
                            <span class="issue-card__body"><strong>Team 3 has no active leader</strong><span>Software Engineering Capstone · Reported by you</span><small>Today, 10:24 AM</small></span>
                            <span class="issue-card__meta"><x-badge tone="warning">Professor reviewing</x-badge></span>
                        </button>
                        <button class="issue-card" type="button" role="listitem" data-issue-id="issue-2" data-status="resolved" data-priority="medium" aria-pressed="false">
                            <span class="issue-priority-mark issue-priority-mark--medium" aria-hidden="true"></span>
                            <span class="issue-card__body"><strong>M2 PDF is corrupted</strong><span>Database Systems · Reported by you</span><small>Yesterday, 3:40 PM</small></span>
                            <span class="issue-card__meta"><x-badge tone="success">Resolved</x-badge></span>
                        </button>
                    </div>
                    <p class="issues-empty" data-empty-state hidden>No issues match these filters.</p>

                    <aside class="issue-detail" aria-live="polite" aria-labelledby="detail-title">
                        <div class="detail-heading">
                            <div><p class="issues-eyebrow" data-detail-project>SOFTWARE ENGINEERING CAPSTONE</p><h2 id="detail-title" data-detail-title>Team 3 has no active leader</h2></div>
                            <button class="issues-close-detail" type="button" aria-label="Close issue details">×</button>
                        </div>
                        <div class="detail-badges"><span data-detail-status><x-badge tone="warning">Professor reviewing</x-badge></span><span data-detail-priority><x-badge tone="danger">High priority</x-badge></span></div>
                        <dl class="detail-facts"><div><dt>Reported by</dt><dd data-detail-reporter>Sarah Ahmed · Teaching assistant</dd></div><div><dt>Team</dt><dd data-detail-team>Team 3</dd></div><div><dt>Reported</dt><dd data-detail-date>Today at 10:24 AM</dd></div></dl>
                        <div class="detail-description"><h3>What happened</h3><p data-detail-description>Team 3’s previous leader left the project, and the team cannot assign a new leader. They are blocked from submitting their milestone work until the roster is updated.</p></div>
                        <div class="detail-updates"><div class="detail-updates__heading"><h3>Updates</h3><span data-comment-count>1 update</span></div><div class="comment-list" data-comment-list>
                            <article class="issue-comment"><span class="comment-avatar">SA</span><div><p><strong>Sarah Ahmed</strong><time>Today, 10:24 AM</time></p><p>I checked the team roster and confirmed there is no active leader assigned.</p></div></article>
                        </div></div>
                        <form class="comment-form" data-comment-form><label for="issue-comment">Add an update</label><textarea id="issue-comment" rows="2" maxlength="500" placeholder="Share a follow-up with the professor…" required></textarea><div class="comment-form__footer"><span>Visible to the professor</span><x-button type="submit">Add comment</x-button></div></form>
                        <div class="detail-status-control"><label for="issue-status">Update status <span>(frontend preview)</span></label><select id="issue-status"><option value="reviewing">Professor reviewing</option><option value="in-progress">In progress</option><option value="resolved">Resolved</option></select></div>
                    </aside>
                </div>
            </section>

            <section class="report-panel" id="report-form" aria-labelledby="report-title">
                <div class="report-heading"><span class="report-icon" aria-hidden="true">♧</span><div><h2 id="report-title">Report a problem</h2><p>The project professor receives this report.</p></div></div>
                <form data-report-form>
                    <label class="issues-field" for="report-project">Project
                        <select id="report-project" required><option>Software Engineering Capstone</option><option>Database Systems</option><option>Human Computer Interaction</option></select>
                    </label>
                    <label class="issues-field" for="report-description">Description
                        <textarea id="report-description" rows="4" maxlength="1000" placeholder="Explain what is blocked, who is affected, and what you have already tried." required></textarea>
                    </label>
                    <div class="report-footer"><p>Use this for blockers that need the professor’s attention.</p><x-button type="submit">Send issue</x-button></div>
                </form>
            </section>
        </div>
        <p class="issues-toast" data-issues-toast role="status" aria-live="polite" hidden></p>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/ta-issues.js')
@endpush
