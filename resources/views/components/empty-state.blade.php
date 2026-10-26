@props(['title'])
<div {{ $attributes->class(['ms-empty']) }}>
    <strong class="ms-empty__title">{{ $title }}</strong>
    @if ($slot->isNotEmpty())<div class="ms-empty__body">{{ $slot }}</div>@endif
    @isset($action)<div class="ms-empty__action">{{ $action }}</div>@endisset
</div>
