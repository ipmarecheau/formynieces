<?php

use App\Livewire\CaptainsOrders;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Motivation\DailyPlanComposer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function dtlStudent(): User
{
    return User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now(), 'welcomed_at' => now()]);
}

function dtlTarget(User $student, string $topic, string $status): SyllabusModule
{
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => $topic, 'pacing_week' => 1]);
    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed' => $status === 'mastered',
    ]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => $status]);

    return $module;
}

it('CO-02: lists this week\'s paced lessons as today\'s tasks', function () {
    $student = dtlStudent();
    dtlTarget($student, 'Fractions: Adding', 'needs_work');
    dtlTarget($student, 'Number: Place Value', 'needs_work');

    $tasks = app(DailyPlanComposer::class)->todaysLessonTasks($student->id);

    expect($tasks)->toHaveCount(2)
        ->and(collect($tasks)->pluck('topic')->all())
        ->toContain('Fractions: Adding', 'Number: Place Value')
        ->and($tasks[0]['done'])->toBeFalse();
})->group('scenario:CO-02');

it('CO-02: a mastered topic shows checked off, unfinished ones lead', function () {
    $student = dtlStudent();
    dtlTarget($student, 'Done Topic', 'mastered');
    dtlTarget($student, 'Todo Topic', 'needs_work');

    $tasks = app(DailyPlanComposer::class)->todaysLessonTasks($student->id);

    // Unfinished first, then done.
    expect($tasks[0]['topic'])->toBe('Todo Topic')
        ->and($tasks[0]['done'])->toBeFalse()
        ->and($tasks[1]['topic'])->toBe('Done Topic')
        ->and($tasks[1]['done'])->toBeTrue();
})->group('scenario:CO-02');

it('CO-02: the paced task list renders in the Captain\'s Orders', function () {
    $student = dtlStudent();
    dtlTarget($student, 'Fractions: Adding', 'needs_work');

    Livewire::actingAs($student)
        ->test(CaptainsOrders::class)
        ->assertSee('finish these to stay on course')
        ->assertSee('Fractions: Adding');
})->group('scenario:CO-02');
