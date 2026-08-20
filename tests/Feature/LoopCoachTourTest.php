<?php

use App\Livewire\LoopCoach;
use App\Models\User;
use Livewire\Livewire;

/**
 * TR-07 — the first-run tour's coach on the downstream learning-loop pages advances the
 * student's tour position (never backwards) and only shows for a student mid-tour.
 */
function loopStudent(?string $stage): User
{
    return User::create([
        'name' => 'Maya',
        'email' => 'maya-loop-'.($stage ?? 'none').'@test.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
        'tour_stage' => $stage,
    ]);
}

it('opens and advances the tour stage for a student mid-tour', function () {
    $student = loopStudent('lesson');

    Livewire::actingAs($student)
        ->test(LoopCoach::class, ['leg' => 'learn'])
        ->assertSet('open', true);

    expect($student->fresh()->tour_stage)->toBe('learn');
})->group('scenario:TR-07');

it('never rewinds the tour to an earlier leg', function () {
    $student = loopStudent('practice');

    Livewire::actingAs($student)
        ->test(LoopCoach::class, ['leg' => 'learn']);

    // Already at practice — coaching the lesson page must not send her backwards.
    expect($student->fresh()->tour_stage)->toBe('practice');
})->group('scenario:TR-07');

it('stays hidden for a student who is not on the tour', function () {
    $student = loopStudent(null);

    Livewire::actingAs($student)
        ->test(LoopCoach::class, ['leg' => 'practice'])
        ->assertSet('open', false);

    expect($student->fresh()->tour_stage)->toBeNull();
})->group('scenario:TR-07');

it('ends the whole tour when skipped or finished', function () {
    $student = loopStudent('practice');

    Livewire::actingAs($student)
        ->test(LoopCoach::class, ['leg' => 'practice'])
        ->call('finish')
        ->assertSet('open', false);

    expect($student->fresh()->tour_stage)->toBe('done');
})->group('scenario:TR-07');
