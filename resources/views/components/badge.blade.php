@props(['tone' => 'neutral'])
@php
    $tone = in_array($tone, ['neutral', 'info', 'success', 'warning', 'danger', 'review'], true) ? $tone : 'neutral';
@endphp
<span {{ $attributes->class(['ms-badge', 'ms-badge--'.$tone => $tone !== 'neutral']) }}>{{ $slot }}</span>
