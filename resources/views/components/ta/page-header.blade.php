@props(['eyebrow' => 'Teaching assistant workspace', 'title', 'description'])
<header {{ $attributes->class(['ta-header']) }}>
    <div>
        <p class="eyebrow">{{ $eyebrow }}</p>
        <h1>{{ $title }}</h1>
        <p class="lead">{{ $description }}</p>
    </div>
    {{ $slot }}
</header>
