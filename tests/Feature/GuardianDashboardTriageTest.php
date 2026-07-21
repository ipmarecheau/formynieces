<?php

use App\Livewire\GuardianDashboard;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('presents Math-first triage as weekly steps when the student is 4+ weeks behind', function () {
    $guardian = User::create([
        'name' => 'Test Guardian',
        'email' => 'guardian-gd04@test.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
        'email_verified_at' => now(),
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-gd04@test.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
    ]);

    // Significantly behind: WT-03 flags 4+ weeks behind with a warning.
    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(10)->toDateString(),
        'exam_date' => Carbon::today()->addWeeks(20)->toDateString(),
        'pace_status' => 'warning',
        'weeks_behind' => 5,
    ]);

    // Expected modules with no progress → all behind in both subjects.
    SyllabusModule::factory()->count(3)->create(['subject' => 'Math', 'pacing_week' => 1]);
    SyllabusModule::factory()->count(2)->create(['subject' => 'ELA', 'pacing_week' => 1]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSeeText('Catch-up plan')
        ->assertSeeText('Start with Mathematics')
        ->assertSeeText('per week');
})->group('scenario:GD-04');

it('shows no triage block when the student is on pace', function () {
    $guardian = User::create([
        'name' => 'Test Guardian',
        'email' => 'guardian-gd04b@test.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
        'email_verified_at' => now(),
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'aaliyah-gd04b@test.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date' => Carbon::today()->addWeeks(26)->toDateString(),
        'pace_status' => null,
        'weeks_behind' => null,
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertDontSeeText('Catch-up plan');
})->group('scenario:GD-04');
