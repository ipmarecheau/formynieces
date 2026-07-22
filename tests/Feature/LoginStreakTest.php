<?php

use App\Models\StudentStreak;
use App\Models\User;
use Illuminate\Support\Carbon;

it('extends the login streak when the student logs in on a consecutive day', function () {
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-ml04@students.formynieces.com',
        'password' => 'secret-pw',   // raw; the hashed cast hashes it
        'role' => 'student',
    ]);
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'login',
        'count' => 3,
        'last_activity_date' => Carbon::yesterday()->toDateString(),
    ]);

    $this->post('/login', [
        'email' => $student->email,
        'password' => 'secret-pw',
    ]);

    $streak = StudentStreak::where('student_id', $student->id)->where('type', 'login')->first();
    expect($streak->count)->toBe(4);
})->group('scenario:ML-04');

it('shows the login streak on the student dashboard', function () {
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-ml04b@students.formynieces.com',
        'password' => 'secret-pw',
        'role' => 'student',
        'email_verified_at' => now(),
    ]);
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'login',
        'count' => 4,
        'last_activity_date' => Carbon::today()->toDateString(),
    ]);

    $this->actingAs($student)
        ->get('/my-map')
        ->assertOk()
        ->assertSeeText('4 day login streak');
})->group('scenario:ML-04');
