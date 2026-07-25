<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Pacing\AdventureMapBuilder;
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

it('shows the overworld with the 13 painted islands and per-island conquered counts', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();

    // Conquer the first two modules (sequence order) so the opening island's
    // count is non-zero.
    SyllabusModule::orderBy('sequence_order')->take(2)->get()
        ->each(fn ($m) => StudentProgress::create([
            'student_id' => $student->id, 'module_id' => $m->id, 'status' => 'mastered', 'score' => 3,
        ]));

    $response = $this->actingAs($student)->get(route('student.voyage'));

    $response->assertOk()
        ->assertSee('Feather Isle')   // first painted island
        ->assertSee('Crystal Peak')   // last painted island
        ->assertSee('2 / ', false);   // 2 conquered on the opening island
})->group('scenario:AM-01');

it('balance-chunks the whole syllabus across the 13 islands and gates them sequentially', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $islands = app(AdventureMapBuilder::class)->buildVoyage($student);

    expect($islands)->toHaveCount(13);
    // Every module lands on exactly one island; chunks are balanced (differ by <= 1).
    $totals = array_column($islands, 'total');
    expect(array_sum($totals))->toBe(SyllabusModule::count());
    expect(max($totals) - min($totals))->toBeLessThanOrEqual(1);

    // Nothing conquered yet: island 0 is playable, the rest are locked.
    expect($islands[0]['state'])->toBe('playable');
    expect($islands[1]['state'])->toBe('locked');
    expect($islands[0]['current'])->toBeTrue();
})->group('scenario:AM-01');

it('unlocks the next island once the current one is fully conquered', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $builder = app(AdventureMapBuilder::class);

    // Master every module on the opening island.
    $first = $builder->buildVoyage($student)[0];
    foreach ($first['levels'] as $level) {
        StudentProgress::create([
            'student_id' => $student->id, 'module_id' => $level['id'], 'status' => 'mastered', 'score' => 3,
        ]);
    }

    $islands = $builder->buildVoyage($student);
    expect($islands[0]['state'])->toBe('mastered');
    expect($islands[1]['state'])->toBe('playable');
    expect($islands[1]['current'])->toBeTrue();
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
