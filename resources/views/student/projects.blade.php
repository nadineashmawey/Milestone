@extends('layouts.base')


@section('title', 'My Projects')


@push('styles')
    @vite('resources/css/student-projects.css')
@endpush


@section('body')
    <main class="projects-page">
        <div class="projects-container">
            <header class="projects-heading">
                <div>
                    <p class="projects-eyebrow">Student workspace</p>
                    <h1>My Projects</h1>
                    <p class="projects-intro">Academic and independent projects share task tools while keeping their rules distinct.</p>
                </div>
                <button class="ms-button ms-button--primary projects-create" type="button" disabled title="Project creation will be connected in a later phase"><span aria-hidden="true">＋</span> New independent project</button>
            </header>


            @if (count($projects))
                <section class="project-grid" aria-label="Your projects">
                    @foreach ($projects as $project)
                        <a class="project-card {{ $loop->first ? 'project-card--featured' : '' }}" href="{{ route('student.projects.show', $project['id']) }}">
                            <div class="project-card__top">
                                <span class="project-folder" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M3.5 6.5A1.5 1.5 0 0 1 5 5h5l2 2h7A1.5 1.5 0 0 1 20.5 8.5v9A1.5 1.5 0 0 1 19 19H5a1.5 1.5 0 0 1-1.5-1.5v-11Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg></span>
                                <span class="project-type {{ $project['kind'] === 'Independent' ? 'project-type--independent' : '' }}"><span></span>{{ $project['kind'] }}</span>
                            </div>
                            <div class="project-card__title">
                                <h2>{{ $project['title'] }}</h2>
                                <p>{{ $project['course'] }}@if($project['professor']) · {{ $project['professor'] }} · {{ $project['team'] }}@else · {{ $project['members'] }} of 10 members · No grading@endif</p>
                            </div>
                            <div class="project-progress">
                                <div><span>{{ $project['kind'] === 'Academic' ? 'Overall progress' : 'Task progress' }}</span><strong>{{ $project['progress'] }}%</strong></div>
                                <div class="project-progress__track"><span style="width: {{ $project['progress'] }}%"></span></div>
                            </div>
                            <div class="project-card__footer"><span>{{ $project['next'] }}</span><svg viewBox="0 0 20 20" aria-hidden="true"><path d="m7.5 4 6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8"/></svg></div>
                        </a>
                    @endforeach
                </section>
            @else
                <section class="projects-empty"><span class="project-folder" aria-hidden="true">＋</span><h2>No projects yet</h2><p>When you join a project, it will appear here.</p></section>
            @endif
        </div>
    </main>
@endsection
