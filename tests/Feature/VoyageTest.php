<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * The Voyage — the gamified, standalone alternative to the student dashboard.
 * Tier 1 is the overworld: a hub of island-worlds, each showing how many of its
 * levels have been conquered (a count, never a percentage). She can switch back
 * to the classic dashboard at any time.
 */
function makeVoyageStudent(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'voyage-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

it('shows the overworld with an island hub and per-island conquered counts', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();

    // Conquer two Number Isle modules so its count is non-zero.
    SyllabusModule::where('subject', 'Math')->take(2)->get()
        ->each(fn ($m) => StudentProgress::create([
            'student_id' => $student->id, 'module_id' => $m->id, 'status' => 'mastered', 'score' => 3,
        ]));

    $response = $this->actingAs($student)->get(route('student.voyage'));

    $response->assertOk()
        ->assertSee('Number Isle')
        ->assertSee('Story Cove')
        ->assertSee('Word Harbour')
        ->assertSee('conquered')          // the count label
        ->assertSee('2 / ', false);       // 2 conquered on Number Isle
})->group('scenario:AM-01');

it('offers a switch back to the classic dashboard from the voyage', function () {
    $student = makeVoyageStudent();

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee(route('student.map'), false); // the "Dashboard" switcher link
})->group('scenario:AM-01');

it('lets an unverified student reach her own voyage (synthetic emails are never verified)', function () {
    $student = makeVoyageStudent(); // deliberately unverified

    $this->actingAs($student)->get(route('student.voyage'))->assertOk();
})->group('scenario:AM-01');
