<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\ExamAgentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/**
 * Regression: after the 2026_06_13 remap, syllabus_modules.subject is
 * constrained to ('Math','ELA'). analyse() must iterate the live subjects,
 * not the dead pre-remap strings, or every ELA module is invisible to it.
 */
it('analyses the live Math and ELA subjects only', function () {
    $student = User::factory()->create(['role' => 'student']);

    // Two modules per subject, all in week 1 so both are "expected" now.
    $mathA = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    $mathB = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    $elaA = SyllabusModule::factory()->create(['subject' => 'ELA',  'pacing_week' => 1]);
    $elaB = SyllabusModule::factory()->create(['subject' => 'ELA',  'pacing_week' => 1]);

    // Master one of each subject; leave the other behind.
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $mathA->id, 'status' => 'mastered']);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $elaA->id,  'status' => 'mastered']);

    $result = app(ExamAgentService::class)->analyse($student);
    $subjects = $result['subject_analysis'];

    // Exactly the two live subjects, no dead keys.
    expect(array_keys($subjects))->toEqualCanonicalizing(['Math', 'ELA']);

    // ELA is actually seen now (the bug made this 0 expected / 0 behind).
    expect($subjects['ELA']['expected'])->toBe(2)
        ->and($subjects['ELA']['completed'])->toBe(1)
        ->and($subjects['ELA']['behind_count'])->toBe(1);

    expect($subjects['Math']['expected'])->toBe(2)
        ->and($subjects['Math']['behind_count'])->toBe(1);
});

/**
 * Regression: a student a few weeks into her OWN journey must be paced against
 * her journey_start (via PacingClock), not the global term calendar. Before the
 * fix, a cycle sitting outside the hard-coded constants scored her at week 36
 * (revision), marking every module "expected" and reporting her behind by the
 * entire syllabus.
 */
it('paces a mid-journey student against her journey, not the whole syllabus', function () {
    $student = User::factory()->create(['role' => 'student']);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(3)->toDateString(),
        'exam_date' => Carbon::today()->addWeeks(28)->toDateString(),
    ]);

    // 20 modules spread across weeks 1..20; only weeks 1..3 are "expected" now.
    for ($week = 1; $week <= 20; $week++) {
        SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => $week]);
    }

    $result = app(ExamAgentService::class)->analyse($student);
    $math = $result['subject_analysis']['Math'];

    expect($result['current_week'])->toBe(4)              // 3 whole weeks elapsed + 1
        ->and($math['expected'])->toBeLessThan(20)        // NOT the whole syllabus
        ->and($math['behind_count'])->toBeLessThanOrEqual($math['expected'])
        ->and($math['total'])->toBe(20);
})->group('scenario:GD-15');
