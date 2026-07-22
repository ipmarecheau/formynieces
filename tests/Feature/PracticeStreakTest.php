<?php

use App\Models\PracticeQuestion;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Motivation\StreakService;
use App\Services\Practice\RecordPracticeAttempt;
use Illuminate\Support\Carbon;

function ml01Student(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-ml01-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'email_verified_at' => now(),
    ]);
}

it('extends the practice streak on a consecutive-day activity', function () {
    $student = ml01Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 4,
        'last_activity_date' => Carbon::yesterday()->toDateString(),
    ]);

    app(StreakService::class)->recordActivity($student->id, 'practice');

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'practice')->first();
    expect($streak->count)->toBe(5);
    expect($streak->last_activity_date->toDateString())->toBe(Carbon::today()->toDateString());
})->group('scenario:ML-01');

it('advances the practice streak when a practice attempt is recorded', function () {
    $student = ml01Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 4,
        'last_activity_date' => Carbon::yesterday()->toDateString(),
    ]);

    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Test', 'sea_section' => 'Number',
        'sequence_order' => 1, 'pacing_week' => 1,
    ]);
    $question = PracticeQuestion::factory()->create([
        'module_id' => $module->id,
        'difficulty' => 1,
        'options' => ['a', 'b', 'c', 'd'],
        'correct_index' => 0,
    ]);

    app(RecordPracticeAttempt::class)->handle($student->id, $question->id, 0);

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'practice')->first();
    expect($streak->count)->toBe(5);
})->group('scenario:ML-01');

it('shows the practice day streak on the student dashboard', function () {
    $student = ml01Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 5,
        'last_activity_date' => Carbon::today()->toDateString(),
    ]);

    // Students have synthetic emails and are never "verified"; their dashboard
    // is served at /my-map (auth-only), not the verified-gated /dashboard.
    $this->actingAs($student)
        ->get('/my-map')
        ->assertOk()
        ->assertSeeText('5 day streak');
})->group('scenario:ML-01');
