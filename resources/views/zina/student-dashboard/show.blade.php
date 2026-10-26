@extends('layouts.app-shell', ['shellRole' => 'student', 'shellActive' => 'dashboard', 'shellStates' => true])

@section('title', 'Student dashboard')

@push('styles')
    @vite('resources/css/pages/student-dashboard.css')
@endpush

@section('prototype-controls')
    @include('zina.partials.context-switch', [
        'field' => 'viewer',
        'label' => 'Viewing as',
        'options' => ['aya' => 'Aya · team leader', 'malek' => 'Malek · team member'],
        'current' => $context['viewer'],
    ])
@endsection

@section('content')
    @php($d = $dashboard)
    <div class="as-page sd">
        @if ($state === 'loading')
            @include('zina.partials.state-loading', ['label' => 'Loading your dashboard', 'blocks' => 4])
        @elseif ($state === 'error')
            @include('zina.partials.state-error', ['title' => 'We couldn’t load your dashboard.'])
        @else
            <header class="as-head">
                <div>
                    <p class="as-eyebrow">{{ $d['today'] }}</p>
                    <h1>{{ $d['greeting'] }}, {{ $d['viewer']['first'] }}</h1>
                    <p class="as-head-lede">{{ $d['summary'] }}</p>
                </div>
            </header>

            @if ($state === 'empty')
                <section class="as-empty" aria-labelledby="sd-empty-title">
                    <span class="as-empty-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'folder', 'size' => 24])</span>
                    <h2 id="sd-empty-title">You’re not in any projects yet.</h2>
                    <p>Join a course project with the code your professor shared, or start an independent project and invite up to nine teammates.</p>
                    <div class="as-empty-actions">
                        <x-button :href="$joinHref">Join with a code</x-button>
                        <x-button variant="secondary" :href="$createHref">@include('public.partials.icon', ['name' => 'plus', 'size' => 18]) Create an independent project</x-button>
                    </div>
                    <p class="as-note">Tasks, deadlines and grades show up here once you’re on a team.</p>
                </section>
            @else
                {{-- DSH-1: things only the student can unblock, each linking to where it's fixed. --}}
                <section class="as-panel sd-actions" aria-labelledby="sd-actions-title">
                    <div class="as-panel-head">
                        <div>
                            <h2 id="sd-actions-title">Needs your action</h2>
                            <p>Changes requested, overdue work and pending requests.</p>
                        </div>
                        <x-badge :tone="count($d['actions']) ? 'warning' : 'success'">{{ count($d['actions']) ?: 'No' }} {{ count($d['actions']) === 1 ? 'item' : 'items' }}</x-badge>
                    </div>
                    @if (count($d['actions']))
                        <ul class="as-rows sd-action-list">
                            @foreach ($d['actions'] as $action)
                                <li class="sd-action sd-action--{{ $action['tone'] }}">
                                    <span class="sd-action-icon" aria-hidden="true">@include('public.partials.icon', ['name' => $action['icon'], 'size' => 20])</span>
                                    <div class="sd-action-body">
                                        <span class="sd-action-kind">{{ $action['kind'] }}</span>
                                        <strong>{{ $action['title'] }}</strong>
                                        <p>{{ $action['detail'] }}</p>
                                    </div>
                                    <a class="ms-button ms-button--small sd-action-cta" href="{{ $action['href'] }}">{{ $action['cta'] }}<span class="as-sr">: {{ $action['title'] }}</span></a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="sd-clear">@include('public.partials.icon', ['name' => 'check', 'size' => 18]) You’re all caught up. Nothing needs your action right now.</p>
                    @endif
                </section>

                <div class="sd-grid">
                    <section class="as-panel sd-projects" aria-labelledby="sd-projects-title">
                        <div class="as-panel-head">
                            <div>
                                <h2 id="sd-projects-title">My projects</h2>
                                <p>Academic and independent work</p>
                            </div>
                            <a class="as-text-link" href="{{ $joinHref }}">All projects @include('public.partials.icon', ['name' => 'arrow', 'size' => 16])</a>
                        </div>
                        <ul class="as-rows">
                            @foreach ($d['projects'] as $project)
                                <li class="sd-project">
                                    @include('zina.partials.ring', ['value' => $project['progress'], 'tone' => $project['ring_tone'], 'label' => $project['type'] === 'Academic' ? 'Overall progress' : 'Task progress'])
                                    <div class="sd-project-main">
                                        <div class="as-badges">
                                            @include('zina.partials.project-type', ['type' => $project['type']])
                                            <x-badge :tone="$project['status_tone']">{{ $project['status'] }}</x-badge>
                                        </div>
                                        <h3>{{ $project['title'] }}</h3>
                                        <p class="as-meta">{{ $project['subtitle'] }}</p>
                                        <p class="sd-next">@include('public.partials.icon', ['name' => 'calendar', 'size' => 16]) Next: {{ $project['next'] }} <span class="sd-next-count">· {{ $project['next_countdown']['label'] }}</span></p>
                                    </div>
                                    <a class="as-text-link sd-open" href="{{ $project['href'] }}">Open project<span class="as-sr">: {{ $project['title'] }}</span> @include('public.partials.icon', ['name' => 'arrow', 'size' => 16])</a>
                                </li>
                            @endforeach
                        </ul>
                    </section>

                    <section class="as-panel sd-deadlines" aria-labelledby="sd-deadlines-title">
                        <div class="as-panel-head">
                            <div>
                                <h2 id="sd-deadlines-title">Upcoming deadlines</h2>
                                <p>Nearest first</p>
                            </div>
                        </div>
                        <ol class="as-rows">
                            @foreach ($d['deadlines'] as $deadline)
                                <li>
                                    <a class="sd-deadline" href="{{ $deadline['href'] }}">
                                        <span class="sd-date" aria-hidden="true"><strong>{{ $deadline['day'] }}</strong><small>{{ $deadline['month'] }}</small></span>
                                        <span class="sd-deadline-main">
                                            <strong>{{ $deadline['title'] }}</strong>
                                            <small>{{ $deadline['project']['title'] }} · <time datetime="{{ $deadline['due']->toIso8601String() }}">{{ $deadline['when'] }}</time></small>
                                        </span>
                                        <x-badge :tone="$deadline['countdown']['tone']">{{ $deadline['countdown']['label'] }}</x-badge>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                </div>

                <section class="as-panel sd-tasks" aria-labelledby="sd-tasks-title">
                    <div class="as-panel-head">
                        <div>
                            <h2 id="sd-tasks-title">My upcoming tasks</h2>
                            <p>Ordered by due date. Overdue and Blocked are flags, not columns.</p>
                        </div>
                        <x-badge tone="info">{{ count($d['tasks']) }} open</x-badge>
                    </div>
                    @if (count($d['tasks']))
                        <table class="sd-task-table">
                            <caption class="as-sr">Your upcoming tasks</caption>
                            <thead>
                                <tr><th scope="col">Task</th><th scope="col">Project</th><th scope="col">Due</th><th scope="col">Priority</th><th scope="col"><span class="as-sr">Open</span></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($d['tasks'] as $task)
                                    <tr @class(['is-overdue' => $task['overdue']])>
                                        <th scope="row" class="sd-task-name">
                                            <a href="{{ $task['href'] }}">{{ $task['title'] }}</a>
                                            @if ($task['overdue'] || $task['blocked'])
                                                <span class="sd-flags">
                                                    @if ($task['overdue'])<span class="as-flag as-flag--danger">@include('public.partials.icon', ['name' => 'clock', 'size' => 13]) Overdue</span>@endif
                                                    @if ($task['blocked'])<span class="as-flag as-flag--warning" title="{{ $task['blocked'] }}">@include('public.partials.icon', ['name' => 'lock', 'size' => 13]) Blocked</span>@endif
                                                </span>
                                            @endif
                                            @if ($task['blocked'])<small class="as-meta sd-blocked-why">{{ $task['blocked'] }}</small>@endif
                                        </th>
                                        <td data-label="Project">
                                            <span class="sd-task-project">{{ $task['project']['title'] }}</span>
                                            @include('zina.partials.project-type', ['type' => $task['project']['type']])
                                        </td>
                                        <td data-label="Due"><time datetime="{{ $task['due']->toIso8601String() }}">{{ $task['when'] }}</time></td>
                                        <td data-label="Priority"><x-badge :tone="$task['priority_tone']">{{ $task['priority'] }}</x-badge></td>
                                        <td class="sd-task-go"><a href="{{ $task['href'] }}" class="as-text-link" tabindex="-1" aria-hidden="true">@include('public.partials.icon', ['name' => 'arrow', 'size' => 16])</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <x-empty-state title="No open tasks.">
                            <p>New tasks from your team leader or professor show up here.</p>
                        </x-empty-state>
                    @endif
                </section>

                <section class="as-panel" aria-labelledby="sd-completed-title">
                    <div class="as-panel-head">
                        <div>
                            <h2 id="sd-completed-title">Completed projects</h2>
                            <p>Released grades stay locked. Independent projects have no grade.</p>
                        </div>
                        <x-badge tone="success">{{ count($d['completed']) }} completed</x-badge>
                    </div>
                    <ul class="sd-completed">
                        @foreach ($d['completed'] as $project)
                            <li>
                                <span class="sd-completed-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'check', 'size' => 20])</span>
                                <div>
                                    @include('zina.partials.project-type', ['type' => $project['type']])
                                    <h3>{{ $project['title'] }}</h3>
                                    <p class="as-meta">{{ $project['term'] }}</p>
                                </div>
                                <p class="sd-result">
                                    <strong @class(['is-none' => $project['type'] === 'Independent'])>{{ $project['result'] }}</strong>
                                    <small>{{ $project['note'] }}</small>
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        @endif
    </div>
@endsection
