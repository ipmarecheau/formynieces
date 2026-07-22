<?php

use App\Models\StudentStreak;
use App\Models\User;

function ml07Student(): User
{
    $student = User::factory()->create([
        'role' => 'student',
        'email' => 'aaliyah-ml07-'.uniqid().'@students.formynieces.com',
        'onboarding_completed_at' => now(),
    ]);
    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 5,
        'last_activity_date' => now()->toDateString(),
    ]);

    return $student;
}

it('sends a logging-in student with active streaks to the streak splash', function () {
    $student = ml07Student();

    $this->post('/login', [
        'email' => $student->email,
        'password' => 'password', // factory default
    ])->assertRedirect(route('student.splash'));
})->group('scenario:ML-07');

it('shows the streak splash celebrating streaks with a continue link to the map', function () {
    $student = ml07Student();

    $this->actingAs($student)
        ->get(route('student.splash'))
        ->assertOk()
        ->assertSeeText('5 day practice streak')  // celebrates her current streaks
        ->assertSee(route('student.map'));         // can continue to her learning map
})->group('scenario:ML-07');

// Note: every login creates a 'login' streak (ML-04 hook fires during authentication),
// so a logging-in student always has at least one active streak and always sees the
// splash — matching the intended "splash on login" UX. The redirect's map fallback
// remains as defensive code for non-login contexts.
