@props(['title', 'eyebrow' => null, 'description' => null, 'level' => 2, 'titleId' => null])
@php
    $tag = (int) $level === 3 ? 'h3' : 'h2';
@endphp
<div {{ $attributes->class(['ms-section-heading']) }}>
    @if ($eyebrow)<p class="ms-section-heading__eyebrow">{{ $eyebrow }}</p>@endif
    <{{ $tag }} @if ($titleId) id="{{ $titleId }}" @endif class="ms-section-heading__title">{{ $title }}</{{ $tag }}>
    @if ($description)<p class="ms-section-heading__description">{{ $description }}</p>@endif
    {{ $slot }}
</div>
