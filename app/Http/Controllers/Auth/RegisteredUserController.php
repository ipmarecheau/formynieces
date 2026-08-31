<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\Turnstile;
use App\Services\Verification\PhoneVerifier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Report whether an email already has an account, so the registration form
     * can redirect the guardian to sign in before they fill anything else out.
     *
     * @return JsonResponse{exists: bool}
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        return response()->json([
            'exists' => User::whereRaw('LOWER(email) = ?', [mb_strtolower($validated['email'])])->exists(),
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, PhoneVerifier $phoneVerifier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'age_attestation' => ['accepted'],
            'terms' => ['accepted'],
            'cf-turnstile-response' => [new Turnstile($request->ip())],
        ], [
            'email.unique' => 'An account with this email already exists. Please sign in to your dashboard instead.',
            'phone.regex' => 'Enter your phone number in full international format, e.g. +18685551234.',
            'terms.accepted' => 'You must read and accept the Terms & Conditions to create an account.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'guardian',
            'age_attested_at' => now(),
            'terms_accepted_at' => now(),
            'terms_version' => config('legal.terms_version'),
        ]);

        // Fires the email verification (link + code) via the Registered event.
        event(new Registered($user));

        // Start phone verification WhatsApp-first — only when the feature is on.
        // At the free launch the number is captured but not verified. A provider
        // hiccup must not block sign-up; she can resend / use SMS on the screen.
        if (config('services.phone_verification.enabled')) {
            try {
                $phoneVerifier->start($user->phone, 'whatsapp');
            } catch (\Throwable) {
                // swallowed — the verification screen offers resend / SMS fallback
            }
        }

        Auth::login($user);

        return redirect(route('verification.notice'));
    }
}
