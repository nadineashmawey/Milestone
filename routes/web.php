<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfessorCourseController;
use App\Http\Controllers\ProfessorSubmissionController;
use App\Http\Controllers\ProfessorRiskRadarController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/professor/courses', [
    ProfessorCourseController::class,
    'index',
])->name('professor.courses.index');

if (app()->environment('local')) {
    Route::view('/ui-kit', 'ui-kit')->name('ui-kit');
}

Route::view('/professor/projects/create', 'projects.create')->name('professor.projects.create');
Route::get('/professor/submissions', [ProfessorSubmissionController::class, 'index'])->name('professor.submissions.index');
Route::get('/professor/risk-radar', [ProfessorRiskRadarController::class, 'index'])->name('professor.risk-radar.index');
