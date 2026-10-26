@extends('layouts.app')

{{--
    Zina's signed-in pages: the shared layout (navbar included there), a prototype-controls bar, then the page.
    Extend with data: @extends('layouts.app-shell', ['shellStates' => true])
    Sections: content (required), prototype-controls (optional extra switches for the prototype bar).
    Pages that support ?state= previews pass 'shellStates' => true.
--}}
@php
    $shellStates = ($shellStates ?? false) && app(\App\Support\ZinaDemoData::class)->statesEnabled();
@endphp

{{-- Prepended: Blade runs the page's @push first, and page styles must load after the shell's. --}}
@prepend('styles')
    @vite('resources/css/pages/app-shell.css')
@endprepend

@push('scripts')
    @vite('resources/js/pages/app-shell.js')
@endpush

@section('page')
    <div class="as">
        @include('zina.partials.prototype-bar', ['shellStates' => $shellStates])

        <main id="main" class="as-main">
            @yield('content')
        </main>
    </div>
@endsection
