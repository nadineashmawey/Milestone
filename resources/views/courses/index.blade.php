@extends('layouts.professor')

@section('title', 'My Courses')

@section('content')
    <div class="courses-page ms-stack">

        <header class="courses-page__header">
            <div>
                <p class="courses-page__semester">Fall 2026</p>
                <h1>My Courses</h1>
            </div>
            <a class="ms-button ms-button--primary" href="{{ route('professor.projects.create') }}">
                + Create academic project
            </a>
        </header>

        <div class="courses-page__layout">

            {{-- Course selector --}}
            <nav
                class="courses-page__list"
                aria-label="Select a course"
            >
                @foreach ($courses as $id => $course)
                    <a
                        href="{{ route('professor.courses.index', ['course' => $id]) }}"
                        @class([
                            'courses-page__course',
                            'is-active' => $selectedId === $id,
                        ])
                        @if ($selectedId === $id)
                            aria-current="page"
                        @endif
                    >
                        <span
                            class="courses-page__mark courses-page__mark--{{ $course['tone'] }}"
                            aria-hidden="true"
                        >
                            {{ substr($course['code'], -3) }}
                        </span>

                        <span>
                            <strong>{{ $course['code'] }}</strong>

                            <small>
                                {{ $course['name'] }}
                                · {{ $course['studentCount'] }} students
                            </small>
                        </span>
                    </a>
                @endforeach
            </nav>

            {{-- Selected course --}}
            <x-card class="courses-page__detail ms-stack">

                <div class="courses-page__detail-header">
                    <div>
                        <x-badge tone="success">
                            Active course
                        </x-badge>

                        <h2 class="courses-page__title">
                            {{ $selectedCourse['code'] }}
                            · {{ $selectedCourse['name'] }}
                        </h2>

                        <p class="ms-muted">
                            Fall 2026
                            · {{ $selectedCourse['schedule'] }}
                            · {{ $selectedCourse['studentCount'] }} enrolled students
                        </p>
                    </div>

                    <x-button variant="secondary" class="courses-page__export" aria-describedby="courses-demo-note">
                        Export roster
                    </x-button>
                </div>

                {{-- Summary figures --}}
                <dl class="courses-page__metrics">
                    <div>
                        <dt>Students</dt>
                        <dd>{{ $selectedCourse['studentCount'] }}</dd>
                    </div>

                    <div>
                        <dt>Academic projects</dt>
                        <dd>{{ count($selectedCourse['projects']) }}</dd>
                    </div>

                    <div>
                        <dt>Assigned TAs</dt>
                        <dd>{{ $selectedCourse['taCount'] }}</dd>
                    </div>

                    <div>
                        <dt>Unassigned</dt>
                        <dd>{{ $selectedCourse['unassignedCount'] }}</dd>
                    </div>
                </dl>

                {{-- Projects --}}
                <section class="ms-stack">
                    <div>
                        <h3>Course projects</h3>
                        <p class="ms-muted">
                            Each academic project has exactly one professor.
                        </p>
                    </div>

                    @forelse ($selectedCourse['projects'] as $project)
                        <article class="courses-page__project">
                            <div>
                                <h4>{{ $project['title'] }}</h4>

                                <p class="ms-muted">
                                    {{ $project['teams'] }} teams
                                    · {{ $project['milestones'] }} milestones
                                    · Total grade: {{ $project['totalGrade'] }} points
                                </p>
                            </div>

                            <x-badge tone="success">
                                Published
                            </x-badge>
                        </article>
                    @empty
                        <p class="ms-muted">
                            No academic projects have been added to this course.
                        </p>
                    @endforelse
                </section>

                {{-- Roster --}}
                <section class="ms-stack">
                    <div class="courses-page__section-header">
                        <div>
                            <h3>Course roster</h3>
                            <p class="ms-muted">
                                Sample roster preview
                            </p>
                        </div>

                        <button type="button" class="courses-page__manage" aria-describedby="courses-demo-note">
                            Manage roster
                        </button>
                    </div>

                    @forelse ($selectedCourse['students'] as $student)
                        <div class="courses-page__student">
                            <span
                                class="courses-page__avatar"
                                aria-hidden="true"
                            >
                                {{ substr($student['name'], 0, 1) }}
                            </span>

                            <div class="courses-page__student-info">
                                <strong>{{ $student['name'] }}</strong>
                                <small class="ms-muted">Sample student</small>
                            </div>

                            <x-badge
                                :tone="$student['team'] ? 'success' : 'warning'"
                            >
                                {{ $student['team'] ?? 'Unassigned' }}
                            </x-badge>
                        </div>
                    @empty
                        <p class="ms-muted">
                            No sample roster entries are available for this course.
                        </p>
                    @endforelse

                    @if (count($selectedCourse['students']) > 0)
                        <p class="ms-caption ms-muted">
                            Showing {{ count($selectedCourse['students']) }}
                            sample entries out of
                            {{ $selectedCourse['studentCount'] }} enrolled students.
                        </p>
                    @endif
                </section>

                <p id="courses-demo-note" class="ms-caption ms-muted">
                    Export and roster management are not connected yet.
                </p>

            </x-card>
        </div>
    </div>
@endsection
