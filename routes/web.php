<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChildSetupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamAgentController;
use App\Http\Controllers\GuardianChildrenController;
use App\Http\Controllers\GuardianPauseController;
use App\Http\Controllers\GuardianReconciliationController;
use App\Http\Controllers\LessonExportController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SchoolJournalClipController;
use App\Http\Controllers\VoyageController;
use App\Livewire\DiagnosticWalk;
use App\Livewire\GuardianAccount;
use App\Livewire\GuardianDashboard;
use App\Livewire\GuardianFamily;
use App\Livewire\GuardianProgress;
use App\Livewire\LessonWalk;
use App\Livewire\ModuleEntry;
use App\Livewire\MorningTide;
use App\Livewire\PracticeWalk;
use App\Livewire\ReteachWalk;
use App\Livewire\SchoolJournal;
use App\Livewire\StudentSchoolJournal;
use App\Livewire\SyllabusMap;
use App\Livewire\TutorialWalk;
use App\Livewire\Upgrade;
use App\Livewire\WelcomeAboard;
use App\Livewire\WritingStop;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\SessionLifecycle;
use App\Services\GuidedTime;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Public marketing pages + the parent onboarding call (AB/FQ/CU/OC).
Route::get('/about', [PublicPageController::class, 'about'])->name('about');
Route::get('/faq', [PublicPageController::class, 'faq'])->name('faq');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('contact');
Route::get('/terms', [PublicPageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('privacy');
Route::get('/sitemap.xml', [PublicPageController::class, 'sitemap'])->name('sitemap');

// Public blog / resources library (BLOG).
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{article}', [BlogController::class, 'show'])->name('blog.show');
Route::post('/contact', [PublicPageController::class, 'sendContact'])->name('contact.send');
Route::get('/book-a-call', [PublicPageController::class, 'book'])->name('book.call');
Route::post('/book-a-call', [PublicPageController::class, 'bookCall'])->name('book.store');

// Smooth chat widget (LC-01..06) — public, throttled.
Route::post('/chat/session', [ChatController::class, 'start'])->middleware('throttle:10,1')->name('chat.start');
Route::post('/chat/message', [ChatController::class, 'message'])->middleware('throttle:30,1')->name('chat.message');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Guardian-only — the Parent Portal.
    Route::middleware('role:guardian')->group(function () {
        Route::get('/guardian/dashboard', GuardianDashboard::class)
            ->name('guardian.dashboard');
        Route::get('/guardian/progress', GuardianProgress::class)
            ->name('guardian.progress');
        Route::get('/account', GuardianAccount::class)
            ->name('guardian.account');
        Route::get('/family', GuardianFamily::class)
            ->name('guardian.family');
        Route::get('/exam-agent', [ExamAgentController::class, 'index'])
            ->name('exam-agent');
        Route::get('/child-setup', [ChildSetupController::class, 'create'])
            ->name('child.setup');
        Route::post('/child-setup', [ChildSetupController::class, 'store'])
            ->name('child.store');

        // Parent Portal — manage children's logins (reveal / reset password on demand).
        Route::get('/guardian/children', [GuardianChildrenController::class, 'index'])
            ->name('guardian.children');
        Route::post('/guardian/children/{child}/reveal', [GuardianChildrenController::class, 'reveal'])
            ->name('guardian.children.reveal');
        Route::post('/guardian/children/{child}/reset', [GuardianChildrenController::class, 'reset'])
            ->name('guardian.children.reset');

        // RR-04: a guardian resolves a pending reconciliation from the Parent Portal.
        Route::post('/guardian/reconciliation/{student}/proceed', [GuardianReconciliationController::class, 'proceed'])
            ->name('guardian.reconciliation.proceed');
        Route::post('/guardian/reconciliation/{student}/keep', [GuardianReconciliationController::class, 'keep'])
            ->name('guardian.reconciliation.keep');

        // Pause / resume a student from the Parent Portal. [WT-04 / WT-05 / ML-03]
        Route::post('/guardian/students/{student}/pause', [GuardianPauseController::class, 'pause'])
            ->name('guardian.pause');
        Route::post('/guardian/students/{student}/resume', [GuardianPauseController::class, 'resume'])
            ->name('guardian.resume');

        // School journal (SJ-01..09) — the guardian's honest-layer view.
        Route::get('/guardian/students/{student}/journal', SchoolJournal::class)
            ->name('guardian.journal');

        // SJ-12 — question clips (photo of the question + its marked solution).
        Route::get('/guardian/journal-question/{question}/clip', [SchoolJournalClipController::class, 'show'])
            ->name('guardian.journal.clip');
    });
});

Route::middleware('auth')->group(function () {
    // Admin/guardian: download a single lesson as a JSON bundle (LB-02). Authorised in the controller.
    Route::get('/lesson-bank/export/{lesson}', LessonExportController::class)->name('lessons.export');

    // Syllabus coverage — read-only map of every objective to its lesson + progress. For students
    // (their own) and guardians (their child). Lessons open from the Voyage, never from here.
    Route::get('/syllabus', SyllabusMap::class)->name('syllabus');

    // Student-only — the learning loop.
    Route::middleware('role:student')->group(function () {
        Route::get('/diagnostic', function () {
            return view('student.diagnostic-intro');
        })->name('diagnostic.intro');

        // School journal — the student's own, score-free filing view (SJ-01/SJ-06).
        Route::get('/journal', StudentSchoolJournal::class)->name('student.journal');

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

        // First-login welcome + joining bonus, before her first Voyage. [TR-01/05]
        Route::get('/welcome', WelcomeAboard::class)
            ->name('student.welcome');

        // The Voyage — gamified standalone alternative to the dashboard. [AM]
        Route::get('/voyage', [VoyageController::class, 'overworld'])
            ->name('student.voyage');

        // Tier 2 — an island's own mini-voyage: a walkable interior path of levels.
        Route::get('/voyage/{island}', [VoyageController::class, 'island'])
            ->name('student.voyage.island');

        // The upgrade wall — where every free-plan lock leads. [free_tier.feature]
        Route::get('/upgrade', Upgrade::class)
            ->name('upgrade');

        // The Morning Tide — the daily reading + vocabulary ritual. [DR/DV]
        Route::get('/morning-tide', MorningTide::class)
            ->name('student.morning-tide');

        // The Writer's Log — the parallel writing track's home, reached from the
        // Writer's Log stop on any island. [WR-01/02/03]
        Route::get('/writing', WritingStop::class)
            ->name('student.writing');

        // Streak-celebration splash shown after login to students with active streaks.
        Route::get('/welcome-back', [DashboardController::class, 'studentSplash'])
            ->name('student.splash');

        // The front door to a module's loop: explainer -> competency check -> outcome. [LL-19/20/21]
        Route::get('/practice/{module}/enter', ModuleEntry::class)
            ->name('practice.enter');

        // AG-05: guided pages heartbeat here while she is actively engaged; each beat credits
        // a fixed active interval to today's 2-hour pool (practice never beats).
        Route::post('/guided-time/beat', function (GuidedTime $guidedTime) {
            $guidedTime->beat(auth()->id());

            return response()->json(['remaining' => $guidedTime->remainingSecondsToday(auth()->id())]);
        })->name('guided-time.beat');

        Route::get('/practice/{module}/lesson', LessonWalk::class)
            ->name('practice.lesson');

        Route::get('/practice/{module}/tutorial', TutorialWalk::class)
            ->name('practice.tutorial');

        // The AI-assisted re-teach, entered when practice goes badly (LL-14…16, LL-22).
        Route::get('/practice/{module}/reteach', ReteachWalk::class)
            ->name('practice.reteach');

        Route::get('/practice/{module}', PracticeWalk::class)
            ->name('practice.walk');
    });
});

// Admin lesson verification (LE-11): walk any lesson in the real student renderer — as a student,
// or in the re-teach flow — with nothing recorded, so lessons can be checked on an ongoing basis.
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/lessons/{module}/preview', LessonWalk::class)
        ->defaults('mode', 'student')
        ->name('admin.lessons.preview');

    Route::get('/admin/lessons/{module}/preview-reteach', LessonWalk::class)
        ->defaults('mode', 'reteach')
        ->name('admin.lessons.preview-reteach');
});

require __DIR__.'/auth.php';
