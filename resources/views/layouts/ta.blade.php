<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="data:">
        <title>@yield('title', 'TA Workspace') · Milestone</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'admin-static.css']) }}">
        <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'ta-static.css']) }}">
        @stack('styles')
    </head>
    <body>
        <a class="ms-skip-link" href="#main">Skip to content</a>
        <main class="ta-page" id="main">
            <div class="ta-container">
                @yield('content')
            </div>
        </main>
        @stack('overlays')
        <script src="{{ route('ta.assets.js', ['path' => 'components/ta-dropdowns.js']) }}"></script>
        @stack('scripts')
    </body>
</html>
