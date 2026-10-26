@extends('layouts.professor')
@section('title', 'Create Project')
@section('content')
@php
    $steps = ['Basics' => 'Identity and goals', 'Milestones' => 'Weighted deliverables', 'Rubric & grading' => 'Points and policies', 'Teams' => 'Formation rules', 'People & TA' => 'Roster and reviewers', 'Review' => 'Check and publish'];
    $headings = [['Project details', 'Start from scratch or let Milestone draft from your brief.', 'blue', 'folder'], ['Milestones', 'Create a clear path from proposal to final demo.', 'violet', 'calendar'], ['Rubric and grading', 'Define how the professor will evaluate and release final grades.', 'violet', 'chart'], ['Team formation', 'Set fair boundaries while students form their teams.', 'yellow', 'users'], ['People and teaching assistants', 'Import the roster and invite up to two reviewers.', 'green', 'users'], ['Ready to publish', 'Review every choice before inviting students.', 'blue', 'check']];
@endphp
<div class="create-project" data-project-wizard>
    <header class="cp-header">
        <div><p class="cp-eyebrow">New academic project</p><h1>Create a supervised project</h1><p class="ms-muted">Build the complete project structure before students begin.</p></div>
        <x-button data-publish hidden>Publish project</x-button>
    </header>
    <nav class="cp-steps" aria-label="Project setup steps">
        @foreach($steps as $label => $description)
            <button type="button" data-step="{{ $loop->index }}" @if($loop->first) aria-current="step" @endif><span class="cp-step-number">{{ $loop->iteration }}</span><span><strong>{{ $label }}</strong><small>{{ $description }}</small></span></button>
        @endforeach
    </nav>
    <div class="cp-layout">
        <form class="cp-panel" data-project-form novalidate>
            <p class="cp-error" data-error role="alert" hidden></p>
            @foreach($headings as [$heading, $description, $tone, $icon])
            <section data-panel="{{ $loop->index }}" @if(!$loop->first) hidden @endif aria-labelledby="cp-heading-{{ $loop->index }}">
                <div class="cp-heading"><span class="cp-icon cp-icon--{{ $tone }}"><x-project-icon :name="$icon" /></span><div><h2 id="cp-heading-{{ $loop->index }}" tabindex="-1">{{ $heading }}</h2><p>{{ $description }}</p></div></div>
                @switch($loop->index)
                @case(0)
                    <div class="cp-upload"><x-project-icon name="upload"/><div><strong>Brief-to-Project</strong><small data-brief-label>Upload a PDF brief to pre-fill milestones, rubric, and deadlines</small></div><label class="cp-file-button">Choose PDF<input type="file" name="brief" accept="application/pdf,.pdf" data-brief></label></div>
                    <div class="cp-fields">
                        <label>Project title<input class="ms-input" name="title" placeholder="e.g. Community Impact Platform" maxlength="200" required></label>
                        <label>Project color<select class="ms-input" name="color"><option>Royal blue</option><option>Violet</option><option>Green</option><option>Yellow</option></select></label>
                        <label>Course<select class="ms-input" name="course"><option>CS 490 · Software Engineering</option><option>CS 342 · Database Systems</option><option>CS 210 · Web Development</option></select></label>
                        <label>Semester<select class="ms-input" name="semester"><option>Fall 2026</option><option>Spring 2027</option></select></label>
                        <label class="cp-full">Description<textarea class="ms-input" name="description" placeholder="Give students a concise project overview." rows="4"></textarea></label>
                    </div>
                    <fieldset><legend>Project goals</legend><div data-goals class="cp-list">
                        @foreach(['Apply an iterative software engineering process', 'Document technical decisions and evaluate the final product'] as $goal)
                        <div class="cp-goal" data-row><input class="ms-input" name="goals[]" aria-label="Project goal" value="{{ $goal }}"><button type="button" class="cp-remove" data-remove aria-label="Remove goal"><x-project-icon name="trash"/></button></div>
                        @endforeach
                    </div><x-button variant="secondary" data-add="goal">＋ Add goal</x-button></fieldset>
                    @break
                @case(1)
                    <div class="cp-weight"><small>Milestone weight</small><span class="ms-badge ms-badge--success" data-weight>100% total</span></div>
                    <div class="cp-list" data-milestones>
                        @foreach([['Proposal','2026-10-11','PDF report',20], ['Design Document','2026-10-18','Mixed',35], ['Final Demo','2026-11-08','Demo video link',45]] as [$name,$date,$deliverable,$weight])
                        <div class="cp-milestone" data-row>
                            <span class="cp-icon cp-icon--{{ ['blue','violet','yellow'][$loop->index] }}" data-milestone-number>M{{ $loop->iteration }}</span>
                            <label>Name<input class="ms-input" name="milestones[{{ $loop->index }}][name]" value="{{ $name }}" required></label>
                            <label>Deadline<input class="ms-input" type="date" name="milestones[{{ $loop->index }}][deadline]" value="{{ $date }}" required></label>
                            <label>Deliverable<select class="ms-input" name="milestones[{{ $loop->index }}][deliverable]">@foreach(['PDF report','PowerPoint','GitHub link','Demo video link','Written answer','Mixed'] as $option)<option @selected($option === $deliverable)>{{ $option }}</option>@endforeach</select></label>
                            <label>Weight %<input class="ms-input" type="number" name="milestones[{{ $loop->index }}][weight]" value="{{ $weight }}" min="1" max="100" required data-weight-input></label>
                            <button type="button" class="cp-remove" data-remove aria-label="Remove milestone"><x-project-icon name="trash"/></button>
                        </div>
                        @endforeach
                    </div><x-button variant="secondary" data-add="milestone">＋ Add milestone</x-button>
                    @break
                @case(2)
                    <div class="cp-fields"><label>Total project grade<input class="ms-input" type="number" name="grade" value="50" min="1" required></label><label>Late policy<select class="ms-input" name="late_policy"><option>Allow with a Late flag</option><option>Block after deadline</option></select></label></div>
                    <fieldset><legend>Rubric criteria</legend><div class="cp-list" data-criteria>
                        @foreach([['Research and rationale',10],['Technical quality',20],['Collaboration',10],['Presentation',10]] as [$name,$points])
                        <div class="cp-criterion" data-row><input class="ms-input" name="criteria[{{ $loop->index }}][name]" value="{{ $name }}" aria-label="Criterion name" required><input class="ms-input" type="number" name="criteria[{{ $loop->index }}][points]" value="{{ $points }}" aria-label="Criterion points" min="1" required><small>points</small><button type="button" class="cp-remove" data-remove aria-label="Remove criterion"><x-project-icon name="trash"/></button></div>
                        @endforeach
                    </div><x-button variant="secondary" data-add="criterion">＋ Add criterion</x-button></fieldset>
                    <fieldset><legend>Submission policy</legend>
                        <label class="cp-choice"><input type="checkbox" name="resubmit" checked><span><strong>Edit and resubmit until the deadline</strong><small>Every submitted version remains in history.</small></span></label>
                        <label class="cp-choice"><input type="checkbox" name="reminders" checked><span><strong>Automatic reminders</strong><small>Send reminders 3 days and 1 day before each deadline.</small></span></label>
                    </fieldset>
                    @break
                @case(3)
                    <div class="cp-fields">
                        <label>Minimum team size<input class="ms-input" type="number" name="team_min" min="1" value="2" required></label><label>Maximum team size<input class="ms-input" type="number" name="team_max" min="1" value="4" required></label>
                        <label>How teams are formed<select class="ms-input" name="formation"><option>Students choose</option><option>Professor assigns</option><option>Random</option></select></label><label>Team-choosing deadline<input class="ms-input" type="date" name="team_deadline" value="2026-10-11" required></label>
                    </div>
                    <fieldset><legend>When the deadline passes</legend><label class="cp-choice"><input type="radio" name="leftover" value="Random placement" checked><span><strong>Auto-place randomly</strong><small>Fill teams with available space</small></span></label><label class="cp-choice"><input type="radio" name="leftover" value="Professor decides"><span><strong>Professor decides</strong><small>Keep unassigned students in your queue</small></span></label></fieldset>
                    <div class="cp-notice"><x-project-icon name="users"/><div><strong>Team leaders are assigned by the professor or TA</strong><p>Students do not vote for team leaders.</p></div></div>
                    @break
                @case(4)
                    <div class="cp-upload cp-upload--loaded"><x-project-icon name="check"/><div><strong data-roster-name>cs490-students.csv · sample roster</strong><small data-roster-label>48 students · preview data</small></div><label class="cp-file-button">Replace CSV<input type="file" name="roster" accept=".csv,text/csv" data-roster></label></div>
                    <div class="cp-invite"><span class="cp-icon cp-icon--green">SA</span><div><strong>Sara Ahmed</strong><small>sara.ahmed@university.edu</small></div><x-badge tone="success">Invite ready</x-badge></div>
                    <div class="cp-notice cp-notice--violet"><x-project-icon name="users"/><div><strong>Maximum two TAs per project</strong><p>Invitations expire after 7 days. Students can view TA feedback.</p></div><x-badge tone="review">1 of 2</x-badge></div>
                    <fieldset><legend>Review permission</legend><label class="cp-choice"><input type="radio" name="permission" value="Comment only"><span><strong>Comment only</strong><small>Can review submissions and leave visible feedback</small></span></label><label class="cp-choice"><input type="radio" name="permission" value="Full review" checked><span><strong>Full review</strong><small>Can comment, review, and recommend scores—but never assign final grades</small></span></label></fieldset>
                    @break
                @case(5)
                    <dl class="cp-summary">@foreach(['Course','Project','Milestones','Teams','TA','Grading'] as $label)<div><dt>{{ $label }}</dt><dd data-summary="{{ strtolower($label) }}"></dd></div>@endforeach</dl>
                    <div class="cp-notice"><x-project-icon name="bell"/><div><strong>Publishing sends invitations</strong><p>Students can begin forming teams immediately. This action requires confirmation.</p></div></div>
                    @break
                @endswitch
            </section>
            @endforeach
            <footer class="cp-actions"><x-button variant="secondary" data-back disabled>Back</x-button><x-button data-next>Continue <span aria-hidden="true">›</span></x-button></footer>
        </form>
        <aside class="cp-readiness cp-panel" aria-label="Setup progress"><div class="cp-readiness-header"><div><h2>Readiness</h2><p>Updates as you<br>configure</p></div><div class="cp-ring" role="progressbar" aria-label="Setup progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="17"><span data-progress>17%</span></div></div>
            <ul>@foreach(['Basics complete','3 milestones · 100%','Rubric · 50 points','Teams · 2–4 members','48 students · 1 TA','Ready to publish'] as $item)<li data-readiness><span aria-hidden="true">◷</span><span>{{ $item }}</span></li>@endforeach</ul>
            <div class="cp-readiness-note"><strong>Academic project</strong><p>Exactly one professor · Final grades are professor-only.</p></div>
        </aside>
    </div>
    <p class="cp-status" role="status" data-status></p>
    <dialog class="cp-dialog"><h2>Preview publishing</h2><p>Backend publishing is not connected yet. No invitations will be sent. You can download the configured project as JSON for backend integration.</p><div class="ms-row"><x-button variant="secondary" data-close>Keep editing</x-button><x-button data-download>Download project draft</x-button></div></dialog>
</div>
@endsection
