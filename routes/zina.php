<?php

// Zina: signed-in prototype pages (Student Dashboard, Milestones, Student Team, Professor Dashboard,
// Profile). Loaded from web.php after home.php. There is no auth yet: the "prototype controls" below
// stand in for the signed-in user, and every change is kept in the session only.

use App\Http\Controllers\PrototypeContextController;
use Illuminate\Support\Facades\Route;

Route::post('/prototype/context', [PrototypeContextController::class, 'update'])->name('prototype.context');

// ---- Student dashboard ----
// Added on zina/student-dashboard.
Route::get('/student/dashboard', [App\Http\Controllers\StudentDashboardController::class, 'show'])->name('student.dashboard');

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
Route::prefix('/student/projects/{project}/team')->name('student.team.')
    ->controller(App\Http\Controllers\StudentTeamController::class)->group(function () {
        Route::get('/', 'show')->name('show');
        // Academic team formation (TEAM-1–3) and leave requests (TEAM-8).
        Route::post('/requests/{item}/accept', 'act')->name('accept');
        Route::post('/requests/{item}/decline', 'act')->name('decline');
        Route::post('/outgoing', 'act')->middleware('throttle:20,1')->name('send');
        Route::post('/outgoing/{item}/withdraw', 'act')->name('withdraw');
        Route::post('/leave', 'act')->name('leave');
        Route::post('/leave/withdraw', 'act')->name('withdraw-leave');
        // Independent projects (TEAM-12–13).
        Route::post('/invitations', 'act')->middleware('throttle:20,1')->name('invite');
        Route::post('/invitations/{item}/cancel', 'act')->name('cancel-invitation');
        Route::post('/join-requests/{item}/approve', 'act')->name('approve-join');
        Route::post('/join-requests/{item}/decline', 'act')->name('decline-join');
        Route::post('/members/{item}/remove', 'act')->name('remove-member');
    });

// ---- Professor dashboard ----
// Added on zina/professor-dashboard.

// ---- Profile ----
// Added on zina/profile.
