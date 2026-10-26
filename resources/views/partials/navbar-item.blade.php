{{-- One navbar item, used by components/navbar.blade.php for the bar and the mobile panel. Not for use in pages. --}}
@php
    $badge = $item['badge'];
    $spoken = $badge ? ', '.$badge['count'].' '.$badge['label'] : '';
@endphp
@if ($item['available'])
    <a href="{{ $item['href'] }}" @class([$class, 'is-active' => $item['active']]) data-label="{{ $item['label'] }}" @if ($item['active'])aria-current="page"@endif>
        @include('public.partials.icon', ['name' => $item['icon'], 'size' => 18])
        <span class="ms-navbar__label">{{ $item['label'] }}</span>
        @if ($badge)<span class="ms-navbar__badge" aria-hidden="true">{{ $badge['count'] }}</span>@endif
        @if ($spoken)<span class="ms-sr">{{ $spoken }}</span>@endif
    </a>
@else
    {{-- The owner's page doesn't exist yet: shown, but not a link. --}}
    <span class="{{ $class }} is-disabled" aria-disabled="true" title="Coming soon" data-tooltip="Coming soon" data-label="{{ $item['label'] }}">
        @include('public.partials.icon', ['name' => $item['icon'], 'size' => 18])
        <span class="ms-navbar__label">{{ $item['label'] }}</span>
        <span class="ms-sr">(coming soon)</span>
    </span>
@endif
