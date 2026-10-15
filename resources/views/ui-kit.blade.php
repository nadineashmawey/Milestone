@extends('layouts.app')

@section('title', 'UI kit preview')
@section('workspace-name', 'Shared foundation preview')

@section('navigation')
    <a href="{{ route('ui-kit') }}" aria-current="page">UI kit preview</a>
    <a href="#buttons">Buttons</a>
    <a href="#fields">Inputs</a>
    <a href="#badges">Badges</a>
@endsection

@section('topbar-actions')
    <x-badge>Local preview only</x-badge>
@endsection

@section('content')
    <x-page-header title="Milestone UI kit" description="Shared pieces your team can reuse. This is a component preview, not a finished Figma screen." />

    <x-card title="What you are looking at">
        <p>The sidebar and topbar are basic layout containers. Their links here only navigate this preview. Your assigned pages have not been built.</p>
        <p class="ms-muted">The sample controls below do not save, delete or submit anything.</p>
    </x-card>

    <x-card title="Buttons" id="buttons" class="ms-stack">
        <div class="ms-row">
            <x-button>Primary</x-button>
            <x-button variant="secondary">Secondary</x-button>
            <x-button variant="dark">Dark</x-button>
            <x-button variant="danger">Danger</x-button>
            <x-button disabled>Disabled</x-button>
        </div>
    </x-card>

    <div class="ms-grid">
        <x-card title="Inputs" id="fields" class="ms-stack">
            <x-input id="preview-title" name="preview_title" label="Project title" placeholder="Enter a title" help="A normal input with supporting text." />
            <x-input id="preview-email" name="preview_email" label="Email" type="email" value="invalid-address" error="Example validation error." />
            <x-input id="preview-role" name="preview_role" label="Role · read only" value="Student" readonly />
        </x-card>

        <x-card title="Badges" id="badges" class="ms-stack">
            <div class="ms-row">
                <x-badge>To Do</x-badge>
                <x-badge tone="info">In Progress</x-badge>
                <x-badge tone="review">In Review</x-badge>
                <x-badge tone="success">Done</x-badge>
                <x-badge tone="warning">Changes requested</x-badge>
                <x-badge tone="danger">Overdue</x-badge>
            </div>
            <p class="ms-muted">These labels demonstrate colors. Task flags and workflow states remain separate in the actual application.</p>
        </x-card>
    </div>

    <x-card title="Typography">
        <p class="ms-display">Display text</p>
        <h2>Section heading</h2>
        <p>Body text uses Plus Jakarta Sans, with a system fallback if the font cannot load.</p>
        <p class="ms-caption ms-muted">Caption text for supporting information.</p>
    </x-card>
@endsection
