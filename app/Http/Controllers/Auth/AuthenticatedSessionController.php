<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\Motivation\StreakService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private StreakService $streaks) {}

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Students build a login streak: each day they sign in extends it.
        if ($request->user()->isStudent()) {
            $this->streaks->recordActivity($request->user()->id, 'login');
        }

        return redirect()->intended($this->redirectTo($request->user()));
    }

    /**
     * Decide where a user goes after logging in.
     */
    private function redirectTo($user): string
    {
        // A student who hasn't finished onboarding goes to the diagnostic.
        if ($user->isStudent() && ! $user->hasCompletedOnboarding()) {
            return route('diagnostic.intro');
        }

        // A guardian with no linked students goes to child setup.
        if ($user->isGuardian() && $user->students()->doesntExist()) {
            return route('child.setup');
        }

        return route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
