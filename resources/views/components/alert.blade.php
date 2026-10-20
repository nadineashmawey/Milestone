@props(['tone' => 'info', 'title' => null])
@php
    $tone = in_array($tone, ['info', 'success', 'error'], true) ? $tone : 'info';
@endphp
<div {{ $attributes->merge(['role' => $tone === 'error' ? 'alert' : 'status'])->class(['ms-alert', 'ms-alert--'.$tone]) }}>
    <svg class="ms-alert__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        @if ($tone === 'success')
            <path d="M21 11.5V12a9 9 0 1 1-5.34-8.23"/><path d="m21 4-9 9-3-3"/>
        @else
            <circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16.5v.01"/>
        @endif
    </svg>
    <div class="ms-alert__body">
        @if ($title)<strong class="ms-alert__title">{{ $title }}</strong>@endif
        {{ $slot }}
    </div>
</div>
