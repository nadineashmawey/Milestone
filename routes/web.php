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

    Route::get('/admin/imports', [AdminImportController::class, 'index'])->name('admin.imports');
    Route::post('/admin/imports/preview', [AdminImportController::class, 'preview'])->name('admin.imports.preview');
    Route::post('/admin/imports/confirm', [AdminImportController::class, 'confirm'])->name('admin.imports.confirm');
});
/* Frontend-only prototypes: application and database logic follow in phase two. */
Route::get('/admin/assets/css/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('css/'.$path);
    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'text/css; charset=UTF-8']);
})->where('path', '.*')->name('admin.assets.css');

Route::get('/admin/assets/js/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('js/'.$path);
    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'text/javascript; charset=UTF-8']);
})->where('path', '.*')->name('admin.assets.js');

Route::redirect('/admin', '/admin/dashboard');
Route::view('/admin/dashboard', 'admin.dashboard')->name('admin.dashboard');
Route::view('/admin/users', 'admin.users')->name('admin.users');
Route::view('/admin/courses', 'admin.courses')->name('admin.courses');
Route::view('/admin/projects', 'admin.projects')->name('admin.projects');
Route::view('/admin/announcements', 'admin.announcements')->name('admin.announcements');
Route::view('/admin/audit', 'admin.audit')->name('admin.audit');

Route::get('/ta/assets/css/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('css/'.$path);
    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'text/css; charset=UTF-8']);
})->where('path', '.*')->name('ta.assets.css');

Route::get('/ta/assets/js/{path}', function (string $path) {
    abort_if(str_contains($path, '..'), 404);
    $file = resource_path('js/'.$path);
    abort_unless(is_file($file), 404);

    return response()->file($file, ['Content-Type' => 'text/javascript; charset=UTF-8']);
})->where('path', '.*')->name('ta.assets.js');

Route::redirect('/ta', '/ta/dashboard');
Route::view('/ta/dashboard', 'ta.dashboard')->name('ta.dashboard');
Route::view('/ta/reviews', 'ta.reviews')->name('ta.reviews');
Route::view('/ta/issues', 'ta.issues')->name('ta.issues');
Route::view('/ta/invitations', 'ta.assignments')->name('ta.assignments');
Route::view('/ta/tasks', 'ta.tasks')->name('ta.tasks');

require __DIR__.'/home.php'; // Home page and Help center. Loaded last, so its "/" replaces the welcome route above.
require __DIR__.'/zina.php'; // Zina's signed-in prototype pages: dashboards, milestones, team and profile.
