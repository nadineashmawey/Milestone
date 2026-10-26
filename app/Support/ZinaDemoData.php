<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;

/**
 * Zina's fictional data for the signed-in prototype pages (dashboards, milestones, team, profile).
 *
 * Views only ever receive the plain arrays these methods return. When real models exist, rewrite the
 * method bodies to query Eloquent and keep the same array shapes; no view has to change.
 *
 * Actions that "change" something write to the session under SESSION_KEY, so the demo behaves like a
 * real app for one visitor and resets when their session ends. Nothing here touches the database.
 */
final class ZinaDemoData
{
    public const SESSION_KEY = 'zina_demo';

    /** Page states a grader can preview with ?state=… (local and testing environments only). */
    public const STATES = ['populated', 'empty', 'loading', 'error'];

    public const ROLES = ['student', 'professor', 'ta', 'admin'];

    public function __construct(private Session $session)
    {
    }

    /**
     * The demo clock. Every countdown and "due in" label is measured from here, so the pages agree.
     */
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::create(2026, 10, 13, 9, 0);
    }

    public function statesEnabled(): bool
    {
        return app()->environment(['local', 'testing']);
    }

    /**
     * The page state to render: "populated" unless a dev-only ?state= override is present.
     */
    public function state(Request $request): string
    {
        $state = $request->query('state');

        return $this->statesEnabled() && in_array($state, self::STATES, true) ? $state : 'populated';
    }

    /**
     * Prototype controls standing in for the signed-in user until Jana's auth is merged.
     *
     * @return array{viewer: string, phase: string, role: string}
     */
    public function context(): array
    {
        return [
            // Who the student pages are viewed as: Aya (team leader) or Malek (team member).
            'viewer' => $this->get('context.viewer', 'aya'),
            // Team formation as it is today (locked) or as it was on Sep 9, before the deadline.
            'phase' => $this->get('context.phase', 'locked'),
            // The role the shared Profile page is shown for.
            'role' => $this->get('context.role', 'student'),
        ];
    }

    /**
     * @param  array{viewer?: string, phase?: string, role?: string}  $changes
     */
    public function updateContext(array $changes): void
    {
        foreach ($changes as $key => $value) {
            $this->put('context.'.$key, $value);
        }
    }

    /**
     * The student the student pages are shown for.
     *
     * @return array<string, mixed>
     */
    public function viewer(): array
    {
        return $this->person($this->context()['viewer']);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function people(): array
    {
        return [
            'aya' => ['id' => 'aya', 'name' => 'Aya Hassan', 'first' => 'Aya', 'last' => 'Hassan', 'email' => 'aya.hassan@university.edu', 'role' => 'student', 'avatar' => 0],
            'malek' => ['id' => 'malek', 'name' => 'Malek Mansour', 'first' => 'Malek', 'last' => 'Mansour', 'email' => 'malek.mansour@university.edu', 'role' => 'student', 'avatar' => 1],
            'nour' => ['id' => 'nour', 'name' => 'Nour Saleh', 'first' => 'Nour', 'last' => 'Saleh', 'email' => 'nour.saleh@university.edu', 'role' => 'student', 'avatar' => 2],
            'omar' => ['id' => 'omar', 'name' => 'Omar Khaled', 'first' => 'Omar', 'last' => 'Khaled', 'email' => 'omar.khaled@university.edu', 'role' => 'student', 'avatar' => 3],
            'salma' => ['id' => 'salma', 'name' => 'Salma Nabil', 'first' => 'Salma', 'last' => 'Nabil', 'email' => 'salma.nabil@university.edu', 'role' => 'student', 'avatar' => 4],
            'lee' => ['id' => 'lee', 'name' => 'Professor Lee', 'first' => 'Daniel', 'last' => 'Lee', 'email' => 'lee@university.edu', 'role' => 'professor', 'avatar' => 0],
            'sara' => ['id' => 'sara', 'name' => 'Sara Ahmed', 'first' => 'Sara', 'last' => 'Ahmed', 'email' => 'sara.ahmed@university.edu', 'role' => 'ta', 'avatar' => 2],
            'admin' => ['id' => 'admin', 'name' => 'University Admin', 'first' => 'University', 'last' => 'Admin', 'email' => 'admin@university.edu', 'role' => 'admin', 'avatar' => 3],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function person(string $id): array
    {
        return $this->people()[$id] ?? $this->people()['aya'];
    }

    /**
     * Aya's projects. Ids match Jana's StudentProjectCatalog so links between our pages line up.
     *
     * @return array<string, array<string, mixed>>
     */
    public function projects(): array
    {
        return [
            'software-engineering-capstone' => [
                'id' => 'software-engineering-capstone',
                'title' => 'Software Engineering Capstone',
                'type' => 'Academic',
                'course' => 'CS 490 · Software Engineering',
                'course_code' => 'CS 490',
                'professor' => 'Professor Lee',
                'ta' => 'Sara Ahmed',
                'team' => 'Team 1',
                'progress' => 62,
                'status' => 'On track',
                'status_tone' => 'success',
            ],
            'campus-navigation-app' => [
                'id' => 'campus-navigation-app',
                'title' => 'Campus Navigation App',
                'type' => 'Independent',
                'course' => null,
                'course_code' => null,
                'professor' => null,
                'ta' => null,
                'team' => null,
                'progress' => 38,
                'status' => 'In progress',
                'status_tone' => 'info',
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function project(string $id): ?array
    {
        return $this->projects()[$id] ?? null;
    }

    /**
     * A short countdown from the demo clock, for chips like "5 days left".
     *
     * @return array{label: string, tone: string, overdue: bool, days: int}
     */
    public function countdown(CarbonImmutable $due): array
    {
        $now = $this->now();

        if ($due->lessThan($now)) {
            $days = (int) $due->startOfDay()->diffInDays($now->startOfDay());

            return ['label' => $days <= 1 ? 'Overdue' : "Overdue by {$days} days", 'tone' => 'danger', 'overdue' => true, 'days' => -$days];
        }

        $hours = (int) $now->diffInHours($due);
        $days = intdiv($hours, 24);

        $label = match (true) {
            $hours < 24 => $hours <= 1 ? 'Due within the hour' : "{$hours} hours left",
            $days === 1 => '1 day left',
            default => "{$days} days left",
        };

        return ['label' => $label, 'tone' => $days <= 2 ? 'danger' : ($days <= 7 ? 'warning' : 'info'), 'overdue' => false, 'days' => $days];
    }

    public function date(string $value): CarbonImmutable
    {
        return CarbonImmutable::parse($value);
    }

    public function greeting(): string
    {
        $hour = (int) $this->now()->format('G');

        return $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
    }

    /**
     * A link that may point at a teammate's page. Resolves to "#" until that route is registered.
     *
     * @param  array<string, mixed>  $parameters
     */
    public function link(string $route, array $parameters = []): string
    {
        return AppShellNavigation::href($route, $parameters);
    }

    /**
     * Initials for avatars, e.g. "Aya Hassan" → "AH", "Professor Lee" → "PL".
     */
    public function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(implode('', array_map(fn ($part) => mb_substr($part, 0, 1), array_slice($parts, 0, 2))));
    }

    private function get(string $key, mixed $default = null): mixed
    {
        return $this->session->get(self::SESSION_KEY.'.'.$key, $default);
    }

    private function put(string $key, mixed $value): void
    {
        $this->session->put(self::SESSION_KEY.'.'.$key, $value);
    }

    private function forget(string $key): void
    {
        $this->session->forget(self::SESSION_KEY.'.'.$key);
    }

    /*
    |--------------------------------------------------------------------------
    | Page data. Each page branch adds its methods under its own heading.
    |--------------------------------------------------------------------------
    */

    // ---- Student dashboard ----
    // Added on zina/student-dashboard.

    // ---- Milestones ----
    // Added on zina/milestones.

    // ---- Student team ----
    // Added on zina/student-team.

    /** TEAM-1: a student may have at most this many pending requests. */
    public const MAX_PENDING_REQUESTS = 5;

    /** PRJ-2: Independent projects have at most 10 members. */
    public const INDEPENDENT_MEMBER_LIMIT = 10;

    /**
     * The team page for a project (TEAM-1–14). Null when the project doesn't exist.
     *
     * @return array<string, mixed>|null
     */
    public function team(string $projectId): ?array
    {
        $project = $this->project($projectId);
        if (! $project) {
            return null;
        }

        $viewer = $this->viewer();
        $record = $this->teamRecord($projectId);
        $leave = $this->get('team.'.$projectId.'.leave');

        $members = array_map(fn ($id) => [
            ...$this->classmate($id),
            'is_leader' => $id === $record['leader'],
            'is_viewer' => $id === $viewer['id'],
        ], $record['members']);

        $base = [
            'project' => $project,
            'viewer' => $viewer,
            'record' => $record,
            'members' => $members,
            'member_count' => count($members),
            'is_member' => in_array($viewer['id'], $record['members'], true),
            'is_leader' => $viewer['id'] === $record['leader'],
            'leader' => $record['leader'] ? $this->person($record['leader']) : null,
            'leave' => $leave ? [...$leave, 'when' => $leave['sent_at']->format('M j, g:i A')] : null,
        ];

        if ($project['type'] === 'Independent') {
            $pendingInvites = collect($record['invitations'])->where('status', 'Pending')->count();

            return [
                ...$base,
                'kind' => 'independent',
                'capacity' => self::INDEPENDENT_MEMBER_LIMIT,
                'is_full' => count($members) >= self::INDEPENDENT_MEMBER_LIMIT,
                'seats_left' => self::INDEPENDENT_MEMBER_LIMIT - count($members) - $pendingInvites,
                'join_code' => 'CNA-7Q4K',
                'join_link' => url('/join/CNA-7Q4K'),
                'invitations' => $record['invitations'],
                'join_requests' => array_values(array_filter($record['join_requests'], fn ($request) => $request['status'] === 'Pending')),
            ];
        }

        $forming = $this->context()['phase'] === 'forming';
        $deadline = $this->date('2026-09-11 17:00');
        $clock = $forming ? $this->date('2026-09-09 09:00') : $this->now();
        $hoursLeft = (int) $clock->diffInHours($deadline, false);
        $pendingOutgoing = collect($record['outgoing'])->where('status', 'Pending')->count();

        return [
            ...$base,
            'kind' => 'academic',
            'phase' => $forming ? 'forming' : 'locked',
            'clock' => $clock->format('l, F j'),
            'capacity' => 4,
            'minimum' => 2,
            'is_full' => count($members) >= 4,
            'deadline' => $deadline,
            'deadline_label' => $deadline->format('D, M j · g:i A'),
            'deadline_countdown' => $hoursLeft > 0
                ? ['label' => intdiv($hoursLeft, 24) >= 1 ? intdiv($hoursLeft, 24).' '.(intdiv($hoursLeft, 24) === 1 ? 'day' : 'days').' left' : $hoursLeft.' hours left', 'tone' => $hoursLeft <= 72 ? 'warning' : 'info']
                : ['label' => 'Closed', 'tone' => 'neutral'],
            'incoming' => $record['incoming'],
            'outgoing' => $record['outgoing'],
            'pending_outgoing' => $pendingOutgoing,
            'max_pending' => self::MAX_PENDING_REQUESTS,
            'classmates' => collect($this->classmates())->except([...$record['members'], ...collect($record['outgoing'])->where('status', 'Pending')->pluck('student')->all(), ...collect($record['incoming'])->pluck('student')->all()])->map(fn ($person) => $person['name'])->all(),
        ];
    }

    /**
     * Why the viewer may not do $action on this team, or null when they may. Shared by the page and the server checks.
     */
    public function teamRefusal(string $projectId, string $action): ?string
    {
        $team = $this->team($projectId);
        if (! $team) {
            return 'This project doesn’t exist.';
        }

        if (! $team['is_member']) {
            return 'You’re not on this team.';
        }

        if ($team['kind'] === 'independent') {
            return match ($action) {
                'invite', 'cancel-invitation', 'approve-join', 'decline-join', 'remove-member' => $team['is_leader'] ? null : 'Only the project leader, '.$team['leader']['name'].', can manage members and invitations.',
                default => 'That action is only for academic teams.',
            };
        }

        $locked = $team['phase'] === 'locked';

        return match ($action) {
            'accept', 'decline', 'withdraw', 'send' => $locked ? 'Team formation closed on '.$team['deadline']->format('M j').'. Teams are locked, so requests can’t change. Ask Professor Lee if something needs fixing.' : null,
            'leave' => $team['leave'] ? 'You already asked to leave. Professor Lee will reply soon.' : null,
            'withdraw-leave' => $team['leave'] ? null : 'There’s no leave request to withdraw.',
            default => 'That action is only for independent projects.',
        };
    }

    /**
     * Apply a team action and keep the result in the session. Returns the success message.
     *
     * @param  array<string, mixed>  $input
     */
    public function applyTeamAction(string $projectId, string $action, ?string $target, array $input = []): string
    {
        $record = $this->teamRecord($projectId);
        $find = fn (string $list) => collect($record[$list])->search(fn ($item) => $item['id'] === $target);
        $stamp = $this->now()->format('M j');

        switch ($action) {
            case 'accept':
                $index = $find('incoming');
                $request = $record['incoming'][$index];
                $record['members'][] = $request['student'];
                $record['incoming'][$index]['status'] = 'Accepted';
                $message = $this->classmate($request['student'])['name'].' joined Team 1.';
                break;
            case 'decline':
                $index = $find('incoming');
                $record['incoming'][$index]['status'] = 'Declined';
                $message = $this->classmate($record['incoming'][$index]['student'])['name'].'’s request was declined.';
                break;
            case 'withdraw':
                $index = $find('outgoing');
                $record['outgoing'][$index]['status'] = 'Withdrawn';
                $message = 'Your request to '.$this->classmate($record['outgoing'][$index]['student'])['name'].' was withdrawn.';
                break;
            case 'send':
                $student = collect($this->classmates())->search(fn ($person) => $person['name'] === $input['student']);
                $record['outgoing'][] = ['id' => 'out-'.$student, 'student' => $student, 'status' => 'Pending', 'sent' => 'Sep 9'];
                $message = 'Request sent to '.$input['student'].'.';
                break;
            case 'leave':
                $this->put('team.'.$projectId.'.leave', ['reason' => $input['reason'], 'sent_at' => $this->now(), 'by' => $this->viewer()['name']]);

                return 'Leave request sent to Professor Lee. You stay on Team 1 until they approve it.';
            case 'withdraw-leave':
                $this->forget('team.'.$projectId.'.leave');

                return 'Leave request withdrawn. Nothing changed on your team.';
            case 'invite':
                $record['invitations'][] = ['id' => 'inv-'.(count($record['invitations']) + 1), 'to' => $input['identifier'], 'status' => 'Pending', 'note' => 'Sent '.$stamp.' · expires '.$this->now()->addDays(7)->format('M j')];
                $message = 'Invitation sent to '.$input['identifier'].'. It expires in 7 days.';
                break;
            case 'cancel-invitation':
                $index = $find('invitations');
                $record['invitations'][$index]['status'] = 'Cancelled';
                $record['invitations'][$index]['note'] = 'Cancelled by you · '.$stamp;
                $message = 'Invitation to '.$record['invitations'][$index]['to'].' cancelled. The link no longer works.';
                break;
            case 'approve-join':
            case 'decline-join':
                $index = $find('join_requests');
                $approved = $action === 'approve-join';
                $record['join_requests'][$index]['status'] = $approved ? 'Approved' : 'Rejected';
                $student = $record['join_requests'][$index]['student'];
                if ($approved) {
                    $record['members'][] = $student;
                }
                $this->put('team.'.$projectId.'.join_requests.'.$target, $approved ? 'Approved' : 'Rejected');
                $message = $this->classmate($student)['name'].($approved ? ' joined the project.' : '’s request was declined.');
                break;
            case 'remove-member':
                $record['members'] = array_values(array_diff($record['members'], [$target]));
                $message = $this->classmate($target)['name'].' was removed. Their finished work stays credited.';
                break;
            default:
                return '';
        }

        $this->put($this->teamRecordKey($projectId), $record);

        return $message;
    }

    /**
     * Raw team record: the session copy once anything changed, otherwise the starting data.
     *
     * @return array<string, mixed>
     */
    private function teamRecord(string $projectId): array
    {
        $stored = $this->get($this->teamRecordKey($projectId));
        if ($stored) {
            return $stored;
        }

        if ($projectId === 'campus-navigation-app') {
            return [
                'leader' => 'aya',
                'members' => ['aya', 'malek', 'omar', 'salma'],
                'invitations' => [
                    ['id' => 'inv-1', 'to' => 'hana.youssef@university.edu', 'status' => 'Pending', 'note' => 'Sent Oct 13 · expires Oct 20'],
                    ['id' => 'inv-2', 'to' => 'omar.khaled', 'status' => 'Accepted', 'note' => 'Joined Oct 2'],
                    ['id' => 'inv-3', 'to' => 'layla.mostafa@university.edu', 'status' => 'Declined', 'note' => 'Declined Oct 5'],
                    ['id' => 'inv-4', 'to' => 'ziad.amr', 'status' => 'Expired', 'note' => 'Expired Oct 8 · not answered within 7 days'],
                    ['id' => 'inv-5', 'to' => 'tarek.hosny@university.edu', 'status' => 'Cancelled', 'note' => 'Cancelled by you · Oct 3'],
                ],
                'join_requests' => [
                    'karim' => ['id' => 'karim', 'student' => 'karim', 'status' => 'Pending', 'note' => 'Requested with the join code · Oct 12', 'message' => 'I built the indoor maps for the library app last year and would love to help.'],
                ],
            ];
        }

        $forming = $this->context()['phase'] === 'forming';

        return [
            'leader' => $forming ? null : 'aya',
            'members' => ['aya', 'malek', 'nour'],
            'incoming' => [
                ['id' => 'in-dina', 'student' => 'dina', 'status' => $forming ? 'Pending' : 'Cancelled', 'sent' => 'Sep 8'],
                ['id' => 'in-karim', 'student' => 'karim', 'status' => $forming ? 'Pending' : 'Cancelled', 'sent' => 'Sep 8'],
            ],
            'outgoing' => [
                ['id' => 'out-laila', 'student' => 'laila', 'status' => $forming ? 'Pending' : 'Cancelled', 'sent' => 'Sep 7'],
                ['id' => 'out-ziad', 'student' => 'ziad', 'status' => $forming ? 'Pending' : 'Cancelled', 'sent' => 'Sep 7'],
                ['id' => 'out-mariam', 'student' => 'mariam', 'status' => $forming ? 'Pending' : 'Cancelled', 'sent' => 'Sep 8'],
            ],
        ];
    }

    private function teamRecordKey(string $projectId): string
    {
        // The Sep 9 "formation open" preview is kept apart from today's locked team.
        return 'team.'.$projectId.'.record'.($projectId === 'software-engineering-capstone' ? '_'.$this->context()['phase'] : '');
    }

    /**
     * Students in CS 490 and the independent project, beyond the core people.
     *
     * @return array<string, array<string, mixed>>
     */
    public function classmates(): array
    {
        $extra = [
            'dina' => 'Dina Nasser', 'karim' => 'Karim Saleh', 'laila' => 'Laila Fawzy', 'ziad' => 'Ziad Amr',
            'mariam' => 'Mariam Adel', 'tarek' => 'Tarek Hosny', 'yasmin' => 'Yasmin Fathy', 'rana' => 'Rana Samir', 'hany' => 'Hany Wael',
        ];

        $people = collect($extra)->map(fn ($name, $id) => ['id' => $id, 'name' => $name, 'avatar' => crc32($id) % 5, 'role' => 'student'])->all();

        return [...array_filter($this->people(), fn ($person) => $person['role'] === 'student'), ...$people];
    }

    /**
     * @return array<string, mixed>
     */
    public function classmate(string $id): array
    {
        return $this->classmates()[$id] ?? ['id' => $id, 'name' => ucfirst($id), 'avatar' => 0, 'role' => 'student'];
    }

    // ---- Professor dashboard ----
    // Added on zina/professor-dashboard.

    // ---- Profile ----
    // Added on zina/profile.
}
