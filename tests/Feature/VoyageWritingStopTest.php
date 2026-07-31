<?php

use App\Models\User;
use App\Services\Pacing\AdventureMapBuilder;
use App\Support\VoyageInteriors;
use App\Support\VoyageIslands;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * Every Voyage island carries one Writing stop — the writing strand straddles
 * the whole map. Until the writing-submission track is built the stop is a calm
 * "coming soon" marker, but it is present and evenly placed on all 13 islands,
 * sitting in a reserved slot the level stops leave free (never on top of one).
 */
function vwsStudent(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'vws-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

it('gives every one of the 13 islands a writing stop coordinate', function () {
    foreach (VoyageIslands::all() as $island) {
        $stop = VoyageInteriors::writingStopFor($island['slug'], 7);

        expect($stop)->toHaveKeys(['x', 'y'])
            ->and($stop['x'])->toBeGreaterThanOrEqual(0.0)
            ->and($stop['y'])->toBeGreaterThanOrEqual(0.0);
    }
});

it('places the writing stop in a slot the level stops do not use', function () {
    foreach (VoyageIslands::all() as $island) {
        $levelStops = VoyageInteriors::stopsFor($island['slug'], 7);
        $writingStop = VoyageInteriors::writingStopFor($island['slug'], 7);

        expect($levelStops)->not->toContain($writingStop);
    }
});

it('renders the Writer\'s Log marker on an unlocked island interior', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = vwsStudent();
    $first = app(AdventureMapBuilder::class)->buildVoyage($student)[0];

    $this->actingAs($student)->get(route('student.voyage.island', $first['slug']))
        ->assertOk()
        ->assertSee("Writer's Log", false) // static Blade text — literal apostrophe
        ->assertSee('Coming soon')
        ->assertSee('✍️');
});

it('spreads used stops across the whole trail rather than clustering them', function () {
    // 6 levels across a 12-waypoint island still reach the final tuned waypoint,
    // proving the selection spans end to end (not stops 1-6 bunched at the start).
    $stops = VoyageInteriors::stopsFor('feather-isle', 6);

    expect($stops)->toHaveCount(6)
        ->and($stops[0])->toBe(['x' => 19.5, 'y' => 66.8])   // first tuned waypoint
        ->and($stops[5])->toBe(['x' => 77.4, 'y' => 33.5]);  // last tuned waypoint
});
