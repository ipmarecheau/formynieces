<?php

use App\Livewire\VoyageTour;
use App\Livewire\WelcomeAboard;
use App\Models\StreakReward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function tourStudent(bool $welcomed = false): User
{
    return User::create([
        'name' => 'Amara',
        'email' => 'tour-'.uniqid().'@students.local',
        'password' => bcrypt('secret-password'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
        'welcomed_at' => $welcomed ? now() : null,
    ]);
}

/** TR-01 — a first login (onboarded, not yet welcomed) lands on the welcome page. */
it('sends a newly-onboarded student to the welcome page on login', function () {
    $student = tourStudent();

    $this->post('/login', [
        'email' => $student->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('student.welcome'));
})->group('scenario:TR-01');

/** TR-01 — the welcome page greets her aboard by name. */
it('greets the student aboard by name on the welcome page', function () {
    $student = tourStudent();

    Livewire::actingAs($student)
        ->test(WelcomeAboard::class)
        ->assertSee('Welcome aboard')
        ->assertSee('Amara');
})->group('scenario:TR-01');

/** TR-05 — being welcomed grants one of every perk, exactly once. */
it('grants one of each perk as a joining bonus, only once', function () {
    $student = tourStudent();

    Livewire::actingAs($student)->test(WelcomeAboard::class);

    foreach (StreakReward::TYPES as $type) {
        expect(StreakReward::where('student_id', $student->id)->where('type', $type)->value('quantity'))
            ->toBe(1);
    }
    expect($student->fresh()->welcomed_at)->not->toBeNull()
        ->and($student->fresh()->tour_stage)->toBe('overworld'); // queued to start the tour

    // Revisiting the welcome page does not grant the bonus again.
    Livewire::actingAs($student->fresh())->test(WelcomeAboard::class);

    foreach (StreakReward::TYPES as $type) {
        expect(StreakReward::where('student_id', $student->id)->where('type', $type)->value('quantity'))
            ->toBe(1);
    }
})->group('scenario:TR-05');

/** TR-02 — the tour auto-opens on a welcomed student's first Voyage, in chapters. */
it('auto-opens the chaptered tour for a welcomed student who has not seen it', function () {
    $student = tourStudent(welcomed: true);
    $student->setTourStage('overworld');

    $component = Livewire::actingAs($student->fresh())->test(VoyageTour::class)
        ->assertSet('open', true);

    expect(count($component->instance()->tour()['chapters']))->toBeGreaterThanOrEqual(3);
})->group('scenario:TR-02');

/** TR-03 — once seen, the tour does not auto-open again. */
it('does not auto-open the tour once it has been seen', function () {
    $student = tourStudent(welcomed: true);
    $student->markGuideSeen('tour');

    Livewire::actingAs($student->fresh())->test(VoyageTour::class)
        ->assertSet('open', false);
})->group('scenario:TR-03');

/** TR-04 — the "take the tour" control reopens it any time. */
it('reopens the tour on the start-tour event', function () {
    $student = tourStudent(welcomed: true);
    $student->markGuideSeen('tour');

    Livewire::actingAs($student->fresh())->test(VoyageTour::class)
        ->assertSet('open', false)
        ->dispatch('start-tour')
        ->assertSet('open', true);
})->group('scenario:TR-04');

/** TR-02 — a student who has not been welcomed does not get the tour yet. */
it('does not open the tour before the student has been welcomed', function () {
    $student = tourStudent(welcomed: false);

    Livewire::actingAs($student)->test(VoyageTour::class)
        ->assertSet('open', false);
})->group('scenario:TR-02');

/** TR-06 — the welcome and tour copy carry no guardian-layer metrics. */
it('shows no pace, percentage, target, or grade in the welcome or tour', function () {
    $student = tourStudent();

    Livewire::actingAs($student)->test(WelcomeAboard::class)
        ->assertDontSee('%')
        ->assertDontSee('pace')
        ->assertDontSee('target')
        ->assertDontSee('grade');

    $chapters = collect(config('tour.chapters'))->flatMap(fn ($c) => $c['lines'])->implode(' ');
    expect($chapters)->not->toContain('%')
        ->and(strtolower($chapters))->not->toContain('pace')
        ->and(strtolower($chapters))->not->toContain('grade');
})->group('scenario:TR-06');
