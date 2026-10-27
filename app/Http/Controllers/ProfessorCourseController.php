<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Project;
use App\Support\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfessorCourseController extends Controller
{
    /**
     * Sample roster figures until enrolments are stored (there is no roster table yet), by course code.
     */
    private const SAMPLE_ROSTERS = [
        'CS 490' => [
            'studentCount' => 24, 'taCount' => 1, 'unassignedCount' => 2,
            'students' => [
                ['name' => 'Aya Hassan', 'team' => 'Team 1'],
                ['name' => 'Malek Mansour', 'team' => 'Team 2'],
                ['name' => 'Nour Saleh', 'team' => 'Team 3'],
                ['name' => 'Karim Saleh', 'team' => null],
            ],
        ],
        'CS 342' => ['studentCount' => 28, 'taCount' => 0, 'unassignedCount' => 28, 'students' => []],
        'CS 210' => ['studentCount' => 31, 'taCount' => 0, 'unassignedCount' => 31, 'students' => []],
    ];

    public function index(Request $request): View
    {
        $professor = CurrentUser::get();

        // The professor's courses and their projects come from the database.
        $courses = Course::where('professor_id', $professor?->id)
            ->with(['projects' => fn ($query) => $query->withCount(['teams', 'milestones'])->orderBy('created_at')])
            // Highest course code first, the order the page always listed them in (CS 490, CS 342, CS 210).
            ->orderByDesc('code')
            ->get()
            ->mapWithKeys(fn (Course $course) => [Str::slug($course->code, '') => [
                'code' => $course->code,
                'name' => $course->name,
                'semester' => $course->semester,
                'tone' => $course->tone,
                'schedule' => $course->schedule ?? 'Schedule not assigned',
                ...(self::SAMPLE_ROSTERS[$course->code] ?? ['studentCount' => 0, 'taCount' => 0, 'unassignedCount' => 0, 'students' => []]),
                'projects' => $course->projects->map(fn (Project $project) => [
                    'title' => $project->title,
                    'teams' => $project->teams_count,
                    'milestones' => $project->milestones_count,
                    'totalGrade' => $project->total_grade ?? 0,
                    'status' => $project->status === 'active' ? 'Published' : $project->statusLabel(),
                    'tone' => $project->status === 'active' ? 'success' : 'neutral',
                    'href' => route('professor.projects.show', $project),
                ])->all(),
            ]])
            ->all();

        $selectedId = $request->query('course', array_key_first($courses));

        if (! is_string($selectedId) || ! isset($courses[$selectedId])) {
            $selectedId = array_key_first($courses);
        }

        return view('courses.index', [
            'courses' => $courses,
            'selectedId' => $selectedId,
            'selectedCourse' => $selectedId !== null ? $courses[$selectedId] : null,
        ]);
    }
}
