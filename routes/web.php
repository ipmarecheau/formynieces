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
    Route::get('/child-setup', [\App\Http\Controllers\ChildSetupController::class, 'create'])
        ->name('child.setup');
    Route::post('/child-setup', [\App\Http\Controllers\ChildSetupController::class, 'store'])
        ->name('child.store');
});

require __DIR__.'/auth.php';