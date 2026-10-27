@extends('layouts.app')

@section('title', 'Tasks')

@push('styles')
    @vite('resources/css/student-tasks.css')
@endpush

@section('page')
    <main id="main" class="tasks-page"><div class="tasks-container">
        <a class="tasks-back" href="{{ route('projects.index') }}">← My Projects</a>
        <header class="tasks-heading"><div><p class="projects-eyebrow">Student · Work queue</p><h1>Tasks</h1><p>Individual and team work across your active projects.</p></div></header>
        <section class="tasks-panel"><div class="tasks-tools"><div class="tasks-filters" role="group" aria-label="Filter tasks"><button type="button" class="is-active" data-task-filter="all">All tasks</button><button type="button" data-task-filter="Individual">Individual</button><button type="button" data-task-filter="Team">Team</button><button type="button" data-task-filter="week">Due this week</button></div><span>{{ count($tasks) }} open tasks</span></div>
            <div class="task-table" role="table" aria-label="Student tasks"><div class="task-row task-row--head" role="row"><span>Task</span><span>Type</span><span>Mode</span><span>Due date</span><span>Status</span><span></span></div>
                @foreach ($tasks as $task)
                    <a class="task-row" role="row" data-task-mode="{{ $task['mode'] }}" data-task-week="{{ $loop->index < 4 ? 'yes' : 'no' }}" href="{{ route('student.tasks.show', $task['id']) }}"><span><strong>{{ $task['name'] }}</strong><small>{{ $task['project'] }}</small></span><span>{{ $task['type'] }}</span><span><i class="task-badge task-badge--{{ $task['mode'] === 'Team' ? 'info' : 'success' }}">{{ $task['mode'] }}</i></span><time>{{ $task['due'] }}</time><span><i class="task-badge task-badge--{{ $task['tone'] }}">{{ $task['status'] }}</i></span><span aria-hidden="true">→</span></a>
                @endforeach
            </div>
        </section>
    </div></main>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-task-filter]').forEach((button) => button.addEventListener('click', () => {
            document.querySelectorAll('[data-task-filter]').forEach((filter) => filter.classList.toggle('is-active', filter === button));
            document.querySelectorAll('[data-task-mode]').forEach((row) => {
                const mode = button.dataset.taskFilter;
                row.hidden = mode === 'all' ? false : mode === 'week' ? row.dataset.taskWeek !== 'yes' : row.dataset.taskMode !== mode;
            });
        }));
    </script>
@endpush
