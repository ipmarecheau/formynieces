<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\StudentStreak;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\ReconciliationResolver;
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

        $target = $this->redirectTo($request->user());

        // Onboarding gates (the diagnostic and the reconciliation waiting page)
        // must never be bypassed by a stale "intended" URL left in the session
        // from earlier navigation — force those destinations. [RR-11]
        if (in_array($target, [route('diagnostic.intro'), route('student.awaiting-guardian')], true)) {
            return redirect()->to($target);
        }

        return redirect()->intended($target);
    }

    /**
     * Decide where a user goes after logging in.
     */
    private function redirectTo($user): string
    {
        // A student who hasn't finished onboarding goes to the diagnostic —
        // unless her guardian's reconciliation decision is pending. Then she is
        // held on the waiting page (RR-11), except once the 3-day hold has
        // elapsed, when we proceed her with the diagnostic result right now so
        // she isn't stuck waiting (RR-10 resolved lazily on login).
        if ($user->isStudent() && ! $user->hasCompletedOnboarding()) {
            $reconciliation = app(DiagnosticReconciliation::class);

            if ($reconciliation->isPending($user)) {
                if ($reconciliation->hasTimedOut($user)) {
                    app(ReconciliationResolver::class)->proceedWithDiagnostic($user);
                    $user->refresh();
                } else {
                    return route('student.awaiting-guardian');
                }
            } else {
                return route('diagnostic.intro');
            }
        }

        // A guardian with no linked students goes to child setup.
        if ($user->isGuardian() && $user->students()->doesntExist()) {
            return route('child.setup');
        }

        // An onboarded student lands on a streak-celebration splash when she has
        // at least one active streak, otherwise she goes straight to her map.
        if ($user->isStudent()) {
            $hasActiveStreak = StudentStreak::where('student_id', $user->id)
                ->where('count', '>', 0)
                ->exists();

            return $hasActiveStreak
                ? route('student.splash')
                : route('student.map');
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
