<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Frontend-only student project previews. The data is intentionally local until
// the backend project API is ready.
Route::get('/student/projects', function () {
    return view('student.projects', ['projects' => \App\Support\StudentProjectCatalog::all()]);
})->name('student.projects');


Route::get('/student/projects/{project}', function (string $project) {
    $selectedProject = \App\Support\StudentProjectCatalog::find($project);


    abort_unless($selectedProject, 404);


    return view('student.project-detail', ['project' => $selectedProject]);
})->name('student.projects.show');

if (app()->environment('local')) {
    Route::view('/ui-kit', 'ui-kit')->name('ui-kit');
}
