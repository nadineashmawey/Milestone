<?php

use App\Http\Controllers\AdminImportController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Professor\ProjectController as ProfessorProjectController;
use App\Http\Controllers\ProfessorCourseController;
use App\Http\Controllers\ProfessorExtensionController;
use App\Http\Controllers\ProfessorRiskRadarController;
use App\Http\Controllers\ProfessorSubmissionController;
use App\Http\Controllers\ProfessorTeamController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

/*
| Every role page sits under its role's prefix and name (student.*, professor.*, ta.*, admin.*) and opens only for
| that role ("role:…" middleware, App\Http\Middleware\EnsureRole). Anyone else gets the access-denied page.
| Shared pages (Home, Help, Profile, Notifications, /ui-kit) keep the visitor's own navbar.
*/

Route::middleware('guest')->group(function () {
    Route::get('/sign-in', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/sign-in', [AuthController::class, 'login'])->name('login.submit');

    Route::get('/sign-up', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/sign-up', [AuthController::class, 'register'])->name('register.submit');
});

Route::post('/sign-out', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

if (app()->environment('local')) {
    Route::view('/ui-kit', 'ui-kit')->name('ui-kit');
}

// ---- Student ----
Route::prefix('student')->name('student.')->middleware(['auth', 'role:student'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [ProjectController::class, 'tasks'])->name('index');
        Route::get('/{task}', [ProjectController::class, 'task'])->name('show');
        Route::post('/{task}/draft', [ProjectController::class, 'saveTaskDraft'])->name('draft');
        Route::post('/{task}/submit', [ProjectController::class, 'submitTask'])->name('submit');
    });
});
// Old addresses of Jana's student pages, kept so bookmarks still work.
Route::redirect('/my-projects', '/student/projects');
Route::redirect('/student-tasks', '/student/tasks');

// ---- Professor ----
Route::prefix('professor')->name('professor.')->middleware('role:professor')->group(function () {
    Route::get('/courses', [ProfessorCourseController::class, 'index'])->name('courses.index');

    Route::prefix('projects')->name('projects.')->controller(ProfessorProjectController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->middleware('throttle:20,1')->name('store');
        Route::get('/{project}', 'show')->whereNumber('project')->name('show');
        Route::get('/{project}/edit', 'edit')->whereNumber('project')->name('edit');
        Route::put('/{project}', 'update')->whereNumber('project')->middleware('throttle:20,1')->name('update');
    });

    Route::get('/submissions', [ProfessorSubmissionController::class, 'index'])->name('submissions.index');
    Route::get('/risk-radar', [ProfessorRiskRadarController::class, 'index'])->name('risk-radar.index');
    Route::get('/extensions', [ProfessorExtensionController::class, 'index'])->name('extensions.index');

    Route::middleware('auth')->group(function () {
        Route::prefix('teams')->name('teams.')->group(function () {
            Route::get('/', [ProfessorTeamController::class, 'index'])->name('index');
            Route::post('/auto-place', [ProfessorTeamController::class, 'autoPlace'])->name('auto-place');
            Route::post('/members', [ProfessorTeamController::class, 'addSelectedMember'])->name('members.add');
            Route::post('/{team}/members/{member}/remove', [ProfessorTeamController::class, 'removeMember'])->name('members.remove');
            Route::post('/{team}/leader', [ProfessorTeamController::class, 'changeLeader'])->name('leader');
        });
        Route::post('/leave-requests/{requestId}', [ProfessorTeamController::class, 'resolveLeaveRequest'])
            ->name('teams.leave-request');
    });
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

// ---- Admin ----
Route::redirect('/admin', '/admin/dashboard');
Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
    Route::view('/users', 'admin.users')->name('users');
    Route::view('/courses', 'admin.courses')->name('courses');
    Route::view('/projects', 'admin.projects')->name('projects');
    Route::view('/announcements', 'admin.announcements')->name('announcements');
    Route::view('/audit', 'admin.audit')->name('audit');

    Route::middleware('auth')->group(function () {
        Route::get('/imports', [AdminImportController::class, 'index'])->name('imports');
        Route::post('/imports/preview', [AdminImportController::class, 'preview'])->name('imports.preview');
        Route::post('/imports/confirm', [AdminImportController::class, 'confirm'])->name('imports.confirm');
    });
});

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

// ---- TA ----
Route::redirect('/ta', '/ta/dashboard');
Route::prefix('ta')->name('ta.')->middleware('role:ta')->group(function () {
    Route::view('/dashboard', 'ta.dashboard')->name('dashboard');
    Route::view('/reviews', 'ta.reviews')->name('reviews');
    Route::view('/issues', 'ta.issues')->name('issues');
    Route::view('/invitations', 'ta.assignments')->name('assignments');
    Route::view('/tasks', 'ta.tasks')->name('tasks');
});

require __DIR__.'/home.php'; // Home page and Help center. Loaded last, so its "/" replaces the welcome route above.
require __DIR__.'/zina.php'; // Zina's signed-in prototype pages: dashboards, milestones, team and profile.

Route::view('/notifications', 'shared.notifications')->name('notifications');
