@props(['id', 'name', 'label', 'value' => null, 'help' => null, 'error' => null])
@php
    $message = $error ?? $errors->first($name);
    $description = trim(($attributes->get('aria-describedby') ?? '').' '.(($message || $help) ? $id.'-message' : ''));
@endphp
<div class="ms-field">
    <label for="{{ $id }}" class="ms-field__label">{{ $label }}</label>
    <textarea id="{{ $id }}" name="{{ $name }}"
        @if ($message) aria-invalid="true" @endif
        @if ($description) aria-describedby="{{ $description }}" @endif
        {{ $attributes->except(['aria-describedby', 'aria-invalid'])->class(['ms-input', 'ms-textarea']) }}>{{ $value }}</textarea>
    @if ($message || $help)
        <small id="{{ $id }}-message" class="{{ $message ? 'ms-field__error' : 'ms-field__help' }}">{{ $message ?: $help }}</small>
    @endif
</div>
