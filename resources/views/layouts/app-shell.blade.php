@extends('layouts.app')

{{--
    Zina's signed-in pages: the shared layout (navbar included there), then the page in the shell's container.
    Sections: content (required). Pages that have empty, loading and error states read ?state= in their controller
    (local and testing only); there is no visible switcher.
--}}
{{-- Prepended: Blade runs the page's @push first, and page styles must load after the shell's. --}}
@prepend('styles')
    @vite('resources/css/pages/app-shell.css')
@endprepend

@push('scripts')
    @vite('resources/js/pages/app-shell.js')
@endpush

@section('page')
    <div class="as">
        <main id="main" class="as-main">
            @yield('content')
        </main>
    </div>
@endsection
