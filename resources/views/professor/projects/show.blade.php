@extends('layouts.app-shell')

@section('title', $project['title'])

@push('styles')
    @vite('resources/css/pages/professor-projects.css')
@endpush

@section('content')
    @php($p = $project)
    <div class="as-page pp">
        <a class="as-text-link pp-back" href="{{ route('professor.projects.index') }}">@include('public.partials.icon', ['name' => 'back', 'size' => 16]) My projects</a>

        @if (session('success'))
            <x-alert tone="success" :title="session('success')" />
        @endif

        <header class="as-head">
            <div>
                <div class="as-badges">
                    @include('zina.partials.project-type', ['type' => $p['type']])
                    <x-badge :tone="$p['status_tone']">{{ $p['status'] }}</x-badge>
                </div>
                <h1>{{ $p['title'] }}</h1>
                <p class="as-head-lede">{{ $p['course'] }}@if ($p['semester']) · {{ $p['semester'] }}@endif · {{ $p['published'] ? 'Published '.$p['published'] : 'Draft, not published' }}</p>
            </div>
            @if ($p['edit_href'])
                <div class="as-head-actions"><x-button :href="$p['edit_href']">Edit and publish</x-button></div>
            @endif
        </header>

        <dl class="as-stats">
            @foreach ([['Teams', $p['teams'], 'users'], ['Milestones', $p['milestone_count'], 'calendar'], ['Total grade', $p['total_grade'] ? $p['total_grade'].' pts' : '–', 'chart'], ['Progress', $p['progress'].'%', 'check']] as [$label, $value, $icon])
                <div class="as-stat">
                    <span class="as-stat-icon" aria-hidden="true">@include('public.partials.icon', ['name' => $icon, 'size' => 20])</span>
                    <div><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                </div>
            @endforeach
        </dl>

        <section class="as-panel" aria-labelledby="pp-overview-title">
            <div class="as-panel-head"><div><h2 id="pp-overview-title">Overview</h2></div></div>
            <p>{{ $p['description'] ?: 'No description yet.' }}</p>
            @if ($p['goals'])
                <h3 class="pp-subhead">Goals</h3>
                <ul class="pp-goals">@foreach ($p['goals'] as $goal)<li>{{ $goal }}</li>@endforeach</ul>
            @endif
        </section>

        <section class="as-panel" aria-labelledby="pp-milestones-title">
            <div class="as-panel-head"><div><h2 id="pp-milestones-title">Milestones</h2><p>In order, with what students hand in.</p></div></div>
            @if ($p['milestones'])
                <ol class="as-rows pp-milestones">
                    @foreach ($p['milestones'] as $milestone)
                        <li>
                            <span class="pp-number" aria-hidden="true">{{ $milestone['number'] }}</span>
                            <div>
                                <strong>{{ $milestone['name'] }}</strong>
                                <small class="as-meta">Due @if ($milestone['due_iso'])<time datetime="{{ $milestone['due_iso'] }}">{{ $milestone['due'] }}</time>@else{{ $milestone['due'] }}@endif · {{ $milestone['deliverables'] }}</small>
                            </div>
                            <span class="pp-weight">{{ $milestone['weight'] ? $milestone['weight'].'%' : '–' }}@if ($milestone['points']) · {{ $milestone['points'] }} pts @endif</span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="as-meta">No milestones yet.</p>
            @endif
        </section>

        <div class="pp-grid">
            <section class="as-panel" aria-labelledby="pp-teams-title">
                <div class="as-panel-head"><div><h2 id="pp-teams-title">Team rules</h2></div></div>
                <dl class="pp-pairs">@foreach ($p['team_rules'] as $rule)<div><dt>{{ $rule['label'] }}</dt><dd>{{ $rule['value'] }}</dd></div>@endforeach</dl>
            </section>
            <section class="as-panel" aria-labelledby="pp-grading-title">
                <div class="as-panel-head"><div><h2 id="pp-grading-title">Grading</h2></div></div>
                <dl class="pp-pairs">@foreach ($p['grading'] as $rule)<div><dt>{{ $rule['label'] }}</dt><dd>{{ $rule['value'] }}</dd></div>@endforeach</dl>
            </section>
        </div>

        <section class="as-panel" aria-labelledby="pp-rubric-title">
            <div class="as-panel-head"><div><h2 id="pp-rubric-title">Rubric</h2><p>Final grades are professor-only.</p></div></div>
            @if ($p['criteria'])
                <table class="pp-rubric">
                    <caption class="as-sr">Rubric criteria and points</caption>
                    <thead><tr><th scope="col">Criterion</th><th scope="col">Points</th></tr></thead>
                    <tbody>@foreach ($p['criteria'] as $criterion)<tr><th scope="row">{{ $criterion['name'] }}</th><td>{{ $criterion['points'] }}</td></tr>@endforeach</tbody>
                    <tfoot><tr><th scope="row">Total</th><td>{{ collect($p['criteria'])->sum('points') }}</td></tr></tfoot>
                </table>
            @else
                <p class="as-meta">No rubric: the project is graded out of its total grade only.</p>
            @endif
        </section>
    </div>
@endsection
