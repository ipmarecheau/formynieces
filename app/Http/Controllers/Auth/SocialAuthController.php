<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\SocialProviders;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

/**
 * SocialAuthController — one-tap guardian sign-up / sign-in via a social provider.
 *
 * The low-friction front door: a provider vouches for the email, so there is no
 * verify-email step. New accounts require the same 18+/terms consent as email sign-up
 * (captured on the auth page and carried in the session across the OAuth round-trip);
 * an existing email is linked and logged straight in.
 */
class SocialAuthController extends Controller
{
    private const CONSENT_KEY = 'social_consent';

    /** Send the guardian to the provider. Consent (18+/terms) is stashed for the callback. */
    public function redirect(Request $request, string $provider): RedirectResponse
    {
        abort_unless(SocialProviders::isEnabled($provider), 404);

        // The auth page gates the button on a single "I'm 18+ and accept the Terms"
        // checkbox; carry that consent across the provider round-trip for NEW accounts.
        if ($request->boolean('agree')) {
            $request->session()->put(self::CONSENT_KEY, true);
        }

        return Socialite::driver($provider)->redirect();
    }

    /** Handle the provider callback: link or create the guardian, then sign in. */
    public function callback(Request $request, string $provider): RedirectResponse
    {
        abort_unless(SocialProviders::isEnabled($provider), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable) {
            return redirect()->route('login')->withErrors([
                'social' => "We couldn't finish signing you in with that account. Please try again.",
            ]);
        }

        $email = $socialUser->getEmail();
        if ($email === null) {
            return redirect()->route('login')->withErrors([
                'social' => 'That account did not share an email address, so we could not sign you in.',
            ]);
        }

        $existing = User::whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();

        if ($existing !== null) {
            // Link the provider to the existing account (first social sign-in) and log in.
            if ($existing->social_provider === null) {
                $existing->forceFill([
                    'social_provider' => $provider,
                    'social_id' => $socialUser->getId(),
                    'email_verified_at' => $existing->email_verified_at ?? now(),
                ])->save();
            }

            Auth::login($existing, remember: true);

            return redirect()->intended(route('dashboard'));
        }

        // A brand-new guardian must have accepted the terms + attested 18+ first.
        if (! $request->session()->pull(self::CONSENT_KEY, false)) {
            return redirect()->route('register')->withErrors([
                'social' => 'Please tick the box to confirm you are 18+ and accept the Terms, then continue.',
            ]);
        }

        $user = User::create([
            'name' => $socialUser->getName() ?: Str::before($email, '@'),
            'email' => mb_strtolower($email),
            'password' => Hash::make(Str::random(40)),
            'social_provider' => $provider,
            'social_id' => $socialUser->getId(),
            'role' => 'guardian',
            'age_attested_at' => now(),
            'terms_accepted_at' => now(),
            'terms_version' => config('legal.terms_version'),
        ]);

        // The provider vouches for the address — mark verified so there is no verify-email
        // step (email_verified_at is intentionally not mass-assignable, so set it directly).
        $user->forceFill(['email_verified_at' => now()])->save();

        Auth::login($user, remember: true);

        // Straight to the dashboard's "add your child" empty state — step 2 of onboarding.
        return redirect()->route('dashboard');
    }
}
