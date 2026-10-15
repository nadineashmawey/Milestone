<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Milestone') · Milestone</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <a class="ms-skip-link" href="#main">Skip to content</a>
    @yield('body')
    @stack('scripts')
</body>
</html>
