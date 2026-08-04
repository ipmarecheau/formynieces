<?php

use App\Livewire\GuardianDashboard;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Motivation\StreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * GD-05 — the honest layer never borrows the child's motivational styling. Even
 * when the student has a live streak, the guardian's pace and readiness answers
 * carry no streak counter and no celebration: those belong to her sea alone.
 */
it('shows no streak counter or celebration in the guardian pace and readiness sections', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date' => Carbon::parse('2026-05-21')->toDateString(),
    ]);
    SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);

    // An unmistakably active streak on the child, built through the real service.
    app(StreakService::class)->recordActivity($student->id, 'practice', Carbon::yesterday());
    app(StreakService::class)->recordActivity($student->id, 'practice', Carbon::today());

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('Pace')
        ->assertSee('Recommendation')
        ->assertDontSee('streak')
        ->assertDontSee('day streak')
        ->assertDontSee('🔥')
        ->assertDontSee('🎉');
})->group('scenario:GD-05');
