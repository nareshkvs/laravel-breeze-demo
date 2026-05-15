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
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {

    // Time Log Form Page
    Route::get('/time-logs', [TimeLogController::class, 'index'])->name('time-logs.index');

    // Time Log List
    Route::get('/time-logs/list', [TimeLogController::class, 'list'])->name('time-logs.list');

    // Store Time Log
    Route::post('/time-logs', [TimeLogController::class, 'store'])->name('time-logs.store');

    // Leave Form
    Route::get('/leaves', [UserLeaveController::class, 'index'])->name('leaves.index');

    // Leave List
    Route::get('/leaves/list', [UserLeaveController::class, 'list'])->name('leaves.list');

    // Store Leave
    Route::post('/leaves', [UserLeaveController::class, 'store'])->name('leaves.store');

});