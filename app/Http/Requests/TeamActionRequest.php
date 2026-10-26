<?php

namespace App\Http\Requests;

use App\Support\ZinaDemoData;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Every Student Team action (TEAM-1–14). The action comes from the route name, e.g. student.team.accept.
 * Permissions are checked here on the server (BR-12); the page shows the same reasons next to each control.
 */
class TeamActionRequest extends FormRequest
{
    public function action(): string
    {
        return Str::after((string) $this->route()->getName(), 'student.team.');
    }

    private function demo(): ZinaDemoData
    {
        return app(ZinaDemoData::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function team(): array
    {
        $team = $this->demo()->team((string) $this->route('project'));
        abort_if($team === null, 404);

        return $team;
    }

    public function authorize(): bool
    {
        $refusal = $this->demo()->teamRefusal((string) $this->route('project'), $this->action());
        if ($refusal !== null) {
            throw new AuthorizationException($refusal);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return match ($this->action()) {
            'send' => ['student' => ['required', 'string', Rule::in(array_values($this->team()['classmates']))]],
            'leave' => ['reason' => ['required', 'string', 'min:10', 'max:500']],
            'invite' => ['identifier' => ['required', 'string', 'max:150', 'regex:/^([^@\s]+@[^@\s]+\.[^@\s]+|[a-z0-9._-]{3,30})$/i']],
            'accept' => ['confirmed' => ['nullable', 'boolean']],
            default => [],
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student.required' => 'Choose who to send a request to.',
            'student.in' => 'Choose a classmate from the list. They may already be on a team.',
            'reason.required' => 'Tell Professor Lee why you want to leave, in at least 10 characters.',
            'reason.min' => 'Tell Professor Lee why you want to leave, in at least 10 characters.',
            'reason.max' => 'Keep the reason to 500 characters or fewer.',
            'identifier.required' => 'Enter an email address or a username.',
            'identifier.regex' => 'Enter an email like name@university.edu or a username like omar.khaled.',
            'identifier.max' => 'That’s too long for an email address or username.',
        ];
    }

    /**
     * Rules that depend on the team's current state.
     *
     * @return list<callable>
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            $team = $this->team();
            $target = (string) $this->route('item');
            $pending = fn (string $list) => collect($team['record'][$list] ?? [])->first(fn ($item) => $item['id'] === $target && $item['status'] === 'Pending');

            switch ($this->action()) {
                case 'send':
                    if ($team['is_full']) {
                        $validator->errors()->add('student', 'Team 1 is full (4 of 4), so you can’t send more requests.');
                    } elseif ($team['pending_outgoing'] >= $team['max_pending']) {
                        $validator->errors()->add('student', 'You already have '.$team['max_pending'].' pending requests, the most allowed. Withdraw one before you send another.');
                    }
                    break;
                case 'accept':
                    if (! $pending('incoming')) {
                        $validator->errors()->add('request', 'That request isn’t pending any more.');
                    } elseif ($team['is_full']) {
                        $validator->errors()->add('request', 'Team 1 is already full (4 of 4). Decline this request instead.');
                    } elseif ($team['member_count'] + 1 >= $team['capacity'] && ! $this->boolean('confirmed')) {
                        $validator->errors()->add('request', 'Accepting fills Team 1. Confirm to continue.');
                    }
                    break;
                case 'decline':
                    if (! $pending('incoming')) {
                        $validator->errors()->add('request', 'That request isn’t pending any more.');
                    }
                    break;
                case 'withdraw':
                    if (! $pending('outgoing')) {
                        $validator->errors()->add('request', 'That request isn’t pending any more.');
                    }
                    break;
                case 'invite':
                    $identifier = Str::lower(trim((string) $this->input('identifier')));
                    $taken = collect($team['members'])->flatMap(fn ($member) => array_filter([Str::lower($member['email'] ?? ''), Str::lower(Str::before($member['email'] ?? '', '@'))]));
                    if ($taken->contains($identifier)) {
                        $validator->errors()->add('identifier', 'That person is already a member.');
                    } elseif (collect($team['invitations'])->contains(fn ($invite) => $invite['status'] === 'Pending' && Str::lower($invite['to']) === $identifier)) {
                        $validator->errors()->add('identifier', 'You already invited them. Their invitation is still pending.');
                    } elseif ($team['seats_left'] <= 0) {
                        $validator->errors()->add('identifier', 'The project is at its limit of 10 members, counting pending invitations.');
                    }
                    break;
                case 'cancel-invitation':
                    if (! $pending('invitations')) {
                        $validator->errors()->add('invitation', 'Only pending invitations can be cancelled.');
                    }
                    break;
                case 'approve-join':
                case 'decline-join':
                    if (! $pending('join_requests')) {
                        $validator->errors()->add('join', 'That request isn’t pending any more.');
                    } elseif ($this->action() === 'approve-join' && $team['is_full']) {
                        $validator->errors()->add('join', 'The project already has 10 members, the most allowed.');
                    }
                    break;
                case 'remove-member':
                    if ($target === $team['record']['leader']) {
                        $validator->errors()->add('member', 'Transfer leadership before the leader leaves the project.');
                    } elseif (! in_array($target, $team['record']['members'], true)) {
                        $validator->errors()->add('member', 'That person isn’t a member.');
                    }
                    break;
            }
        }];
    }
}
