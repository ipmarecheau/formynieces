<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Pacing\AdventureMapBuilder;
use App\Support\VoyageInteriors;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Support\Carbon;

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

it('glows a mastered level red on the map while it is due for review', function () {
    Carbon::setTestNow('2026-08-12 10:00:00');
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $module = SyllabusModule::orderBy('sequence_order')->first();

    // Past the due day (mastered_at + 14) but inside the 5-day grace → needs review.
    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'mastered',
        'mastered_at' => now()->subDays(15),
        'score' => 100,
    ]);

    $first = app(AdventureMapBuilder::class)->buildVoyage($student)[0];

    $this->actingAs($student)->get(route('student.voyage.island', $first['slug']))
        ->assertOk()
        ->assertSee('is-review', false)               // red-glow class on the stop + legend row
        ->assertSee('Needs review', false)            // legend status text
        ->assertSee('A level needs a quick review', false)   // Smooth alert popup...
        ->assertSee('three tricky questions', false); // ...with the steps

    Carbon::setTestNow();
})->group('scenario:LL-25');

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

it('numbers each island stop on the map and names them all in a legend, leaking no pace', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $first = app(AdventureMapBuilder::class)->buildVoyage($student)[0];

    $response = $this->actingAs($student)->get(route('student.voyage.island', $first['slug']));

    $response->assertOk()
        // Each stop carries a compact position number on the map (no more overlapping labels).
        ->assertSee('vy-num')
        // A legend beside the map names every stop in order with its status.
        ->assertSee('vy-legend')
        ->assertSee($first['levels'][0]['topic'])
        ->assertSee($first['levels'][array_key_last($first['levels'])]['topic'])
        ->assertSee('Current')                 // the boat's stop, named in the legend
        ->assertSee('Locked')                  // stops ahead of her, named in the legend
        ->assertSee("Writer's Log", false);    // the writing stop rides in the legend too
})->group('scenario:AM-08');

it('sails a student back to the overworld when an island is still locked', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $islands = app(AdventureMapBuilder::class)->buildVoyage($student);
    $locked = collect($islands)->firstWhere('state', 'locked');

    $this->actingAs($student)->get(route('student.voyage.island', $locked['slug']))
        ->assertRedirect(route('student.voyage'));
})->group('scenario:AM-03');

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
        ->toBe('/images/voyage/interiors/feather-isle.webp');
    expect(VoyageInteriors::backgroundFor('lantern-rock'))
        ->toBe('/images/voyage/interiors/lantern-rock.webp');
    expect(VoyageInteriors::backgroundFor('palm-point'))
        ->toBe('/images/voyage/interiors/palm-point.webp');
    expect(VoyageInteriors::backgroundFor('coral-reef'))
        ->toBe('/images/voyage/interiors/coral-reef.webp');
    expect(VoyageInteriors::backgroundFor('twin-palms'))
        ->toBe('/images/voyage/interiors/twin-palms.webp');
    expect(VoyageInteriors::backgroundFor('flag-bay'))
        ->toBe('/images/voyage/interiors/flag-bay.webp');
    expect(VoyageInteriors::backgroundFor('lagoon-isle'))
        ->toBe('/images/voyage/interiors/lagoon-isle.webp');
    expect(VoyageInteriors::backgroundFor('library-isle'))
        ->toBe('/images/voyage/interiors/library-isle.webp');
    expect(VoyageInteriors::backgroundFor('beacon-shoal'))
        ->toBe('/images/voyage/interiors/beacon-shoal.webp');
    expect(VoyageInteriors::backgroundFor('harbour-town'))
        ->toBe('/images/voyage/interiors/harbour-town.webp');
    expect(VoyageInteriors::backgroundFor('sandbar'))
        ->toBe('/images/voyage/interiors/sandbar.webp');
    expect(VoyageInteriors::backgroundFor('sunset-palms'))
        ->toBe('/images/voyage/interiors/sunset-palms.webp');
    expect(VoyageInteriors::backgroundFor('crystal-peak'))
        ->toBe('/images/voyage/interiors/crystal-peak.webp');
    expect(VoyageInteriors::backgroundFor('no-such-island'))
        ->toBeNull();
})->group('scenario:AM-01');

it('spans Lagoon Isle\'s tuned trail from the left bank to the top-right cave', function () {
    $stops = VoyageInteriors::stopsFor('lagoon-isle', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 15.0, 'y' => 84.0]);   // left-bank start
    expect($stops[6])->toBe(['x' => 90.0, 'y' => 14.5]);   // top-right cave
})->group('scenario:AM-01');

it('spans Flag Bay\'s tuned pier from the start disc to the top-right', function () {
    $stops = VoyageInteriors::stopsFor('flag-bay', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 10.2, 'y' => 88.2]);   // bottom-left start disc
    expect($stops[6])->toBe(['x' => 87.8, 'y' => 22.4]);   // top-right disc
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

it('spans Sandbar\'s tuned stepping-stone arc from the lower-left to the top-right corner', function () {
    $stops = VoyageInteriors::stopsFor('sandbar', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 13.0, 'y' => 81.0]);   // lower-left start stone
    expect($stops[6])->toBe(['x' => 87.0, 'y' => 20.0]);   // top-right corner stone
})->group('scenario:AM-01');

it('spans Harbour Town\'s tuned street from the lower-left disc to the top-right corner', function () {
    $stops = VoyageInteriors::stopsFor('harbour-town', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 10.0, 'y' => 88.0]);   // lower-left start disc
    expect($stops[6])->toBe(['x' => 90.0, 'y' => 10.0]);   // top-right corner disc
})->group('scenario:AM-01');

it('spans Beacon Shoal\'s tuned trail from the beach disc to the top-right disc', function () {
    $stops = VoyageInteriors::stopsFor('beacon-shoal', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 9.0, 'y' => 86.0]);    // beach start disc
    expect($stops[6])->toBe(['x' => 89.0, 'y' => 12.0]);   // top-right disc
})->group('scenario:AM-01');

it('spans Library Isle\'s tuned trail from the beach disc to the top-right stone', function () {
    $stops = VoyageInteriors::stopsFor('library-isle', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 16.0, 'y' => 84.0]);   // beach start disc
    expect($stops[6])->toBe(['x' => 84.0, 'y' => 14.0]);   // top-right stone
})->group('scenario:AM-01');

it('spans Lantern Rock\'s tuned trail from the dock to the lighthouse', function () {
    $stops = VoyageInteriors::stopsFor('lantern-rock', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 14.0, 'y' => 80.0]);   // the dock
    expect($stops[6])->toBe(['x' => 79.0, 'y' => 31.0]);   // the lighthouse
})->group('scenario:AM-01');

it('spans Sunset Palms\' tuned stepping-stone trail from the lower-left to the top-right sunset stone', function () {
    $stops = VoyageInteriors::stopsFor('sunset-palms', 7);

    expect($stops)->toHaveCount(7);
    expect($stops[0])->toBe(['x' => 17.5, 'y' => 77.5]);   // lower-left start stone
    expect($stops[6])->toBe(['x' => 86.0, 'y' => 27.0]);   // top-right sunset stone
})->group('scenario:AM-01');

it('spans Crystal Peak\'s tuned gem-disc horseshoe from the lower-left to the top sea opening', function () {
    $stops = VoyageInteriors::stopsFor('crystal-peak', 6);

    expect($stops)->toHaveCount(6);
    expect($stops[0])->toBe(['x' => 10.0, 'y' => 90.0]);   // lower-left start disc
    expect($stops[5])->toBe(['x' => 53.0, 'y' => 27.5]);   // top disc by the sea opening
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
})->group('scenario:AM-02');

it('makes the island level the one door into practice, with no competing roadmap link', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();

    // SH-03: the Voyage no longer surfaces the percentage roadmap as a competing
    // way in — the switcher link is gone.
    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertDontSee(route('student.map'), false);

    // The one door into practice is tapping a level on the opening (playable) island.
    $firstIsland = app(AdventureMapBuilder::class)->buildVoyage($student)[0];
    $firstLevelId = $firstIsland['levels'][0]['id'];

    $this->actingAs($student)->get(route('student.voyage.island', $firstIsland['slug']))
        ->assertOk()
        ->assertSee(route('practice.enter', $firstLevelId), false);
})->group('scenario:SH-03');

it('lets an unverified student reach her own voyage (synthetic emails are never verified)', function () {
    $student = makeVoyageStudent(); // deliberately unverified

    $this->actingAs($student)->get(route('student.voyage'))->assertOk();
})->group('scenario:AM-01');

it('links a playable level on an open island to its module so tapping plays it', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();
    $first = app(AdventureMapBuilder::class)->buildVoyage($student)[0];
    $playableLevel = $first['levels'][0];

    // The first, playable level links to its module's lesson — tapping plays it.
    $this->actingAs($student)->get(route('student.voyage.island', $first['slug']))
        ->assertOk()
        ->assertSee(route('practice.enter', $playableLevel['id']), false);
})->group('scenario:AM-04');

it('shows a behind-pace student the same kind voyage — mastery only, no pace or percentages', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    // A student behind the pacing calendar: buildVoyage reads mastery only and
    // never touches her journey/pace, so the map cannot render a pace state.
    $student = makeVoyageStudent();

    foreach (app(AdventureMapBuilder::class)->buildVoyage($student) as $island) {
        expect(in_array($island['state'], ['locked', 'playable', 'mastered'], true))->toBeTrue()
            ->and($island)->not->toHaveKey('pace')
            ->and($island)->not->toHaveKey('percentage')
            ->and($island)->not->toHaveKey('weeks_behind')
            // Progress is a conquered COUNT out of total, never a percentage.
            ->and($island)->toHaveKeys(['conquered', 'total']);
    }

    // The overworld surfaces the conquered count ("N / M"), not a percentage or warning.
    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('/ ', false);
})->group('scenario:AM-06');

// AM-12 — a locked island never reads as a contradiction (no bare conquered count).
it('reframes an already-known level on a locked island instead of a bare count', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = makeVoyageStudent();

    // Master a level that lives on the SECOND (locked) island without finishing the first.
    $islands = app(AdventureMapBuilder::class)->buildVoyage($student);
    $lockedLevelId = $islands[1]['levels'][0]['id'];
    StudentProgress::create([
        'student_id' => $student->id, 'module_id' => $lockedLevelId, 'status' => 'mastered', 'score' => 3,
    ]);

    // The second island now has a conquered level but stays locked.
    $islands = app(AdventureMapBuilder::class)->buildVoyage($student);
    expect($islands[1]['state'])->toBe('locked')
        ->and($islands[1]['conquered'])->toBeGreaterThan(0);

    // Its legend row reframes as "N known", never a bare "N / total" beside the lock.
    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('known');
})->group('scenario:AM-12');
