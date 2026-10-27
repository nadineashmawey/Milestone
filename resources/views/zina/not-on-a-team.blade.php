@extends('layouts.app-shell')

@section('title', $page)

@section('content')
    {{-- A real account (not a demo one) that isn't on a course team yet: nothing sample to show. --}}
    <div class="as-page">
        <header class="as-head">
            <div>
                <p class="as-eyebrow">{{ $page }}</p>
                <h1>{{ $page }}</h1>
            </div>
        </header>
        <section class="as-empty" aria-labelledby="not-on-team-title">
            <span class="as-empty-icon" aria-hidden="true">@include('public.partials.icon', ['name' => $page === 'Team' ? 'users' : 'clock', 'size' => 24])</span>
            <h2 id="not-on-team-title">You’re not on a course team yet.</h2>
            <p>{{ $page === 'Team' ? 'Your team and its members' : 'Milestones, deadlines and submissions' }} show up here once you join a course project with the code your professor shares.</p>
            <div class="as-empty-actions">
                <x-button :href="route('student.projects.index')">Go to My projects</x-button>
            </div>
        </section>
    </div>
@endsection
