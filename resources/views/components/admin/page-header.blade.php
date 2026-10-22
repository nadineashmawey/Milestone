@props(['eyebrow', 'title', 'description'])
<header {{ $attributes->class(['page-header']) }}>
    <div>
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        <p>{{ $description }}</p>
    </div>
    {{ $slot }}
</header>
