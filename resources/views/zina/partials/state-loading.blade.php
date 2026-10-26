{{-- Loading state (DSH-3): a skeleton of the page. $blocks: how many panels. $label: what is loading. --}}
<div class="as-loading" role="status" aria-live="polite">
    <span class="as-sr">{{ $label ?? 'Loading' }}…</span>
    <div class="as-skeleton-head" aria-hidden="true">
        <i class="as-skeleton as-skeleton--eyebrow"></i>
        <i class="as-skeleton as-skeleton--title"></i>
        <i class="as-skeleton as-skeleton--line"></i>
    </div>
    <div class="as-skeleton-grid" aria-hidden="true">
        @for ($i = 0; $i < ($blocks ?? 4); $i++)
            <div class="as-panel as-skeleton-panel">
                <i class="as-skeleton as-skeleton--heading"></i>
                <i class="as-skeleton as-skeleton--line"></i>
                <i class="as-skeleton as-skeleton--line as-skeleton--short"></i>
                <i class="as-skeleton as-skeleton--block"></i>
            </div>
        @endfor
    </div>
</div>
