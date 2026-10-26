{{-- Prototype controls: stand-ins for sign-in, plus dev-only state previews. Never part of the real product. --}}
@php
    $currentState = request()->query('state', 'populated');
    $stateLabels = ['populated' => 'Populated', 'empty' => 'Empty', 'loading' => 'Loading', 'error' => 'Error'];
@endphp
<aside class="as-proto" aria-label="Prototype controls">
    <div class="as-proto-inner">
        <p class="as-proto-note"><strong>Prototype</strong> Sample data. Changes are saved for this session only.</p>

        @hasSection('prototype-controls')
            <div class="as-proto-group">@yield('prototype-controls')</div>
        @endif

        @if ($shellStates)
            <nav class="as-proto-group" aria-label="Preview page state">
                <span class="as-proto-label">State</span>
                <span class="as-segmented">
                    @foreach ($stateLabels as $state => $label)
                        <a href="{{ $state === 'populated' ? request()->fullUrlWithoutQuery(['state', 'confirm']) : request()->fullUrlWithQuery(['state' => $state, 'confirm' => null]) }}"
                            @if ($currentState === $state || ($state === 'populated' && ! in_array($currentState, array_keys($stateLabels), true))) aria-current="true" @endif>{{ $label }}</a>
                    @endforeach
                </span>
            </nav>
        @endif
    </div>
</aside>
