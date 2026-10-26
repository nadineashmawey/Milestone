<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="data:">
        <title>@yield('title', 'Admin Workspace') · Milestone</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'tokens.css']) }}">
        <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'base.css']) }}">
        <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'components.css']) }}">
        <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'admin-static.css']) }}">
        @stack('styles')
    </head>
    <body>
        <a class="ms-skip-link" href="#main">Skip to content</a>
        <main id="main" class="@yield('page-class', 'page')">
            <div class="container">
                @yield('content')
            </div>
            @stack('overlays')
        </main>
        <script src="{{ route('admin.assets.js', ['path' => 'components/admin-dropdowns.js']) }}"></script>
        @stack('scripts')
    </body>
</html>
