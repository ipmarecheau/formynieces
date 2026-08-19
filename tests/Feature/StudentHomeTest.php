<?php

use App\Models\StudentStreak;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Models\WritingPrompt;
use App\Services\Pacing\AdventureMapBuilder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * Student home — the Voyage is the front door (SH-01…06). The child lives on the
 * Voyage; the percentage roadmap is never her landing, and the daily threads
 * (this week's focus, her streak, her writing) are all reached from the sea.
 */
function shHomeStudent(): User
{
    return User::create([
        'name' => 'Maya',
        'email' => 'home-'.uniqid().'@students.formynieces.com',
        'password' => 'secret-password', // hashed cast; log in with the raw string
        'role' => 'student',
        'onboarding_completed_at' => now(),
        'welcomed_at' => now(), // an established returning student
    ]);
}

function shSeedVoyage(): void
{
    test()->seed(SyllabusModuleSeeder::class);
    test()->seed(ModulePrerequisiteSeeder::class);
}

it('lands an onboarded student on her Voyage, never the percentage roadmap', function () {
    shSeedVoyage();
    $student = shHomeStudent();

    // Logging in records a login streak, so she lands on the celebration splash —
    // which flows on to the Voyage (SH-06). She is never dropped on the roadmap.
    $response = $this->post('/login', [
        'email' => $student->email,
        'password' => 'secret-password',
    ]);

    $response->assertRedirect(route('student.splash'));
    expect($response->headers->get('Location'))->not->toContain('/my-map');

    // The Voyage is her home and renders for her.
    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSeeText('Your Voyage');
})->group('scenario:SH-01');

it('shimmers this week\'s target islands and levels on the Voyage, with no pace language', function () {
    shSeedVoyage();
    $student = shHomeStudent();

    // Name a module on the opening (playable) island in this week's target.
    $firstIsland = app(AdventureMapBuilder::class)->buildVoyage($student)[0];
    $targetModuleId = $firstIsland['levels'][0]['id'];
    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $targetModuleId,
        'week_start_date' => now()->startOfWeek()->toDateString(),
    ]);

    // Overworld: the island holding it is flagged for this week. Progress is a
    // count, never a percentage (pace-free child surface — see also AM-06).
    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSeeText('This week')
        ->assertSeeText('0 / 7');

    // Island interior: the target level is flagged too.
    $this->actingAs($student)->get(route('student.voyage.island', $firstIsland['slug']))
        ->assertOk()
        ->assertSeeText('This week');
})->group('scenario:SH-02');

it('shows the student\'s streak on the Voyage', function () {
    shSeedVoyage();
    $student = shHomeStudent();

    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 5,
        'last_activity_date' => now()->toDateString(),
    ]);

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSeeText('5 day streak');
})->group('scenario:SH-04');

it('opens this week\'s writing prompt from the island Writer\'s Log stop', function () {
    shSeedVoyage();
    $student = shHomeStudent();

    WritingPrompt::create([
        'week_start_date' => now()->startOfWeek()->toDateString(),
        'title' => 'The Mystery Door',
        'prompt' => 'Write a story about a door that should not have been opened.',
        'type' => 'narrative',
    ]);

    $firstIsland = app(AdventureMapBuilder::class)->buildVoyage($student)[0];

    $this->actingAs($student)->get(route('student.voyage.island', $firstIsland['slug']))
        ->assertOk()
        ->assertSee(route('student.writing'), false)  // the stop links to the prompt
        ->assertDontSeeText('Coming soon');           // no longer a placeholder
})->group('scenario:SH-05');

it('flows the welcome-back splash into the Voyage, not the roadmap', function () {
    shSeedVoyage();
    $student = shHomeStudent();

    StudentStreak::create([
        'student_id' => $student->id,
        'type' => 'practice',
        'count' => 3,
        'last_activity_date' => now()->toDateString(),
    ]);

    // Login lands on the splash…
    $this->post('/login', [
        'email' => $student->email,
        'password' => 'secret-password',
    ])->assertRedirect(route('student.splash'));

    // …and the splash continues to the Voyage, never the percentage roadmap.
    $this->actingAs($student)->get(route('student.splash'))
        ->assertOk()
        ->assertSee(route('student.voyage'), false)
        ->assertDontSee(route('student.map'), false);
})->group('scenario:SH-06');
