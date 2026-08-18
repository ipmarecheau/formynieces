<?php

use App\Livewire\ModuleEntry;
use App\Models\PracticeAttempt;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingPrompt;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\Motivation\WritingGate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/** A student on a Monday (writing day) with a prompt this week, writing not yet done. */
function wgSetup(): array
{
    Carbon::setTestNow(Carbon::parse('2026-08-17 09:00')); // Monday
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    WritingPrompt::create([
        'week_start_date' => Carbon::parse('2026-08-17')->toDateString(),
        'title' => 'Prompt', 'prompt' => 'Write.', 'type' => 'narrative',
    ]);
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);

    return [$student, $module];
}

// WR-07 — writing is the writing-day gate to advancing on the map.
it('blocks a new level on a writing day until writing is done', function () {
    [$student, $module] = wgSetup();

    expect(app(WritingGate::class)->blocksNewLevel($student->id, $module->id))->toBeTrue();
})->group('scenario:WR-07');

// CO-05 — already-started levels remain playable; the gate is only for new ones.
it('never gates an already-started level', function () {
    [$student, $module] = wgSetup();
    PracticeAttempt::factory()->create(['student_id' => $student->id, 'module_id' => $module->id]);

    expect(app(WritingGate::class)->blocksNewLevel($student->id, $module->id))->toBeFalse();
})->group('scenario:CO-05');

// AM-11 — once the day's writing is done, the new level opens normally.
it('lifts the gate once the day writing is done', function () {
    [$student, $module] = wgSetup();
    app(DailyPlanComposer::class)->markDuty($student->id, 'writing');

    expect(app(WritingGate::class)->blocksNewLevel($student->id, $module->id))->toBeFalse();
})->group('scenario:AM-11');

// CO-05 — sailing to a new level sends her back to the map (still explorable), not a hard wall.
it('sails a new level back to the map when writing is pending', function () {
    [$student, $module] = wgSetup();

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertRedirect();
})->group('scenario:CO-05');
