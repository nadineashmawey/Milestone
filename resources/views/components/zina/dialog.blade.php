{{--
    Confirm dialog (Zina's pages). Opened by a link with data-as-dialog-open="{id}" and href="?confirm={id}".
    With JavaScript it opens as a modal; without it, the link reloads the page and the server renders it open.
--}}
@props(['id', 'title', 'open' => false])
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" {{ $attributes->class(['as-dialog']) }}
    data-as-dialog @if ($open) open data-as-dialog-static @endif>
    <div class="as-dialog-panel">
        <h2 id="{{ $id }}-title" class="as-dialog-title">{{ $title }}</h2>
        {{ $slot }}
    </div>
</dialog>
