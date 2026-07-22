<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;

it('routes a student with incomplete onboarding to the diagnostic intro', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'email' => 'aaliyah@students.formynieces.com',
        'onboarding_completed_at' => null,
    ]);

    $response = $this->post('/login', [
        'email' => $student->email,
        'password' => 'password', // factory default
    ]);

    $response->assertRedirect(route('diagnostic.intro'));
})->group('scenario:GO-05');

it('routes an onboarded student to her populated dashboard on login', function () {
    $guardian = User::factory()->create([
        'role' => 'guardian',
        'age_attested_at' => now(),
    ]);

    $student = User::factory()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'email' => 'aaliyah-onboarded@students.formynieces.com',
        'onboarding_completed_at' => now(),
    ]);

    // Seed progress so the dashboard is populated, not an empty shell.
    $module = SyllabusModule::factory()->create();
    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'mastered',
        'score' => 3,
    ]);

    // Logging in records a login-streak, so she lands on the ML-07 welcome-back
    // splash first (NOT back on the diagnostic) — the splash leads on to her map.
    $this->post('/login', [
        'email' => $student->email,
        'password' => 'password', // factory default
    ])->assertRedirect(route('student.splash'));

    // Her map is the populated dashboard — it renders her progress buckets.
    $this->actingAs($student)->get(route('student.map'))
        ->assertOk()
        ->assertSeeText('Mastered');
})->group('scenario:RR-09');
