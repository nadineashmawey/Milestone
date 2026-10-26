{{-- Academic team (TEAM-1–11). --}}
@php
    $forming = $team['phase'] === 'forming';
    $fillsTeam = $team['member_count'] + 1 >= $team['capacity'];
    $leaveOpen = $confirm === 'leave-dialog' || $errors->has('reason');
@endphp

<header class="as-head">
    <div>
        <p class="as-eyebrow">{{ $project['title'] }} · {{ $project['course_code'] }}</p>
        <h1>{{ $project['team'] }}</h1>
        <p class="as-head-lede">{{ $team['member_count'] }} of {{ $team['capacity'] }} members · Formation {{ $forming ? 'closes' : 'closed' }} {{ $team['deadline_label'] }}</p>
        <div class="as-badges st-head-badges">
            @include('zina.partials.project-type', ['type' => $project['type']])
            <x-badge :tone="$forming ? 'warning' : 'neutral'">{{ $forming ? 'Formation open' : 'Locked' }}</x-badge>
            @if ($team['is_full'])<x-badge tone="info">Team full</x-badge>@endif
        </div>
    </div>
    <div class="as-head-actions">
        @if ($team['leave'])
            <x-badge tone="review">Leave request pending</x-badge>
        @else
            <a class="ms-button ms-button--danger" href="{{ request()->fullUrlWithQuery(['confirm' => 'leave-dialog']) }}#leave-dialog" data-as-dialog-open="leave-dialog">Request to leave team</a>
        @endif
    </div>
</header>

@include('zina.team.partials.flash')

@if ($forming)
    <x-alert tone="info" title="Team formation is open until {{ $team['deadline_label'] }}.">
        <p>This is Team 1 as it was on {{ $team['clock'] }}. Send, accept and withdraw requests until the deadline. Changes here stay in this preview and don’t change today’s locked team.</p>
    </x-alert>
@else
    <x-alert tone="info" title="Teams are locked.">
        <p>Formation closed on {{ $team['deadline_label'] }}. Requests that were still pending were cancelled automatically, and changes now go through Professor Lee.</p>
    </x-alert>
@endif

<dl class="st-summary">
    <div>
        <dt>Members</dt>
        <dd><strong>{{ $team['member_count'] }} of {{ $team['capacity'] }}</strong>
            <span class="as-bar" aria-hidden="true"><span style="width: {{ $team['member_count'] / $team['capacity'] * 100 }}%"></span></span>
            <small>{{ $team['is_full'] ? 'Team full' : ($team['capacity'] - $team['member_count']).' seat'.($team['capacity'] - $team['member_count'] === 1 ? '' : 's').' open' }} · minimum {{ $team['minimum'] }}</small>
        </dd>
    </div>
    <div>
        <dt>Formation deadline</dt>
        <dd><strong>{{ $team['deadline']->format('M j, g:i A') }}</strong> <x-badge :tone="$team['deadline_countdown']['tone']">{{ $team['deadline_countdown']['label'] }}</x-badge></dd>
    </div>
    <div>
        <dt>Team leader</dt>
        <dd>
            @if ($team['leader'])
                <strong>{{ $team['leader']['name'] }}</strong><small>Assigned by Professor Lee · no voting</small>
            @else
                <strong>Not assigned yet</strong><small>Professor Lee assigns a leader after teams lock.</small>
            @endif
        </dd>
    </div>
</dl>

<div class="st-grid">
    <section class="as-panel" aria-labelledby="st-members-title">
        <div class="as-panel-head">
            <div>
                <h2 id="st-members-title">Members</h2>
                <p>The professor or TA assigns the leader. There’s no vote.</p>
            </div>
        </div>
        <ul class="as-rows st-members">
            @foreach ($team['members'] as $member)
                <li>
                    @include('zina.partials.avatar', ['name' => $member['name'], 'index' => $member['avatar']])
                    <div>
                        <strong>{{ $member['name'] }}@if ($member['is_viewer'])<span class="st-you"> (you)</span>@endif</strong>
                        <small>{{ $member['is_leader'] ? 'Assigned by Professor Lee' : 'Member' }}</small>
                    </div>
                    @if ($member['is_leader'])<x-badge tone="warning">★ Leader</x-badge>@endif
                </li>
            @endforeach
        </ul>

        @if ($team['leave'])
            <div class="st-leave" aria-labelledby="st-leave-title">
                <h3 id="st-leave-title">Your leave request is waiting for Professor Lee</h3>
                <p>Sent {{ $team['leave']['when'] }}. You stay on {{ $project['team'] }} and keep working until it’s approved.</p>
                <blockquote>“{{ $team['leave']['reason'] }}”</blockquote>
                <form method="POST" action="{{ route('student.team.withdraw-leave', $project['id']) }}">
                    @csrf
                    <x-button type="submit" variant="secondary" size="small">Withdraw leave request</x-button>
                </form>
            </div>
        @endif
    </section>

    <section class="as-panel" aria-labelledby="st-requests-title">
        <div class="as-panel-head">
            <div>
                <h2 id="st-requests-title">Team requests</h2>
                <p>{{ $forming ? $team['pending_outgoing'].' of '.$team['max_pending'].' pending requests sent' : 'Closed when formation locked' }}</p>
            </div>
        </div>

        <h3 class="st-subhead">Requests to join {{ $project['team'] }}</h3>
        <ul class="as-rows st-requests">
            @forelse ($team['incoming'] as $request)
                @php($person = app(\App\Support\ZinaDemoData::class)->classmate($request['student']))
                <li>
                    @include('zina.partials.avatar', ['name' => $person['name'], 'index' => $person['avatar']])
                    <div><strong>{{ $person['name'] }}</strong><small>Asked to join · {{ $request['sent'] }}</small></div>
                    @if ($request['status'] === 'Pending' && $forming)
                        <div class="st-request-actions">
                            <form method="POST" action="{{ route('student.team.decline', [$project['id'], $request['id']]) }}">
                                @csrf
                                <x-button type="submit" variant="secondary" size="small">Decline<span class="as-sr"> {{ $person['name'] }}</span></x-button>
                            </form>
                            @if ($team['is_full'])
                                <x-button size="small" disabled aria-describedby="st-full-note">Accept<span class="as-sr"> {{ $person['name'] }}</span></x-button>
                            @elseif ($fillsTeam)
                                <a class="ms-button ms-button--primary ms-button--small" href="{{ request()->fullUrlWithQuery(['confirm' => 'accept-'.$request['id']]) }}#accept-{{ $request['id'] }}" data-as-dialog-open="accept-{{ $request['id'] }}">Accept<span class="as-sr"> {{ $person['name'] }}</span></a>
                                <x-zina.dialog id="accept-{{ $request['id'] }}" title="Accept {{ $person['name'] }}?" :open="$confirm === 'accept-'.$request['id']">
                                    <p>{{ $person['name'] }} joins {{ $project['team'] }}, and the team reaches its maximum of {{ $team['capacity'] }} members. After that you can’t accept anyone else, and your other requests stay pending until you withdraw them.</p>
                                    <form method="POST" action="{{ route('student.team.accept', [$project['id'], $request['id']]) }}" class="as-dialog-actions">
                                        @csrf
                                        <input type="hidden" name="confirmed" value="1">
                                        <a class="ms-button" href="{{ request()->fullUrlWithoutQuery(['confirm']) }}" data-as-dialog-close>Cancel</a>
                                        <x-button type="submit">Accept and fill the team</x-button>
                                    </form>
                                </x-zina.dialog>
                            @else
                                <form method="POST" action="{{ route('student.team.accept', [$project['id'], $request['id']]) }}">
                                    @csrf
                                    <x-button type="submit" size="small">Accept<span class="as-sr"> {{ $person['name'] }}</span></x-button>
                                </form>
                            @endif
                        </div>
                    @else
                        <x-badge :tone="['Accepted' => 'success', 'Declined' => 'danger', 'Cancelled' => 'neutral'][$request['status']] ?? 'neutral'">{{ $request['status'] }}</x-badge>
                    @endif
                </li>
            @empty
                <li class="st-none">No requests to join.</li>
            @endforelse
        </ul>
        @if ($forming && $team['is_full'])
            <p class="as-restriction" id="st-full-note">@include('public.partials.icon', ['name' => 'users', 'size' => 18]) <span><strong>{{ $project['team'] }} is full.</strong> It has the maximum of {{ $team['capacity'] }} members, so new requests can’t be accepted.</span></p>
        @endif

        <h3 class="st-subhead">Requests you sent</h3>
        <ul class="as-rows st-requests">
            @foreach ($team['outgoing'] as $request)
                @php($person = app(\App\Support\ZinaDemoData::class)->classmate($request['student']))
                <li>
                    @include('zina.partials.avatar', ['name' => $person['name'], 'index' => $person['avatar']])
                    <div><strong>{{ $person['name'] }}</strong><small>Invited to {{ $project['team'] }} · {{ $request['sent'] }}</small></div>
                    @if ($request['status'] === 'Pending' && $forming)
                        <form method="POST" action="{{ route('student.team.withdraw', [$project['id'], $request['id']]) }}">
                            @csrf
                            <x-button type="submit" variant="secondary" size="small">Withdraw<span class="as-sr"> request to {{ $person['name'] }}</span></x-button>
                        </form>
                    @else
                        <x-badge :tone="$request['status'] === 'Pending' ? 'warning' : 'neutral'">{{ $request['status'] }}</x-badge>
                    @endif
                </li>
            @endforeach
        </ul>

        @if ($forming)
            <form method="POST" action="{{ route('student.team.send', $project['id']) }}" class="st-send">
                @csrf
                <x-select id="st-student" name="student" label="Send a request" placeholder="Choose a classmate"
                    :options="array_combine($team['classmates'], $team['classmates'])" :selected="old('student')"
                    :help="$team['is_full'] ? 'Team 1 is full.' : ($team['max_pending'] - $team['pending_outgoing']).' of '.$team['max_pending'].' requests left. Only classmates without a team are listed.'" />
                <x-button type="submit" variant="secondary">Send request</x-button>
            </form>
        @else
            <p class="as-restriction">@include('public.partials.icon', ['name' => 'lock', 'size' => 18]) <span>{{ $refusals['send'] }}</span></p>
        @endif
    </section>
</div>

@unless ($team['leave'])
    <x-zina.dialog id="leave-dialog" title="Request to leave {{ $project['team'] }}?" :open="$leaveOpen">
        <p>Students can’t leave a team on their own. Professor Lee gets your request and decides. Until then you stay on {{ $project['team'] }}, and your tasks stay assigned to you.</p>
        <form method="POST" action="{{ route('student.team.leave', $project['id']) }}" class="as-form">
            @csrf
            <x-textarea id="st-reason" name="reason" label="Why do you want to leave?" rows="4" :value="old('reason')" help="Professor Lee sees this. At least 10 characters." required />
            <div class="as-dialog-actions">
                <a class="ms-button" href="{{ request()->fullUrlWithoutQuery(['confirm']) }}" data-as-dialog-close>Cancel</a>
                <x-button type="submit" variant="danger">Send leave request</x-button>
            </div>
        </form>
    </x-zina.dialog>
@endunless
