<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Pacing\AdventureMapBuilder;
use App\Support\VoyageInteriors;
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

it('opens an unlocked island as a mini-voyage listing its own levels', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $first = app(AdventureMapBuilder::class)->buildVoyage($student)[0];

    $response = $this->actingAs($student)->get(route('student.voyage.island', $first['slug']));

    $response->assertOk()
        ->assertSee($first['name'])
        ->assertSee($first['levels'][0]['topic'])          // a level stop on the interior
        ->assertSee('Back to the sea');
})->group('scenario:AM-01');

it('sails a student back to the overworld when an island is still locked', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $islands = app(AdventureMapBuilder::class)->buildVoyage($student);
    $locked = collect($islands)->firstWhere('state', 'locked');

    $this->actingAs($student)->get(route('student.voyage.island', $locked['slug']))
        ->assertRedirect(route('student.voyage'));
})->group('scenario:AM-01');

it('samples an island\'s level stops evenly across its tuned waypoints', function () {
    $stops = VoyageInteriors::stopsFor('feather-isle', 7);

    expect($stops)->toHaveCount(7);
    // The trail still spans end to end: first and last tuned waypoints are kept.
    expect($stops[0])->toBe(['x' => 19.5, 'y' => 66.8]);
    expect($stops[6])->toBe(['x' => 77.4, 'y' => 33.5]);
})->group('scenario:AM-01');

it('falls back to a generated path for an island without tuned waypoints', function () {
    $stops = VoyageInteriors::stopsFor('crystal-peak', 6);

    expect($stops)->toHaveCount(6);
    expect($stops[0])->toHaveKeys(['x', 'y']);
})->group('scenario:AM-01');

it('serves bespoke interior art when the file exists', function () {
    expect(VoyageInteriors::backgroundFor('feather-isle'))
        ->toBe('/images/voyage/interiors/feather-isle.png');
    expect(VoyageInteriors::backgroundFor('lantern-rock'))
        ->toBe('/images/voyage/interiors/lantern-rock.png');
    expect(VoyageInteriors::backgroundFor('palm-point'))
        ->toBe('/images/voyage/interiors/palm-point.png');
    expect(VoyageInteriors::backgroundFor('coral-reef'))
        ->toBe('/images/voyage/interiors/coral-reef.png');
    expect(VoyageInteriors::backgroundFor('twin-palms'))
        ->toBe('/images/voyage/interiors/twin-palms.png');
    expect(VoyageInteriors::backgroundFor('no-such-island'))
        ->toBeNull();
})->group('scenario:AM-01');

it('spans Twin Palms\' tuned boardwalk from the ramp to the sunset dock', function () {
    $stops = VoyageInteriors::stopsFor('twin-palms', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 18.0, 'y' => 72.0]);   // ramp start
    expect($stops[6])->toBe(['x' => 90.0, 'y' => 27.5]);   // sunset dock
})->group('scenario:AM-01');

it('spans Coral Reef\'s tuned disc path from the corner disc to the top-right', function () {
    $stops = VoyageInteriors::stopsFor('coral-reef', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 14.4, 'y' => 82.0]);   // bottom-left start disc
    expect($stops[6])->toBe(['x' => 79.0, 'y' => 34.5]);   // top-right disc
})->group('scenario:AM-01');

it('spans Palm Point\'s tuned boardwalk from the start disc to the sunset', function () {
    $stops = VoyageInteriors::stopsFor('palm-point', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 68.0, 'y' => 65.6]);   // start disc
    expect($stops[6])->toBe(['x' => 79.0, 'y' => 25.0]);   // sunset stone
})->group('scenario:AM-01');

it('spans Lantern Rock\'s tuned trail from the dock to the lighthouse', function () {
    $stops = VoyageInteriors::stopsFor('lantern-rock', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 14.0, 'y' => 80.0]);   // the dock
    expect($stops[6])->toBe(['x' => 79.0, 'y' => 31.0]);   // the lighthouse
})->group('scenario:AM-01');

it('404s on an island slug that does not exist', function () {
    $student = makeVoyageStudent();

    $this->actingAs($student)->get(route('student.voyage.island', 'no-such-island'))
        ->assertNotFound();
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
