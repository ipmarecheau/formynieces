<?php

use App\Http\Controllers\Api\Mobile\AuthController;
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
    });
});
