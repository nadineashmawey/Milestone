@extends('layouts.base')

{{--
    The shared signed-in layout: the role-based navbar, then the page. Pages never add their own navbar.
    Pages define "content", which goes inside <main id="main" class="ms-content ms-stack"> (change the class with "main-class").
    Layouts that wrap content themselves (admin, ta, app-shell) or pages with their own <main> define "page" instead.
    See docs/navbar.md.
--}}
@section('body')
    <x-navbar />
    @hasSection('page')
        @yield('page')
    @else
        <main id="main" class="@yield('main-class', 'ms-content ms-stack')">
            @yield('content')
        </main>
    @endif
@endsection
