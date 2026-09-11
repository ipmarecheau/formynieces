<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\DeviceHandoffController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\VerifyAccount;
use Illuminate\Support\Facades\Route;

// The kid-branded child sign-in (/go) is reachable whether or not someone is signed
// in — so an authenticated parent on a shared device is NOT bounced to their dashboard;
// the page itself explains the parent-vs-child options. It posts to the shared /login.
Route::view('go', 'auth.student-login')->name('student.login');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::post('register/check-email', [RegisteredUserController::class, 'checkEmail'])
        ->name('register.check-email');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    // Social login (one-tap guardian sign-up / sign-in). Provider-agnostic; each
    // provider is active only when its credentials are configured.
    Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
        ->name('social.redirect');

    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])
        ->name('social.callback');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // A guardian hands their shared device over to a child: a countdown interstitial,
    // then sign the guardian out and land on /go with the child's login ID prefilled.
    Route::get('go/handoff/{child}', [DeviceHandoffController::class, 'show'])
        ->name('student.handoff');

    // The commit is a SIGNED GET (no CSRF/session-token dependency, so it can't 419);
    // the signature is minted while the guardian is authenticated and expires in 15 min.
    Route::get('go/handoff/{child}/commit', [DeviceHandoffController::class, 'commit'])
        ->name('student.handoff.commit')
        ->middleware('signed');

    Route::get('verify-email', VerifyAccount::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
