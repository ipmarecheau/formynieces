<?php

namespace App\Http\Controllers;

use App\Services\UserTesting\ScenarioFixtures;
use App\Services\UserTesting\TestingCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UserTestingController extends Controller
{
    /**
     * Seed a scenario's state, sign the tester in as its dedicated test account, and send the
     * embedded preview to the screen under test. HARD-GATED: refused in production unless
     * explicitly enabled, and it will only ever sign in an account flagged is_test.
     */
    public function enter(string $scenario, ScenarioFixtures $fixtures): RedirectResponse
    {
        abort_unless(config('user_testing.impersonation'), Response::HTTP_FORBIDDEN);

        $definition = TestingCatalog::scenario($scenario);

        abort_if($definition === null, Response::HTTP_NOT_FOUND);

        $user = $fixtures->prepare($definition['id'], $definition['persona']);

        if ($user === null) {
            // A "guest" scenario — make sure no one is signed in.
            Auth::logout();
        } else {
            // Defence in depth: never impersonate a real account, only seeded test ones.
            abort_unless($user->is_test, Response::HTTP_FORBIDDEN);
            Auth::login($user);

            // The Filament admin panel runs AuthenticateSession, which logs a user straight
            // back out (→ /admin/login) unless the session carries a password hash matching
            // the current user. A programmatic Auth::login never sets it, so an embedded
            // admin scenario would bounce to the login screen. Stamp it ourselves so the
            // seeded admin stays signed in inside the preview.
            session()->put(
                'password_hash_'.Auth::getDefaultDriver(),
                $user->getAuthPassword(),
            );
        }

        return redirect()->to($definition['start']);
    }
}
