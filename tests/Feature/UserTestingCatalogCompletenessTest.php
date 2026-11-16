<?php

use App\Models\User;
use App\Services\UserTesting\ScenarioFingerprint;
use App\Services\UserTesting\ScenarioFixtures;
use App\Services\UserTesting\TestingCatalog;
use Illuminate\Support\Facades\Auth;

/*
 * The "nothing is missing" guarantee for /user-testing. Every scenario a tester is offered
 * must be fully executable: a web scenario needs a persona + start screen + a seeded account
 * that satisfies its gates; a device scenario needs an install URL AND a real login the tester
 * can type into the installed app. This vets the whole catalog so a half-provisioned scenario
 * cannot ship — the web-gate Playwright harness proves the screens *reach* the feature; this
 * proves every scenario has everything required to be attempted at all.
 */

beforeEach(function () {
    config()->set('user_testing.impersonation', true);
});

/** Every feature's code matches its key, and its scenarios are structurally complete. */
it('gives every scenario the plain-English essentials a tester needs', function () {
    foreach (TestingCatalog::features() as $code => $feature) {
        expect($feature['code'])->toBe($code, "feature {$code} code mismatch");
        expect($feature['scenarios'])->not->toBeEmpty("feature {$code} has no scenarios");

        foreach ($feature['scenarios'] as $scenario) {
            $where = $scenario['id'] ?? $code;
            expect($scenario['id'] ?? '')->not->toBe('', "{$where}: missing id");
            expect($scenario['title'] ?? '')->not->toBe('', "{$where}: missing title");
            expect($scenario['goal'] ?? '')->not->toBe('', "{$where}: missing goal");
            expect($scenario['expect'] ?? '')->not->toBe('', "{$where}: missing expect");
            expect($scenario['steps'] ?? [])->not->toBeEmpty("{$where}: no steps");
        }
    }
});

/** Each web scenario can actually be seeded into a signed-in, gate-passing state. */
it('can seed a gate-passing account for every web scenario', function () {
    $fixtures = app(ScenarioFixtures::class);

    foreach (TestingCatalog::features() as $code => $feature) {
        if (TestingCatalog::isDevice($feature)) {
            continue;
        }

        foreach ($feature['scenarios'] as $scenario) {
            $id = $scenario['id'];

            expect($scenario['persona'] ?? '')->toBeIn(['guest', 'parent', 'child', 'admin'], "{$id}: bad persona");
            expect($scenario['start'] ?? '')->toStartWith('/', "{$id}: start is not a path");

            $user = $fixtures->prepare($id, $scenario['persona']);

            if ($scenario['persona'] === 'guest') {
                expect($user)->toBeNull("{$id}: a guest scenario must not seed a login");

                continue;
            }

            expect($user)->toBeInstanceOf(User::class, "{$id}: no account seeded");
            expect($user->is_test)->toBeTrue("{$id}: seeded account is not is_test");
            // Verified, or the guardian/student/admin screens bounce to the email-verify gate.
            expect($user->hasVerifiedEmail())->toBeTrue("{$id}: seeded account is not email-verified");
        }
    }
});

/** Every scenario maps to real implementation files and a hashable Gherkin block. */
it('maps every scenario to existing source files and a fingerprintable spec', function () {
    $fingerprint = app(ScenarioFingerprint::class);

    foreach (TestingCatalog::features() as $feature) {
        foreach ($feature['scenarios'] as $scenario) {
            $id = $scenario['id'];

            $sources = TestingCatalog::sources($id);
            expect($sources)->not->toBeEmpty("{$id}: no implementation sources mapped");

            foreach ($sources as $path) {
                expect(is_file(base_path($path)))->toBeTrue("{$id}: source file missing — {$path}");
            }

            // The change-tracker needs a hashable spec block and impl hash for every scenario.
            expect($fingerprint->specHash($id))->not->toBeNull("{$id}: no @scenario block to fingerprint");
            expect($fingerprint->implHash($sources))->not->toBeNull("{$id}: implementation hashed to nothing");
        }
    }
});

/** Each delayed scenario declares a valid clock time so its reminders can be scheduled. */
it('gives every delayed scenario a well-formed notification time', function () {
    foreach (TestingCatalog::features() as $code => $feature) {
        foreach ($feature['scenarios'] as $scenario) {
            if (! TestingCatalog::isDelayed($scenario)) {
                continue;
            }

            $id = $scenario['id'];
            expect($scenario['delayed']['at'])->toMatch('/^([01]\d|2[0-3]):[0-5]\d$/', "{$id}: notification time is not HH:MM");
            expect($scenario['delayed']['label'] ?? '')->not->toBe('', "{$id}: missing a human label for the notification");
        }
    }
});

/** Each device scenario has an install URL and a real login the tester can actually use. */
it('provides a working install URL and test login for every device scenario', function () {
    $fixtures = app(ScenarioFixtures::class);

    foreach (TestingCatalog::features() as $code => $feature) {
        if (! TestingCatalog::isDevice($feature)) {
            continue;
        }

        $app = $feature['device']['app'] ?? '';
        expect($app)->not->toBe('', "{$code}: device.app missing");
        expect(config("user_testing.apps.{$app}"))->not->toBeEmpty("{$code}: no install URL configured for '{$app}'");
        expect($feature['device']['intro'] ?? '')->not->toBe('', "{$code}: missing install intro");
        expect($feature['device']['permissions'] ?? [])->not->toBeEmpty("{$code}: no permissions walkthrough");

        // The credentials shown to the tester must belong to a real is_test account that
        // actually authenticates — otherwise the device scenario cannot be started.
        $login = $fixtures->deviceLogin($app);
        expect($login['email'])->not->toBe('', "{$code}: no device login email");

        $user = User::where('email', $login['email'])->first();
        expect($user)->not->toBeNull("{$code}: device login account was not seeded");
        expect($user->is_test)->toBeTrue("{$code}: device login account is not is_test");
        expect(Auth::attempt(['email' => $login['email'], 'password' => $login['password']]))
            ->toBeTrue("{$code}: device login credentials do not authenticate");
        Auth::logout();
    }
});
