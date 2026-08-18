<?php

use App\Livewire\GuardianDashboard;
use App\Models\DiagnosticSession;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Motivation\StreakEconomyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function gdSeedGuardianWithStudent(): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = User::factory()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date' => Carbon::parse('2026-05-21')->toDateString(),
    ]);

    $math = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    $ela = SyllabusModule::factory()->create(['subject' => 'ELA',  'pacing_week' => 1]);

    return compact('guardian', 'student', 'math', 'ela');
}

it('renders the four Sunday answers for a guardian whose student has an active roadmap', function () {
    ['guardian' => $guardian, 'student' => $student, 'math' => $math] = gdSeedGuardianWithStudent();

    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $math->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed' => false,
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('targetCompleted', false)
        ->assertViewHas('pace', fn ($pace) => isset($pace['Math'], $pace['ELA'], $pace['Writing'])
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
            'student_id' => $student->id,
            'module_id' => $module->id,
            'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
            'is_completed' => true,
        ]);
    }

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('targetCompleted', true);
})->group('scenario:GD-01');

it('surfaces the pace-warning flag from the student journey', function () {
    ['guardian' => $guardian, 'student' => $student] = gdSeedGuardianWithStudent();

    StudentJourney::where('student_id', $student->id)->update([
        'pace_status' => 'warning',
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
        'student_id' => $student->id,
        'module_id' => $math->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed' => false,
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('four questions')
        ->assertSee('Pace')
        ->assertSee('Recommendation')
        ->assertSee('Writing feedback');
})->group('scenario:GD-01');

// GD-07 — the dashboard is headed by the child it is about.
it('heads the dashboard with the child name and the week it covers', function () {
    ['guardian' => $guardian, 'student' => $student] = gdSeedGuardianWithStudent();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSeeText($student->name)   // names the child
        ->assertSee('Week of');           // and the week it covers
})->group('scenario:GD-07');

it('lets a guardian with more than one student switch between them', function () {
    ['guardian' => $guardian] = gdSeedGuardianWithStudent();
    User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'name' => 'Second Niece']);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('Second Niece');       // the switcher lists the other child
})->group('scenario:GD-07');

// GD-11 — every pace figure is labelled so it cannot be misread.
it('labels the pace figures so they cannot be misread', function () {
    ['guardian' => $guardian] = gdSeedGuardianWithStudent();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('of the exam')          // the weight is labelled
        ->assertSee('modules mastered');    // the raw count is labelled as modules
})->group('scenario:GD-11');

// SE-15 — a guardian grants a streak reward that lands in the student's Locker.
it('lets a guardian grant a reward into the student Locker', function () {
    ['guardian' => $guardian, 'student' => $student] = gdSeedGuardianWithStudent();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->call('grantReward', 'anchor')
        ->assertDispatched('reward-granted');

    expect(app(StreakEconomyService::class)->balance($student->id, 'anchor'))->toBe(1);
})->group('scenario:SE-15');

/**
 * GD-09 — the dashboard is where the guardian acts: she can pause or resume the
 * journey, grant a reward, and request a diagnostic retake, all from here. These
 * controls live in the honest layer and are never shown to the child.
 */
it('lets the guardian pause and resume the journey from the dashboard', function () {
    ['guardian' => $guardian, 'student' => $student] = gdSeedGuardianWithStudent();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->call('pauseJourney')
        ->assertDispatched('journey-paused');

    expect($student->fresh()->isPaused())->toBeTrue();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->call('resumeJourney')
        ->assertDispatched('journey-resumed');

    expect($student->fresh()->isPaused())->toBeFalse();
})->group('scenario:GD-09');

it('lets the guardian request a diagnostic retake from the dashboard', function () {
    ['guardian' => $guardian, 'student' => $student] = gdSeedGuardianWithStudent();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->call('requestRetake')
        ->assertDispatched('retake-requested');

    expect(
        DiagnosticSession::where('student_id', $student->id)
            ->where('status', 'in_progress')->exists()
    )->toBeTrue();
})->group('scenario:GD-09');

it('grants a reward into the student Locker from the dashboard', function () {
    ['guardian' => $guardian, 'student' => $student] = gdSeedGuardianWithStudent();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->call('grantReward', 'anchor')
        ->assertDispatched('reward-granted');

    expect(app(StreakEconomyService::class)->balance($student->id, 'anchor'))->toBe(1);
})->group('scenario:GD-09');
