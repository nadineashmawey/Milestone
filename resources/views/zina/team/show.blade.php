@extends('layouts.app-shell')

@section('title', 'Team · '.$team['project']['title'])

@push('styles')
    @vite('resources/css/pages/student-team.css')
@endpush

@section('content')
    @php
        $project = $team['project'];
        $teamErrors = collect(['request', 'invitation', 'join', 'member'])->map(fn ($key) => $errors->first($key))->filter();
    @endphp
    <div class="as-page st">
        @if ($state === 'loading')
            @include('zina.partials.state-loading', ['label' => 'Loading your team', 'blocks' => 2])
        @elseif ($state === 'error')
            @include('zina.partials.state-error', ['title' => 'We couldn’t load your team.'])
        @elseif ($state === 'empty')
            <header class="as-head"><div><p class="as-eyebrow">{{ $project['title'] }}</p><h1>Your team</h1></div></header>
            <section class="as-empty" aria-labelledby="st-empty-title">
                <span class="as-empty-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'users', 'size' => 24])</span>
                <h2 id="st-empty-title">You’re not on a team yet.</h2>
                <p>Send a request to classmates or accept one you’ve received before formation closes. If you’re still unassigned at the deadline, you’re placed in a team with open seats.</p>
                <p class="as-note">Teams hold 2 to 4 students. You can have up to 5 pending requests at once.</p>
            </section>
        @else
            @if ($team['kind'] === 'academic')
                @include('zina.team.partials.academic')
            @else
                @include('zina.team.partials.independent')
            @endif
        @endif
    </div>
@endsection
