<?php

use App\Models\User;
use App\Services\Pacing\AdventureMapBuilder;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;

beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
});

function mapNavStudent(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'mapnav-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

it('gives the overworld map zoom, pan and find-me controls in a bounded window', function () {
    $student = mapNavStudent();

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('mv-viewport')          // bounded, pannable window
        ->assertSee('aria-label="Zoom in"', false)
        ->assertSee('aria-label="Zoom out"', false)
        ->assertSee('Find me');
})->group('scenario:AM-09');

it('gives an island map the same navigation controls', function () {
    $student = mapNavStudent();
    $islands = app(AdventureMapBuilder::class)->buildVoyage($student);
    $slug = $islands[0]['slug'];

    $this->actingAs($student)->get(route('student.voyage.island', $slug))
        ->assertOk()
        ->assertSee('mv-viewport')
        ->assertSee('Find me')
        ->assertSee('Stops on this island');
})->group('scenario:AM-09');

it('shows the overworld legend and companion beside the map', function () {
    $student = mapNavStudent();

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('vy-panel')             // the half-page side panel
        ->assertSee('vy-legend')            // the legend lives in it
        ->assertSeeText('Islands');         // legend heading
})->group('scenario:AM-10');
