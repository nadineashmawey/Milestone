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

    // ---- Professor dashboard ----
    // Added on zina/professor-dashboard.

    // ---- Profile ----
    // Added on zina/profile.
}
