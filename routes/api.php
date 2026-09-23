<?php

use App\Http\Controllers\Api\Mobile\AuthController;
use App\Http\Controllers\Api\Mobile\ChildController;
use App\Http\Controllers\Api\Mobile\ParentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Mobile API (/api/mobile) — screen-shaped JSON for the Flutter parent + child apps.
|--------------------------------------------------------------------------
| Contract: formynieces-spec/MOBILE_API_CONTRACT.md
| Features: mobile_parent_app.feature (MP-01..08), mobile_child_app.feature (MC-01..07)
|
| Tokens are scoped by experience ability ('parent' | 'child') at login so the two app
| surfaces can never reach each other's endpoints (MC-06, MP-07).
*/

Route::prefix('mobile')->group(function () {
    // Unauthenticated health/version probe.
    Route::get('/ping', fn () => response()->json([
        'ok' => true,
        'service' => 'smoothseas-mobile-api',
        'time' => now()->toIso8601String(),
    ]))->name('api.mobile.ping');

    // Auth
    Route::post('/login', [AuthController::class, 'login'])->name('api.mobile.login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('api.mobile.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.mobile.logout');

        // Parent app (MP-01..07) — parent-scoped tokens only.
        Route::middleware('ability:parent')->group(function () {
            Route::get('/children', [ParentController::class, 'children'])->name('api.mobile.children');
            Route::get('/children/{child}/overview', [ParentController::class, 'overview'])->name('api.mobile.child.overview');
            Route::get('/children/{child}/weak-topics', [ParentController::class, 'weakTopics'])->name('api.mobile.child.weak-topics');
            Route::get('/children/{child}/writing', [ParentController::class, 'writing'])->name('api.mobile.child.writing');
            Route::get('/children/{child}/readiness', [ParentController::class, 'readiness'])->name('api.mobile.child.readiness');
            Route::get('/children/{child}/login', [ParentController::class, 'childLogin'])->name('api.mobile.child.login');
            Route::post('/children/{child}/login/reset', [ParentController::class, 'resetChildLogin'])->name('api.mobile.child.login.reset');
            Route::get('/children/{child}/dashboard', [ParentController::class, 'dashboard'])->name('api.mobile.child.dashboard');

            // School journal — upload graded papers, term timeline + per-question breakdown (SJ).
            Route::get('/children/{child}/journal', [ParentController::class, 'journal'])->name('api.mobile.child.journal');
            Route::post('/children/{child}/journal', [ParentController::class, 'uploadJournalPaper'])->name('api.mobile.child.journal.upload');
            Route::post('/children/{child}/journal/{entry}/confirm', [ParentController::class, 'confirmJournalEntry'])->name('api.mobile.child.journal.confirm');
        });

        // Child app (MC-01..07) — child-scoped tokens only.
        Route::middleware('ability:child')->group(function () {
            Route::get('/child/welcome-back', [ChildController::class, 'welcomeBack'])->name('api.mobile.child.welcome-back');
            Route::get('/child/voyage', [ChildController::class, 'voyage'])->name('api.mobile.child.voyage');
            Route::get('/child/captains-orders', [ChildController::class, 'captainsOrders'])->name('api.mobile.child.captains-orders');
            Route::get('/child/island/{slug}', [ChildController::class, 'island'])->name('api.mobile.child.island');
            Route::get('/child/module/{module}/lesson', [ChildController::class, 'lesson'])->name('api.mobile.child.lesson');
            Route::get('/child/today', [ChildController::class, 'today'])->name('api.mobile.child.today');
            Route::post('/child/module/{module}/check/start', [ChildController::class, 'checkStart'])->name('api.mobile.child.check.start');
            Route::post('/child/check/{session}/answer', [ChildController::class, 'checkAnswer'])->name('api.mobile.child.check.answer');
            Route::post('/child/practice/start', [ChildController::class, 'start'])->name('api.mobile.child.practice.start');
            Route::post('/child/practice/{session}/answer', [ChildController::class, 'answer'])->name('api.mobile.child.practice.answer');
            Route::post('/child/practice/{session}/finish', [ChildController::class, 'finish'])->name('api.mobile.child.practice.finish');

            // Morning Tide — daily reading (DR) + vocabulary (DV) ritual.
            Route::get('/child/morning-tide', [ChildController::class, 'morningTide'])->name('api.mobile.child.morning-tide');
            Route::post('/child/morning-tide/comprehension', [ChildController::class, 'morningTideComprehension'])->name('api.mobile.child.morning-tide.comprehension');
            Route::post('/child/morning-tide/vocabulary', [ChildController::class, 'morningTideVocabulary'])->name('api.mobile.child.morning-tide.vocabulary');

            // Writer's Log — weekly prompt + rubric feedback (WR).
            Route::get('/child/writing', [ChildController::class, 'writing'])->name('api.mobile.child.writer-log');
            Route::post('/child/writing', [ChildController::class, 'submitWriting'])->name('api.mobile.child.writer-log.submit');
        });
    });
});
