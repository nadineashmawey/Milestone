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

    /**
     * Everything the student dashboard shows (DSH-1). $empty gives a brand-new student with no projects.
     *
     * @return array<string, mixed>
     */
    public function studentDashboard(bool $empty = false): array
    {
        $now = $this->now();
        $viewer = $this->viewer();
        $isLeader = $viewer['id'] === 'aya';

        if ($empty) {
            return [
                'viewer' => $viewer,
                'today' => $now->format('l, F j'),
                'greeting' => $this->greeting(),
                'summary' => 'You’re not in any projects yet.',
                'actions' => [],
                'projects' => [],
                'deadlines' => [],
                'tasks' => [],
                'completed' => [],
            ];
        }

        $capstone = $this->project('software-engineering-capstone');
        $campus = $this->project('campus-navigation-app');

        $deadlines = collect([
            ['title' => 'M2 · Design Document', 'project' => $capstone, 'due' => $this->date('2026-10-18 17:00'), 'href' => $this->link('student.milestones.index', ['project' => $capstone['id']])],
            ['title' => 'Prototype checkpoint', 'project' => $campus, 'due' => $this->date('2026-10-23 17:00'), 'href' => $this->link('student.projects.show', ['project' => $campus['id']])],
            ['title' => 'M3 · Final Demo', 'project' => $capstone, 'due' => $this->date('2026-11-08 17:00'), 'href' => $this->link('student.milestones.index', ['project' => $capstone['id']])],
        ])->sortBy(fn ($deadline) => $deadline['due'])->map(fn ($deadline) => [
            ...$deadline,
            'day' => $deadline['due']->format('j'),
            'month' => $deadline['due']->format('M'),
            'when' => $deadline['due']->format('D, M j · g:i A'),
            'countdown' => $this->countdown($deadline['due']),
        ])->values();

        $tasks = collect($isLeader ? [
            ['title' => 'Research summary', 'project' => $capstone, 'due' => $this->date('2026-10-11 17:00'), 'priority' => 'High', 'blocked' => null, 'href' => $this->link('student.tasks.index')],
            ['title' => 'Upload design document', 'project' => $capstone, 'due' => $this->date('2026-10-18 17:00'), 'priority' => 'Medium', 'blocked' => null, 'href' => $this->link('student.tasks.show', ['task' => 'design-document'])],
            ['title' => 'Share interactive prototype', 'project' => $campus, 'due' => $this->date('2026-10-19 17:00'), 'priority' => 'Medium', 'blocked' => 'Waiting on Wireframes (Nour Saleh)', 'href' => $this->link('student.tasks.show', ['task' => 'interactive-prototype'])],
            ['title' => 'Submit API implementation', 'project' => $capstone, 'due' => $this->date('2026-10-20 17:00'), 'priority' => 'High', 'blocked' => null, 'href' => $this->link('student.tasks.show', ['task' => 'api-implementation'])],
        ] : [
            // Malek's own work, for the team-member view.
            ['title' => 'API contract', 'project' => $capstone, 'due' => $this->date('2026-10-14 17:00'), 'priority' => 'Medium', 'blocked' => null, 'href' => $this->link('student.tasks.index')],
            ['title' => 'Indoor map data', 'project' => $campus, 'due' => $this->date('2026-10-21 17:00'), 'priority' => 'Low', 'blocked' => null, 'href' => $this->link('student.tasks.index')],
        ])->map(fn ($task) => [
            ...$task,
            'when' => $task['due']->format('M j · g:i A'),
            'overdue' => $task['due']->lessThan($now),
            'priority_tone' => ['High' => 'danger', 'Medium' => 'warning', 'Low' => 'neutral'][$task['priority']],
        ])->sortBy(fn ($task) => $task['due'])->values();

        // Items that need the student to act (DSH-1). Each one goes away once it's handled on its own page this session.
        $actions = [];
        if (! $this->get('milestones.software-engineering-capstone.versions')) {
            $actions[] = [
                'kind' => 'Changes requested',
                'tone' => 'review',
                'icon' => 'file',
                'title' => 'M2 Design Document needs a resubmission',
                'detail' => 'Sara Ahmed asked for ERD cardinality labels and testing owners. Due Oct 18, 5:00 PM.',
                'href' => $this->link('student.milestones.index', ['project' => $capstone['id']]),
                'cta' => $isLeader ? 'Resubmit M2' : 'See the feedback',
            ];
        }
        foreach ($tasks->where('overdue', true) as $task) {
            $actions[] = [
                'kind' => 'Overdue task',
                'tone' => 'danger',
                'icon' => 'clock',
                'title' => $task['title'].' is overdue',
                'detail' => $task['project']['title'].' · was due '.$task['due']->format('M j').'. No penalty is applied.',
                'href' => $task['href'],
                'cta' => 'Open task',
            ];
        }
        if ($isLeader && ! $this->get('team.campus-navigation-app.join_requests.karim')) {
            $actions[] = [
                'kind' => 'Join request',
                'tone' => 'warning',
                'icon' => 'users',
                'title' => 'Karim Saleh asked to join Campus Navigation App',
                'detail' => 'Independent project · 4 of 10 members. Approve or decline the request.',
                'href' => $this->link('student.team.show', ['project' => $campus['id']]),
                'cta' => 'Review request',
            ];
        }

        $projects = [
            [...$capstone, 'ring_tone' => 'green', 'subtitle' => $capstone['course'].' · '.$capstone['team'], 'next' => 'M2 Design Document · Oct 18', 'next_countdown' => $this->countdown($this->date('2026-10-18 17:00')), 'href' => $this->link('student.projects.show', ['project' => $capstone['id']])],
            [...$campus, 'ring_tone' => 'blue', 'subtitle' => 'Independent · 4 of 10 members · No grading', 'next' => 'Prototype checkpoint · Oct 23', 'next_countdown' => $this->countdown($this->date('2026-10-23 17:00')), 'href' => $this->link('student.projects.show', ['project' => $campus['id']])],
        ];

        $thisWeek = $deadlines->filter(fn ($deadline) => ! $deadline['countdown']['overdue'] && $deadline['due']->lessThanOrEqualTo($now->addDays(7)))->count();
        $taskCount = $tasks->count();

        return [
            'viewer' => $viewer,
            'today' => $now->format('l, F j'),
            'greeting' => $this->greeting(),
            'summary' => "{$taskCount} ".($taskCount === 1 ? 'task' : 'tasks').' to focus on, '.$thisWeek.' '.($thisWeek === 1 ? 'deadline' : 'deadlines').' this week.',
            'actions' => $actions,
            'projects' => $projects,
            'deadlines' => $deadlines->all(),
            'tasks' => $tasks->all(),
            'completed' => [
                ['title' => 'Human-Centered Design Study', 'type' => 'Academic', 'term' => 'CS 380 · Spring 2026', 'result' => '47 / 50', 'note' => 'Grade released by Professor Lee'],
                ['title' => 'Student Budget Planner', 'type' => 'Independent', 'term' => 'Summer 2026', 'result' => 'No grade', 'note' => 'Independent projects aren’t graded'],
            ],
        ];
    }

    // ---- Milestones ----
    // Added on zina/milestones.

    // ---- Student team ----
    // Added on zina/student-team.

    // ---- Professor dashboard ----
    // Added on zina/professor-dashboard.

    // ---- Profile ----
    // Added on zina/profile.
}
