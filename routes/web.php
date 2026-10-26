<?php

use App\Http\Controllers\AdminImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfessorTeamController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfessorCourseController;
use App\Http\Controllers\ProfessorSubmissionController;
use App\Http\Controllers\ProfessorRiskRadarController;
use App\Http\Controllers\ProfessorExtensionController;

Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/sign-in', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/sign-up', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/sign-up', [AuthController::class, 'register'])->name('register.submit');
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
Route::get('/professor/extensions', [ProfessorExtensionController::class, 'index'])->name('professor.extensions.index');

Route::post('/sign-out', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('projects.index'))->name('home');

    Route::get('/my-projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/my-projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/my-projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/my-projects/{project}', [ProjectController::class, 'show'])->name('student.projects.show');

    Route::prefix('student-tasks')->name('student.tasks.')->group(function () {
        Route::get('/', [ProjectController::class, 'tasks'])->name('index');
        Route::get('/{task}', [ProjectController::class, 'task'])->name('show');
        Route::post('/{task}/draft', [ProjectController::class, 'saveTaskDraft'])->name('draft');
        Route::post('/{task}/submit', [ProjectController::class, 'submitTask'])->name('submit');
    });

    Route::prefix('professor/teams')->name('professor.teams.')->group(function () {
        Route::get('/', [ProfessorTeamController::class, 'index'])->name('index');
        Route::post('/auto-place', [ProfessorTeamController::class, 'autoPlace'])->name('auto-place');
        Route::post('/members', [ProfessorTeamController::class, 'addSelectedMember'])->name('members.add');
        Route::post('/{team}/members/{member}/remove', [ProfessorTeamController::class, 'removeMember'])->name('members.remove');
        Route::post('/{team}/leader', [ProfessorTeamController::class, 'changeLeader'])->name('leader');
    });
    Route::post('/professor/leave-requests/{requestId}', [ProfessorTeamController::class, 'resolveLeaveRequest'])
        ->name('professor.teams.leave-request');

    Route::prefix('admin/imports')->name('admin.imports.')->group(function () {
        Route::get('/', [AdminImportController::class, 'index'])->name('index');
        Route::post('/preview', [AdminImportController::class, 'preview'])->name('preview');
        Route::post('/confirm', [AdminImportController::class, 'confirm'])->name('confirm');
    });
});
