<?php

use App\Models\StudentJourney;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Pacing\WeeklyRollover;
use Illuminate\Support\Carbon;

function ml06Student(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-ml06-'.uniqid().'@example.com',
        'password' => 'secret-pw',
        'role' => 'student',
        'email_verified_at' => now(),
    ]);
}

function ml06Journey(User $student): void
{
    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date' => Carbon::today()->addWeeks(26)->toDateString(),
    ]);
}

function ml06LastWeekTarget(User $student, bool $completed): void
{
    $lastWeekStart = Carbon::today()->startOfWeek()->subWeek();
    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Pace T', 'sea_section' => 'Number',
        'sequence_order' => 1, 'pacing_week' => 1,
    ]);
    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'week_start_date' => $lastWeekStart->toDateString(),
        'is_completed' => $completed,
    ]);
}

it('extends the on-pace streak when the rollover runs and last week was met', function () {
    $student = ml06Student();
    ml06Journey($student);

    $weekStart = Carbon::today()->startOfWeek();
    // Streak already at 3, last credited two Mondays ago (consecutive with last week).
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'pace_weeks',
        'count' => 3,
        'last_activity_date' => $weekStart->copy()->subWeeks(2)->toDateString(),
    ]);
    ml06LastWeekTarget($student, completed: true);

    app(WeeklyRollover::class)->runFor($student);

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'pace_weeks')->first();
    expect($streak->count)->toBe(4);
})->group('scenario:ML-06');

it('resets the on-pace streak when last week was not met', function () {
    $student = ml06Student();
    ml06Journey($student);

    $weekStart = Carbon::today()->startOfWeek();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'pace_weeks',
        'count' => 3,
        'last_activity_date' => $weekStart->copy()->subWeeks(2)->toDateString(),
    ]);
    ml06LastWeekTarget($student, completed: false);

    app(WeeklyRollover::class)->runFor($student);

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'pace_weeks')->first();
    expect($streak->count)->toBe(0);
})->group('scenario:ML-06');

it('shows the on-pace streak in weeks on the student dashboard', function () {
    $student = ml06Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'pace_weeks',
        'count' => 4,
        'last_activity_date' => Carbon::today()->startOfWeek()->toDateString(),
    ]);

    $this->actingAs($student)
        ->get('/my-map')
        ->assertOk()
        ->assertSeeText('4 week on-pace streak');
})->group('scenario:ML-06');
