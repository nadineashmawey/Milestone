<?php

// Zina: signed-in prototype pages (Student Dashboard, Milestones, Student Team, Professor Dashboard,
// Profile). Loaded from web.php after home.php. There is no auth yet: the "prototype controls" below
// stand in for the signed-in user, and every change is kept in the session only.

use App\Http\Controllers\PrototypeContextController;
use Illuminate\Support\Facades\Route;

Route::post('/prototype/context', [PrototypeContextController::class, 'update'])->name('prototype.context');

// ---- Student dashboard ----
// Added on zina/student-dashboard.

// ---- Milestones ----
// Added on zina/milestones.
Route::prefix('/student/projects/{project}/milestones')->name('student.milestones.')
    ->controller(App\Http\Controllers\StudentMilestoneController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{milestone}/draft', 'draft')->middleware('throttle:20,1')->name('draft');
        Route::post('/{milestone}/draft/discard', 'discard')->name('discard');
        Route::post('/{milestone}/submit', 'submit')->middleware('throttle:20,1')->name('submit');
    });

// ---- Student team ----
// Added on zina/student-team.

// ---- Professor dashboard ----
// Added on zina/professor-dashboard.

// ---- Profile ----
// Added on zina/profile.
