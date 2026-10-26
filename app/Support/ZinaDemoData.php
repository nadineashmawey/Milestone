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

    /** SUB-4 submission statuses, in order. */
    public const SUBMISSION_STATUSES = ['Not started', 'Submitted', 'TA reviewed', 'Needs resubmission', 'Approved', 'Graded'];

    /** DLN-7 file types for milestone deliverables. The final allowlist is still open under DEC-12. */
    public const DELIVERABLE_EXTENSIONS = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg', 'zip', 'psd', 'pkt', 'txt', 'md'];

    /** DLN-7: 25 MB per file, in kilobytes for Laravel's max rule. */
    public const MAX_UPLOAD_KB = 25600;

    /**
     * The project's milestone timeline (PROF-2, SUB-1–4). Null when the project doesn't exist.
     *
     * @return array<string, mixed>|null
     */
    public function milestones(string $projectId): ?array
    {
        $project = $this->project($projectId);
        if (! $project) {
            return null;
        }

        $viewer = $this->viewer();
        $leader = $this->person('aya');
        $base = [
            'project' => $project,
            'viewer' => $viewer,
            'leader' => $leader,
            'can_submit' => $viewer['id'] === $leader['id'],
        ];

        // SUB-1: Independent projects have no official milestone submissions.
        if ($project['type'] === 'Independent') {
            return [...$base, 'milestones' => [], 'can_submit' => false];
        }

        $m2Due = $this->date('2026-10-18 17:00');
        $m2Versions = [
            [
                'number' => 1,
                'submitted_at' => $this->date('2026-10-09 15:40'),
                'submitter' => $leader['name'],
                'files' => [
                    ['label' => 'Design document', 'name' => 'team1-design-v1.pdf', 'size' => '2.1 MB'],
                    ['label' => 'Presentation', 'name' => 'team1-design-v1.pptx', 'size' => '6.4 MB'],
                ],
                'link' => 'https://github.com/team-1/capstone',
                'status' => 'Needs resubmission',
            ],
            ...$this->get('milestones.'.$projectId.'.versions', []),
        ];
        $m2Versions = array_map(fn ($version) => $this->presentVersion($version, $m2Due), $m2Versions);
        $latest = end($m2Versions);
        $resubmitted = $latest['number'] > 1;

        $m2Timeline = [
            ['status' => 'Submitted', 'when' => 'Oct 9, 3:40 PM', 'by' => 'Aya Hassan · version 1'],
            ['status' => 'TA reviewed', 'when' => 'Oct 10, 11:05 AM', 'by' => 'Sara Ahmed · full review'],
            ['status' => 'Needs resubmission', 'when' => 'Oct 11, 2:30 PM', 'by' => 'Professor Lee'],
        ];
        if ($resubmitted) {
            $m2Timeline[] = ['status' => 'Submitted', 'when' => $latest['when'], 'by' => $latest['submitter'].' · version '.$latest['number']];
        }

        return [
            ...$base,
            'milestones' => [
                [
                    'code' => 'M1',
                    'title' => 'Proposal',
                    'state' => 'graded',
                    'status' => 'Graded',
                    'due' => $this->date('2026-09-24 17:00'),
                    'due_label' => 'Thu, Sep 24 · 5:00 PM',
                    'grade' => ['score' => 41, 'out_of' => 50, 'released' => 'Released by Professor Lee on Oct 2 · locked'],
                    'rubric' => [
                        ['criterion' => 'Problem framing', 'score' => 9, 'max' => 10],
                        ['criterion' => 'Research', 'score' => 8, 'max' => 10],
                        ['criterion' => 'Feasibility', 'score' => 8, 'max' => 10],
                        ['criterion' => 'Team plan', 'score' => 7, 'max' => 10],
                        ['criterion' => 'Communication', 'score' => 9, 'max' => 10],
                    ],
                    'feedback' => [
                        ['author' => 'Professor Lee', 'role' => 'Professor', 'when' => 'Oct 2', 'text' => 'Strong foundation and a clear user need. Narrow the first release and make testing ownership explicit.'],
                    ],
                    'versions' => [
                        $this->presentVersion([
                            'number' => 1,
                            'submitted_at' => $this->date('2026-09-24 17:12'),
                            'submitter' => $leader['name'],
                            'files' => [['label' => 'Proposal', 'name' => 'team1-proposal.pdf', 'size' => '1.4 MB']],
                            'link' => null,
                            'status' => 'Graded',
                        ], $this->date('2026-09-24 17:00')),
                    ],
                ],
                [
                    'code' => 'M2',
                    'title' => 'Design Document',
                    'state' => 'active',
                    'status' => $resubmitted ? 'Submitted' : 'Needs resubmission',
                    'description' => 'Document your system architecture, data model and interface design, and explain the trade-offs your team chose. Reviewers read the document and the presentation together, so keep the two consistent.',
                    'due' => $m2Due,
                    'due_label' => 'Sun, Oct 18 · 5:00 PM',
                    'countdown' => $this->countdown($m2Due),
                    'deliverables' => $this->m2Deliverables(),
                    'checklist' => [
                        'final' => 'Every required deliverable is the final version.',
                        'access' => 'The repository link is accessible to reviewers.',
                        'approved' => 'All team members approved this version.',
                    ],
                    'timeline' => $m2Timeline,
                    'feedback' => [
                        ['author' => 'Sara Ahmed', 'role' => 'Teaching assistant', 'when' => 'Oct 10', 'text' => 'Strong architecture. Add cardinality labels to the ERD and name an owner for each testing phase.'],
                        ['author' => 'Professor Lee', 'role' => 'Professor', 'when' => 'Oct 11', 'text' => 'I agree with Sara. Resubmit with those two changes before the deadline; the rest is ready.'],
                    ],
                    'versions' => array_reverse($m2Versions),
                    'draft' => $this->get('milestones.'.$projectId.'.draft'),
                ],
                [
                    'code' => 'M3',
                    'title' => 'Final Demo',
                    'state' => 'locked',
                    'status' => 'Not started',
                    'due' => $this->date('2026-11-08 17:00'),
                    'due_label' => 'Sun, Nov 8 · 5:00 PM',
                    'lock_reason' => 'Opens after Professor Lee reviews M2.',
                    'requirements' => ['Live demonstration', 'Demo video link', 'Final code repository', 'Team reflection'],
                ],
            ],
        ];
    }

    /**
     * The three M2 deliverables (PROF-2: files and a GitHub link).
     *
     * @return array<string, array{label: string, kind: string, help: string, accept?: string}>
     */
    public function m2Deliverables(): array
    {
        return [
            'design_document' => ['label' => 'Design document', 'kind' => 'file', 'help' => 'PDF or Word document, up to 25 MB.', 'accept' => '.pdf,.doc,.docx'],
            'presentation' => ['label' => 'Presentation', 'kind' => 'file', 'help' => 'PowerPoint or PDF, up to 25 MB.', 'accept' => '.ppt,.pptx,.pdf'],
            'repository' => ['label' => 'GitHub repository', 'kind' => 'link', 'help' => 'For example https://github.com/team-1/capstone'],
        ];
    }

    /**
     * Keep the validated deliverables as a draft. Only file names and sizes are kept; the prototype stores no file contents.
     *
     * @param  array<string, mixed>  $draft
     */
    public function saveMilestoneDraft(string $projectId, array $draft): void
    {
        $this->put('milestones.'.$projectId.'.draft', $draft);
    }

    public function discardMilestoneDraft(string $projectId): void
    {
        $this->forget('milestones.'.$projectId.'.draft');
    }

    /**
     * Turn the draft into the next version (SUB-2). Returns the stored version, or null when there's no draft.
     *
     * @return array<string, mixed>|null
     */
    public function submitMilestoneDraft(string $projectId): ?array
    {
        $draft = $this->get('milestones.'.$projectId.'.draft');
        if (! $draft) {
            return null;
        }

        $versions = $this->get('milestones.'.$projectId.'.versions', []);
        $version = [
            'number' => count($versions) + 2,
            'submitted_at' => $this->now()->addMinutes(count($versions) * 7 + 12),
            'submitter' => $this->viewer()['name'],
            'files' => $draft['files'],
            'link' => $draft['link'],
            'status' => 'Submitted',
        ];

        $this->put('milestones.'.$projectId.'.versions', [...$versions, $version]);
        $this->discardMilestoneDraft($projectId);

        return $this->presentVersion($version, $this->date('2026-10-18 17:00'));
    }

    /**
     * @param  array<string, mixed>  $version
     * @return array<string, mixed>
     */
    private function presentVersion(array $version, CarbonImmutable $due): array
    {
        return [
            ...$version,
            'when' => $version['submitted_at']->format('M j, g:i A'),
            'late' => $version['submitted_at']->greaterThan($due),
        ];
    }

    /**
     * Human-readable file size, e.g. 2.1 MB.
     */
    public function fileSize(int $bytes): string
    {
        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1).' MB' : max(1, (int) round($bytes / 1024)).' KB';
    }

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
