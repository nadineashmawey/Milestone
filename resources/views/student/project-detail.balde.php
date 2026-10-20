@extends('layouts.base')


@section('title', $project['title'])


@push('styles')
    @vite('resources/css/student-projects.css')
@endpush


@push('scripts')
    @vite('resources/js/student-project-board.js')
@endpush


@section('body')
    <main class="detail-page" data-project-board>
        <div class="detail-container">
            <a class="back-link" href="{{ route('student.projects') }}"><span aria-hidden="true">←</span> My Projects</a>
            <header class="detail-heading">
                <div>
                    <p class="projects-eyebrow">{{ $project['course'] }}@if($project['team']) · {{ $project['team'] }}@endif</p>
                    <h1>{{ $project['title'] }}</h1>
                    <p class="projects-intro">{{ $project['due'] }}</p>
                </div>
            </header>


            <nav class="project-tabs" aria-label="Project sections" role="tablist">
                <button class="project-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="board-panel" data-tab="board">Board</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="formation-panel" data-tab="formation">Formation</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="submit-panel" data-tab="submit">Submit</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="grades-panel" data-tab="grades">Grades</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="chat-panel" data-tab="chat">Chat</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="files-panel" data-tab="files">Files</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="calendar-panel" data-tab="calendar">Calendar</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="risk-panel" data-tab="risk">Risk</button>
                <button class="project-tab" type="button" role="tab" aria-selected="false" aria-controls="activity-panel" data-tab="activity">Activity</button>
            </nav>


            <section class="detail-panel" id="board-panel" role="tabpanel" data-panel="board">
                <div class="board-summary">
                    <div class="board-summary__info"><p class="projects-eyebrow">Project board</p><h2><span data-complete-count>1</span> of {{ count($project['tasks']) }} tasks complete</h2><div class="summary-progress"><span data-summary-progress style="width: {{ round(count(array_filter(project['tasks'],fn(task) => task['status']==='Done'))/max(count(project['tasks']), 1) * 100) }}%"></span></div><p class="board-meta"><span data-summary-percent>25</span>% complete · 0 unassigned · 1 blocked</p></div>
                    <div class="board-summary__actions"><label for="board-view">Prototype permission view</label><select id="board-view" class="ms-input"><option>Aya · Team Leader</option><option>Malek · Team Member</option></select><button class="ms-button ms-button--primary" type="button" data-add-task>＋ Create Task</button><span class="board-summary__hint">Task creation permissions</span></div>
                </div>


                <div class="paper-stage">
                    <div class="paper-sheet">
                        <span class="pushpin" aria-hidden="true"><span></span></span>
                        <div class="board-columns">
                            @foreach (['To do', 'In progress', 'Review', 'Done'] as $status)
                                <section class="board-column" data-column="{{ $status }}" aria-label="{{ $status }} tasks">
                                    <header><h3>{{ $status }}</h3><span data-column-count>{{ count(array_filter(project['tasks'],fn(task) => $task['status'] === $status)) }}</span></header>
                                    <div class="task-list">
                                        @foreach ($project['tasks'] as $task)
                                            @if ($task['status'] === $status)
                                                <article class="task-card" data-task>
                                                    <div class="task-card__top"><h4>{{ $task['title'] }}</h4><span class="priority priority--{{ strtolower($task['priority']) }}">{{ $task['priority'] }}</span></div>
                                                    <p class="task-description">{{ $task['description'] }}</p>
                                                    <div class="task-owner"><span class="avatar">{{ $task['initials'] }}</span><span><strong>{{ $task['owner'] }}</strong><small>{{ $task['initials'] === 'AH' ? 'Team Leader' : 'Team Member' }}</small></span></div>
                                                    <div class="task-due"><span>Due {{ $task['due'] }}</span>@if(!empty($task['flag']))<span class="task-flag {{ $task['flag'] === 'Blocked' ? 'task-flag--blocked' : '' }}">{{ $task['flag'] }}</span>@endif</div>
                                                    <div class="task-actions"><button type="button" data-move="back" @disabled($status === 'To do')>‹ <span>Move Back</span></button><button type="button" data-move="forward" @disabled($status === 'Done')><span>Move Forward</span> ›</button></div>
                                                </article>
                                            @endif
                                        @endforeach
                                    </div>
                                    <button type="button" class="add-task-link" data-add-task>＋ Add task</button>
                                </section>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>


            <section class="detail-panel detail-panel--secondary" id="formation-panel" role="tabpanel" data-panel="formation" hidden><p class="projects-eyebrow">Team formation</p><h2>Your project team</h2><p>Project members and roles will appear here.</p></section>
            <section class="detail-panel detail-panel--secondary" id="submit-panel" role="tabpanel" data-panel="submit" hidden><p class="projects-eyebrow">Submissions</p><h2>Project submissions</h2><p>Submission milestones and files will appear here.</p></section>
            <section class="detail-panel detail-panel--secondary" id="grades-panel" role="tabpanel" data-panel="grades" hidden><p class="projects-eyebrow">Evaluation</p><h2>{{ $project['kind'] === 'Academic' ? 'Grades & feedback' : 'Independent project' }}</h2><p>{{ $project['kind'] === 'Academic' ? 'Grades and instructor feedback for this project will appear here.' : 'Independent projects are not graded.' }}</p></section>
            @foreach (['chat' => 'Project chat', 'files' => 'Project files', 'calendar' => 'Project calendar', 'risk' => 'Project risks', 'activity' => 'Recent activity'] as $key => $label)
                <section class="detail-panel detail-panel--secondary" id="{{ $key }}-panel" role="tabpanel" data-panel="{{ $key }}" hidden><p class="projects-eyebrow">{{ $label }}</p><h2>{{ $label }}</h2><p>Project {{ strtolower($label) }} will appear here.</p></section>
            @endforeach
        </div>
    </main>
@endsection
