@extends('layouts.app')

{{-- Admin pages. The navbar comes from layouts.app; this layout keeps the admin stylesheets and page wrapper. --}}

@prepend('styles')
    {{-- Tokens, base and components already load once through app.css. --}}
    <link rel="stylesheet" href="{{ route('admin.assets.css', ['path' => 'admin-static.css']) }}">
@endprepend

@prepend('scripts')
    <script src="{{ route('admin.assets.js', ['path' => 'components/admin-dropdowns.js']) }}"></script>
@endprepend

@section('page')
    <main id="main" class="@yield('page-class', 'page')">
        <div class="container">
            @yield('content')
        </div>
        @stack('overlays')
    </main>
@endsection
