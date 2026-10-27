@extends('layouts.app-shell', ['shellStates' => true])

@section('title', 'My projects')

@push('styles')
    @vite('resources/css/pages/professor-projects.css')
@endpush

@section('content')
    @php($filtered = $filters['q'] !== '' || $filters['course'] !== '' || $filters['status'] !== '')
    <div class="as-page pp">
        <header class="as-head">
            <div>
                <p class="as-eyebrow">Academic projects</p>
                <h1>My projects</h1>
                <p class="as-head-lede">Every project you’ve published or saved as a draft, with its next deadline.</p>
            </div>
            <div class="as-head-actions">
                <x-button :href="route('professor.projects.create')">@include('public.partials.icon', ['name' => 'plus', 'size' => 18]) Create project</x-button>
            </div>
        </header>

        @if (session('success'))
            <x-alert tone="success" :title="session('success')" />
        @endif

        @if ($state === 'loading')
            @include('zina.partials.state-loading', ['label' => 'Loading your projects', 'blocks' => 3])
        @elseif ($state === 'error')
            @include('zina.partials.state-error', ['title' => 'We couldn’t load your projects.'])
        @elseif ($state === 'empty' || $total === 0)
            <section class="as-empty" aria-labelledby="pp-empty-title">
                <span class="as-empty-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'folder', 'size' => 24])</span>
                <h2 id="pp-empty-title">You haven’t published a project yet.</h2>
                <p>Set up the milestones, rubric and team rules once. Students in the course are invited when you publish.</p>
                <div class="as-empty-actions">
                    <x-button :href="route('professor.projects.create')">@include('public.partials.icon', ['name' => 'plus', 'size' => 18]) Create project</x-button>
                </div>
            </section>
        @else
            <form class="as-panel pp-filters" method="GET" action="{{ route('professor.projects.index') }}" role="search" aria-label="Filter projects">
                <label class="pp-search">
                    <span>Search by title</span>
                    <input class="ms-input" type="search" name="q" value="{{ $filters['q'] }}" placeholder="e.g. Capstone">
                </label>
                <label>
                    <span>Course</span>
                    <select class="ms-input" name="course">
                        <option value="">All courses</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" @selected($filters['course'] === (string) $course->id)>{{ $course->label() }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Status</span>
                    <select class="ms-input" name="status">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\Project::STATUSES as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    <span>Sort</span>
                    <select class="ms-input" name="sort">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="deadline" @selected($filters['sort'] === 'deadline')>Next deadline</option>
                    </select>
                </label>
                <div class="pp-filter-actions">
                    <x-button type="submit" variant="secondary">Apply</x-button>
                    @if ($filtered)<a class="as-text-link" href="{{ route('professor.projects.index') }}">Clear filters</a>@endif
                </div>
            </form>

            <p class="as-meta" role="status">Showing {{ count($list) }} of {{ $total }} {{ $total === 1 ? 'project' : 'projects' }}</p>

            @if ($list === [])
                <x-empty-state title="No projects match these filters.">
                    <p>Try another title, or clear the filters to see all your projects.</p>
                    <x-slot:action><x-button variant="secondary" :href="route('professor.projects.index')">Clear filters</x-button></x-slot:action>
                </x-empty-state>
            @else
                <ul class="pp-list" role="list">
                    @foreach ($list as $project)
                        <li class="pp-row">
                            <div class="pp-main">
                                <div class="as-badges">
                                    @include('zina.partials.project-type', ['type' => $project['type']])
                                    <x-badge :tone="$project['status_tone']">{{ $project['status'] }}</x-badge>
                                </div>
                                <h2><a href="{{ $project['href'] }}">{{ $project['title'] }}</a></h2>
                                <p class="as-meta">{{ $project['course'] }} · {{ $project['teams'] }} {{ $project['teams'] === 1 ? 'team' : 'teams' }}</p>
                            </div>
                            <dl class="pp-facts">
                                <div>
                                    <dt>Next deadline</dt>
                                    <dd>
                                        @if ($project['next'])
                                            {{ $project['next']['name'] }} · {{ $project['next']['label'] }}
                                            <x-badge :tone="$project['next']['countdown']['tone']">{{ $project['next']['countdown']['label'] }}</x-badge>
                                        @else
                                            None coming up
                                        @endif
                                    </dd>
                                </div>
                                <div class="pp-progress">
                                    <dt>Progress</dt>
                                    <dd><strong>{{ $project['progress'] }}%</strong><span class="as-bar" aria-hidden="true"><span style="width: {{ $project['progress'] }}%"></span></span></dd>
                                </div>
                                <div>
                                    <dt>Published</dt>
                                    <dd>{{ $project['published'] ?? 'Not yet · draft' }}</dd>
                                </div>
                            </dl>
                            <div class="pp-actions">
                                @if ($project['edit_href'])
                                    <x-button size="small" :href="$project['edit_href']">Edit and publish<span class="as-sr">: {{ $project['title'] }}</span></x-button>
                                @endif
                                <a class="as-text-link" href="{{ $project['href'] }}">Open project<span class="as-sr">: {{ $project['title'] }}</span> @include('public.partials.icon', ['name' => 'arrow', 'size' => 16])</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif
    </div>
@endsection
