@extends('layouts.base')

@section('body')
    <div class="ms-shell">
        <aside class="ms-sidebar">
            <a class="ms-brand" href="{{ url('/') }}"><svg viewBox="0 0 32 24" aria-hidden="true"><path d="m2 20 7-5 6 2 8-9 6-4"/><circle cx="3" cy="20" r="2"/><circle cx="9" cy="15" r="2"/><circle cx="15" cy="17" r="2"/><circle cx="23" cy="8" r="2"/><circle class="ms-brand__accent" cx="29" cy="4" r="2.5"/></svg><span>Milestone</span></a>
            <nav class="ms-navigation" aria-label="Main navigation">
                @yield('navigation')
            </nav>
        </aside>
        <div class="ms-workspace">
            <header class="ms-topbar">
                <span>@yield('workspace-name', 'Milestone')</span>
                <div class="ms-row">
                    <span class="ms-user-role" aria-label="Teaching assistant account">TA</span>
                    <button class="ms-topbar-icon" type="button" data-theme-toggle aria-label="Toggle dark mode" title="Toggle dark mode"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 15.5A8.5 8.5 0 0 1 8.5 3.5a8.5 8.5 0 1 0 12 12Z"/></svg></button>
                    <button class="ms-topbar-icon" type="button" aria-label="Help" title="Help">?</button>
                    <div class="ms-notification-menu">
                        <button class="ms-notifications-link" type="button" data-notifications-toggle aria-label="Notifications" title="Notifications" aria-expanded="false" aria-controls="ms-notifications-popover">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                            <span class="ms-notifications-link__dot" aria-hidden="true"></span>
                        </button>
                        <section class="ms-notifications-popover" id="ms-notifications-popover" data-notifications-popover hidden aria-label="Notification preview">
                            <header><strong>Notifications</strong><a data-notifications-view-all href="{{ route('notifications') }}">View all</a></header>
                            <div data-notification-preview></div>
                        </section>
                    </div>
                    <span class="ms-user-avatar" aria-label="Sara Ahmed">SA</span>
                    @yield('topbar-actions')
                </div>
            </header>
            <main id="main" class="ms-content ms-stack">
                @yield('content')
            </main>
        </div>
    </div>
@endsection
