@extends('layouts.base')

{{-- Public pages (Home, Help center). Assets load only here, not through app.css or app.js. --}}
@push('styles')
    <meta name="description" content="@yield('description', 'Milestone brings professors, teaching assistants and student teams into one workspace, from forming teams to the final grade.')">
    @vite('resources/css/pages/home.css')
    <noscript>
        <style>
            .hp .hp-faq-answer[hidden] { display: block; }
            .hp .hp-menu-toggle { display: none; }
            .hp .hp-search-submit { display: inline-flex; }
            @media (max-width: 900px) {
                .hp .hp-nav-inner { flex-wrap: wrap; height: auto; padding-block: 12px; }
                .hp .hp-nav-links { display: flex; order: 3; width: 100%; overflow-x: auto; }
                .hp .hp-type-switch { display: none; }
                .hp .hp-type:not(.active) { display: block; }
            }
        </style>
    </noscript>
@endpush

@push('scripts')
    @vite('resources/js/pages/home.js')
@endpush

@section('body')
    <div class="hp">
        @include('public.partials.nav', ['full' => $full ?? true])
        <main id="main">
            @yield('content')
        </main>
        @include('public.partials.footer')
    </div>
@endsection
