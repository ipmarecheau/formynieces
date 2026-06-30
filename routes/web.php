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

Route::middleware('auth')->group(function () {
    Route::get('/diagnostic', function () {
        return view('student.diagnostic-intro');
    })->name('diagnostic.intro');

    Route::get('/practice/{module}', \App\Livewire\PracticeWalk::class)
        ->name('practice.walk');

    Route::get('/diagnostic/start', function () {
        try {
            app(\App\Services\Diagnostic\SessionLifecycle::class)
                ->startOrResume(auth()->id());
        } catch (\DomainException $e) {
            return redirect()->route('diagnostic.intro');
        }
        return redirect()->route('diagnostic.walk');
    })->name('diagnostic.start');

    Route::get('/diagnostic/walk', \App\Livewire\DiagnosticWalk::class)
        ->name('diagnostic.walk');

    // Student's own roadmap — auth-only, never verified (synthetic emails).
    Route::get('/my-map', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('student.map');

        Route::get('/practice/{module}/lesson', \App\Livewire\LessonWalk::class)
        ->name('practice.lesson');

    Route::get('/practice/{module}', \App\Livewire\PracticeWalk::class)
        ->name('practice.walk');
});

require __DIR__.'/auth.php';