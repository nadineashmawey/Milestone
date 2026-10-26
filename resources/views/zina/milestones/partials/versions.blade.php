{{-- Version history (SUB-2, DLN-7): every version is kept, newest first. Late is a flag with no penalty (SUB-3). --}}
<details class="ml-versions" @if ($open ?? false) open @endif>
    <summary>Version history <span class="as-meta">· {{ count($versions) }} {{ count($versions) === 1 ? 'version' : 'versions' }}, all kept</span></summary>
    <ol>
        @foreach ($versions as $version)
            <li>
                <div class="ml-version-head">
                    <strong>Version {{ $version['number'] }}</strong>
                    @if ($loop->first)<x-badge tone="info">Latest</x-badge>@endif
                    <x-badge>{{ $version['status'] }}</x-badge>
                    @if ($version['late'])<span class="as-flag as-flag--warning">@include('public.partials.icon', ['name' => 'flag', 'size' => 13]) Late · no penalty</span>@endif
                </div>
                <p class="as-meta">{{ $version['when'] }} · submitted by {{ $version['submitter'] }}</p>
                <ul class="ml-version-files">
                    @foreach ($version['files'] as $file)<li>@include('public.partials.icon', ['name' => 'file', 'size' => 14]) {{ $file['name'] }} <span>{{ $file['size'] }}</span></li>@endforeach
                    @if ($version['link'])<li class="ml-break">@include('public.partials.icon', ['name' => 'link', 'size' => 14]) {{ $version['link'] }}</li>@endif
                </ul>
            </li>
        @endforeach
    </ol>
</details>
