@props(['variant' => 'primary', 'type' => 'button', 'size' => null, 'href' => null, 'loading' => false])
@php
    $variant = in_array($variant, ['primary', 'secondary', 'dark', 'danger', 'ghost'], true) ? $variant : 'primary';
    $classes = [
        'ms-button',
        'ms-button--'.$variant => $variant !== 'secondary',
        'ms-button--'.$size => in_array($size, ['small', 'large'], true),
        'ms-button--loading' => $loading,
    ];
    if ($loading) {
        $attributes = $attributes->merge(['disabled' => true, 'aria-busy' => 'true']);
    }
@endphp
@if ($href)
<a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
<button type="{{ $type }}" {{ $attributes->class($classes) }}>@if ($loading)<span class="ms-spinner" aria-hidden="true"></span>@endif{{ $slot }}</button>
@endif
