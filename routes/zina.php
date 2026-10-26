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

// ---- Student team ----
// Added on zina/student-team.

// ---- Professor dashboard ----
// Added on zina/professor-dashboard.

// ---- Profile ----
// Added on zina/profile.
Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->middleware('throttle:30,1')->name('profile.update');
