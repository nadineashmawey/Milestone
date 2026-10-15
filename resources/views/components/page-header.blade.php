@props(['title', 'description' => null])
<header {{ $attributes->class(['ms-page-header']) }}>
    <div>
        <h1>{{ $title }}</h1>
        @if ($description)<p class="ms-muted">{{ $description }}</p>@endif
    </div>
    {{ $slot }}
</header>
