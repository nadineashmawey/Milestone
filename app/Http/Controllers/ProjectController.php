<?php

namespace App\Http\Controllers;

use App\Support\StudentProjectCatalog;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(): View
    {
        return view('student.projects', [
            'projects' => StudentProjectCatalog::all(),
        ]);
    }

    public function show(string $project): View
    {
        $project = StudentProjectCatalog::find($project);

        abort_if($project === null, 404);

        return view('student.project-detail', [
            'project' => $project,
        ]);
    }

    public function create(): View
    {
        return view('student.projects', [
            'projects' => StudentProjectCatalog::all(),
        ]);
    }
}
