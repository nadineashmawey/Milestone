@extends('layouts.app')

{{-- TA pages. The navbar comes from layouts.app; this layout keeps the TA stylesheets and page wrapper. --}}

@prepend('styles')
    <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'admin-static.css']) }}">
    <link rel="stylesheet" href="{{ route('ta.assets.css', ['path' => 'ta-static.css']) }}">
@endprepend

@prepend('scripts')
    <script src="{{ route('ta.assets.js', ['path' => 'components/ta-dropdowns.js']) }}"></script>
@endprepend

@section('page')
    <main class="ta-page" id="main">
        <div class="ta-container">
            @yield('content')
        </div>
    </main>
    @stack('overlays')
@endsection
