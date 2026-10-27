@extends('layouts.app')

@section('title', 'Professor Teams')

@push('styles')
    @vite('resources/css/academic-admin.css')
@endpush

@section('page')
<main id="main" class="workspace-page"><div class="workspace-container"><header class="workspace-heading"><div><p class="projects-eyebrow">Software Engineering Capstone</p><h1>Teams</h1><p>Formation locks October 11 at 5:00 PM · 2–4 students per team</p></div><form method="post" action="{{ route('professor.teams.auto-place') }}">@csrf<button class="ms-button ms-button--primary" type="submit">Auto-place randomly</button></form></header>
    @if(session('status'))<p class="workspace-flash" role="status">{{ session('status') }}</p>@endif
    @if($errors->any())<p class="workspace-error" role="alert">{{ $errors->first() }}</p>@endif
    <section class="workspace-team-grid" aria-label="Project teams">@foreach($teams as $team)<article class="workspace-card"><div class="workspace-card-head"><div><span class="workspace-badge workspace-badge--{{ $team['tone'] }}">{{ $team['tone'] === 'danger' ? 'At risk' : ($team['tone'] === 'warning' ? 'Watch' : 'On track') }}</span><h2>{{ $team['name'] }}</h2></div><div class="workspace-progress"><strong>{{ $team['progress'] }}%</strong><span><i style="width:{{ $team['progress'] }}%"></i></span></div></div>
        <div class="workspace-members">@foreach($team['members'] as $member)<div class="workspace-member"><div><strong>{{ $member }} @if(($team['leader'] ?? $team['members'][0]) === $member)<em>Leader</em>@endif</strong><small>{{ ($team['leader'] ?? $team['members'][0]) === $member ? 'Team leader' : 'Team member' }} · Active today</small></div><form method="post" action="{{ route('professor.teams.members.remove', [$team['id'], $member]) }}" onsubmit="return confirm('Remove {{ $member }} from {{ $team['name'] }}?')">@csrf<button class="workspace-link workspace-danger" type="submit">Remove</button></form></div>@endforeach</div>
        <div class="workspace-team-footer"><span>{{ count($team['members']) }} of 4 seats</span><form method="post" action="{{ route('professor.teams.leader', $team['id']) }}">@csrf<label class="workspace-inline">Leader<select name="leader">@foreach($team['members'] as $member)<option value="{{ $member }}" @selected(($team['leader'] ?? $team['members'][0]) === $member)>{{ $member }}</option>@endforeach</select></label><button class="workspace-link" type="submit">Change leader</button></form></div>
    </article>@endforeach</section>
    <section class="workspace-section workspace-card"><div class="workspace-section-head"><div><h2>Unassigned students</h2><p>These students need a team before formation locks.</p></div><span class="workspace-badge workspace-badge--warning">{{ count($unassigned) }} students</span></div>
        @forelse($unassigned as $student)<form class="workspace-unassigned" method="post" action="{{ route('professor.teams.members.add') }}">@csrf<div><strong>{{ $student }}</strong><small>CS 490 · No pending invitations</small></div><select name="team_id" aria-label="Team for {{ $student }}" required><option value="">Choose team</option>@foreach($teams as $team)<option value="{{ $team['id'] }}" @disabled(count($team['members']) >= 4)>{{ $team['name'] }} · {{ count($team['members']) }}/4</option>@endforeach</select><input type="hidden" name="student" value="{{ $student }}"><button class="ms-button" type="submit">Assign</button></form>@empty<p class="workspace-empty">Everyone has a team. All enrolled students are ready to collaborate.</p>@endforelse
    </section>
    <section class="workspace-section workspace-card"><div class="workspace-section-head"><div><h2>Incoming leave requests</h2><p>Students remain on their team until you approve a move.</p></div><span class="workspace-badge workspace-badge--warning">{{ count($leave_requests) }} pending</span></div>
        @forelse($leave_requests as $leave)<div class="workspace-leave"><div><strong>{{ $leave['student'] }} wants to leave {{ $leave['team'] }}</strong><small>“{{ $leave['reason'] }}” · 3 hours ago</small></div><form method="post" action="{{ route('professor.teams.leave-request', $leave['id']) }}">@csrf<button class="ms-button ms-button--danger" name="decision" value="reject">Reject</button><button class="ms-button" name="decision" value="approve">Approve</button></form></div>@empty<p class="workspace-empty">No pending leave requests.</p>@endforelse
    </section>
</div></main>
@endsection

