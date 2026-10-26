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

    /**
     * Professor Lee's dashboard (PROF-1). $empty gives a new professor with no projects.
     * Links to teammates' pages resolve once their routes exist; each item names the page owner.
     *
     * @return array<string, mixed>
     */
    public function professorDashboard(bool $empty = false): array
    {
        $now = $this->now();
        $professor = $this->person('lee');
        $base = [
            'professor' => $professor,
            'today' => $now->format('l, F j'),
            'greeting' => $this->greeting(),
            'create_href' => $this->link('professor.projects.create'),
        ];

        if ($empty) {
            return [...$base, 'summary' => 'You don’t have any projects yet.', 'stats' => [], 'attention' => [], 'projects' => [], 'deadlines' => [], 'activity' => []];
        }

        $submissions = ['href' => $this->link('professor.submissions.index'), 'owner' => 'Nadine', 'page' => 'Submissions'];
        $teams = ['href' => $this->link('professor.teams.index'), 'owner' => 'Jana', 'page' => 'Professor Teams'];
        $risk = ['href' => $this->link('professor.risk-radar.index'), 'owner' => 'Nadine', 'page' => 'Risk Radar'];
        $extensions = ['href' => $this->link('professor.extensions.index'), 'owner' => 'Nadine', 'page' => 'Extensions'];
        $notifications = ['href' => $this->link('notifications'), 'owner' => 'Raghad', 'page' => 'Notifications'];

        // What students did this session on the other pages shows up here too.
        $resubmissions = $this->get('milestones.software-engineering-capstone.versions', []);
        $leave = $this->get('team.software-engineering-capstone.leave');

        $attention = [];
        if ($resubmissions) {
            $latest = end($resubmissions);
            $attention[] = ['tone' => 'danger', 'priority' => 'Due soon', 'title' => 'Team 1 resubmitted M2 Design Document', 'meta' => 'Software Engineering Capstone · version '.$latest['number'].' by '.$latest['submitter'], 'action' => 'Review', ...$submissions];
        }
        $attention[] = ['tone' => 'danger', 'priority' => 'Due soon', 'title' => 'Team 3 submitted M2 Design Document', 'meta' => 'Database Systems · 42 minutes ago · M2 closes Oct 14', 'action' => 'Review', ...$submissions];
        if ($leave) {
            $attention[] = ['tone' => 'warning', 'priority' => 'Waiting on you', 'title' => $leave['by'].' asked to leave Team 1', 'meta' => 'Software Engineering Capstone · “'.\Illuminate\Support\Str::limit($leave['reason'], 60).'”', 'action' => 'Decide', ...$teams];
        }
        $attention[] = ['tone' => 'warning', 'priority' => 'Waiting on you', 'title' => 'Karim Saleh and Dina Nasser are unassigned', 'meta' => 'Software Engineering Capstone · formation closed in Manual mode', 'action' => 'Place students', ...$teams];
        $attention[] = ['tone' => 'review', 'priority' => 'Watch', 'title' => 'Team Delta has been inactive for 6 days', 'meta' => 'Database Systems · simulated High risk · M2 at risk', 'action' => 'Inspect', ...$risk];
        $attention[] = ['tone' => 'review', 'priority' => 'Watch', 'title' => 'Team 3 emailed you about an M2 extension', 'meta' => 'Software Engineering Capstone · request arrived by email Oct 12', 'action' => 'Record extension', ...$extensions];
        $attention[] = ['tone' => 'neutral', 'priority' => 'FYI', 'title' => 'Sara Ahmed reported an issue: Team 3 has no active leader', 'meta' => 'Software Engineering Capstone · reported by your TA yesterday', 'action' => 'View', ...$notifications];

        $projects = [
            ['title' => 'Software Engineering Capstone', 'type' => 'Academic', 'course' => 'CS 490 · 8 teams', 'progress' => 62, 'risk' => 'Low', 'risk_tone' => 'success', 'reason' => 'All teams active this week', 'next' => 'M2 Design Document', 'next_due' => $this->date('2026-10-18 17:00'), 'extended' => '1 team extended', ...$teams],
            ['title' => 'Database Systems', 'type' => 'Academic', 'course' => 'CS 342 · 6 teams', 'progress' => 35, 'risk' => 'High', 'risk_tone' => 'danger', 'reason' => '3 teams inactive for 4+ days', 'next' => 'M2 Design Document', 'next_due' => $this->date('2026-10-14 17:00'), 'extended' => null, ...$teams],
            ['title' => 'Web Development Workshop', 'type' => 'Academic', 'course' => 'CS 210 · 5 teams', 'progress' => 88, 'risk' => 'Medium', 'risk_tone' => 'warning', 'reason' => '2 reviews waiting', 'next' => 'M3 Demo', 'next_due' => $this->date('2026-10-22 17:00'), 'extended' => null, ...$teams],
        ];
        $projects = array_map(fn ($project) => [...$project, 'next_label' => $project['next_due']->format('M j'), 'countdown' => $this->countdown($project['next_due'])], $projects);

        $deadlines = collect([
            ['title' => 'M2 · Design Document', 'project' => 'Database Systems', 'due' => $this->date('2026-10-14 17:00'), 'note' => '6 teams · 2 submitted'],
            ['title' => 'M2 · Design Document', 'project' => 'Software Engineering Capstone', 'due' => $this->date('2026-10-18 17:00'), 'note' => '8 teams · 3 submitted · Team 3 extended to Oct 21'],
            ['title' => 'M3 · Demo', 'project' => 'Web Development Workshop', 'due' => $this->date('2026-10-22 17:00'), 'note' => '5 teams'],
            ['title' => 'M3 · Final Demo', 'project' => 'Software Engineering Capstone', 'due' => $this->date('2026-11-08 17:00'), 'note' => 'Opens after M2 review'],
        ])->sortBy(fn ($deadline) => $deadline['due'])->map(fn ($deadline) => [
            ...$deadline,
            'day' => $deadline['due']->format('j'),
            'month' => $deadline['due']->format('M'),
            'when' => $deadline['due']->format('D, M j · g:i A'),
            'countdown' => $this->countdown($deadline['due']),
        ])->values()->all();

        $activity = [
            ['who' => 'Aya Hassan', 'text' => 'moved “API contract” to In Review', 'meta' => 'Team 1 · Capstone · 12 minutes ago', 'avatar' => 0],
            ['who' => 'Sara Ahmed', 'text' => 'drafted feedback on M2 Design Document', 'meta' => 'Team 3 · Database Systems · 38 minutes ago', 'avatar' => 2],
            ['who' => 'Team 2', 'text' => 'submitted M1 Proposal', 'meta' => 'Web Development Workshop · 1 hour ago', 'avatar' => 3],
            ['who' => 'Nour Saleh', 'text' => 'completed “User interviews”', 'meta' => 'Team 1 · Capstone · 2 hours ago', 'avatar' => 1],
        ];
        if ($resubmissions) {
            array_unshift($activity, ['who' => end($resubmissions)['submitter'], 'text' => 'submitted M2 Design Document, version '.end($resubmissions)['number'], 'meta' => 'Team 1 · Capstone · just now', 'avatar' => 0]);
        }

        $waiting = 7 + count($resubmissions);
        $needsYou = collect($attention)->whereIn('priority', ['Due soon', 'Waiting on you'])->count();

        return [
            ...$base,
            'summary' => "3 supervised projects across 19 teams. {$needsYou} ".($needsYou === 1 ? 'item needs' : 'items need').' you today.',
            'stats' => [
                ['label' => 'Active projects', 'value' => 3, 'note' => 'Across 3 courses', 'icon' => 'folder', 'tone' => ''],
                ['label' => 'Teams', 'value' => 19, 'note' => '2 students unassigned', 'icon' => 'users', 'tone' => 'success'],
                ['label' => 'Waiting for review', 'value' => $waiting, 'note' => '2 due before Oct 14', 'icon' => 'check', 'tone' => 'review'],
                ['label' => 'At-risk projects', 'value' => 2, 'note' => 'Simulated risk · 1 High, 1 Medium', 'icon' => 'chart', 'tone' => 'danger'],
            ],
            'attention' => $attention,
            'projects' => $projects,
            'deadlines' => $deadlines,
            'activity' => $activity,
            'notifications_href' => $notifications['href'],
        ];
    }

    // ---- Profile ----
    // Added on zina/profile.
}
