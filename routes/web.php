<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TimeLogController;
use App\Http\Controllers\UserLeaveController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->middleware('password.confirm')->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    // Time Log Form Page
    Route::get('/time-logs/create', [TimeLogController::class, 'create'])->name('time-logs.create');

    // Time Log List
    Route::get('/time-logs', [TimeLogController::class, 'index'])->name('time-logs.index');
    // Delete Time Log
    Route::delete('/time-logs/{id}', [TimeLogController::class, 'destroy'])->name('time-logs.destroy');

    // Store Time Log
    Route::post('/time-logs', [TimeLogController::class, 'store'])->name('time-logs.store');

    // Leave Form
    Route::get('/leaves/create', [UserLeaveController::class, 'create'])->name('leaves.create');

    // Leave List
    Route::get('/leaves', [UserLeaveController::class, 'index'])->name('leaves.index');
    // Delete Leave
    Route::delete('/leaves/{id}', [UserLeaveController::class, 'destroy'])->name('leaves.destroy');
    // Update Leave Status (approve/unapprove)
    Route::patch('/leaves/{id}/status', [UserLeaveController::class, 'updateStatus'])->name('leaves.updateStatus');

    // Store Leave
    Route::post('/leaves', [UserLeaveController::class, 'store'])->name('leaves.store');

    // Admin overview (leaves + timelogs)
    Route::get('/admin/overview', [TimeLogController::class, 'adminOverview'])->name('admin.overview');

});