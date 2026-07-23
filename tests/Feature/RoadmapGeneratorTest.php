<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Pacing\RoadmapGenerator;

/**
 * RR-06 — on roadmap generation the student's starting week is the earliest
 * pacing week containing a not-started (non-mastered) module, and a weekly
 * target for the current week is created from that frontier.
 *
 * "not-started" is defined as "not mastered", to stay consistent with
 * WeeklyRollover (which drops only status='mastered' from its frontier).
 */
function makeOnboardedStudentWithJourney(): User
{
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr06-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => today(),
        'exam_date' => today()->addWeeks(30),
    ]);

    return $student;
}

it('computes the starting week as the earliest pacing week with a not-started module', function () {
    $student = makeOnboardedStudentWithJourney();

    // Week 1 module is mastered (drops off); week 2 modules are not-started.
    $week1 = SyllabusModule::create(['subject' => 'Math', 'topic' => 'A: One', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);
    SyllabusModule::create(['subject' => 'Math', 'topic' => 'B: Two', 'sea_section' => 'Section I', 'sequence_order' => 2, 'pacing_week' => 2]);
    SyllabusModule::create(['subject' => 'Math', 'topic' => 'C: Three', 'sea_section' => 'Section I', 'sequence_order' => 3, 'pacing_week' => 2]);

    StudentProgress::create(['student_id' => $student->id, 'module_id' => $week1->id, 'status' => 'mastered', 'score' => 3]);

    $startingWeek = app(RoadmapGenerator::class)->generate($student);

    expect($startingWeek)->toBe(2);
})->group('scenario:RR-06');

it('creates a weekly target for the current week from the not-started frontier', function () {
    $student = makeOnboardedStudentWithJourney();

    // Five not-started (no progress row) modules at pacing week 1.
    foreach (range(1, 5) as $i) {
        SyllabusModule::create(['subject' => 'Math', 'topic' => "M{$i}: x", 'sea_section' => 'Section I', 'sequence_order' => $i, 'pacing_week' => 1]);
    }

    app(RoadmapGenerator::class)->generate($student);

    $targets = WeeklyTarget::where('student_id', $student->id)
        ->where('week_start_date', now()->startOfWeek()->toDateString())
        ->get();

    expect($targets)->not->toBeEmpty()
        ->and($targets->pluck('module_id')->all())
        ->each->toBeIn(SyllabusModule::pluck('id')->all());
})->group('scenario:RR-06');

it('creates the student journey from her target year when none exists yet', function () {
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr06-nojourney-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'target_sea_year' => 2027,
        'onboarding_completed_at' => now(),
    ]);
    SyllabusModule::create(['subject' => 'Math', 'topic' => 'A: One', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);

    expect(StudentJourney::where('student_id', $student->id)->exists())->toBeFalse();

    app(RoadmapGenerator::class)->generate($student);

    $journey = StudentJourney::where('student_id', $student->id)->first();
    expect($journey)->not->toBeNull()
        ->and($journey->journey_start->format('Y-m-d'))->toBe(today()->format('Y-m-d'))
        ->and($journey->exam_date->format('Y-m-d'))->toBe('2027-04-01'); // derived default from target year
})->group('scenario:RR-06');
