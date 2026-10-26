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

    // ---- Professor dashboard ----
    // Added on zina/professor-dashboard.

    // ---- Profile ----
    // Added on zina/profile.

    /** Notification categories × channels (DLN-6). */
    public const NOTIFICATION_CATEGORIES = [
        'tasks' => ['Tasks', 'Assignments, status changes and comments that mention you'],
        'deadlines' => ['Deadlines', 'Reminders 3 days and 1 day before a due date'],
        'announcements' => ['Announcements', 'Messages from professors and administrators'],
        'team_requests' => ['Team requests', 'Requests, invitations and leave decisions'],
        'feedback' => ['Feedback', 'Reviews, resubmission requests and released grades'],
    ];

    public const NOTIFICATION_CHANNELS = ['in_app' => 'In-app', 'email' => 'Email'];

    public const DISPLAY_OPTIONS = [
        'date_format' => ['M j, Y' => 'Oct 18, 2026', 'j M Y' => '18 Oct 2026', 'Y-m-d' => '2026-10-18'],
        'time_format' => ['12h' => '12-hour (5:00 PM)', '24h' => '24-hour (17:00)'],
        'week_start' => ['saturday' => 'Saturday', 'sunday' => 'Sunday', 'monday' => 'Monday'],
    ];

    /**
     * The shared Profile page for the role picked in the prototype controls (ACC-9–10, DLN-6).
     *
     * @return array<string, mixed>
     */
    public function profile(bool $newAccount = false): array
    {
        $role = $this->context()['role'];
        $person = match ($role) {
            'professor' => $this->person('lee'),
            'ta' => $this->person('sara'),
            'admin' => $this->person('admin'),
            default => $this->viewer(),
        };

        $defaults = [
            'first_name' => $person['first'],
            'last_name' => $person['last'],
            'date_format' => 'M j, Y',
            'time_format' => '12h',
            'week_start' => 'saturday',
            'notifications' => collect(self::NOTIFICATION_CATEGORIES)->map(fn ($label, $key) => [
                'in_app' => true,
                'email' => in_array($key, ['deadlines', 'feedback'], true),
            ])->all(),
        ];

        $saved = $newAccount ? [] : $this->get('profile.'.$role.'.'.$person['id'], []);
        $values = array_replace_recursive($defaults, $saved);

        $details = match ($role) {
            'professor' => [
                ['label' => 'Department', 'value' => 'Software Engineering'],
                ['label' => 'Courses', 'list' => ['CS 490 · Software Engineering', 'CS 342 · Database Systems', 'CS 210 · Web Development']],
            ],
            'ta' => [
                ['label' => 'Assigned courses', 'list' => ['CS 490 · Software Engineering Capstone · Full review', 'CS 342 · Database Systems · Comment only']],
            ],
            'admin' => [
                ['label' => 'Two-factor authentication', 'value' => 'Off', 'tone' => 'warning', 'link' => ['label' => 'Manage in Security', 'href' => $this->link('admin.security'), 'owner' => 'Habiba']],
            ],
            default => [
                ['label' => 'Program', 'value' => $newAccount ? 'Not set yet' : 'Computer Science'],
            ],
        };

        return [
            'role' => $role,
            'role_label' => ['student' => 'Student', 'professor' => 'Professor', 'ta' => 'Teaching assistant', 'admin' => 'Admin'][$role],
            'person' => $person,
            'display_name' => trim($values['first_name'].' '.$values['last_name']),
            'values' => $values,
            'saved_at' => $saved['saved_at'] ?? null,
            'locked' => array_values(array_filter([
                ['label' => 'University email', 'value' => $person['email']],
                $role === 'student' ? ['label' => 'Student ID', 'value' => $person['id'] === 'malek' ? '20230047' : '20230012'] : null,
                ['label' => 'Role', 'value' => ['student' => 'Student', 'professor' => 'Professor', 'ta' => 'Teaching assistant', 'admin' => 'Admin'][$role]],
            ])),
            'details' => $details,
            'dashboard_href' => $this->link(['student' => 'student.dashboard', 'professor' => 'professor.dashboard', 'ta' => 'ta.dashboard', 'admin' => 'admin.dashboard'][$role]),
        ];
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function saveProfile(array $values): void
    {
        $profile = $this->profile();
        $this->put('profile.'.$profile['role'].'.'.$profile['person']['id'], [...$values, 'saved_at' => $this->now()->format('g:i A')]);
    }
}
