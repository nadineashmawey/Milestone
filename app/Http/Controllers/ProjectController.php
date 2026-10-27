<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\StudentProjectCatalog;
use App\Support\ZinaDemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private const TASKS = [
        ['id' => 'design-document', 'name' => 'Upload design document', 'project' => 'Software Engineering Capstone', 'type' => 'File upload', 'mode' => 'Team', 'due' => 'Oct 18 · 5:00 PM', 'status' => 'In progress', 'tone' => 'info', 'assignee' => 'Aya Hassan', 'priority' => 'Medium', 'dependency' => 'Research summary', 'reviewer' => 'Sara Ahmed · TA'],
        ['id' => 'interactive-prototype', 'name' => 'Share interactive prototype', 'project' => 'Campus Navigation App', 'type' => 'Link', 'mode' => 'Team', 'due' => 'Oct 19', 'status' => 'To do', 'tone' => 'neutral', 'assignee' => 'Aya Hassan', 'priority' => 'Medium', 'dependency' => 'Wireframes', 'reviewer' => 'Sara Ahmed · TA'],
        ['id' => 'api-implementation', 'name' => 'Submit API implementation', 'project' => 'Software Engineering Capstone', 'type' => 'Code repository', 'mode' => 'Individual', 'due' => 'Oct 20', 'status' => 'In review', 'tone' => 'review', 'assignee' => 'Aya Hassan', 'priority' => 'High', 'dependency' => 'API contract', 'reviewer' => 'Sara Ahmed · TA'],
        ['id' => 'architecture-reflection', 'name' => 'Architecture reflection', 'project' => 'Database Systems Lab', 'type' => 'Written answer', 'mode' => 'Individual', 'due' => 'Oct 22', 'status' => 'To do', 'tone' => 'neutral', 'assignee' => 'Aya Hassan', 'priority' => 'Low', 'dependency' => 'Database schema', 'reviewer' => 'Sara Ahmed · TA'],
        ['id' => 'research-package', 'name' => 'Final research package', 'project' => 'Campus Navigation App', 'type' => 'Multiple items', 'mode' => 'Team', 'due' => 'Oct 25', 'status' => 'To do', 'tone' => 'neutral', 'assignee' => 'Aya Hassan', 'priority' => 'Medium', 'dependency' => 'Research summary', 'reviewer' => 'Sara Ahmed · TA'],
    ];

    public function index(): View
    {
        // Demo accounts keep the sample projects; a real account sees only its own.
        return view('student.projects', [
            'projects' => [...($this->showsSamples() ? StudentProjectCatalog::all() : []), ...$this->ownProjects()],
        ]);
    }

    public function show(string $project): View
    {
        $own = collect($this->ownProjects())->firstWhere('id', $project);
        $record = $own ?? ($this->showsSamples() ? StudentProjectCatalog::find($project) : null);

        abort_if($record === null, 404);

        return view('student.project-detail', [
            'project' => $record,
            'own' => $own !== null,
        ]);
    }

    /**
     * The signed-in student's independent projects, from the database, in the catalog's card shape.
     *
     * @return list<array<string, mixed>>
     */
    private function ownProjects(): array
    {
        return Project::independentFor(auth()->user())->latest()->latest('id')->get()->map(fn (Project $project) => [
            'id' => (string) $project->id, 'title' => $project->title, 'kind' => 'Independent',
            'course' => 'Student-led', 'professor' => null, 'team' => null,
            'progress' => 0, 'next' => 'No tasks yet', 'due' => $project->description ?: 'Your independent project.',
            'members' => 1, 'open_tasks' => 0,
            'created' => $project->created_at?->format('M j, Y'),
        ])->all();
    }

    /** Only the demo accounts (config/demo.php) see the sample projects and tasks. */
    private function showsSamples(): bool
    {
        return app(ZinaDemoData::class)->ownAccount() === null;
    }

    /**
     * @return list<array<string, string>>
     */
    private function sampleTasks(): array
    {
        return $this->showsSamples() ? self::TASKS : [];
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()->project_type === 'independent', 403);

        return view('student.project-create');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->project_type === 'independent', 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);
        // Saved for good: independent projects belong to the student who created them and have no professor.
        $project = Project::create([
            'type' => 'independent',
            'owner_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => 'active',
            'published_at' => now(),
        ]);

        return redirect()->route('student.projects.show', $project->id)->with('success', 'Independent project created.');
    }

    public function tasks(): View
    {
        return view('student.tasks', ['tasks' => $this->sampleTasks()]);
    }

    public function task(string $task, Request $request): View
    {
        $record = collect($this->sampleTasks())->firstWhere('id', $task);
        abort_if($record === null, 404);
        $draft = $request->session()->get('task_drafts.'.$task, []);
        $submitted = $request->session()->get('task_submitted.'.$task, $record['status'] === 'In review');

        return view('student.task-detail', ['task' => $record, 'draft' => $draft, 'submitted' => $submitted]);
    }

    public function saveTaskDraft(string $task, Request $request): RedirectResponse
    {
        abort_unless(collect($this->sampleTasks())->contains('id', $task), 404);
        $data = $request->validate(['url' => ['nullable', 'url', 'max:2048'], 'answer' => ['nullable', 'string', 'max:10000'], 'branch' => ['nullable', 'string', 'max:120'], 'commit' => ['nullable', 'string', 'max:80'], 'criteria' => ['nullable', 'array'], 'items' => ['nullable', 'array']]);
        $request->session()->put('task_drafts.'.$task, $data);

        return back()->with('status', 'Task draft saved.');
    }

    public function submitTask(string $task, Request $request): RedirectResponse
    {
        $record = collect($this->sampleTasks())->firstWhere('id', $task);
        abort_if($record === null, 404);
        $data = $request->validate(['url' => ['nullable', 'url', 'max:2048'], 'answer' => ['nullable', 'string', 'max:10000'], 'branch' => ['nullable', 'string', 'max:120'], 'commit' => ['nullable', 'string', 'max:80'], 'criteria' => ['required', 'array', 'size:3'], 'criteria.*' => ['accepted'], 'items' => ['nullable', 'array']]);
        $valid = match ($record['type']) {
            'File upload' => $request->hasFile('files'),
            'Link' => filter_var($data['url'] ?? '', FILTER_VALIDATE_URL) !== false,
            'Code repository' => isset($data['url']) && str_contains(parse_url($data['url'], PHP_URL_HOST) ?: '', 'github.com'),
            'Written answer' => str_word_count(trim($data['answer'] ?? '')) >= 20,
            default => count($data['items'] ?? []) === 3,
        };
        if (!$valid) {
            return back()->withErrors(['submission' => 'Complete every required submission field before sending this task for review.'])->withInput();
        }
        $request->session()->put('task_submitted.'.$task, true);

        return redirect()->route('student.tasks.show', $task)->with('status', 'Task sent to the TA review queue.');
    }
}
