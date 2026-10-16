@extends('layouts.base')

@section('body')
    {{-- The shared professor navbar will be added here later. --}}
    <main id="main" class="ms-content">
        @yield('content')
    </main>
@endsection
