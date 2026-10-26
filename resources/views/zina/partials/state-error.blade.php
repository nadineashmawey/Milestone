{{-- Error state (DSH-3, NFR-34): a plain message and a retry link. No technical details. --}}
<section class="as-panel as-error" role="alert" aria-labelledby="as-error-title">
    <span class="as-error-icon" aria-hidden="true">@include('public.partials.icon', ['name' => 'alert', 'size' => 24])</span>
    <div>
        <h2 id="as-error-title">{{ $title }}</h2>
        <p>{{ $message ?? 'Your work is safe. This is usually temporary, so try again in a moment.' }}</p>
        <div class="as-error-actions">
            <x-button :href="request()->fullUrlWithoutQuery(['state'])">@include('public.partials.icon', ['name' => 'refresh', 'size' => 18]) Try again</x-button>
            <x-button variant="secondary" :href="route('help').'#report'">Report a problem</x-button>
        </div>
    </div>
</section>
