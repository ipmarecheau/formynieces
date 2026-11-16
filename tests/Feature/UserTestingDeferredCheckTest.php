<?php

use App\Livewire\UserTesting;
use App\Mail\DeferredCheckReminder;
use App\Models\VerificationRun;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/*
 * Deferred checks for delayed-feedback scenarios (a notification that fires later): the tester
 * chooses "remind me", a waiting check is recorded with the expected time computed in their
 * timezone, and a scheduled sweep emails them before and after to record the outcome.
 */

it('defers a delayed scenario into a waiting check with the expected time computed', function () {
    Livewire::test(UserTesting::class, ['feature' => 'pn'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->set('testerTimezone', 'Europe/London')
        ->call('defer')
        ->assertSet('index', 1); // advanced past the deferred scenario

    $run = VerificationRun::sole();
    expect($run->scenario_id)->toBe('PN-01');
    expect($run->result)->toBe(VerificationRun::RESULT_WAITING);
    expect($run->tester_timezone)->toBe('Europe/London');
    expect($run->expected_at)->not->toBeNull();
    // 18:30 London is the declared notification time.
    expect($run->expected_at->copy()->setTimezone('Europe/London')->format('H:i'))->toBe('18:30');
})->group('scenario:UT-06');

it('does not defer a scenario whose feedback is immediate', function () {
    Livewire::test(UserTesting::class, ['feature' => 'hc']) // HC-01 is not delayed
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->call('defer');

    expect(VerificationRun::count())->toBe(0);
});

it('shows checks the tester is waiting on, on the chooser', function () {
    VerificationRun::factory()->waiting()->create([
        'tester_email' => 'maria@example.com',
        'tester_name' => 'Maria',
    ]);

    Livewire::test(UserTesting::class)
        ->set('testerEmail', 'maria@example.com')
        ->assertSee("Checks you're waiting on", false)
        ->assertSee('Your device registers for alerts'); // the PN-01 scenario title
});

it('lets the tester convert a waiting check into a real verdict without duplicating it', function () {
    $c = Livewire::test(UserTesting::class, ['feature' => 'pn'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->set('testerTimezone', 'Europe/London')
        ->call('defer'); // PN-01 is now waiting, index at 1

    $c->call('prev') // back to PN-01
        ->set('notes', 'Notification not received.')
        ->call('record', VerificationRun::RESULT_ISSUE);

    $run = VerificationRun::where('scenario_id', 'PN-01')->sole();
    expect($run->result)->toBe(VerificationRun::RESULT_ISSUE);
    expect($run->notes)->toBe('Notification not received.');
    expect(VerificationRun::where('scenario_id', 'PN-01')->count())->toBe(1);
});

it('deep-links from a reminder email straight to the scenario, pre-filling the missed note', function () {
    // As the "no — it never arrived" button in the reminder email would open it.
    $this->withSession(['tester_email' => 'maria@example.com', 'tester_name' => 'Maria'])
        ->get('/user-testing/lr?scenario=LR-07&outcome=missed')
        ->assertOk()
        ->assertSee('LR-07')                       // jumped straight to that scenario
        ->assertSee('Notification not received.'); // with the missed note pre-filled
});

/*
 * The sweep that sends the two reminder emails.
 */

it('sends the "watch for it" reminder once the lead window opens, exactly once', function () {
    Mail::fake();

    // Expected in 3 minutes: the 5-minutes-before window is already open; the after window is not.
    $run = VerificationRun::factory()->waiting(now()->addMinutes(3))->create([
        'tester_email' => 'tester@example.com',
    ]);

    $this->artisan('verification:send-reminders')->assertSuccessful();
    // Running again must not re-send.
    $this->artisan('verification:send-reminders')->assertSuccessful();

    Mail::assertSent(DeferredCheckReminder::class, 1);
    Mail::assertSent(DeferredCheckReminder::class, fn ($m) => $m->phase === DeferredCheckReminder::PHASE_BEFORE && $m->hasTo('tester@example.com'));

    $run->refresh();
    expect($run->first_reminder_sent_at)->not->toBeNull();
    expect($run->second_reminder_sent_at)->toBeNull();
})->group('scenario:UT-07');

it('sends the "did it arrive?" reminder once the event plus grace has passed', function () {
    Mail::fake();

    // Expected 25 minutes ago: both the before (−5) and after (+20) windows have opened.
    VerificationRun::factory()->waiting(now()->subMinutes(25))->create([
        'tester_email' => 'tester@example.com',
    ]);

    $this->artisan('verification:send-reminders')->assertSuccessful();

    Mail::assertSent(DeferredCheckReminder::class, 2);
    Mail::assertSent(DeferredCheckReminder::class, fn ($m) => $m->phase === DeferredCheckReminder::PHASE_AFTER);
});

it('sends nothing while both reminder windows are still in the future', function () {
    Mail::fake();

    VerificationRun::factory()->waiting()->create(); // expected in one hour

    $this->artisan('verification:send-reminders')->assertSuccessful();

    Mail::assertNothingSent();
});

it('ignores checks that already have a real verdict', function () {
    Mail::fake();

    VerificationRun::factory()->create([ // result worked, no expected_at
        'tester_email' => 'tester@example.com',
    ]);

    $this->artisan('verification:send-reminders')->assertSuccessful();

    Mail::assertNothingSent();
});
