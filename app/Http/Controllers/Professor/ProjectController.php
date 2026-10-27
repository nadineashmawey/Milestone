<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Project;
use App\Models\User;
use App\Support\CurrentUser;
use App\Support\ProfessorProjects;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The professor's academic projects: the Create project wizard (Nadine's page), My projects and the project page.
 */
class ProjectController extends Controller
{
    public function index(Request $request, ProfessorProjects $projects): View
    {
        $professor = $this->professor();

        return view('professor.projects.index', [
            'state' => $projects->state($request),
            'list' => $projects->list($professor, $request->only(['q', 'course', 'status', 'sort'])),
            'filters' => [
                'q' => (string) $request->query('q', ''),
                'course' => (string) $request->query('course', ''),
                'status' => (string) $request->query('status', ''),
                'sort' => $request->query('sort') === 'deadline' ? 'deadline' : 'newest',
            ],
            'courses' => Course::where('professor_id', $professor->id)->orderByDesc('code')->get(),
            'total' => Project::ownedBy($professor)->count(),
        ]);
    }

    public function create(): View
    {
        $this->authorizeFor('create', Project::class);

        return $this->wizard(null);
    }

    public function edit(Project $project): View
    {
        $this->authorizeFor('update', $project);

        return $this->wizard($project->load(['milestones', 'criteria']));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $professor = $this->professor();
        $key = $request->input('submission_key');

        // A second click or a resend of the same wizard page opens the project the first one created.
        if ($key && ($existing = Project::ownedBy($professor)->where('submission_key', $key)->first())) {
            return $this->redirectTo($existing);
        }

        try {
            $project = DB::transaction(function () use ($request, $professor, $key) {
                $project = Project::create([...$this->attributes($request), 'professor_id' => $professor->id, 'submission_key' => $key]);
                $this->saveChildren($project, $request);
                $this->afterSave($project, $professor, $request);

                return $project;
            });
        } catch (UniqueConstraintViolationException) {
            // Both clicks reached the database at once; the other request created the project.
            return $this->redirectTo(Project::ownedBy($professor)->where('submission_key', $key)->firstOrFail());
        }

        return $this->redirectTo($project);
    }

    public function update(StoreProjectRequest $request, Project $project): RedirectResponse
    {
        $professor = $this->professor();

        DB::transaction(function () use ($request, $project, $professor) {
            $project->update($this->attributes($request));
            $project->milestones()->delete();
            $project->criteria()->delete();
            $this->saveChildren($project, $request);
            $this->afterSave($project, $professor, $request);
        });

        return $this->redirectTo($project);
    }

    public function show(Project $project, ProfessorProjects $projects): View
    {
        $this->authorizeFor('view', $project);

        return view('professor.projects.show', ['project' => $projects->detail($project)]);
    }

    private function wizard(?Project $project): View
    {
        $professor = $this->professor();

        return view('projects.create', [
            'project' => $project,
            'courses' => Course::where('professor_id', $professor->id)->orderByDesc('code')->get(), // CS 490 first, as the wizard always listed them
            'submissionKey' => (string) Str::uuid(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(StoreProjectRequest $request): array
    {
        $data = $request->validated();
        $publish = $request->publishing();

        return [
            'course_id' => $data['course'] ?? null,
            'type' => 'academic',
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'goals' => $data['goals'] ?? [],
            'total_grade' => $data['grade'] ?? null,
            'team_min' => $data['team_min'] ?? null,
            'team_max' => $data['team_max'] ?? null,
            'formation_deadline' => $data['team_deadline'] ?? null,
            'leftover_mode' => StoreProjectRequest::LEFTOVER[$data['leftover'] ?? ''] ?? 'auto',
            'status' => $publish ? 'active' : 'draft',
            'published_at' => $publish ? now() : null,
            'settings' => [
                'color' => $data['color'] ?? null,
                'formation' => $data['formation'] ?? null,
                'late_policy' => $data['late_policy'] ?? null,
                'resubmit' => $data['resubmit'] ?? false,
                'reminders' => $data['reminders'] ?? false,
                'ta_permission' => $data['permission'] ?? null,
            ],
        ];
    }

    private function saveChildren(Project $project, StoreProjectRequest $request): void
    {
        $data = $request->validated();
        $grade = (int) ($data['grade'] ?? 0);

        foreach ($data['milestones'] ?? [] as $position => $milestone) {
            $project->milestones()->create([
                'name' => $milestone['name'] ?: 'Milestone '.($position + 1),
                'due_at' => isset($milestone['deadline']) ? $milestone['deadline'].' 17:00:00' : null,
                'deliverable_types' => $milestone['deliverable_types'] ?? [],
                'weight' => $milestone['weight'] ?? null,
                'points' => $grade && isset($milestone['weight']) ? (int) round($grade * $milestone['weight'] / 100) : null,
                'position' => $position,
            ]);
        }

        foreach ($data['criteria'] ?? [] as $position => $criterion) {
            $project->criteria()->create(['name' => $criterion['name'], 'max_points' => $criterion['points'], 'position' => $position]);
        }
    }

    private function afterSave(Project $project, User $professor, StoreProjectRequest $request): void
    {
        if (! $request->publishing()) {
            return;
        }

        AuditLog::record($professor, 'project.published', $project, [
            'title' => $project->title,
            'course_id' => $project->course_id,
            'milestones' => $project->milestones()->count(),
        ]);

        // TODO(Raghad): send the real "project published" notification to the course roster once notifications are stored.
        Log::info('Simulated notification: students invited to a newly published project.', ['project_id' => $project->id, 'course_id' => $project->course_id]);
    }

    private function redirectTo(Project $project): RedirectResponse
    {
        return redirect()->route('professor.projects.show', $project)
            ->with('success', $project->isDraft() ? 'Draft saved. Nothing was published and no one was invited.' : 'Project published.');
    }

    private function professor(): User
    {
        $user = CurrentUser::get();
        abort_unless($user !== null, 403);

        return $user;
    }

    private function authorizeFor(string $ability, mixed $subject): void
    {
        abort_unless($this->professor()->can($ability, $subject), 403);
    }
}
