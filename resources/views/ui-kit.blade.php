@extends('layouts.app')

@section('title', 'UI kit preview')

@section('content')
    <x-page-header title="Milestone UI kit" description="Shared pieces your team can reuse. This is a component preview, not a finished Figma screen." />

    <x-card title="What you are looking at">
        <p>The bar at the top is the shared navbar for your current role. The sample controls below do not save, delete or submit anything.</p>
        <p class="ms-muted">On this page:
            <a href="#theme">Typography and colour</a> · <a href="#dark-mode">Dark mode</a> ·<a href="#navbar">Navbar</a> · <a href="#buttons">Buttons</a> · <a href="#fields">Inputs</a> · <a href="#badges">Badges</a> ·
            <a href="#more-buttons">Button options</a> · <a href="#more-fields">Select and textarea</a> · <a href="#feedback">Alerts and empty states</a> ·
            <a href="#section-headings">Section heading</a>
        </p>
    </x-card>

    @php
        $typeScale = [
            ['--font-page-title', 'Page title (h1)', 'bold'], ['--font-display', 'Display 44', 'bold'], ['--font-heading-lg', 'Heading 36', 'bold'],
            ['--font-heading', 'Heading 28', 'bold'], ['--font-subheading', 'Subheading 24', 'semibold'], ['--font-title', 'Title 20 (h2, h3)', 'semibold'],
            ['--font-large', 'Large 18', 'regular'], ['--font-body', 'Body 16', 'regular'], ['--font-ui', 'UI 15', 'regular'], ['--font-small', 'Small 14 (labels)', 'semibold'],
            ['--font-caption', 'Caption 13', 'regular'], ['--font-xs', 'Extra small 12', 'regular'],
        ];
        $colourGroups = [
            'Brand (SRS §12.3)' => ['royal-blue', 'violet', 'yellow', 'coral', 'light-blue', 'green'],
            'Text and surfaces' => ['ink', 'ink-2', 'muted', 'faint', 'icon', 'border', 'line', 'lavender', 'page', 'white'],
            'Soft tints (use -text on -bg)' => ['info-bg', 'info-text', 'success-bg', 'success-text', 'warning-bg', 'warning-text', 'danger-bg', 'danger-text', 'review-bg', 'review-text'],
        ];
        $radii = ['xs', 'sm', 'md', 'control', 'lg', 'xl', 'card', '2xl', 'sidebar', 'pill'];
        $shadows = ['sm', 'raised', 'card', 'popover', 'lg'];
    @endphp
    <x-card title="Typography and colour" id="theme" class="ms-stack">
        <p>Every page uses these tokens from <code>resources/css/tokens.css</code> and nothing else. See “Theme rules” in <code>docs/ui-kit.md</code>.</p>

        <h3>Type scale <span class="ms-muted ms-caption">Plus Jakarta Sans, weights 400 to 800</span></h3>
        <div class="grid gap-2">
            @foreach ($typeScale as [$token, $label, $weight])
                <div class="grid gap-1 border-b border-line pb-2">
                    <span style="font-size: var({{ $token }}); font-weight: var(--weight-{{ $weight }}); line-height: var(--line-heading);">{{ $label }}</span>
                    <code class="ms-caption ms-muted">{{ $token }}</code>
                </div>
            @endforeach
        </div>
        <p class="ms-muted">Weights: <span style="font-weight: var(--weight-regular)">Regular 400</span> · <span style="font-weight: var(--weight-medium)">Medium 500</span> · <span style="font-weight: var(--weight-semibold)">Semibold 600</span> · <span style="font-weight: var(--weight-strong)">Strong 700</span> · <span style="font-weight: var(--weight-bold)">Bold 800</span></p>

        @foreach ($colourGroups as $group => $colours)
            <h3>{{ $group }}</h3>
            <ul class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5" role="list">
                @foreach ($colours as $colour)
                    <li class="grid gap-1">
                        <span class="block h-12 rounded-md border border-border" style="background: var(--color-{{ $colour }});" aria-hidden="true"></span>
                        <code class="ms-caption">--color-{{ $colour }}</code>
                    </li>
                @endforeach
            </ul>
        @endforeach

        <h3>Radii</h3>
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-5" role="list">
            @foreach ($radii as $radius)
                <li class="grid gap-1">
                    <span class="block h-12 border border-border bg-info-soft" style="border-radius: var(--radius-{{ $radius }});" aria-hidden="true"></span>
                    <code class="ms-caption">--radius-{{ $radius }}</code>
                </li>
            @endforeach
        </ul>

        <h3>Shadows</h3>
        <ul class="grid grid-cols-2 gap-5 sm:grid-cols-5" role="list">
            @foreach ($shadows as $shadow)
                <li class="grid gap-2">
                    <span class="block h-14 rounded-xl bg-surface" style="box-shadow: var(--shadow-{{ $shadow }});" aria-hidden="true"></span>
                    <code class="ms-caption">--shadow-{{ $shadow }}</code>
                </li>
            @endforeach
        </ul>
    </x-card>

    <x-card title="Dark mode" id="dark-mode" class="ms-stack">
        <p>One switch for the whole site: the toggle sets <code>class="dark"</code> and <code>data-theme="dark"</code> on <code>&lt;html&gt;</code>, and the dark block in <code>tokens.css</code> redefines the colour tokens. A page that uses tokens is dark-mode ready with no extra CSS. See “Dark mode” in <code>docs/ui-kit.md</code>.</p>
        <div class="ms-row">
            <x-button variant="secondary" data-navbar-theme aria-pressed="false">
                <span class="ms-navbar__theme-dark">@include('public.partials.icon', ['name' => 'moon', 'size' => 18])</span>
                <span class="ms-navbar__theme-light">@include('public.partials.icon', ['name' => 'sun', 'size' => 18])</span>
                Dark mode
            </x-button>
            <span class="ms-muted ms-caption">Same toggle as the navbar. The choice is remembered; with none, the system setting is used.</span>
        </div>
        <ul class="grid grid-cols-2 gap-3 sm:grid-cols-4" role="list">
            @foreach (['page' => 'Page', 'surface' => 'Surface', 'ink' => 'Ink (text)', 'muted' => 'Muted text', 'border' => 'Border', 'link' => 'Link', 'night' => 'Night (stays dark)', 'white' => 'White (stays white)'] as $token => $label)
                <li class="grid gap-1">
                    <span class="block h-12 rounded-md border border-border" style="background: var(--color-{{ $token }});" aria-hidden="true"></span>
                    <code class="ms-caption">--color-{{ $token }}</code>
                    <span class="ms-caption ms-muted">{{ $label }}</span>
                </li>
            @endforeach
        </ul>
        <x-alert title="Readable in both modes">Alerts, badges, fields, tables and dialogs all read their colours from the tokens.</x-alert>
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
