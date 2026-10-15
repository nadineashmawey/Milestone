@extends('layouts.base')

@section('body')
    <div class="ms-shell">
        <aside class="ms-sidebar">
            <a class="ms-brand" href="{{ url('/') }}">Milestone</a>
            <nav class="ms-navigation" aria-label="Main navigation">
                @yield('navigation')
            </nav>
        </aside>
        <div class="ms-workspace">
            <header class="ms-topbar">
                <span>@yield('workspace-name', 'Milestone')</span>
                <div class="ms-row">@yield('topbar-actions')</div>
            </header>
            <main id="main" class="ms-content ms-stack">
                @yield('content')
            </main>
        </div>
    </div>
@endsection
