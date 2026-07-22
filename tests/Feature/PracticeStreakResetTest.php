<?php

use App\Models\StudentStreak;
use App\Models\User;
use App\Services\Motivation\StreakService;
use Illuminate\Support\Carbon;

function ml02Student(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-ml02-'.uniqid().'@example.com',
        'password' => bcrypt('password'),
        'role' => 'student',
        'email_verified_at' => now(),
    ]);
}

it('resets the practice streak to 1 after a missed day and marks the restart', function () {
    $student = ml02Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 9,
        'last_activity_date' => Carbon::today()->subDays(2)->toDateString(), // missed yesterday
    ]);

    app(StreakService::class)->recordActivity($student->id, 'practice');

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'practice')->first();
    expect($streak->count)->toBe(1);
    expect($streak->restarted_at?->toDateString())->toBe(Carbon::today()->toDateString());
})->group('scenario:ML-02');

it('greets a returning student kindly, without referencing the broken streak', function () {
    $student = ml02Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 9,
        'last_activity_date' => Carbon::today()->subDays(2)->toDateString(),
    ]);

    app(StreakService::class)->recordActivity($student->id, 'practice'); // reset → restarted_at = today

    $this->actingAs($student)
        ->get('/my-map')
        ->assertOk()
        ->assertSeeText('fresh streak')     // kind return message (distinct from the hero greeting)
        ->assertSeeText('1 day streak')     // fresh streak, not the old 9
        ->assertDontSeeText('lost');        // no shaming language
})->group('scenario:ML-02');

it('does not show the welcome-back message on a normal continuing day', function () {
    $student = ml02Student();
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 4,
        'last_activity_date' => Carbon::yesterday()->toDateString(),
    ]);

    app(StreakService::class)->recordActivity($student->id, 'practice'); // increments to 5, no restart

    $this->actingAs($student)
        ->get('/my-map')
        ->assertOk()
        ->assertDontSeeText('fresh streak');
})->group('scenario:ML-02');
