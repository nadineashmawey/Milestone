@extends('layouts.app')

@section('title', 'UI kit preview')

@section('content')
    <x-page-header title="Milestone UI kit" description="Shared pieces your team can reuse. This is a component preview, not a finished Figma screen." />

    <x-card title="What you are looking at">
        <p>The bar at the top is the shared navbar for your current role. The sample controls below do not save, delete or submit anything.</p>
        <p class="ms-muted">On this page:
            <a href="#navbar">Navbar</a> · <a href="#buttons">Buttons</a> · <a href="#fields">Inputs</a> · <a href="#badges">Badges</a> ·
            <a href="#more-buttons">Button options</a> · <a href="#more-fields">Select and textarea</a> · <a href="#feedback">Alerts and empty states</a> ·
            <a href="#section-headings">Section heading</a>
        </p>
    </x-card>

    <x-card title="Navbar" id="navbar" class="ms-stack">
        <p>One component for every page: <code>&lt;x-navbar /&gt;</code>, included once by <code>layouts.app</code>. Items come from <code>config/navigation.php</code>, so pages never contain nav markup. See <code>docs/navbar.md</code>.</p>
        <p class="ms-muted">Previews of each role (not sticky). Items whose page doesn’t exist yet show as “Coming soon”.</p>
        @foreach (\App\Support\Navigation::ROLES as $previewRole)
            <x-navbar :role="$previewRole" preview />
        @endforeach
        <p class="ms-muted">Guest variant, used on Home and Help: <code>&lt;x-navbar variant="guest" /&gt;</code></p>
        <x-navbar variant="guest" preview />
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

    <x-card title="Button options" id="more-buttons" class="ms-stack">
        <p class="ms-muted">Optional props. Leaving them out gives the same buttons as above.</p>
        <div class="ms-row">
            <x-button variant="ghost">Ghost</x-button>
            <x-button size="small">Small</x-button>
            <x-button size="large">Large</x-button>
            <x-button loading>Sending…</x-button>
            <x-button variant="secondary" href="#more-buttons">Link styled as a button</x-button>
        </div>
    </x-card>

    <x-card title="Select and textarea" id="more-fields" class="ms-stack">
        <x-select id="preview-category" name="preview_category" label="Category" placeholder="Choose one"
            :options="['teams' => 'Team or membership', 'grades' => 'Grades or feedback']" help="Pass options as value => label." />
        <x-select id="preview-category-error" name="preview_category_error" label="Category with an error"
            :options="['teams' => 'Team or membership']" placeholder="Choose one" error="Choose what the problem is about." />
        <x-textarea id="preview-description" name="preview_description" label="Description" rows="4"
            help="At least 20 characters." />
        <x-textarea id="preview-description-error" name="preview_description_error" label="Description with an error"
            value="Too short" error="Describe the problem in at least 20 characters." />
    </x-card>

    <x-card title="Alerts and empty states" id="feedback" class="ms-stack">
        <x-alert tone="success" title="Report sent">Your reference is MS-4821.</x-alert>
        <x-alert tone="error" title="We couldn’t send your report.">Your details are still here. Try again.</x-alert>
        <x-alert title="Heads up">Info alerts use role="status"; error alerts use role="alert".</x-alert>
        <x-empty-state title="No articles match “grades”.">
            <p>Try a shorter word, or describe the problem in the form.</p>
            <x-slot:action><x-button variant="secondary">Clear search</x-button></x-slot:action>
        </x-empty-state>
    </x-card>

    <x-card title="Section heading" id="section-headings">
        <x-section-heading eyebrow="How it works" title="Structure the project once."
            description="Eyebrow and description are optional. Use level=&quot;3&quot; inside an h2 section." />
    </x-card>
@endsection
