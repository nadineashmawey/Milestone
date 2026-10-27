@extends('layouts.app')

@section('title', 'New independent project')

@push('styles')
    @vite('resources/css/student-projects.css')
@endpush

@section('page')
    <main id="main" class="projects-page">
        <div class="projects-container">
            <a class="back-link" href="{{ route('student.projects.index') }}"><span aria-hidden="true">←</span> Back to My Projects</a>
            <header class="projects-heading">
                <div>
                    <p class="projects-eyebrow">Independent project</p>
                    <h1>Create a project</h1>
                    <p class="projects-intro">Set up a student-led project to organize your work.</p>
                </div>
            </header>

            <section class="project-card" style="max-width: 46rem">
                <form method="post" action="{{ route('student.projects.store') }}" class="ms-form">
                    @csrf
                    <x-input id="title" name="title" label="Project name" :value="old('title')" required maxlength="120" autocomplete="off" />
                    <div class="ms-field">
                        <label class="ms-field__label" for="description">Description <span>(optional)</span></label>
                        <textarea class="ms-input" id="description" name="description" rows="5" maxlength="2000">{{ old('description') }}</textarea>
                        @error('description')<small class="ms-field__error">{{ $message }}</small>@enderror
                    </div>
                    <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1.5rem">
                        <a class="ms-button" href="{{ route('student.projects.index') }}">Cancel</a>
                        <button class="ms-button ms-button--primary" type="submit">Create project</button>
                    </div>
                </form>
            </section>
        </div>
    </main>
@endsection
