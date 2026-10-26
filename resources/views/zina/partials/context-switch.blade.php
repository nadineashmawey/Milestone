{{--
    One prototype switch: a POST form whose buttons set one context value (viewer, phase or role).
    $field: context key. $label: visible label. $options: value => label. $current: current value.
--}}
<form class="as-proto-switch" method="POST" action="{{ route('prototype.context') }}">
    @csrf
    <input type="hidden" name="redirect" value="{{ request()->getRequestUri() }}">
    <span class="as-proto-label" id="proto-{{ $field }}-label">{{ $label }}</span>
    <span class="as-segmented" role="group" aria-labelledby="proto-{{ $field }}-label">
        @foreach ($options as $value => $text)
            <button type="submit" name="{{ $field }}" value="{{ $value }}" aria-pressed="{{ $current === $value ? 'true' : 'false' }}">{{ $text }}</button>
        @endforeach
    </span>
</form>
