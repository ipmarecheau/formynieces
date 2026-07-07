<?php
// tests/Feature/StudentMapTest.php

use App\Models\User;

it('lets an unverified student reach their own map', function () {
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-' . uniqid() . '@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
        // deliberately NOT verified — synthetic student emails never are
    ]);

    $this->actingAs($student)->get(route('student.map'))->assertOk();
})->group('scenario:AM-01');

it('keeps the guardian dashboard behind verification', function () {
    $guardian = User::create([
        'name' => 'Parent',
        'email' => 'parent-' . uniqid() . '@example.com',
        'password' => bcrypt('secret'),
        'role' => 'parent',
        // unverified guardian
    ]);

    // The verified-gated dashboard should NOT be reachable unverified.
    $this->actingAs($guardian)->get(route('dashboard'))->assertRedirect();
})->group('scenario:GO-02');

it('links a needs_work module to its practice page on the map', function () {
    $student = \App\Models\User::factory()->create([
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
    $module = \App\Models\SyllabusModule::factory()->create([
        'topic' => 'Fractions: Adding Like Denominators',
    ]);
    \App\Models\StudentProgress::create([
        'student_id' => $student->id,
        'module_id'  => $module->id,
        'status'     => 'needs_work',
    ]);

    $this->actingAs($student)
        ->get(route('student.map'))
        ->assertOk()
        ->assertSee(route('practice.walk', $module->id), false); // false = don't escape the URL

})->group('scenario:LL-02');