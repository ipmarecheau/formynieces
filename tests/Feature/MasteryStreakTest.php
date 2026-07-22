<?php

use App\Models\PracticeQuestion;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\RecordPracticeAttempt;
use Illuminate\Support\Carbon;

function ml05Student(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-ml05-'.uniqid().'@example.com',
        'password' => 'secret-pw',
        'role' => 'student',
        'email_verified_at' => now(),
    ]);
}

/** Climb a module all the way to mastery via RecordPracticeAttempt. */
function masterModule(int $studentId, int $moduleId): void
{
    $svc = app(RecordPracticeAttempt::class);
    foreach ([1, 2, 3] as $rung) {
        collect(range(1, 3))->each(function () use ($svc, $studentId, $moduleId, $rung) {
            $q = PracticeQuestion::factory()->create([
                'module_id' => $moduleId,
                'difficulty' => $rung,
                'options' => ['A', 'B', 'C', 'D'],
                'correct_index' => 1,
            ]);
            $svc->handle($studentId, $q->id, 1); // correct
        });
    }
}

it('extends the mastery streak when a module is mastered on a consecutive day', function () {
    $student = ml05Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'mastery',
        'count' => 2,
        'last_activity_date' => Carbon::yesterday()->toDateString(),
    ]);
    $module = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Mastery Test', 'sea_section' => 'Number',
        'sequence_order' => 1, 'pacing_week' => 1,
    ]);

    masterModule($student->id, $module->id);

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'mastery')->first();
    expect($streak->count)->toBe(3);
})->group('scenario:ML-05');

it('shows the mastery streak on the student dashboard', function () {
    $student = ml05Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'mastery',
        'count' => 3,
        'last_activity_date' => Carbon::today()->toDateString(),
    ]);

    $this->actingAs($student)
        ->get('/my-map')
        ->assertOk()
        ->assertSeeText('3 day mastery streak');
})->group('scenario:ML-05');
