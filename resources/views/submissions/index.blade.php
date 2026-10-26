@extends('layouts.professor')
@section('title', 'Submissions')
@section('content')
<div class="submissions-page" data-reviews>
    <header class="reviews-header"><div><p class="reviews-eyebrow">Software Engineering Capstone</p><h1>Reviews</h1><p class="ms-muted">Read submissions, inspect evidence, and finalize grades in context.</p></div><x-badge tone="info">● Professor grading</x-badge></header>
    <div class="reviews-workspace">
        <aside class="reviews-queue" aria-label="Review queue">
            <div class="reviews-filters" role="group" aria-label="Filter submissions">@foreach(['All','Needs review','TA reviewed','Graded'] as $filter)<button type="button" data-filter="{{ $filter }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $filter }}</button>@endforeach</div>
            <div class="reviews-queue-heading"><strong>Review queue</strong><span data-count>3 submissions</span></div>
            <div data-queue>
                @foreach($submissions as $submission)
                <button type="button" class="reviews-queue-item" data-submission="{{ $submission['id'] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"><strong>{{ $submission['team'] }}</strong><span>{{ str_replace(' ·', '', $submission['milestone']) }}</span>@if($submission['extended'])<x-badge tone="review">● Extended</x-badge>@endif<x-badge :tone="$submission['tone']">● {{ $submission['status'] }}</x-badge><small>{{ $submission['updated'] }}</small></button>
                @endforeach
            </div>
            <p class="ms-muted" data-empty hidden>No submissions match this filter.</p>
        </aside>
        <section class="reviews-reader" aria-label="Selected submission" data-reader>
            <div class="reviews-reader-heading"><div><p class="reviews-eyebrow" data-milestone>M2 · Design Document</p><h2 data-team>Team 3</h2></div><div class="reviews-versions" role="group" aria-label="Submission versions" data-versions></div></div>
            <div class="reviews-preview"><div class="reviews-paper" aria-label="Illustrative document preview"><h3 data-document-title>System Design Document</h3><p data-document-version>Team 3 · Version 3</p><div class="reviews-lines"><i></i><i></i><i></i></div><div class="reviews-diagram" aria-hidden="true"><i></i><i></i><i></i></div><div class="reviews-lines"><i></i><i></i><i></i></div></div><button type="button" class="reviews-open" data-open-document><x-project-icon name="download"/>Open full document</button></div>
            <section class="reviews-section"><h3>Attached files and links</h3><div data-files></div></section>
            <section class="reviews-section"><div class="reviews-evidence-heading"><div><h3>Contribution evidence</h3><p>Evidence only · Never assigns grades automatically</p></div><x-badge>● GitHub placeholder</x-badge></div><div data-evidence></div></section>
            <section class="reviews-section"><h3>Submission activity</h3><div data-activity></div></section>
        </section>
        <aside class="reviews-grading" aria-label="Professor grading" data-grading>
            <div class="reviews-grade"><div><span>Live team grade</span><strong><output data-grade>40</output><small>/50</small></strong></div><x-badge tone="info">● Draft</x-badge></div>
            <h3 class="reviews-rubric-title">Rubric</h3>
            @foreach([['Research',10],['Technical design',15],['Collaboration',15],['Communication',10]] as [$criterion,$points])
            <label class="reviews-rubric"><span><strong>{{ $criterion }}</strong><output data-score-label="{{ $loop->index }}"></output></span><input type="range" min="0" max="{{ $points }}" step="0.5" name="scores[{{ $loop->index }}]" aria-label="{{ $criterion }} score out of {{ $points }}" data-score="{{ $loop->index }}"><small>{{ $points }} points</small></label>
            @endforeach
            <div class="reviews-ta" data-ta><div class="reviews-ta-heading"><span class="reviews-avatar reviews-avatar--green">SA</span><span><strong>TA feedback</strong><small>28 minutes ago</small></span><button type="button" data-use-ta>Use as base</button></div><p data-ta-feedback></p></div>
            <label class="reviews-feedback">Professor feedback<textarea class="ms-input" name="feedback" rows="5" data-feedback></textarea></label>
            <div class="reviews-comment-bank">@foreach(['Clear rationale','Add evidence','Check formatting','Strong revision'] as $comment)<button type="button" data-comment="{{ $comment }}">{{ $comment }}</button>@endforeach</div>
            <label class="reviews-adjustment">Optional student adjustment<select class="ms-input" name="adjustment" data-adjustment></select><small>Contribution evidence informs—but never determines—adjustments.</small></label>
            <div class="reviews-actions"><x-button variant="secondary" data-save>Save draft</x-button><x-button variant="danger" data-action="resubmit">Request resubmission</x-button><x-button data-action="finalize">Approve and finalize</x-button></div>
        </aside>
    </div>
    <p class="reviews-status" role="status" data-status></p>
    <template data-file-icon><x-project-icon name="file"/></template>
    <template data-folder-icon><x-project-icon name="folder"/></template>
    <dialog class="reviews-dialog"><h2 data-dialog-title></h2><p data-dialog-body></p><div class="ms-row"><x-button variant="secondary" data-dialog-close>Keep reviewing</x-button><x-button data-dialog-confirm>Confirm preview</x-button></div></dialog>
    <script type="application/json" data-submissions-json>{!! json_encode($submissions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</div>
@endsection

