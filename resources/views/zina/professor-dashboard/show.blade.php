@extends('layouts.app-shell', ['shellStates' => true])

@section('title', 'Professor dashboard')

@push('styles')
    @vite('resources/css/pages/professor-dashboard.css')
@endpush

@section('content')
    @php($d = $dashboard)
    <div class="as-page pd">
        @if ($state === 'loading')
            @include('zina.partials.state-loading', ['label' => 'Loading your dashboard', 'blocks' => 4])
        @elseif ($state === 'error')
            @include('zina.partials.state-error', ['title' => 'We couldn’t load your dashboard.'])
        @else
            <header class="pd-hero">
                <div>
                    <p class="pd-hero-eyebrow">{{ $d['today'] }}</p>
                    <h1>{{ $d['greeting'] }}, {{ $d['professor']['name'] }}</h1>
                    <p>{{ $d['summary'] }}</p>
                </div>
                <x-button :href="$d['create_href']" size="large" class="pd-hero-cta" :aria-disabled="$d['create_href'] === '#' ? 'true' : null">
                    @include('public.partials.icon', ['name' => 'plus', 'size' => 18]) Create project
                    @if ($d['create_href'] === '#')<span class="as-sr"> (Nadine’s page, coming soon)</span>@endif
                </x-button>
            </header>

            @if ($state === 'empty')
                <section class="as-empty" aria-labelledby="pd-empty-title">
                    <span class="as-empty-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'folder', 'size' => 24])</span>
                    <h2 id="pd-empty-title">Create your first project.</h2>
                    <p>Set the milestones, rubric and team rules once. Students are invited when you publish, and their progress shows up here.</p>
                    <div class="as-empty-actions">
                        <x-button :href="$d['create_href']">@include('public.partials.icon', ['name' => 'plus', 'size' => 18]) Create your first project</x-button>
                    </div>
                </section>
            @else
                <dl class="as-stats">
                    @foreach ($d['stats'] as $stat)
                        <div class="as-stat">
                            <span @class(['as-stat-icon', 'as-stat-icon--'.$stat['tone'] => $stat['tone']]) aria-hidden="true">@include('public.partials.icon', ['name' => $stat['icon'], 'size' => 20])</span>
                            <div>
                                <dt>{{ $stat['label'] }}</dt>
                                <dd>{{ $stat['value'] }}<small>{{ $stat['note'] }}</small></dd>
                            </div>
                        </div>
                    @endforeach
                </dl>

                <section class="as-panel" aria-labelledby="pd-attention-title">
                    <div class="as-panel-head">
                        <div>
                            <h2 id="pd-attention-title">Needs attention</h2>
                            <p>Most urgent first. Each item opens the page where you deal with it.</p>
                        </div>
                        <x-badge tone="danger">{{ count($d['attention']) }} items</x-badge>
                    </div>
                    <ol class="as-rows pd-attention">
                        @foreach ($d['attention'] as $item)
                            <li class="pd-attention-item pd-attention-item--{{ $item['tone'] }}">
                                <span class="pd-mark" aria-hidden="true"></span>
                                <div class="pd-attention-body">
                                    <span class="pd-priority">{{ $item['priority'] }}</span>
                                    <strong>{{ $item['title'] }}</strong>
                                    <small>{{ $item['meta'] }}</small>
                                </div>
                                <div class="pd-attention-action">
                                    <a class="ms-button ms-button--small" href="{{ $item['href'] }}" @if ($item['href'] === '#') aria-disabled="true" @endif>{{ $item['action'] }}<span class="as-sr">: {{ $item['title'] }}</span></a>
                                    @if ($item['href'] === '#')<small class="as-note">{{ $item['page'] }} is {{ $item['owner'] }}’s page, coming soon</small>@endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>

                <section aria-labelledby="pd-projects-title">
                    <div class="pd-section-head">
                        <h2 id="pd-projects-title">Active projects</h2>
                        <p class="as-meta">Risk values are simulated in this prototype and never decide a grade.</p>
                    </div>
                    <ul class="pd-projects">
                        @foreach ($d['projects'] as $project)
                            <li class="pd-project">
                                <div class="as-badges">
                                    @include('zina.partials.project-type', ['type' => $project['type']])
                                    <x-badge :tone="$project['risk_tone']">{{ $project['risk'] }} risk · Simulated</x-badge>
                                    @if ($project['extended'])<x-badge tone="review">{{ $project['extended'] }}</x-badge>@endif
                                </div>
                                <h3>{{ $project['title'] }}</h3>
                                <p class="as-meta">{{ $project['course'] }}</p>
                                <p class="pd-reason">{{ $project['reason'] }}</p>
                                <div class="pd-progress">
                                    <span>Overall progress</span><strong>{{ $project['progress'] }}%</strong>
                                    <span class="as-bar" aria-hidden="true"><span style="width: {{ $project['progress'] }}%"></span></span>
                                </div>
                                <p class="pd-next">@include('public.partials.icon', ['name' => 'calendar', 'size' => 16]) Next: {{ $project['next'] }} · {{ $project['next_label'] }} <x-badge :tone="$project['countdown']['tone']">{{ $project['countdown']['label'] }}</x-badge></p>
                                <a class="as-text-link pd-open" href="{{ $project['href'] }}" @if ($project['href'] === '#') aria-disabled="true" @endif>Open teams<span class="as-sr"> for {{ $project['title'] }}</span> @include('public.partials.icon', ['name' => 'arrow', 'size' => 16])</a>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <div class="pd-grid">
                    <section class="as-panel" aria-labelledby="pd-deadlines-title">
                        <div class="as-panel-head">
                            <div>
                                <h2 id="pd-deadlines-title">Upcoming deadlines</h2>
                                <p>Across all your projects, nearest first</p>
                            </div>
                        </div>
                        <ol class="as-rows pd-deadlines">
                            @foreach ($d['deadlines'] as $deadline)
                                <li>
                                    <span class="pd-date" aria-hidden="true"><strong>{{ $deadline['day'] }}</strong><small>{{ $deadline['month'] }}</small></span>
                                    <div>
                                        <strong>{{ $deadline['title'] }}</strong>
                                        <small>{{ $deadline['project'] }} · <time datetime="{{ $deadline['due']->toIso8601String() }}">{{ $deadline['when'] }}</time></small>
                                        <small>{{ $deadline['note'] }}</small>
                                    </div>
                                    <x-badge :tone="$deadline['countdown']['tone']">{{ $deadline['countdown']['label'] }}</x-badge>
                                </li>
                            @endforeach
                        </ol>
                    </section>

                    <section class="as-panel" aria-labelledby="pd-activity-title">
                        <div class="as-panel-head">
                            <div>
                                <h2 id="pd-activity-title">Recent activity</h2>
                                <p>What happened in your projects. Alerts meant for you are in <a href="{{ $d['notifications_href'] }}">Notifications</a>.</p>
                            </div>
                        </div>
                        <ul class="as-rows pd-activity">
                            @foreach ($d['activity'] as $event)
                                <li>
                                    @include('zina.partials.avatar', ['name' => $event['who'], 'index' => $event['avatar']])
                                    <div><p><strong>{{ $event['who'] }}</strong> {{ $event['text'] }}</p><small>{{ $event['meta'] }}</small></div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                </div>
            @endif
        @endif
    </div>
@endsection
