{{-- PRJ-1: every project shows its type in words. $type: "Academic" or "Independent". --}}
<x-badge :tone="$type === 'Independent' ? 'success' : 'info'" class="as-type">{{ $type }}</x-badge>
