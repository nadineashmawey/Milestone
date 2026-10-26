@extends('layouts.app')

@section('title', 'TA Reviews')
@section('workspace-name', 'Teaching assistant workspace')

@section('navigation')
    <a href="#dashboard">Dashboard</a>
    <a href="{{ route('ta.reviews') }}" aria-current="page">Reviews <span class="review-nav-count">5</span></a>
    <a href="#issues">Issues</a>
    <a href="#invitations">Invitations <span class="review-nav-count">1</span></a>
@endsection

@section('topbar-actions')
    <span class="review-role">TA</span>
    <button class="review-icon-button" type="button" aria-label="Toggle theme" title="Toggle theme">☾</button>
    <button class="review-icon-button" type="button" aria-label="Help" title="Help">?</button>
    <button class="review-icon-button review-notification" type="button" aria-label="Notifications" title="Notifications">♧</button>
    <span class="review-avatar" aria-label="Signed in as Sarah Ahmed">SA</span>
@endsection

@section('content')
<div class="ta-reviews" data-review-app>
    <header class="ta-reviews__heading">
        <div>
            <p class="ta-reviews__eyebrow">Teaching assistant workspace</p>
            <h1>Reviews</h1>
            <p class="ta-reviews__intro">Review assigned work and send feedback to Professor Lee.</p>
        </div>
        <x-badge tone="review">TA · No final grade</x-badge>
    </header>

    <section class="review-workspace" aria-label="Submission review workspace">
        <aside class="review-queue" aria-label="Review queue">
            <div class="review-filters" role="group" aria-label="Filter submissions">
                <button class="is-active" type="button" data-filter="all" aria-pressed="true">All <span>5</span></button>
                <button type="button" data-filter="needs-review" aria-pressed="false">Needs review</button>
                <button type="button" data-filter="reviewed" aria-pressed="false">TA reviewed</button>
                <button type="button" data-filter="graded" aria-pressed="false">Graded</button>
            </div>
            <div class="review-queue__heading"><strong>Review queue</strong><span>3 submissions</span></div>
            <div class="review-submissions" role="list">
                <button class="submission-card is-selected" type="button" role="listitem" data-submission="team-3" data-status="needs-review" aria-pressed="true">
                    <strong>Team 3</strong><span>M2 Design Document</span><span class="submission-chip submission-chip--review">● Extended</span><span class="submission-chip submission-chip--info">● Submitted</span><small>Submitted 42 min ago</small>
                </button>
                <button class="submission-card" type="button" role="listitem" data-submission="team-1" data-status="reviewed" aria-pressed="false">
                    <strong>Team 1</strong><span>M2 Design Document</span><span class="submission-chip submission-chip--review">● TA reviewed</span><small>Updated yesterday</small>
                </button>
                <button class="submission-card" type="button" role="listitem" data-submission="team-2" data-status="needs-review" aria-pressed="false">
                    <strong>Team 2</strong><span>M1 Proposal</span><span class="submission-chip submission-chip--danger">● Late</span><small>Late · Oct 10</small>
                </button>
                <p class="review-empty" hidden>No submissions in this filter.</p>
            </div>
        </aside>

        <main class="review-document" aria-label="Selected submission">
            <div class="review-document__title">
                <div><p class="review-kicker" data-document-kicker>M2 · Design document</p><h2 data-team-title>Team 3</h2></div>
                <div class="review-versions" role="group" aria-label="Submission version">
                    <button type="button" data-version="v1" aria-pressed="false">v1</button>
                    <button type="button" data-version="v2" aria-pressed="false">v2</button>
                    <button type="button" class="is-active" data-version="v3" aria-pressed="true">v3 <small>Latest</small></button>
                </div>
            </div>
            <div class="document-preview" aria-label="Document preview placeholder">
                <article class="paper-preview">
                    <p class="paper-preview__title" data-paper-title>System Design Document</p>
                    <p class="paper-preview__subtitle" data-paper-subtitle>Team 3 · Version 3</p>
                    <div class="paper-line"></div><div class="paper-line paper-line--short"></div>
                    <div class="paper-diagram"><i></i><i></i><i></i></div>
                    <div class="paper-line"></div><div class="paper-line paper-line--medium"></div><div class="paper-line paper-line--short"></div>
                </article>
            </div>
            <a class="document-open" href="#open-document" data-open-document>⇩ <span>Open full document</span></a>
            <section class="review-detail-section">
                <h3>Attached files and links</h3>
                <a class="attachment" href="#team-pdf"><span>▤</span><span><strong data-file-name>team-3-design-v3.pdf</strong><small>2.8 MB · PDF</small></span><span>›</span></a>
                <a class="attachment" href="#repository"><span>▱</span><span><strong>github.com/team-3/capstone</strong><small>Repository · main</small></span><span>›</span></a>
                <a class="attachment" href="#figma"><span>▤</span><span><strong>prototype.fig</strong><small>Figma design link</small></span><span>›</span></a>
            </section>
            <section class="review-detail-section evidence-section">
                <div class="evidence-heading"><div><h3>Contribution evidence</h3><p>Evidence only · Never assigns grades automatically</p></div><x-badge tone="neutral">● GitHub placeholder</x-badge></div>
                <div class="contribution-row"><span class="member-avatar member-avatar--blue">H</span><strong>Hana</strong><small>8 tasks</small><small>5 uploads</small><small>14 commits</small><span class="contribution-bar"><i style="width:82%"></i></span></div>
                <div class="contribution-row"><span class="member-avatar member-avatar--yellow">Y</span><strong>Youssef</strong><small>6 tasks</small><small>3 uploads</small><small>9 commits</small><span class="contribution-bar"><i style="width:61%"></i></span></div>
                <div class="contribution-row"><span class="member-avatar member-avatar--purple">L</span><strong>Layla</strong><small>4 tasks</small><small>6 uploads</small><small>5 commits</small><span class="contribution-bar"><i style="width:48%"></i></span></div>
            </section>
            <section class="review-detail-section activity-section">
                <h3>Submission activity</h3>
                <p><small>10:42 AM</small><i></i>Hana submitted version 3</p>
                <p><small>8:18 AM</small><i></i>Layla replaced the presentation</p>
                <p><small>Yesterday</small><i></i>Youssef linked commit 8d2f4a1</p>
            </section>
        </main>

        <aside class="review-panel" aria-label="Review feedback and rubric">
            <div class="review-score"><div><small>Live team grade</small><strong>43/50</strong></div><x-badge>View only</x-badge></div>
            <form data-review-form>
                <div class="rubric-list">
                    <label class="rubric-item"><span><strong>Research</strong><small><output data-score-output>9</output>/10 · Excellent</small></span><input type="range" min="0" max="10" value="9" aria-label="Research score"><small class="rubric-points"><output data-score-output>9</output> points</small></label>
                    <label class="rubric-item"><span><strong>Technical design</strong><small><output data-score-output>8</output>/10 · Strong</small></span><input type="range" min="0" max="10" value="8" aria-label="Technical design score"><small class="rubric-points"><output data-score-output>15</output> points</small></label>
                    <label class="rubric-item"><span><strong>Collaboration</strong><small><output data-score-output>9</output>/10 · Excellent</small></span><input type="range" min="0" max="10" value="9" aria-label="Collaboration score"><small class="rubric-points"><output data-score-output>15</output> points</small></label>
                    <label class="rubric-item"><span><strong>Communication</strong><small><output data-score-output>8</output>/10 · Strong</small></span><input type="range" min="0" max="10" value="8" aria-label="Communication score"><small class="rubric-points"><output data-score-output>10</output> points</small></label>
                </div>
                <div class="previous-feedback"><span class="feedback-avatar">SA</span><div><strong>TA feedback</strong><small>28 minutes ago</small></div><p>Strong architecture. Add cardinality labels to the ERD and identify testing owners.</p></div>
                <label class="feedback-label" for="review-feedback">Review feedback</label>
                <textarea id="review-feedback" name="feedback" data-feedback rows="4">Your architecture is well considered. Before approval, add cardinality labels to the ERD and assign an owner to each testing phase.</textarea>
                <div class="feedback-suggestions" aria-label="Feedback suggestions">
                    <button type="button" data-suggestion="Please clarify the rationale behind this decision.">Clear rationale</button>
                    <button type="button" data-suggestion="Please add evidence to support this section.">Add evidence</button>
                    <button type="button" data-suggestion="Please check formatting and consistency throughout.">Check formatting</button>
                    <button type="button" data-suggestion="This section needs a stronger revision before approval.">Strong revision</button>
                </div>
                <div class="review-actions">
                    <button class="review-action review-action--secondary" type="button" data-action="save">Save draft</button>
                    <button class="review-action review-action--danger" type="button" data-action="request">Request resubmission</button>
                    <button class="review-action review-action--primary" type="button" data-action="send">Send review</button>
                </div>
            </form>
        </aside>
    </section>
    <p class="review-toast" role="status" aria-live="polite" data-toast hidden></p>
    <dialog class="review-dialog" data-dialog>
        <form method="dialog"><button class="review-dialog__close" aria-label="Close">×</button></form>
        <span class="review-dialog__icon">↗</span><h2 data-dialog-title>Review sent</h2><p data-dialog-message>Your feedback is ready for the team.</p>
        <button type="button" class="review-action review-action--primary" data-dialog-close>Done</button>
    </dialog>
</div>
@endsection
