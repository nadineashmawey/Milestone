@extends('layouts.professor')
@section('title', $project ? 'Edit project' : 'Create Project')
@section('content')
@php
    use App\Http\Requests\StoreProjectRequest;

    $steps = ['Basics' => 'Identity and goals', 'Milestones' => 'Weighted deliverables', 'Rubric & grading' => 'Points and policies', 'Teams' => 'Formation rules', 'People & TA' => 'Roster and reviewers', 'Review' => 'Check and publish'];
    $headings = [['Project details', 'Start from scratch or let Milestone draft from your brief.', 'blue', 'folder'], ['Milestones', 'Create a clear path from proposal to final demo.', 'violet', 'calendar'], ['Rubric and grading', 'Define how the professor will evaluate and release final grades.', 'violet', 'chart'], ['Team formation', 'Set fair boundaries while students form their teams.', 'yellow', 'users'], ['People and teaching assistants', 'Import the roster and invite up to two reviewers.', 'green', 'users'], ['Ready to publish', 'Review every choice before inviting students.', 'blue', 'check']];

    // Values: what was just submitted, else the draft being edited, else the wizard's starting example.
    $settings = $project?->settings ?? [];
    $choiceFor = fn (array $types) => array_search($types, StoreProjectRequest::DELIVERABLE_CHOICES, true) ?: 'Mixed';
    $field = fn (string $name, mixed $default = null) => old($name, $default);
    $goals = old('goals', $project ? ($project->goals ?: ['']) : ['Apply an iterative software engineering process', 'Document technical decisions and evaluate the final product']);
    $milestones = old('milestones', $project
        ? $project->milestones->map(fn ($m) => ['name' => $m->name, 'deadline' => $m->due_at?->format('Y-m-d'), 'deliverable' => $choiceFor($m->deliverable_types ?? []), 'weight' => $m->weight])->all()
        : [['name' => 'Proposal', 'deadline' => '2026-10-11', 'deliverable' => 'PDF report', 'weight' => 20], ['name' => 'Design Document', 'deadline' => '2026-10-18', 'deliverable' => 'Mixed', 'weight' => 35], ['name' => 'Final Demo', 'deadline' => '2026-11-08', 'deliverable' => 'Demo video link', 'weight' => 45]]);
    $criteria = old('criteria', $project
        ? $project->criteria->map(fn ($c) => ['name' => $c->name, 'points' => $c->max_points])->all()
        : [['name' => 'Research and rationale', 'points' => 10], ['name' => 'Technical quality', 'points' => 20], ['name' => 'Collaboration', 'points' => 10], ['name' => 'Presentation', 'points' => 10]]);
    // The list templates need one row, so an emptied list comes back with one blank row.
    $goals = $goals ?: [''];
    $milestones = array_values($milestones ?: [['name' => '', 'deadline' => '', 'deliverable' => 'PDF report', 'weight' => '']]);
    $criteria = array_values($criteria ?: [['name' => '', 'points' => '']]);
    $semesters = $courses->pluck('semester')->unique()->values();
    $selectedCourse = (string) $field('course', $project?->course_id);
    $leftover = $field('leftover', $project ? ($project->leftover_mode === 'manual' ? 'Professor decides' : 'Random placement') : 'Random placement');
    $permission = $field('permission', $settings['ta_permission'] ?? 'Full review');
    $checked = fn (string $name, bool $default) => old('_token') ? (bool) old($name) : ($project ? (bool) ($settings[$name] ?? false) : $default);

    // Open the wizard on the first step with a server error.
    $stepOf = fn (string $key) => match (true) {
        str_starts_with($key, 'milestones') => 1,
        str_starts_with($key, 'grade'), str_starts_with($key, 'criteria'), str_starts_with($key, 'late_policy') => 2,
        str_starts_with($key, 'team_'), str_starts_with($key, 'formation'), str_starts_with($key, 'leftover') => 3,
        str_starts_with($key, 'permission') => 4,
        default => 0,
    };
    $initialStep = $errors->any() ? min(array_map($stepOf, $errors->keys())) : 0;
    $invalid = fn (string $key) => $errors->has($key) ? 'aria-invalid=true aria-describedby='.str_replace('.', '-', $key).'-error' : '';
@endphp
<div class="create-project" data-project-wizard data-initial-step="{{ $initialStep }}">
    <header class="cp-header">
        <div><p class="cp-eyebrow">{{ $project ? 'Draft academic project' : 'New academic project' }}</p><h1>{{ $project ? 'Edit and publish your project' : 'Create a supervised project' }}</h1><p class="ms-muted">Build the complete project structure before students begin.</p></div>
        <x-button data-publish hidden>Publish project</x-button>
    </header>
    <nav class="cp-steps" aria-label="Project setup steps">
        @foreach($steps as $label => $description)
            <button type="button" data-step="{{ $loop->index }}" @if($loop->first) aria-current="step" @endif><span class="cp-step-number">{{ $loop->iteration }}</span><span><strong>{{ $label }}</strong><small>{{ $description }}</small></span></button>
        @endforeach
    </nav>
    <div class="cp-layout">
        <form class="cp-panel" id="cp-form" method="POST" action="{{ $project ? route('professor.projects.update', $project) : route('professor.projects.store') }}" data-project-form novalidate>
            @csrf
            @if ($project) @method('PUT') @endif
            {{-- One key per page load: a double-click or a resend opens the project the first click created. --}}
            <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
            <p class="cp-error" data-error role="alert" @unless($errors->any()) hidden @endunless>@if ($errors->any())We couldn’t {{ old('intent') === 'draft' ? 'save the draft' : 'publish the project' }}. Fix the {{ $errors->count() === 1 ? 'highlighted field' : $errors->count().' highlighted fields' }}: {{ $errors->first() }}@endif</p>
            @foreach($headings as [$heading, $description, $tone, $icon])
            <section data-panel="{{ $loop->index }}" @if(!$loop->first) hidden @endif aria-labelledby="cp-heading-{{ $loop->index }}">
                <div class="cp-heading"><span class="cp-icon cp-icon--{{ $tone }}"><x-project-icon :name="$icon" /></span><div><h2 id="cp-heading-{{ $loop->index }}" tabindex="-1">{{ $heading }}</h2><p>{{ $description }}</p></div></div>
                @switch($loop->index)
                @case(0)
                    <div class="cp-upload"><x-project-icon name="upload"/><div><strong>Brief-to-Project</strong><small data-brief-label>Upload a PDF brief to pre-fill milestones, rubric, and deadlines</small></div><label class="cp-file-button">Choose PDF<input type="file" name="brief" accept="application/pdf,.pdf" data-brief></label></div>
                    <div class="cp-fields">
                        <label>Project title<input class="ms-input" name="title" placeholder="e.g. Community Impact Platform" maxlength="200" required value="{{ $field('title', $project?->title) }}" {!! $invalid('title') !!}>@error('title')<small class="cp-field-error" id="title-error">{{ $message }}</small>@enderror</label>
                        <label>Project color<select class="ms-input" name="color">@foreach(['Royal blue', 'Violet', 'Green', 'Yellow'] as $color)<option @selected($field('color', $settings['color'] ?? null) === $color)>{{ $color }}</option>@endforeach</select></label>
                        <label>Course<select class="ms-input" name="course" required {!! $invalid('course') !!}>@forelse($courses as $course)<option value="{{ $course->id }}" @selected($selectedCourse === (string) $course->id)>{{ $course->label() }}</option>@empty<option value="">No courses assigned to you yet</option>@endforelse</select>@error('course')<small class="cp-field-error" id="course-error">{{ $message }}</small>@enderror</label>
                        <label>Semester<select class="ms-input" name="semester" {!! $invalid('semester') !!}>@foreach($semesters as $semester)<option @selected($field('semester', $project?->course?->semester) === $semester)>{{ $semester }}</option>@endforeach</select>@error('semester')<small class="cp-field-error" id="semester-error">{{ $message }}</small>@enderror</label>
                        <label class="cp-full">Description<textarea class="ms-input" name="description" placeholder="Give students a concise project overview." rows="4" {!! $invalid('description') !!}>{{ $field('description', $project?->description) }}</textarea>@error('description')<small class="cp-field-error" id="description-error">{{ $message }}</small>@enderror</label>
                    </div>
                    <fieldset><legend>Project goals</legend><div data-goals class="cp-list">
                        @foreach($goals as $goal)
                        <div class="cp-goal" data-row><input class="ms-input" name="goals[]" aria-label="Project goal" value="{{ $goal }}"><button type="button" class="cp-remove" data-remove aria-label="Remove goal"><x-project-icon name="trash"/></button></div>
                        @endforeach
                    </div><x-button variant="secondary" data-add="goal">＋ Add goal</x-button></fieldset>
                    @break
                @case(1)
                    <div class="cp-weight"><small>Milestone weight</small><span class="ms-badge ms-badge--success" data-weight>100% total</span></div>
                    @error('milestones')<p class="cp-field-error" id="milestones-error">{{ $message }}</p>@enderror
                    <div class="cp-list" data-milestones>
                        @foreach($milestones as $i => $milestone)
                        <div class="cp-milestone" data-row>
                            <span class="cp-icon cp-icon--{{ ['blue','violet','yellow'][$loop->index % 3] }}" data-milestone-number>M{{ $loop->iteration }}</span>
                            <label>Name<input class="ms-input" name="milestones[{{ $i }}][name]" value="{{ $milestone['name'] ?? '' }}" required {!! $invalid("milestones.$i.name") !!}>@error("milestones.$i.name")<small class="cp-field-error" id="milestones-{{ $i }}-name-error">{{ $message }}</small>@enderror</label>
                            <label>Deadline<input class="ms-input" type="date" name="milestones[{{ $i }}][deadline]" value="{{ $milestone['deadline'] ?? '' }}" required {!! $invalid("milestones.$i.deadline") !!}>@error("milestones.$i.deadline")<small class="cp-field-error" id="milestones-{{ $i }}-deadline-error">{{ $message }}</small>@enderror</label>
                            <label>Deliverable<select class="ms-input" name="milestones[{{ $i }}][deliverable]" {!! $invalid("milestones.$i.deliverable_types") !!}>@foreach(array_keys(StoreProjectRequest::DELIVERABLE_CHOICES) as $option)<option @selected($option === ($milestone['deliverable'] ?? 'PDF report'))>{{ $option }}</option>@endforeach</select>@error("milestones.$i.deliverable_types")<small class="cp-field-error" id="milestones-{{ $i }}-deliverable_types-error">{{ $message }}</small>@enderror</label>
                            <label>Weight %<input class="ms-input" type="number" name="milestones[{{ $i }}][weight]" value="{{ $milestone['weight'] ?? '' }}" min="1" max="100" required data-weight-input {!! $invalid("milestones.$i.weight") !!}>@error("milestones.$i.weight")<small class="cp-field-error" id="milestones-{{ $i }}-weight-error">{{ $message }}</small>@enderror</label>
                            <button type="button" class="cp-remove" data-remove aria-label="Remove milestone"><x-project-icon name="trash"/></button>
                        </div>
                        @endforeach
                    </div><x-button variant="secondary" data-add="milestone">＋ Add milestone</x-button>
                    @break
                @case(2)
                    <div class="cp-fields"><label>Total project grade<input class="ms-input" type="number" name="grade" value="{{ $field('grade', $project ? $project->total_grade : 50) }}" min="1" required {!! $invalid('grade') !!}>@error('grade')<small class="cp-field-error" id="grade-error">{{ $message }}</small>@enderror</label><label>Late policy<select class="ms-input" name="late_policy">@foreach(StoreProjectRequest::LATE_POLICIES as $policy)<option @selected($field('late_policy', $settings['late_policy'] ?? null) === $policy)>{{ $policy }}</option>@endforeach</select></label></div>
                    <fieldset><legend>Rubric criteria</legend>@error('criteria')<p class="cp-field-error" id="criteria-error">{{ $message }}</p>@enderror<div class="cp-list" data-criteria>
                        @foreach($criteria as $i => $criterion)
                        <div class="cp-criterion" data-row><input class="ms-input" name="criteria[{{ $i }}][name]" value="{{ $criterion['name'] ?? '' }}" aria-label="Criterion name" required {!! $invalid("criteria.$i.name") !!}><input class="ms-input" type="number" name="criteria[{{ $i }}][points]" value="{{ $criterion['points'] ?? '' }}" aria-label="Criterion points" min="1" required {!! $invalid("criteria.$i.points") !!}><small>points</small><button type="button" class="cp-remove" data-remove aria-label="Remove criterion"><x-project-icon name="trash"/></button></div>
                        @endforeach
                    </div><x-button variant="secondary" data-add="criterion">＋ Add criterion</x-button></fieldset>
                    <fieldset><legend>Submission policy</legend>
                        <label class="cp-choice"><input type="checkbox" name="resubmit" value="1" @checked($checked('resubmit', true))><span><strong>Edit and resubmit until the deadline</strong><small>Every submitted version remains in history.</small></span></label>
                        <label class="cp-choice"><input type="checkbox" name="reminders" value="1" @checked($checked('reminders', true))><span><strong>Automatic reminders</strong><small>Send reminders 3 days and 1 day before each deadline.</small></span></label>
                    </fieldset>
                    @break
                @case(3)
                    <div class="cp-fields">
                        <label>Minimum team size<input class="ms-input" type="number" name="team_min" min="1" value="{{ $field('team_min', $project ? $project->team_min : 2) }}" required {!! $invalid('team_min') !!}>@error('team_min')<small class="cp-field-error" id="team_min-error">{{ $message }}</small>@enderror</label><label>Maximum team size<input class="ms-input" type="number" name="team_max" min="1" value="{{ $field('team_max', $project ? $project->team_max : 4) }}" required {!! $invalid('team_max') !!}>@error('team_max')<small class="cp-field-error" id="team_max-error">{{ $message }}</small>@enderror</label>
                        <label>How teams are formed<select class="ms-input" name="formation">@foreach(StoreProjectRequest::FORMATION as $formation)<option @selected($field('formation', $settings['formation'] ?? null) === $formation)>{{ $formation }}</option>@endforeach</select></label><label>Team-choosing deadline<input class="ms-input" type="date" name="team_deadline" value="{{ $field('team_deadline', $project ? $project->formation_deadline?->format('Y-m-d') : '2026-10-04') }}" required {!! $invalid('team_deadline') !!}>@error('team_deadline')<small class="cp-field-error" id="team_deadline-error">{{ $message }}</small>@enderror</label>
                    </div>
                    <fieldset><legend>When the deadline passes</legend><label class="cp-choice"><input type="radio" name="leftover" value="Random placement" @checked($leftover === 'Random placement')><span><strong>Auto-place randomly</strong><small>Fill teams with available space</small></span></label><label class="cp-choice"><input type="radio" name="leftover" value="Professor decides" @checked($leftover === 'Professor decides')><span><strong>Professor decides</strong><small>Keep unassigned students in your queue</small></span></label></fieldset>
                    <div class="cp-notice"><x-project-icon name="users"/><div><strong>Team leaders are assigned by the professor or TA</strong><p>Students do not vote for team leaders.</p></div></div>
                    @break
                @case(4)
                    <div class="cp-upload cp-upload--loaded"><x-project-icon name="check"/><div><strong data-roster-name>cs490-students.csv · sample roster</strong><small data-roster-label>48 students · preview data</small></div><label class="cp-file-button">Replace CSV<input type="file" name="roster" accept=".csv,text/csv" data-roster></label></div>
                    <div class="cp-invite"><span class="cp-icon cp-icon--green">SA</span><div><strong>Sara Ahmed</strong><small>sara.ahmed@university.edu</small></div><x-badge tone="success">Invite ready</x-badge></div>
                    <div class="cp-notice cp-notice--violet"><x-project-icon name="users"/><div><strong>Maximum two TAs per project</strong><p>Invitations expire after 7 days. Students can view TA feedback.</p></div><x-badge tone="review">1 of 2</x-badge></div>
                    <fieldset><legend>Review permission</legend><label class="cp-choice"><input type="radio" name="permission" value="Comment only" @checked($permission === 'Comment only')><span><strong>Comment only</strong><small>Can review submissions and leave visible feedback</small></span></label><label class="cp-choice"><input type="radio" name="permission" value="Full review" @checked($permission === 'Full review')><span><strong>Full review</strong><small>Can comment, review, and recommend scores—but never assign final grades</small></span></label></fieldset>
                    @break
                @case(5)
                    <dl class="cp-summary">@foreach(['Course','Project','Milestones','Teams','TA','Grading'] as $label)<div><dt>{{ $label }}</dt><dd data-summary="{{ strtolower($label) }}"></dd></div>@endforeach</dl>
                    <div class="cp-notice"><x-project-icon name="bell"/><div><strong>Publishing sends invitations</strong><p>Students can begin forming teams immediately. This action requires confirmation.</p></div></div>
                    @break
                @endswitch
            </section>
            @endforeach
            <footer class="cp-actions">
                <x-button variant="secondary" data-back disabled>Back</x-button>
                <span class="cp-actions-end">
                    <x-button variant="ghost" :href="$project ? route('professor.projects.show', $project) : route('professor.projects.index')" data-cancel>Cancel</x-button>
                    <x-button variant="secondary" type="submit" name="intent" value="draft" formnovalidate data-save-draft>Save as draft</x-button>
                    <x-button data-next>Continue <span aria-hidden="true">›</span></x-button>
                </span>
            </footer>
        </form>
        <aside class="cp-readiness cp-panel" aria-label="Setup progress"><div class="cp-readiness-header"><div><h2>Readiness</h2><p>Updates as you<br>configure</p></div><div class="cp-ring" role="progressbar" aria-label="Setup progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="17"><span data-progress>17%</span></div></div>
            <ul>@foreach(['Basics complete','3 milestones · 100%','Rubric · 50 points','Teams · 2–4 members','48 students · 1 TA','Ready to publish'] as $item)<li data-readiness><span aria-hidden="true">◷</span><span>{{ $item }}</span></li>@endforeach</ul>
            <div class="cp-readiness-note"><strong>Academic project</strong><p>Exactly one professor · Final grades are professor-only.</p></div>
        </aside>
    </div>
    <p class="cp-status" role="status" data-status></p>
    <dialog class="cp-dialog" aria-labelledby="cp-dialog-title"><h2 id="cp-dialog-title">Publish this project?</h2><p data-confirm-text>Students in the course are invited and can start forming teams. You can’t edit the milestones afterwards from this wizard.</p><div class="ms-row"><x-button variant="secondary" data-close>Keep editing</x-button><x-button type="submit" form="cp-form" name="intent" value="publish" data-confirm-publish>Publish project</x-button></div></dialog>
</div>
@endsection
