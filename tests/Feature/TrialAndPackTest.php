<?php

use App\Livewire\PlacementReportResult;
use App\Models\Lead;
use App\Models\PracticeQuestion;
use App\Models\User;
use App\Services\Funnel\PracticePackService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * lead_capture.feature — the offer conversion: a one-month trial that falls back to free
 * (LG-07/08) and the AI practice-pack PDF (LG-09).
 */
function seedActiveQuestions(int $n = 30): void
{
    foreach (range(1, $n) as $i) {
        PracticeQuestion::factory()->create([
            'subject' => 'Math', 'prompt' => "PQ{$i}?",
            'options' => ['a', 'b', 'c', 'd'], 'correct_index' => 1,
            'explanation' => 'Because.', 'is_active' => true,
        ]);
    }
}

it('provisions a one-month trial when the offer is claimed (LG-07/08)', function () {
    Mail::fake();
    Notification::fake();
    seedActiveQuestions(5);
    $lead = Lead::factory()->withReport()->create();

    Livewire::test(PlacementReportResult::class, ['leadId' => $lead->id])
        ->call('claimTrial')
        ->assertRedirect(route('login'));

    $user = User::where('email', $lead->email)->first();
    expect($user)->not->toBeNull()
        ->and($user->plan)->toBe('trial')
        ->and($user->role)->toBe('guardian')
        ->and($user->trial_ends_at->isFuture())->toBeTrue();

    $lead->refresh();
    expect($lead->converted_user_id)->toBe($user->id)
        ->and($lead->converted_at)->not->toBeNull();
})->group('scenario:LG-07')->group('scenario:LG-08');

it('a lapsed trial no longer has paid access, and the sweep flips it to free (LG-08)', function () {
    config()->set('features.free_tier', true);
    $guardian = User::factory()->create(['role' => 'guardian', 'plan' => 'trial', 'trial_ends_at' => now()->subDay()]);

    // Defensive: access already treats the lapsed trial as free.
    expect($guardian->hasPaidAccess())->toBeFalse();

    $this->artisan('trials:expire')->assertSuccessful();
    expect($guardian->fresh()->plan)->toBe('free');
})->group('scenario:LG-08');

it('an active trial has full access (LG-08)', function () {
    config()->set('features.free_tier', true);
    $guardian = User::factory()->create(['role' => 'guardian', 'plan' => 'trial', 'trial_ends_at' => now()->addWeek()]);
    expect($guardian->hasPaidAccess())->toBeTrue();
})->group('scenario:LG-08');

it('renders the AI practice pack as a real PDF (LG-09)', function () {
    seedActiveQuestions(30);
    config()->set('funnel.pack_questions', 30);

    $path = app(PracticePackService::class)->renderPdf('Standard 4');

    expect(file_exists($path))->toBeTrue()
        ->and(substr(file_get_contents($path), 0, 4))->toBe('%PDF');
    @unlink($path);
})->group('scenario:LG-09');

it('the practice pack assembles the configured number of questions (LG-09)', function () {
    seedActiveQuestions(40);
    config()->set('funnel.pack_questions', 30);
    expect(app(PracticePackService::class)->assemble()->count())->toBe(30);
})->group('scenario:LG-09');
