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
        });

        // Child app (MC-01..07) — child-scoped tokens only.
        Route::middleware('ability:child')->group(function () {
            Route::get('/child/voyage', [ChildController::class, 'voyage'])->name('api.mobile.child.voyage');
            Route::get('/child/captains-orders', [ChildController::class, 'captainsOrders'])->name('api.mobile.child.captains-orders');
            Route::get('/child/island/{slug}', [ChildController::class, 'island'])->name('api.mobile.child.island');
            Route::get('/child/today', [ChildController::class, 'today'])->name('api.mobile.child.today');
            Route::post('/child/practice/start', [ChildController::class, 'start'])->name('api.mobile.child.practice.start');
            Route::post('/child/practice/{session}/answer', [ChildController::class, 'answer'])->name('api.mobile.child.practice.answer');
            Route::post('/child/practice/{session}/finish', [ChildController::class, 'finish'])->name('api.mobile.child.practice.finish');
        });
    });
});
