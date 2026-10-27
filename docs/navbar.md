# The navbar

Milestone has one navbar component: `resources/views/components/navbar.blade.php`. It changes by role, as in the design's `roleNav`. Pages never contain navigation markup themselves.

## Use it on a new page

Extend the shared signed-in layout. It already includes `<x-navbar />`:

```blade
@extends('layouts.app')

@section('title', 'My page')

@section('content')
    {{-- Your page. It goes inside <main id="main" class="ms-content ms-stack">. --}}
@endsection
```

- Need a different class on `<main>`? Add `@section('main-class', 'ms-content')`.
- Need your own `<main>` or wrapper? Define `@section('page')` instead of `content`, and give your `<main>` the id `main` so the "Skip to content" link works.
- The role layouts (`layouts.professor`, `layouts.admin`, `layouts.ta`, `layouts.app-shell`) all extend `layouts.app`, so their pages get the navbar too.

Don't add `<nav>`, a top bar, a role pill or an avatar to a page. A test (`tests/Feature/NavbarTest.php`) fails if a view contains its own site navigation. In-page navigation such as tabs or wizard steps is fine.

## Add or change an item

Items live in `config/navigation.php`, keyed by role (`student`, `professor`, `ta`, `admin`):

```php
['label' => 'Submissions', 'icon' => 'check', 'route' => 'professor.submissions.index', 'badge' => 'submissions'],
```

| Key | Meaning |
| --- | --- |
| `label` | The visible text, also read by screen readers when only icons show. |
| `icon` | A name from `resources/views/public/partials/icon.blade.php`. |
| `route` | The route name. Until the route exists, the item shows as a disabled "Coming soon" entry instead of a broken link. |
| `params` | Optional route parameters. |
| `active` | Optional route patterns that mark the item as the current page, for example `['professor.teams.*']`. It defaults to the route itself. |
| `badge` | Optional key from `badges` in the same file. The count is shown, and screen readers hear "Submissions, 7 waiting". |

When you add a page, register its route with the name already in the config and the item lights up by itself. You don't need to touch the navbar.

## Which role is shown

`App\Support\Navigation::role()` is the only place that decides:

1. If someone is signed in, it uses their role (`users.role`: Student, TA, Professor or Admin).
2. Otherwise it uses the demo role kept in the session.

In the local environment, signed-out visitors also get a "View as …" switcher in the navbar to preview all four menus. Switching opens the new role's dashboard.

No page, controller, view or script picks a role of its own: not from `?role=`, not from `localStorage`, not from a select on the page. The notifications bell and the Notifications page use the same role as the navbar.

## Role pages and access

- Every role page sits under its role's prefix and route name: `student.*` under `/student/…`, `professor.*` under `/professor/…`, `ta.*` under `/ta/…`, `admin.*` under `/admin/…`. Each role's items in `config/navigation.php` use only that role's routes; `NavbarTest` checks it.
- Each prefix group has the `role:…` middleware (`App\Http\Middleware\EnsureRole`). Opening another role's page shows the access-denied page (DSH-4, `errors/access-denied.blade.php`, status 403) with your own navbar. It never switches the navbar to the other role.
- Profile, Help, Notifications and `/ui-kit` are shared and keep the current role's navbar.
- The logo and "Dashboard" go to the current role's dashboard (`DashboardRoute::forRole()`).
- Replay was removed from the project. It isn't in any menu, not even as "Coming soon".

## What's on the right

- The role pill.
- Dark mode, saved in `localStorage` as `milestone-theme`. It sets `data-theme="dark"` on `<html>`. Only the navbar has dark styles so far; a page opts in with its own `:root[data-theme='dark']` rules.
- Help, which links to `/help`.
- The notifications bell. Its preview uses Raghad's notification data, and "View all" opens her Notifications page.
- The account menu, with Profile and Sign out (or Sign in when nobody is signed in).

## Small screens

Under 900px a menu button opens the full list with labels. It closes on Escape, when you choose a link, or when you press the button again, and focus returns to the button. When a role's labels don't fit on a medium-width screen, the bar first tightens its spacing, then shows icons only. Labels then appear as tooltips and stay available to screen readers.

## Public pages

Home and Help use the guest variant of the same component, `<x-navbar variant="guest" />`. Help passes `:full="false"` for its short bar. `layouts.public` includes it.

## Files

- `resources/views/components/navbar.blade.php`: both variants.
- `resources/views/partials/navbar-item.blade.php`: one item, used only by the component.
- `config/navigation.php`: items, badges and role labels.
- `app/Support/Navigation.php`: role, items, links and the signed-in person.
- `resources/css/components/navbar.css`: all navbar styles, loaded through `app.css`.
- `resources/js/components/navbar.js`: the menu, dropdowns, theme, fitting and notifications preview, loaded through `app.js`.
