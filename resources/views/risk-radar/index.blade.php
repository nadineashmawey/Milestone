@extends('layouts.professor')
@section('title', 'Risk Radar')
@section('content')
<div class="risk-page" data-risk-radar>
    <header class="risk-header"><div><p class="risk-eyebrow">Reports and Risk Radar</p><h1>See trouble before it becomes a deadline</h1><p class="ms-muted">Spot teams that need support, understand the signals, and plan your next step.</p></div><x-button data-export><x-project-icon name="download"/>Export report</x-button></header>
    <div class="risk-context"><span>{{ $context['project'] }}</span><span>{{ $context['milestone'] }}</span><span>Sample deadline · Oct 18, 2026</span><x-badge>Sample data · ML integration pending</x-badge></div>
    <div class="risk-overview" aria-label="Risk overview">
        @foreach(['high' => ['Needs attention', 'danger'], 'medium' => ['Keep an eye on', 'warning'], 'low' => ['On track', 'success']] as $level => [$label, $tone])
        <div class="risk-metric risk-metric--{{ $level }}"><x-badge :tone="$tone">{{ ucfirst($level) }} risk</x-badge><strong>{{ count(array_filter($teams, fn ($team) => $team['risk'] === $level)) }} <small>{{ count(array_filter($teams, fn ($team) => $team['risk'] === $level)) === 1 ? 'team' : 'teams' }}</small></strong><span>{{ $label }}</span></div>
        @endforeach
    </div>
    <div class="risk-main">
        <x-card class="risk-map-card">
            <div class="risk-section-heading"><div><h2>Team risk map</h2><p>Choose a team to inspect its signals.</p></div><div class="risk-legend" aria-label="Risk levels"><span class="risk-dot--low">Low</span><span class="risk-dot--medium">Medium</span><span class="risk-dot--high">High</span></div></div>
            <div class="risk-chart-wrap"><span class="risk-y-label">Activity score →</span><div class="risk-chart" role="group" aria-label="Team progress versus activity. Bubble size indicates open workload.">
                <div class="risk-chart-guide risk-chart-guide--horizontal" aria-hidden="true"></div><div class="risk-chart-guide risk-chart-guide--vertical" aria-hidden="true"></div>
                @foreach($teams as $team)
                <button type="button" class="risk-bubble risk-bubble--{{ $team['risk'] }}" data-team="{{ $team['id'] }}" style="--x: {{ $team['progress'] }}%; --y: {{ 100 - $team['activity'] }}%; --bubble: {{ 30 + $team['openTasks'] * 4 }}px" aria-pressed="{{ $team['id'] === '3' ? 'true' : 'false' }}" aria-label="{{ $team['name'] }}, {{ $team['risk'] }} risk, {{ $team['progress'] }} percent progress, activity {{ $team['activity'] }} out of 100, {{ $team['openTasks'] }} open tasks" title="{{ $team['name'] }} · {{ ucfirst($team['risk']) }} risk">T{{ $team['id'] }}</button>
                @endforeach
            </div><div class="risk-x-label"><span>0%</span><span>Project progress →</span><span>100%</span></div></div>
            <p class="risk-chart-note">Bubble size = open tasks. Activity is a sample score out of 100.</p>
            <div class="risk-team-shortcuts" role="group" aria-label="Select any team">@foreach($teams as $team)<button type="button" data-team="{{ $team['id'] }}" aria-pressed="{{ $team['id'] === '3' ? 'true' : 'false' }}">{{ $team['name'] }}<span class="risk-dot--{{ $team['risk'] }}">{{ ucfirst($team['risk']) }}</span></button>@endforeach</div>
        </x-card>
        <x-card class="risk-forecast" aria-labelledby="risk-selected-team">
            <div class="risk-forecast-badges"><x-badge>Sample prediction</x-badge><span class="ms-badge ms-badge--danger" data-risk-badge>High risk</span></div>
            <h2 id="risk-selected-team" data-team-name>Team 3</h2><p class="risk-forecast-title" data-forecast>May miss M2 by about 3 days</p>
            <div class="risk-reasons"><h3>Why this risk?</h3><ul data-reasons></ul></div>
            <div class="risk-suggestion"><h3>Suggested next step</h3><p data-suggestion></p></div>
            <p class="risk-model-note">Illustrative signals only. Predictions and explanations will come from the trained ML model later.</p>
        </x-card>
    </div>
    <div class="risk-bottom">
        <x-card class="risk-contribution">
            <div class="risk-section-heading"><div><h2>Contribution balance</h2><p><span data-contribution-team>Team 3</span> · Last 14 days</p></div><label class="risk-team-select">Choose team<select class="ms-input" data-team-select>@foreach($teams as $team)<option value="{{ $team['id'] }}" @selected($team['id'] === '3')>{{ $team['name'] }}</option>@endforeach</select></label></div>
            <div data-members></div><p class="risk-footnote">Activity scores out of 100, not shares of team work. Use these signals to start a conversation; they do not determine grades.</p>
        </x-card>
        <x-card class="risk-simulator">
            <div class="risk-section-heading"><div><h2>What-if simulator</h2><p>Explore extra time for <strong data-simulator-team>Team 3</strong>.</p></div><x-badge>Illustrative scenario</x-badge></div>
            <label class="risk-scenario-label" for="risk-extra-days"><span>Extend M2 deadline</span><output for="risk-extra-days" data-days>+3 days</output></label><input id="risk-extra-days" type="range" min="0" max="7" step="1" value="3" data-extra-days aria-describedby="risk-scenario-note">
            <div class="risk-scenario-dates"><span>Current: Oct 18</span><span data-proposed-deadline>Proposed: Oct 21</span></div>
            <div class="risk-impact" data-impact><span>Scenario outcome</span><strong data-scenario-result></strong><small data-scenario-detail></small></div>
            <p class="risk-footnote" id="risk-scenario-note">This preview subtracts extra days from the sample delay. It does not run an ML model or change the actual deadline.</p>
        </x-card>
    </div>
    <p class="risk-status" role="status" aria-live="polite" data-risk-status></p>
    <script type="application/json" data-risk-data>{!! json_encode(['teams' => $teams, 'context' => $context], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
</div>
@endsection
