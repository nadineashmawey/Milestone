@props(['variant' => 'primary', 'type' => 'button'])
@php
    $variant = in_array($variant, ['primary', 'secondary', 'dark', 'danger'], true) ? $variant : 'primary';
@endphp
<button type="{{ $type }}" {{ $attributes->class(['ms-button', 'ms-button--'.$variant => $variant !== 'secondary']) }}>{{ $slot }}</button>
