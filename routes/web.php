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
    // Intro screen (Set sail lives here)
    Route::get('/diagnostic', function () {
        return view('student.diagnostic-intro');
    })->name('diagnostic.intro');

    // Set sail: start or resume, then send to the walk
    Route::get('/diagnostic/start', function () {
        try {
            app(\App\Services\Diagnostic\SessionLifecycle::class)
                ->startOrResume(auth()->id());
        } catch (\DomainException $e) {
            // Onboarding not complete — send back to intro rather than 500.
            return redirect()->route('diagnostic.intro');
        }

        return redirect()->route('diagnostic.walk');
    })->name('diagnostic.start');

    // The walk itself — full-page Livewire component
    Route::get('/diagnostic/walk', \App\Livewire\DiagnosticWalk::class)
        ->name('diagnostic.walk');
});

require __DIR__.'/auth.php';