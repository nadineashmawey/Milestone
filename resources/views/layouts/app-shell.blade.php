@extends('layouts.base')

{{--
    Signed-in shell (Zina): role-based top bar from the App.tsx prototype, a prototype-controls bar, and the page.
    Extend with data: @extends('layouts.app-shell', ['shellRole' => 'student', 'shellActive' => 'dashboard'])
    Sections: content (required), prototype-controls (optional extra switches for the prototype bar).
    Pages that support ?state= previews pass 'shellStates' => true.
--}}
@php
    use App\Support\AppShellNavigation;
    use App\Support\ZinaDemoData;

    $demo = app(ZinaDemoData::class);
    $shellRole = $shellRole ?? 'student';
    $shellActive = $shellActive ?? null;
    $shellStates = ($shellStates ?? false) && $demo->statesEnabled();
    $navigation = AppShellNavigation::for($shellRole);
    $roleLabels = ['student' => 'Student', 'professor' => 'Professor', 'ta' => 'Teaching assistant', 'admin' => 'Admin'];
    $shellPerson = match ($shellRole) {
        'professor' => $demo->person('lee'),
        'ta' => $demo->person('sara'),
        'admin' => $demo->person('admin'),
        default => $demo->viewer(),
    };
    $homeHref = $navigation[0]['href'] ?? url('/');
@endphp

@push('styles')
    @vite('resources/css/pages/app-shell.css')
@endpush

@push('scripts')
    @vite('resources/js/pages/app-shell.js')
@endpush

@section('body')
    <div class="as">
        <header class="as-topbar">
            <div class="as-topbar-inner">
                <a class="as-logo" href="{{ $homeHref }}">
                    <img src="{{ asset('images/milestone-logo.png') }}" alt="Milestone dashboard" width="640" height="149">
                </a>

                <nav class="as-nav" aria-label="{{ $roleLabels[$shellRole] ?? 'Main' }} navigation">
                    @foreach ($navigation as $item)
                        <a href="{{ $item['href'] }}"
                            @class(['as-nav-link', 'is-active' => $item['key'] === $shellActive, 'is-unavailable' => ! $item['available']])
                            @if ($item['key'] === $shellActive) aria-current="page" @endif
                            @unless ($item['available']) aria-disabled="true" title="{{ $item['owner'] }}’s page isn’t in this branch yet" @endunless>
                            @include('public.partials.icon', ['name' => $item['icon'], 'size' => 18])
                            <span class="as-nav-label">{{ $item['label'] }}@unless ($item['available'])<span class="as-sr"> (coming soon, {{ $item['owner'] }}’s page)</span>@endunless</span>
                        </a>
                    @endforeach
                </nav>

                <div class="as-actions">
                    <span class="as-role">{{ $roleLabels[$shellRole] ?? 'Student' }}</span>
                    <a class="as-icon-link" href="{{ route('help') }}" title="Help">
                        @include('public.partials.icon', ['name' => 'help', 'size' => 19])<span class="as-sr">Help</span>
                    </a>
                    {{-- TODO(Raghad): the shared Notifications page owns this link once its route is merged. --}}
                    <a class="as-icon-link" href="{{ AppShellNavigation::href('notifications') }}" title="Notifications">
                        @include('public.partials.icon', ['name' => 'bell', 'size' => 19])<span class="as-sr">Notifications</span>
                    </a>
                    <a class="as-avatar-link" href="{{ AppShellNavigation::href('profile.edit') }}" title="Your profile">
                        <span class="as-avatar as-avatar--{{ $shellPerson['avatar'] }}" aria-hidden="true">{{ $demo->initials($shellPerson['name']) }}</span>
                        <span class="as-sr">Your profile: {{ $shellPerson['name'] }}</span>
                    </a>
                </div>
            </div>
        </header>

        @include('zina.partials.prototype-bar', ['shellStates' => $shellStates])

        <main id="main" class="as-main">
            @yield('content')
        </main>
    </div>
@endsection
