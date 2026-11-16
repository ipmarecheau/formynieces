<?php

use App\Livewire\UserTesting;
use App\Mail\ScenarioChangedNotice;
use App\Models\VerificationRun;
use App\Services\UserTesting\ScenarioFingerprint;
use App\Services\UserTesting\TestingCatalog;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/*
 * Change tracking: a verdict is stamped with the fingerprint of the scenario's Gherkin and its
 * implementation. When either changes since, the tester sees an indigo "re-test" status and is
 * emailed once (daily sweep) to look again.
 */

it('stamps a verdict with the spec and implementation fingerprints it was given against', function () {
    Livewire::test(UserTesting::class, ['feature' => 'sh'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->call('record', VerificationRun::RESULT_WORKED); // SH-01

    $run = VerificationRun::where('scenario_id', 'SH-01')->sole();
    $fingerprint = app(ScenarioFingerprint::class);

    expect($run->spec_hash)->toBe($fingerprint->specHash('SH-01'));
    expect($run->impl_hash)->toBe($fingerprint->implHash(TestingCatalog::sources('SH-01')));
    expect($run->change_notified_hash)->toBeNull();
})->group('scenario:UT-08');

it('shows an indigo re-test status when the scenario changed since the verdict', function () {
    // A verdict recorded against stale fingerprints — the live scenario has since "changed".
    VerificationRun::factory()->create([
        'tester_email' => 'maria@example.com',
        'scenario_id' => 'SH-01',
        'feature' => 'SH',
        'result' => VerificationRun::RESULT_WORKED,
        'spec_hash' => 'stalespec000',
        'impl_hash' => 'staleimpl000',
        'verified_commit' => 'deadbeef',
    ]);

    // On the scenario itself: the "updated since your answer" banner.
    Livewire::test(UserTesting::class, ['feature' => 'sh'])
        ->set('testerEmail', 'maria@example.com')
        ->assertSee('Updated since your answer');

    // On the chooser: the feature pill reads "re-test".
    Livewire::test(UserTesting::class)
        ->set('testerEmail', 'maria@example.com')
        ->assertSee('🔄 re-test', false);
})->group('scenario:UT-10');

it('does not flag a scenario whose fingerprints still match', function () {
    $fingerprint = app(ScenarioFingerprint::class);

    VerificationRun::factory()->create([
        'tester_email' => 'maria@example.com',
        'scenario_id' => 'SH-01',
        'feature' => 'SH',
        'result' => VerificationRun::RESULT_WORKED,
        'spec_hash' => $fingerprint->specHash('SH-01'),
        'impl_hash' => $fingerprint->implHash(TestingCatalog::sources('SH-01')),
    ]);

    Livewire::test(UserTesting::class)
        ->set('testerEmail', 'maria@example.com')
        ->assertDontSee('🔄 re-test', false);
});

it('emails the tester once about a changed scenario, then not again for the same change', function () {
    Mail::fake();

    VerificationRun::factory()->create([
        'tester_email' => 'maria@example.com',
        'tester_name' => 'Maria',
        'scenario_id' => 'SH-01',
        'feature' => 'SH',
        'result' => VerificationRun::RESULT_WORKED,
        'spec_hash' => 'stalespec000',
        'impl_hash' => 'staleimpl000',
        'verified_commit' => 'deadbeef',
    ]);

    $this->artisan('verification:flag-changes')->assertSuccessful();
    $this->artisan('verification:flag-changes')->assertSuccessful(); // same change — no re-send

    Mail::assertSent(ScenarioChangedNotice::class, 1);
    Mail::assertSent(ScenarioChangedNotice::class, fn ($m) => $m->hasTo('maria@example.com'));

    // The notified hash is now the current combined fingerprint.
    $fingerprint = app(ScenarioFingerprint::class);
    $run = VerificationRun::where('scenario_id', 'SH-01')->sole();
    expect($run->change_notified_hash)->toBe($fingerprint->specHash('SH-01').'|'.$fingerprint->implHash(TestingCatalog::sources('SH-01')));
})->group('scenario:UT-09');

it('does not email about scenarios that are unchanged or still waiting', function () {
    Mail::fake();
    $fingerprint = app(ScenarioFingerprint::class);

    // Unchanged answered run.
    VerificationRun::factory()->create([
        'tester_email' => 'a@example.com',
        'scenario_id' => 'SH-01',
        'feature' => 'SH',
        'result' => VerificationRun::RESULT_WORKED,
        'spec_hash' => $fingerprint->specHash('SH-01'),
        'impl_hash' => $fingerprint->implHash(TestingCatalog::sources('SH-01')),
    ]);

    // A waiting (deferred) run is not an answered verdict — never flagged.
    VerificationRun::factory()->waiting()->create([
        'tester_email' => 'b@example.com',
        'spec_hash' => 'stalespec000',
        'impl_hash' => 'staleimpl000',
    ]);

    $this->artisan('verification:flag-changes')->assertSuccessful();

    Mail::assertNothingSent();
});
