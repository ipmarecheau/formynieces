<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Phone still outstanding → back to the verification screen to finish it.
        // Both done (or no phone on file) → straight into onboarding.
        if ($user->needsPhoneVerification()) {
            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('child.setup', absolute: false));
    }
}
