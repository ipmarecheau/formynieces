<?php

use App\Livewire\GuardianDashboard;
use App\Models\StreakReward;
use App\Models\StudentJourney;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Motivation\StreakEconomyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function gdOverviewGuardian(): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date' => Carbon::today()->addDays(120)->toDateString(),
    ]);

    SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    SyllabusModule::factory()->create(['subject' => 'ELA', 'pacing_week' => 1]);

    return compact('guardian', 'student');
}

it('heads the dashboard with the exam date and a days-to-exam countdown', function () {
    ['guardian' => $guardian] = gdOverviewGuardian();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertViewHas('daysToExam', fn ($d) => is_int($d) && $d > 0)
        ->assertViewHas('examDate', fn ($d) => is_string($d) && $d !== '')
        ->assertSee('SEA exam in');
})->group('scenario:GD-01');

it('builds a trajectory of mastery against the required-pace plan', function () {
    ['guardian' => $guardian] = gdOverviewGuardian();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertViewHas('trajectory', fn ($t) => isset($t['required_pct'], $t['actual_pct'], $t['total'])
            && $t['required_pct'] >= 0 && $t['required_pct'] <= 100
            && $t['actual_pct'] >= 0 && $t['actual_pct'] <= 100)
        ->assertSee('Trajectory')
        ->assertSee('Required pace to exam');
})->group('scenario:GD-01');

it('leads with a plain-language readiness verdict before any raw count', function () {
    ['guardian' => $guardian] = gdOverviewGuardian();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertViewHas('readiness', fn ($r) => isset($r['tone'], $r['headline'], $r['detail']) && $r['headline'] !== '')
        ->assertSee('Where');
})->group('scenario:GD-11');

it('lazily loads the exam agent guardian briefing without a stored streak in the readiness area', function () {
    ['guardian' => $guardian] = gdOverviewGuardian();

    // The LlmService falls back to a graceful string in tests (no network), so this
    // is deterministic and never hits the model.
    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('aiSummaryLoaded', false)
        ->call('loadAiSummary')
        ->assertSet('aiSummaryLoaded', true)
        ->assertSet('aiSummary', fn ($s) => is_string($s) && $s !== '');
})->group('scenario:GD-01');

it('reports streak day-counts and perks held as plain data for the parent', function () {
    ['guardian' => $guardian, 'student' => $student] = gdOverviewGuardian();

    StudentStreak::create([
        'student_id' => $student->id,
        'type' => StreakEconomyService::MASTER_STREAK,
        'count' => 12,
        'last_activity_date' => Carbon::today()->toDateString(),
    ]);
    StreakReward::create([
        'student_id' => $student->id,
        'type' => 'shore_leave',
        'quantity' => 2,
        'source' => 'guardian',
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertViewHas('streaks', fn ($s) => collect($s)->firstWhere('count', 12) !== null)
        ->assertViewHas('perks', fn ($p) => collect($p)->firstWhere('label', 'Shore Leave')['count'] === 2)
        ->assertSee('Perks in the Locker');
})->group('scenario:GD-01');

it('keeps the readiness and pace sections free of celebration styling', function () {
    ['guardian' => $guardian, 'student' => $student] = gdOverviewGuardian();

    StudentStreak::create([
        'student_id' => $student->id,
        'type' => StreakEconomyService::MASTER_STREAK,
        'count' => 30,
        'last_activity_date' => Carbon::today()->toDateString(),
    ]);

    // GD-05: no motivational styling leaks into the honest layer even when the
    // consistency panel reports counts.
    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('Pace')
        ->assertDontSee('🔥')
        ->assertDontSee('🎉');
})->group('scenario:GD-05');

it('is a sidebar app: sections switch and default to overview', function () {
    ['guardian' => $guardian] = gdOverviewGuardian();

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSet('section', 'overview')
        ->set('section', 'pace')
        ->assertSet('section', 'pace')
        ->assertSee('Pace calendar')
        ->set('section', 'estimator')
        ->assertSee('Projected SEA placement');
})->group('scenario:GD-13');

it('shows when progress was last recalculated', function () {
    ['guardian' => $guardian, 'student' => $student] = gdOverviewGuardian();

    StudentJourney::where('student_id', $student->id)
        ->update(['pace_recalculated_at' => now()->subDay()]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertViewHas('paceUpdatedAt', fn ($d) => $d !== null)
        ->assertSee('Progress updated');
})->group('scenario:GD-14');
