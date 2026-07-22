<?php

use Illuminate\Support\Facades\Artisan;

/**
 * Tooling test for `specs:trace --mvp`. Not a BDD scenario — left untagged.
 * Reads the real feature files + ledger, so it asserts against known-stable tags:
 *   - GD-06 is @v1.1 (scenario-level deferral)
 *   - ER-01 inherits @roadmap from its Feature line (feature-level deferral)
 *   - AC-03 is @mvp
 */
it('shows deferred scenarios by default but hides them under --mvp', function () {
    Artisan::call('specs:trace');
    $all = Artisan::output();

    // Sanity: without the filter, a deferred scenario is present.
    expect($all)->toContain('GD-06');

    Artisan::call('specs:trace', ['--mvp' => true]);
    $mvp = Artisan::output();

    // Scenario-level deferral (@v1.1) is hidden.
    expect($mvp)->not->toContain('GD-06');
    // Feature-level deferral (@roadmap inherited from the Feature line) is hidden.
    expect($mvp)->not->toContain('ER-01');
    // A genuine @mvp scenario is still shown.
    expect($mvp)->toContain('AC-03');
});

it('composes --mvp with --only-problems', function () {
    Artisan::call('specs:trace', ['--only-problems' => true]);
    $problems = Artisan::output();

    Artisan::call('specs:trace', ['--mvp' => true, '--only-problems' => true]);
    $mvpProblems = Artisan::output();

    // GD-06 is a deferred (@v1.1) problem: it appears under --only-problems but is
    // filtered out by --mvp. Asserting the in/out semantics keeps this test stable
    // as MVP scenarios get built and verified (unlike naming a specific MVP row).
    expect($problems)->toContain('GD-06');
    expect($mvpProblems)->not->toContain('GD-06');
});
