@extends('layouts.app')

@section('title', $task['name'])

@push('styles')
    @vite('resources/css/student-tasks.css')
@endpush

@section('page')
    <main id="main" class="tasks-page"><div class="tasks-container task-detail">
        <a class="tasks-back" href="{{ route('student.tasks.index') }}">← Back to tasks</a>
        <header class="tasks-heading"><div><div class="task-title-badges"><i class="task-badge task-badge--{{ $task['mode'] === 'Team' ? 'info' : 'success' }}">{{ $task['mode'] }}</i><i class="task-badge task-badge--{{ $submitted ? 'review' : $task['tone'] }}">{{ $submitted ? 'In review' : $task['status'] }}</i></div><h1>{{ $task['name'] }}</h1><p>{{ $task['project'] }} · Due {{ $task['due'] }}</p></div></header>
        @if (session('status'))<p class="task-flash" role="status">{{ session('status') }}</p>@endif
        @if ($errors->has('submission'))<p class="task-error" role="alert">{{ $errors->first('submission') }}</p>@endif
        <div class="task-detail-grid"><div class="task-detail-main">
            <section class="task-card"><h2>Instructions</h2><p>Complete this task using the required submission format. Your work should be clear enough for the teaching assistant to review without additional context.</p><h3>Acceptance criteria</h3><form id="task-submit" method="post" action="{{ route('student.tasks.submit', $task['id']) }}" enctype="multipart/form-data">@csrf
                @foreach (['The submission addresses every requirement in the task brief.','Files and links open correctly and use clear names.','Team members reviewed the final version before submission.'] as $criterion)<label class="task-check"><input type="checkbox" name="criteria[]" value="yes" {{ in_array('yes', old('criteria', $draft['criteria'] ?? []), true) ? 'checked' : '' }} {{ $submitted ? 'disabled' : '' }}><span>{{ $criterion }}</span></label>@endforeach
                <div class="task-road"><span class="is-done">To do</span><b></b><span class="is-done">In progress</span><b></b><span class="{{ $submitted ? 'is-current' : '' }}">In review</span><b></b><span>Done</span></div>
                <section class="task-submit-card"><div class="task-card-head"><div><h2>Your submission</h2><p>{{ $task['type'] }} · Draft saves are private until submitted</p></div>@if ($submitted)<i class="task-badge task-badge--review">Locked for TA review</i>@endif</div>
                    @if ($task['type'] === 'File upload')
                        <label class="task-upload"><input type="file" name="files[]" multiple {{ $submitted ? 'disabled' : '' }}><strong>Choose files for your submission</strong><span>PDF, DOCX, PPTX, ZIP, images, and code files · 25 MB maximum</span></label>
                    @elseif ($task['type'] === 'Link' || $task['type'] === 'Code repository')
                        <label class="task-field"><span>{{ $task['type'] === 'Link' ? 'Project URL' : 'GitHub repository URL' }}</span><input type="url" name="url" value="{{ old('url', $draft['url'] ?? '') }}" placeholder="https://github.com/team/project" {{ $submitted ? 'disabled' : '' }}></label>
                        @if ($task['type'] === 'Code repository')<div class="task-fields"><label class="task-field"><span>Branch</span><input name="branch" value="{{ old('branch', $draft['branch'] ?? 'main') }}" {{ $submitted ? 'disabled' : '' }}></label><label class="task-field"><span>Commit hash</span><input name="commit" value="{{ old('commit', $draft['commit'] ?? '') }}" placeholder="e.g. 8d2f4a1" {{ $submitted ? 'disabled' : '' }}></label></div>@endif
                    @elseif ($task['type'] === 'Written answer')
                        <label class="task-field"><span>Written response</span><textarea name="answer" rows="7" placeholder="Explain your architectural choices and the trade-offs your team considered…" {{ $submitted ? 'disabled' : '' }}>{{ old('answer', $draft['answer'] ?? '') }}</textarea></label><small>Minimum 20 words.</small>
                    @else
                        <div class="task-required-items">@foreach (['Research report PDF','Presentation deck','Source files archive'] as $item)<label class="task-check"><input type="checkbox" name="items[]" value="{{ $loop->index }}" {{ in_array((string)$loop->index, old('items', $draft['items'] ?? []), true) ? 'checked' : '' }} {{ $submitted ? 'disabled' : '' }}><span>{{ $item }}</span></label>@endforeach</div>
                    @endif
                    @unless ($submitted)<div class="task-actions"><button class="ms-button ms-button--secondary" type="submit" formaction="{{ route('student.tasks.draft', $task['id']) }}" formmethod="post">Save draft</button><button class="ms-button ms-button--primary" type="submit">Submit for review</button></div>@else<p class="task-locked-note">Your submission is locked while the TA reviews it.</p>@endunless
                </section>
            </form></section>
        </div><aside class="task-aside"><section class="task-card"><h2>Task details</h2><dl><div><dt>Assignee</dt><dd>{{ $task['assignee'] }}</dd></div><div><dt>Priority</dt><dd>{{ $task['priority'] }}</dd></div><div><dt>Dependency</dt><dd>{{ $task['dependency'] }}</dd></div><div><dt>Reviewer</dt><dd>{{ $task['reviewer'] }}</dd></div></dl></section><section class="task-notice"><strong>Tasks go to the TA</strong><p>Official milestone submissions are reviewed and graded by the professor.</p></section></aside></div>
    </div></main>
@endsection
