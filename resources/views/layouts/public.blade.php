@extends('layouts.base')

{{-- Public pages (Home, Help center). Page assets load only here; the navbar comes from <x-navbar variant="guest" />. --}}
@push('styles')
    <meta name="description" content="@yield('description', 'Milestone brings professors, teaching assistants and student teams into one workspace, from forming teams to the final grade.')">
    @vite('resources/css/pages/home.css')
    <noscript>
        <style>
            .hp .hp-faq-answer[hidden] { display: block; }
            .hp .hp-search-submit { display: inline-flex; }
            @media (max-width: 900px) {
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
        <x-navbar variant="guest" :full="$full ?? true" />
        <main id="main">
            @yield('content')
        </main>
        @include('public.partials.footer')
    </div>
@endsection
