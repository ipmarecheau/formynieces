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
    Artisan::call('specs:trace', ['--mvp' => true, '--only-problems' => true]);
    $out = Artisan::output();

    // AC-03 is an MVP problem (untested) → shown; GD-06 is deferred → hidden.
    expect($out)->toContain('AC-03');
    expect($out)->not->toContain('GD-06');
});
