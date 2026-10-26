{{-- Progress ring. $value: 0–100. $label: what the percentage measures (read by screen readers). --}}
<span class="as-ring as-ring--{{ $tone ?? 'blue' }}" style="--p: {{ (int) $value }}" role="img" aria-label="{{ $label ?? 'Progress' }}: {{ (int) $value }}%">
    <span aria-hidden="true">{{ (int) $value }}%</span>
</span>
