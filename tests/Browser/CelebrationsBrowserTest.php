<?php

use App\Models\StudentStreak;
use App\Models\User;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * Browser (Playwright) verification for the streak-milestone celebration that
 * plays on the Voyage home (CE-04). CE-01 (milestone animation) and CE-05
 * (week-complete) fire from deeper mastery state and are covered by feature tests.
 */
it('CE-04: a streak-milestone celebration plays on the Voyage, named warmly', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'celeb-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'voyage',
        'count' => 7,
        'celebrated_count' => 0,
        'last_activity_date' => now()->toDateString(),
    ]);

    $this->actingAs($student);

    $page = visit('/voyage');

    $page->assertNoJavascriptErrors()
        ->assertSee('7-day voyage streak'); // named warmly, not as a bare metric
});
