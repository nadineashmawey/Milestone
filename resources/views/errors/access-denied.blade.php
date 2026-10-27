@extends('layouts.app')

@section('title', 'Access denied')

@section('content')
    {{-- DSH-4: one generic page whenever a role opens another role's page. The navbar stays the visitor's own. --}}
    <x-page-header title="You don’t have access to this page" description="This page belongs to a different role. You’re signed in as {{ $roleLabel }}." />
    <x-card>
        <x-empty-state title="Nothing here for your role.">
            <p>If you think you should see this page, ask your administrator to check your role.</p>
            <x-slot:action><x-button :href="$dashboardHref">Go to your dashboard</x-button></x-slot:action>
        </x-empty-state>
    </x-card>
@endsection
