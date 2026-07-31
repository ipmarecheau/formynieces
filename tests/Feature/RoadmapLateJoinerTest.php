<?php

use App\Models\Setting;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Pacing\RoadmapGenerator;
use Illuminate\Support\Carbon;

/**
 * RR-07 — a late joiner receives a compressed, complete journey. When a student
 * completes the diagnostic deep into the school year, her runway is short: the
 * base cap alone cannot fit the whole remaining syllabus before the exam. The
 * roadmap must compress the weekly pace so every remaining module is still
 * scheduled between this week and exam week, with no stop dated in the past.
 */
function rr07LateJoiner(int $weeksToExam, int $masteredCount): User
{
    // Exam is only $weeksToExam weeks out — a compressed runway.
    Setting::put('sea_exam_date_2026', Carbon::today()->copy()->addWeeks($weeksToExam)->toDateString());

    $student = User::create([
        'name' => 'Late Joiner',
        'email' => 'rr07-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'target_sea_year' => 2026,
    ]);

    // Standard 90-module syllabus across 30 pacing weeks (3 modules/week).
    for ($week = 1; $week <= 30; $week++) {
        for ($i = 0; $i < 3; $i++) {
            SyllabusModule::create([
                'subject' => 'Math',
                'topic' => "Number: W{$week} T{$i}",
                'sea_section' => 'Number',
                'sequence_order' => (($week - 1) * 3) + $i + 1,
                'pacing_week' => $week,
            ]);
        }
    }

    // The diagnostic mastered the earliest $masteredCount modules.
    SyllabusModule::orderBy('sequence_order')->take($masteredCount)->get()
        ->each(fn (SyllabusModule $m) => StudentProgress::create([
            'student_id' => $student->id,
            'module_id' => $m->id,
            'status' => 'mastered',
        ]));

    return $student;
}

it('schedules every remaining module for a late joiner within a compressed runway', function () {
    // 15 weeks out, 12 mastered => 78 remain. Base cap 5 fits only 5*15 = 75 < 78,
    // so the pace must compress or three modules would be dropped.
    $student = rr07LateJoiner(weeksToExam: 15, masteredCount: 12);

    app(RoadmapGenerator::class)->generate($student);

    $scheduled = WeeklyTarget::where('student_id', $student->id)
        ->pluck('module_id')->unique()->sort()->values();

    $remaining = SyllabusModule::whereNotIn(
        'id',
        StudentProgress::where('student_id', $student->id)->where('status', 'mastered')->pluck('module_id')
    )->pluck('id')->sort()->values();

    // Complete: every non-mastered module lands in some week.
    expect($scheduled->all())->toEqual($remaining->all());
})->group('scenario:RR-07');

it('places no stop in the past and none past exam week for a late joiner', function () {
    $student = rr07LateJoiner(weeksToExam: 15, masteredCount: 12);

    app(RoadmapGenerator::class)->generate($student);

    $weekStarts = WeeklyTarget::where('student_id', $student->id)
        ->get()->pluck('week_start_date')->map(fn ($d) => $d->toDateString());

    $thisWeek = Carbon::today()->copy()->startOfWeek()->toDateString();
    $examWeek = Carbon::today()->copy()->addWeeks(15)->startOfWeek()->toDateString();

    // No stop before this week (nothing missed/overdue); none past exam week.
    expect($weekStarts->min())->toBeGreaterThanOrEqual($thisWeek)
        ->and($weekStarts->max())->toBeLessThanOrEqual($examWeek);
})->group('scenario:RR-07');
