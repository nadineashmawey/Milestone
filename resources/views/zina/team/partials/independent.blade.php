{{-- Independent project team (TEAM-12–13, PRJ-2). --}}
@php
    $leaderOnly = $refusals['invite'];
    $inviteTones = ['Pending' => 'warning', 'Accepted' => 'success', 'Declined' => 'danger', 'Expired' => 'neutral', 'Cancelled' => 'neutral'];
@endphp

<header class="as-head">
    <div>
        <p class="as-eyebrow">Independent project · Student-led</p>
        <h1>{{ $project['title'] }}</h1>
        <p class="as-head-lede">{{ $team['member_count'] }} of {{ $team['capacity'] }} members · {{ $team['leader']['name'] }} leads this project</p>
        <div class="as-badges st-head-badges">
            @include('zina.partials.project-type', ['type' => $project['type']])
            <x-badge>No professor or grading</x-badge>
            @if ($team['is_full'])<x-badge tone="info">Member limit reached</x-badge>@endif
        </div>
    </div>
</header>

@include('zina.team.partials.flash')

<div class="st-grid">
    <section class="as-panel" aria-labelledby="st-members-title">
        <div class="as-panel-head">
            <div>
                <h2 id="st-members-title">Members</h2>
                <p>Up to {{ $team['capacity'] }} people. The creator leads the project.</p>
            </div>
            <x-badge tone="info">{{ $team['member_count'] }} / {{ $team['capacity'] }}</x-badge>
        </div>
        <ul class="as-rows st-members">
            @foreach ($team['members'] as $member)
                <li>
                    @include('zina.partials.avatar', ['name' => $member['name'], 'index' => $member['avatar']])
                    <div>
                        <strong>{{ $member['name'] }}@if ($member['is_viewer'])<span class="st-you"> (you)</span>@endif</strong>
                        <small>{{ $member['is_leader'] ? 'Created the project' : 'Member' }}</small>
                    </div>
                    @if ($member['is_leader'])
                        <x-badge tone="warning">★ Leader</x-badge>
                    @elseif (! $leaderOnly)
                        <a class="ms-button ms-button--ghost ms-button--small st-remove" href="{{ request()->fullUrlWithQuery(['confirm' => 'remove-'.$member['id']]) }}#remove-{{ $member['id'] }}" data-as-dialog-open="remove-{{ $member['id'] }}">Remove<span class="as-sr"> {{ $member['name'] }}</span></a>
                        <x-zina.dialog id="remove-{{ $member['id'] }}" title="Remove {{ $member['name'] }}?" :open="$confirm === 'remove-'.$member['id']">
                            <p>They lose access to {{ $project['title'] }}. Work they finished stays credited to them, and their open tasks become unassigned for you to reassign.</p>
                            <form method="POST" action="{{ route('student.team.remove-member', [$project['id'], $member['id']]) }}" class="as-dialog-actions">
                                @csrf
                                <a class="ms-button" href="{{ request()->fullUrlWithoutQuery(['confirm']) }}" data-as-dialog-close>Cancel</a>
                                <x-button type="submit" variant="danger">Remove {{ $member['first'] ?? $member['name'] }}</x-button>
                            </form>
                        </x-zina.dialog>
                    @endif
                </li>
            @endforeach
        </ul>
        <p class="as-note st-transfer">@include('public.partials.icon', ['name' => 'lock', 'size' => 14]) {{ $team['is_leader'] ? 'To leave, transfer leadership to another member first.' : 'Only the leader can remove members.' }}</p>
    </section>

    <section class="as-panel" aria-labelledby="st-invite-title">
        <div class="as-panel-head">
            <div>
                <h2 id="st-invite-title">Invite people</h2>
                <p>{{ max(0, $team['seats_left']) }} {{ $team['seats_left'] === 1 ? 'seat' : 'seats' }} left, counting pending invitations.</p>
            </div>
        </div>
        @if ($leaderOnly)
            <p class="as-restriction">@include('public.partials.icon', ['name' => 'lock', 'size' => 18]) <span>{{ $leaderOnly }} Ask {{ $team['leader']['first'] }} to invite someone.</span></p>
        @else
            <form method="POST" action="{{ route('student.team.invite', $project['id']) }}" class="st-send">
                @csrf
                <x-input id="st-identifier" name="identifier" label="Email or username" :value="old('identifier')" autocomplete="off" help="Invitations expire after 7 days." required />
                <x-button type="submit" :disabled="$team['seats_left'] <= 0">@include('public.partials.icon', ['name' => 'mail', 'size' => 18]) Send invitation</x-button>
            </form>
            <div class="st-code">
                <p class="st-subhead">Or share the join code</p>
                <div class="st-code-row">
                    <code>{{ $team['join_code'] }}</code>
                    <button type="button" class="ms-button ms-button--small" data-as-copy="{{ $team['join_link'] }}" hidden>@include('public.partials.icon', ['name' => 'copy', 'size' => 16]) Copy link</button>
                </div>
                <p class="as-note st-break">{{ $team['join_link'] }} · People who use it send you a join request, and see only the project name and your name until you approve them.</p>
            </div>
        @endif
    </section>
</div>

<div class="st-grid">
    <section class="as-panel" aria-labelledby="st-join-title">
        <div class="as-panel-head">
            <div>
                <h2 id="st-join-title">Join requests</h2>
                <p>From people who used your join code.</p>
            </div>
            <x-badge :tone="count($team['join_requests']) ? 'warning' : 'neutral'">{{ count($team['join_requests']) }} pending</x-badge>
        </div>
        <ul class="as-rows st-requests">
            @forelse ($team['join_requests'] as $request)
                @php($person = app(\App\Support\ZinaDemoData::class)->classmate($request['student']))
                <li class="st-join">
                    @include('zina.partials.avatar', ['name' => $person['name'], 'index' => $person['avatar']])
                    <div>
                        <strong>{{ $person['name'] }}</strong><small>{{ $request['note'] }}</small>
                        <p class="st-join-message">“{{ $request['message'] }}”</p>
                    </div>
                    @if ($leaderOnly)
                        <x-badge tone="warning">Pending</x-badge>
                    @else
                        <div class="st-request-actions">
                            <form method="POST" action="{{ route('student.team.decline-join', [$project['id'], $request['id']]) }}">
                                @csrf
                                <x-button type="submit" variant="secondary" size="small">Decline<span class="as-sr"> {{ $person['name'] }}</span></x-button>
                            </form>
                            <form method="POST" action="{{ route('student.team.approve-join', [$project['id'], $request['id']]) }}">
                                @csrf
                                <x-button type="submit" size="small" :disabled="$team['is_full']">Approve<span class="as-sr"> {{ $person['name'] }}</span></x-button>
                            </form>
                        </div>
                    @endif
                </li>
            @empty
                <li class="st-none">No join requests right now.</li>
            @endforelse
        </ul>
    </section>

    <section class="as-panel" aria-labelledby="st-invitations-title">
        <div class="as-panel-head">
            <div>
                <h2 id="st-invitations-title">Invitations</h2>
                <p>Pending, Accepted, Declined, Expired or Cancelled.</p>
            </div>
        </div>
        <ul class="as-rows st-requests">
            @foreach (array_reverse($team['invitations']) as $invite)
                <li>
                    <span class="st-invite-icon" aria-hidden="true">@include('public.partials.icon', ['name' => str_contains($invite['to'], '@') ? 'mail' : 'users', 'size' => 18])</span>
                    <div><strong class="st-break">{{ $invite['to'] }}</strong><small>{{ $invite['note'] }}</small></div>
                    <div class="st-request-actions">
                        <x-badge :tone="$inviteTones[$invite['status']]">{{ $invite['status'] }}</x-badge>
                        @if ($invite['status'] === 'Pending' && ! $leaderOnly)
                            <form method="POST" action="{{ route('student.team.cancel-invitation', [$project['id'], $invite['id']]) }}">
                                @csrf
                                <x-button type="submit" variant="ghost" size="small">Cancel<span class="as-sr"> invitation to {{ $invite['to'] }}</span></x-button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    </section>
</div>
