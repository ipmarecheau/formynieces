<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamAgentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/exam-agent', [ExamAgentController::class, 'index'])
        ->name('exam-agent');
    Route::get('/child-setup', function () {
        return view('guardian.child-setup');
    })->name('child.setup');
});

require __DIR__.'/auth.php';