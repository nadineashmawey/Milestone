{{-- Result of the last action, or why it was refused. --}}
@if (session('team_status'))
    <x-alert tone="success" title="Done">
        <p>{{ session('team_status') }}</p>
    </x-alert>
@endif
@if ($teamErrors->isNotEmpty())
    <x-alert tone="error" title="That didn’t go through.">
        @foreach ($teamErrors as $message)<p>{{ $message }}</p>@endforeach
    </x-alert>
@endif
