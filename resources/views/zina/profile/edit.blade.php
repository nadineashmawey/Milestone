@extends('layouts.app-shell')

@section('title', 'Profile')

@push('styles')
    @vite('resources/css/pages/profile.css')
@endpush

@section('content')
    @php
        $p = $profile;
        $v = $p['values'];
        $correction = route('help').'#report';
        // After a failed save, unticked boxes aren't in the old input, so they must stay unticked.
        $hasOld = session()->hasOldInput();
    @endphp
    <div class="as-page pf">
        @if ($state === 'loading')
            @include('zina.partials.state-loading', ['label' => 'Loading your profile', 'blocks' => 3])
        @elseif ($state === 'error')
            @include('zina.partials.state-error', ['title' => 'We couldn’t load your profile.', 'message' => 'Your settings haven’t changed. This is usually temporary, so try again in a moment.'])
        @else
            <header class="as-head pf-head">
                <div class="pf-identity">
                    @include('zina.partials.avatar', ['name' => $p['display_name'] ?: $p['person']['name'], 'index' => $p['person']['avatar'], 'class' => 'as-avatar--lg'])
                    <div>
                        <p class="as-eyebrow">Account</p>
                        <h1>{{ $p['display_name'] ?: $p['person']['name'] }}</h1>
                        <div class="as-badges pf-badges">
                            <x-badge tone="info">{{ $p['role_label'] }}</x-badge>
                            <span class="as-meta">{{ $p['person']['email'] }}</span>
                        </div>
                    </div>
                </div>
            </header>

            @if ($state === 'empty')
                <x-alert tone="info" title="Welcome to Milestone. Let’s finish your profile.">
                    <p>Check your name and choose how you hear about deadlines and feedback. You can skip this and change it any time.</p>
                    <p><a href="{{ $p['dashboard_href'] }}">Skip for now</a></p>
                </x-alert>
            @endif

            @if (session('profile_saved'))
                <x-alert tone="success" title="Saved">
                    <p>Your profile and notification preferences are up to date.</p>
                </x-alert>
            @endif
            @if ($errors->any())
                <x-alert tone="error" title="We couldn’t save your changes.">
                    <p>Fix {{ $errors->count() === 1 ? 'the highlighted field' : 'the '.$errors->count().' highlighted fields' }} and save again. Nothing else changed.</p>
                </x-alert>
            @endif

            <form method="POST" action="{{ route('profile.update') }}" class="pf-form" data-as-unsaved novalidate>
                @csrf
                @method('PUT')

                <div class="pf-grid">
                    <section class="as-panel" aria-labelledby="pf-personal-title">
                        <div class="as-panel-head"><div><h2 id="pf-personal-title">Personal details</h2><p>Shown to your teams and reviewers.</p></div></div>
                        <div class="pf-fields">
                            <x-input id="pf-first" name="first_name" label="First name" :value="old('first_name', $v['first_name'])" autocomplete="given-name" required />
                            <x-input id="pf-last" name="last_name" label="Last name" :value="old('last_name', $v['last_name'])" autocomplete="family-name" required />
                        </div>

                        <h3 class="pf-subhead">Display preferences</h3>
                        <div class="pf-fields pf-fields--three">
                            <x-select id="pf-date" name="date_format" label="Date format" :options="$options['date_format']" :selected="old('date_format', $v['date_format'])" />
                            <x-select id="pf-time" name="time_format" label="Time format" :options="$options['time_format']" :selected="old('time_format', $v['time_format'])" />
                            <x-select id="pf-week" name="week_start" label="Week starts on" :options="$options['week_start']" :selected="old('week_start', $v['week_start'])" />
                        </div>
                    </section>

                    <section class="as-panel" aria-labelledby="pf-account-title">
                        <div class="as-panel-head"><div><h2 id="pf-account-title">Account details</h2><p>Set by your university. Only an administrator can change them.</p></div></div>
                        <dl class="pf-locked">
                            @foreach ($p['locked'] as $field)
                                <div>
                                    <dt>{{ $field['label'] }}</dt>
                                    <dd>
                                        <span class="pf-locked-value">@include('public.partials.icon', ['name' => 'lock', 'size' => 15]){{ $field['value'] }}<span class="as-sr"> (read-only)</span></span>
                                        <a class="as-text-link" href="{{ $correction }}">Request a correction<span class="as-sr"> to your {{ strtolower($field['label']) }}</span></a>
                                    </dd>
                                </div>
                            @endforeach
                            @foreach ($p['details'] as $detail)
                                <div>
                                    <dt>{{ $detail['label'] }}</dt>
                                    <dd>
                                        @isset($detail['list'])
                                            <ul class="pf-list">@foreach ($detail['list'] as $item)<li>{{ $item }}</li>@endforeach</ul>
                                        @else
                                            @isset($detail['tone'])<x-badge :tone="$detail['tone']">{{ $detail['value'] }}</x-badge>@else<span class="pf-locked-value">{{ $detail['value'] }}</span>@endisset
                                        @endisset
                                        @isset($detail['link'])
                                            <a class="as-text-link" href="{{ $detail['link']['href'] }}" @if ($detail['link']['href'] === '#') aria-disabled="true" @endif>{{ $detail['link']['label'] }}</a>
                                            @if ($detail['link']['href'] === '#')<small class="as-note">Security is {{ $detail['link']['owner'] }}’s page, coming soon</small>@endif
                                        @endisset
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                </div>

                <section class="as-panel" aria-labelledby="pf-notify-title">
                    <div class="as-panel-head"><div><h2 id="pf-notify-title">Notifications</h2><p>Choose where each kind of update reaches you. Email is simulated; nothing is sent.</p></div></div>
                    <table class="pf-notify">
                        <caption class="as-sr">Notification channels for each category</caption>
                        <thead><tr><th scope="col">Category</th>@foreach ($channels as $channel => $label)<th scope="col">{{ $label }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($categories as $category => [$label, $description])
                                @php($message = $errors->first('notifications.'.$category))
                                <tr @class(['has-error' => $message])>
                                    <th scope="row">
                                        <strong id="pf-cat-{{ $category }}">{{ $label }}</strong>
                                        <small>{{ $description }}</small>
                                        @if ($message)<small class="ms-field__error" id="pf-cat-{{ $category }}-error">{{ $message }}</small>@endif
                                    </th>
                                    @foreach ($channels as $channel => $channelLabel)
                                        <td data-label="{{ $channelLabel }}">
                                            <label class="pf-toggle">
                                                <input type="checkbox" name="notifications[{{ $category }}][{{ $channel }}]" value="1"
                                                    @checked($hasOld ? filter_var(old('notifications.'.$category.'.'.$channel), FILTER_VALIDATE_BOOLEAN) : $v['notifications'][$category][$channel])
                                                    @if ($message) aria-invalid="true" aria-describedby="pf-cat-{{ $category }}-error" @endif>
                                                <span class="as-sr">{{ $channelLabel }} notifications for {{ $label }}</span>
                                                <span class="pf-toggle-text" aria-hidden="true">{{ $channelLabel }}</span>
                                            </label>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>

                <div class="pf-actions">
                    <p class="as-unsaved" data-as-unsaved-status hidden>@include('public.partials.icon', ['name' => 'alert', 'size' => 16]) You have unsaved changes.</p>
                    @if ($p['saved_at'])<p class="as-note">Last saved today at {{ $p['saved_at'] }}</p>@endif
                    <div class="pf-buttons">
                        <x-button type="reset" variant="secondary">Undo changes</x-button>
                        <x-button type="submit" data-as-busy="Saving…">Save changes</x-button>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection
