@props(['title' => null])
<section {{ $attributes->class(['ms-card']) }}>
    @if ($title)<h2>{{ $title }}</h2>@endif
    {{ $slot }}
</section>
