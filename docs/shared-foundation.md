# Using Milestone's shared foundation

Everyone writes their own assigned pages. The shared files supply consistent styling and reusable HTML; they do not implement page features or backend logic.

## Files

- `resources/css/tokens.css`: shared design values. Add agreed reusable values here.
- `resources/css/base.css`: font and element defaults.
- `resources/css/components.css`: shared button, input, card, badge and page-heading styles.
- `resources/css/layout.css`: content area and small layout helpers.
- `resources/css/app.css`: loads shared CSS once, including Plus Jakarta Sans and the navbar.
- `resources/views/layouts/base.blade.php`: HTML document and Vite loading.
- `resources/views/layouts/app.blade.php`: the role-based navbar and the content area.
- `resources/views/components/`: reusable Blade markup, including the navbar (`<x-navbar />`).

The navbar picks its items from the signed-in user's role. Pages never add their own navigation; see [navbar.md](navbar.md).

## Start a page

Write your own template inside its assigned feature folder. This is an example to copy and adapt, not a page already implemented:

```blade
@extends('layouts.app')

@section('title', 'Courses')

@section('content')
    <x-page-header title="Courses" description="Your academic courses.">
        <x-button>New course</x-button>
    </x-page-header>

    <x-card title="Software Engineering">
        <p>Write your own course content here.</p>
        <x-badge tone="info">Academic</x-badge>
    </x-card>
@endsection
```

Your team registers the page's real route in `routes/web.php`. The Laravel welcome route has been left unchanged. Buttons do not perform an operation until you implement their action.

## Components

```blade
<x-button>Save</x-button>
<x-button variant="secondary">Cancel</x-button>
<x-button variant="dark">Continue</x-button>
<x-button variant="danger">Delete draft</x-button>
<x-button disabled>Unavailable</x-button>
<x-button type="submit">Submit form</x-button>

<x-input id="project-title" name="title" label="Project title"
    help="Use a clear name." required />
<x-input id="email" name="email" label="Email" type="email"
    error="Enter a valid email address." />

<x-card title="Team progress">Your content</x-card>
<x-badge tone="success">Done</x-badge>
```

Buttons accept standard HTML attributes, including `disabled`. The default type is `button`, so use `type="submit"` inside a form when needed. Use anchors for navigation, optionally with the shared button classes. The input component is for text-like fields, not checkboxes, radios, selects or file-upload widgets. Give every input a unique ID. It displays an explicit error or a Laravel validation error for its name; native browser validation does not automatically populate that error message.

Badge tones are `neutral`, `info`, `success`, `warning`, `danger` and `review`. Always include meaningful status text; colors alone are insufficient. Card titles use h2, so match the surrounding heading hierarchy.

## CSS and interactions

Use `.ms-stack` for a vertical group, `.ms-row` for wrapping horizontal controls and `.ms-grid` for responsive cards. Put feature styles in `resources/css/pages/your-page.css`, scope selectors such as `.courses-page__filters`, and import the stylesheet once from `app.css`.

Reuse variables with `var(--color-primary)`, `var(--space-4)` and other existing names. Do not copy shared component rules into page stylesheets. Agree shared changes with the team before merging.

Forms, navigation behavior, tables, tabs, select fields, dialogs and other feature-specific needs can be added incrementally. This foundation deliberately contains only the first agreed components. The font loads from Google Fonts and needs internet; a system fallback is configured.

Run `npm run dev` while editing CSS/JavaScript, or `npm run build` after changes. Also run Laravel locally with `php artisan serve`. Importing shared CSS into each individual page is unnecessary because the layout already loads it.
