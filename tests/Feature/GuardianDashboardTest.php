<?php

use App\Livewire\GuardianDashboard;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function gdSeedGuardianWithStudent(): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student  = User::factory()->create([
        'role'      => 'student',
        'parent_id' => $guardian->id,
    ]);

    StudentJourney::create([
        'student_id'    => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date'     => Carbon::parse('2026-05-21')->toDateString(),
    ]);

    $math = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    $ela  = SyllabusModule::factory()->create(['subject' => 'ELA',  'pacing_week' => 1]);

    return compact('guardian', 'student', 'math', 'ela');
}

it('renders the four Sunday answers for a guardian whose student has an active roadmap', function () {
    ['guardian' => $guardian, 'student' => $student, 'math' => $math] = gdSeedGuardianWithStudent();

    WeeklyTarget::create([
        'student_id'      => $student->id,
        'module_id'       => $math->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed'    => false,
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('targetCompleted', false)
        ->assertViewHas('pace', fn ($pace) =>
            isset($pace['Math'], $pace['ELA'], $pace['Writing'])
            && $pace['Math']['weight'] === 50
            && $pace['ELA']['weight'] === 30
            && $pace['Writing']['weight'] === 20)
        ->assertViewHas('recommendation', fn ($r) => is_string($r) && $r !== '')
        ->assertViewHas('writingFeedback');
})->group('scenario:GD-01');

it('reports the target as completed when every current-week module row is done', function () {
    ['guardian' => $guardian, 'student' => $student, 'math' => $math, 'ela' => $ela] = gdSeedGuardianWithStudent();

    foreach ([$math, $ela] as $module) {
        WeeklyTarget::create([
            'student_id'      => $student->id,
            'module_id'       => $module->id,
            'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
            'is_completed'    => true,
        ]);
    }

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('targetCompleted', true);
})->group('scenario:GD-01');

it('surfaces the pace-warning flag from the student journey', function () {
    ['guardian' => $guardian, 'student' => $student] = gdSeedGuardianWithStudent();

    StudentJourney::where('student_id', $student->id)->update([
        'pace_status'  => 'warning',
        'weeks_behind' => 5,
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('paceStatus', 'warning')
        ->assertSet('weeksBehind', 5);
})->group('scenario:GD-01');

it('renders the four honest answers on the guardian dashboard screen', function () {
    ['guardian' => $guardian, 'student' => $student, 'math' => $math] = gdSeedGuardianWithStudent();

    WeeklyTarget::create([
        'student_id'      => $student->id,
        'module_id'       => $math->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed'    => false,
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('Weekly guardian summary')
        ->assertSee('Pace')
        ->assertSee('Recommendation')
        ->assertSee('Writing feedback');
})->group('scenario:GD-01');
