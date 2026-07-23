<?php

use App\Http\Controllers\ChildSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamAgentController;
use App\Livewire\DiagnosticWalk;
use App\Livewire\GuardianDashboard;
use App\Livewire\GuardianProgress;
use App\Livewire\LessonWalk;
use App\Livewire\PracticeWalk;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\SessionLifecycle;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    Route::get('/guardian/dashboard', GuardianDashboard::class)
        ->name('guardian.dashboard');
    Route::get('/guardian/dashboard', GuardianDashboard::class)
        ->name('guardian.dashboard');
    Route::get('/guardian/progress', GuardianProgress::class)
        ->name('guardian.progress');
    Route::get('/exam-agent', [ExamAgentController::class, 'index'])
        ->name('exam-agent');
    Route::get('/child-setup', [ChildSetupController::class, 'create'])
        ->name('child.setup');
    Route::post('/child-setup', [ChildSetupController::class, 'store'])
        ->name('child.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/diagnostic', function () {
        return view('student.diagnostic-intro');
    })->name('diagnostic.intro');

    Route::get('/practice/{module}', PracticeWalk::class)
        ->name('practice.walk');

    Route::get('/diagnostic/start', function () {
        try {
            app(SessionLifecycle::class)
                ->startOrResume(auth()->id());
        } catch (DomainException $e) {
            return redirect()->route('diagnostic.intro');
        }

        return redirect()->route('diagnostic.walk');
    })->name('diagnostic.start');

    Route::get('/diagnostic/walk', DiagnosticWalk::class)
        ->name('diagnostic.walk');

    // RR-11: a pending student is held here (naming her guardian's login +
    // support) until the guardian decides or the 3-day hold times out.
    Route::get('/awaiting-guardian', function () {
        $student = auth()->user();

        if (! app(DiagnosticReconciliation::class)->isPending($student)) {
            return redirect()->route('student.map');
        }

        return view('student.awaiting-guardian', [
            'guardianEmail' => $student->guardian?->email,
        ]);
    })->name('student.awaiting-guardian');

    // Student's own roadmap — auth-only, never verified (synthetic emails).
    Route::get('/my-map', [DashboardController::class, 'index'])
        ->name('student.map');

    // Streak-celebration splash shown after login to students with active streaks.
    Route::get('/welcome-back', [DashboardController::class, 'studentSplash'])
        ->name('student.splash');

    Route::get('/practice/{module}/lesson', LessonWalk::class)
        ->name('practice.lesson');

    Route::get('/practice/{module}', PracticeWalk::class)
        ->name('practice.walk');
});

require __DIR__.'/auth.php';
