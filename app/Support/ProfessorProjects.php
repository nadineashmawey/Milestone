<?php

namespace App\Support;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The professor's projects from the database, as the plain arrays the pages render:
 * My projects, the project page, the Professor Dashboard and Courses.
 * Dates are measured from the demo clock (ZinaDemoData::now()) so every page's countdowns agree.
 */
final class ProfessorProjects
{
    public const STATUS_TONES = ['draft' => 'neutral', 'active' => 'success', 'archived' => 'review'];

    public function __construct(private ZinaDemoData $demo)
    {
    }

    public function state(Request $request): string
    {
        return $this->demo->state($request);
    }

    /**
     * My projects, filtered and sorted.
     *
     * @param  array{q?: string|null, course?: string|null, status?: string|null, sort?: string|null}  $filters
     * @return list<array<string, mixed>>
     */
    public function list(User $professor, array $filters = []): array
    {
        $query = Project::ownedBy($professor)->with(['course', 'milestones'])->withCount('teams');

        if ($search = trim((string) ($filters['q'] ?? ''))) {
            $query->where('title', 'like', '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%');
        }
        if ($course = $filters['course'] ?? null) {
            $query->where('course_id', (int) $course);
        }
        if (in_array($filters['status'] ?? null, array_keys(Project::STATUSES), true)) {
            $query->where('status', $filters['status']);
        }

        $rows = $query->latest()->latest('id')->get()->map(fn (Project $project) => $this->row($project));

        if (($filters['sort'] ?? null) === 'deadline') {
            // Soonest next milestone first; projects with nothing coming up go last.
            $rows = $rows->sortBy(fn (array $row) => $row['next'] ? $row['next']['due']->timestamp : PHP_INT_MAX)->values();
        }

        return $rows->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function row(Project $project): array
    {
        $now = $this->demo->now();
        $next = $project->nextMilestone($now);

        return [
            'id' => $project->id,
            'title' => $project->title,
            'type' => ucfirst($project->type),
            'course' => $project->course?->label() ?? 'No course yet',
            'course_code' => $project->course?->code,
            'status' => $project->statusLabel(),
            'status_tone' => self::STATUS_TONES[$project->status] ?? 'neutral',
            'is_draft' => $project->isDraft(),
            'teams' => $project->teams_count ?? $project->teams()->count(),
            'milestone_count' => $project->milestones->count(),
            'total_grade' => $project->total_grade,
            'progress' => $project->progress($now),
            'next' => $next ? [
                'name' => 'M'.($next->position + 1).' '.$next->name,
                'due' => $next->due_at->toImmutable(),
                'label' => $next->due_at->format('M j'),
                'countdown' => $this->demo->countdown($next->due_at->toImmutable()),
            ] : null,
            'published' => $project->published_at?->format('M j, Y'),
            'href' => route('professor.projects.show', $project),
            'edit_href' => $project->isDraft() ? route('professor.projects.edit', $project) : null,
        ];
    }

    /**
     * Everything the project page shows.
     *
     * @return array<string, mixed>
     */
    public function detail(Project $project): array
    {
        $project->load(['course', 'milestones', 'criteria'])->loadCount('teams');
        $settings = $project->settings ?? [];
        $leftover = array_flip(StoreProjectRequest::LEFTOVER)[$project->leftover_mode] ?? 'Random placement';

        return [
            ...$this->row($project),
            'description' => $project->description,
            'goals' => $project->goals ?? [],
            'semester' => $project->course?->semester,
            'milestones' => $project->milestones->map(fn (Milestone $milestone) => [
                'number' => 'M'.($milestone->position + 1),
                'name' => $milestone->name,
                'due' => $milestone->due_at?->format('D, M j, Y · g:i A') ?? 'No deadline yet',
                'due_iso' => $milestone->due_at?->toIso8601String(),
                'deliverables' => $milestone->deliverableLabels() ?: 'Not chosen yet',
                'weight' => $milestone->weight,
                'points' => $milestone->points,
            ])->all(),
            'criteria' => $project->criteria->map(fn ($criterion) => ['name' => $criterion->name, 'points' => $criterion->max_points])->all(),
            'team_rules' => array_values(array_filter([
                ['label' => 'Team size', 'value' => $project->team_min && $project->team_max ? "{$project->team_min}–{$project->team_max} students" : 'Not set yet'],
                ['label' => 'How teams are formed', 'value' => $settings['formation'] ?? 'Not set yet'],
                ['label' => 'Team-choosing deadline', 'value' => $project->formation_deadline?->format('M j, Y') ?? 'Not set yet'],
                ['label' => 'When the deadline passes', 'value' => $project->leftover_mode === 'manual' ? 'Professor decides' : 'Auto-place randomly'],
            ])),
            'grading' => array_values(array_filter([
                ['label' => 'Total project grade', 'value' => $project->total_grade ? $project->total_grade.' points' : 'Not set yet'],
                ['label' => 'Late policy', 'value' => $settings['late_policy'] ?? 'Not set yet'],
                ['label' => 'Resubmissions', 'value' => ($settings['resubmit'] ?? false) ? 'Allowed until the deadline' : 'Not allowed'],
                ['label' => 'Reminders', 'value' => ($settings['reminders'] ?? false) ? '3 days and 1 day before each deadline' : 'Off'],
                ['label' => 'TA permission', 'value' => $settings['ta_permission'] ?? 'Not set yet'],
            ])),
            'leftover' => $leftover,
        ];
    }

    /**
     * Active projects and their upcoming milestones, for the Professor Dashboard.
     *
     * @return array{projects: Collection<int, array<string, mixed>>, deadlines: Collection<int, array<string, mixed>>, teams: int, courses: int}
     */
    public function dashboard(User $professor): array
    {
        $now = $this->demo->now();
        $projects = Project::ownedBy($professor)->where('status', 'active')->with(['course', 'milestones'])->withCount('teams')->orderBy('published_at')->orderBy('id')->get();

        $deadlines = $projects->flatMap(fn (Project $project) => $project->milestones
            ->filter(fn (Milestone $milestone) => $milestone->due_at?->greaterThan($now))
            ->map(fn (Milestone $milestone) => [
                'title' => 'M'.($milestone->position + 1).' · '.$milestone->name,
                'project' => $project->title,
                'due' => $milestone->due_at->toImmutable(),
                'note' => $project->teams_count.' '.($project->teams_count === 1 ? 'team' : 'teams'),
            ]))->sortBy(fn (array $deadline) => $deadline['due'])->values();

        return [
            'projects' => $projects->map(fn (Project $project) => $this->row($project))->values(),
            'deadlines' => $deadlines,
            'teams' => (int) $projects->sum('teams_count'),
            'courses' => $projects->pluck('course_id')->filter()->unique()->count(),
        ];
    }
}
