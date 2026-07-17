<?php

use App\Livewire\GuardianDashboard;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

it('leads with affirmation and presents no action items on an on-track week', function () {
    $guardian = User::create([
        'name'     => 'Test Guardian',
        'email'    => 'guardian-gd03@test.com',
        'password' => bcrypt('secret'),
        'role'     => 'guardian',
        'email_verified_at' => now(),
    ]);

    $student = User::create([
        'name'      => 'Aaliyah',
        'email'     => 'aaliyah-gd03@test.com',
        'password'  => bcrypt('secret'),
        'role'      => 'student',
        'parent_id' => $guardian->id,
    ]);

    // On pace: no warning, zero weeks behind.
    StudentJourney::create([
        'student_id'    => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date'     => Carbon::today()->addWeeks(26)->toDateString(),
        'pace_status'   => null,
        'weeks_behind'  => null,
    ]);

    // Current-week target, fully completed.
    $module = SyllabusModule::factory()->create();
    WeeklyTarget::create([
        'student_id'      => $student->id,
        'module_id'       => $module->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed'    => true,
    ]);

    Livewire::actingAs($guardian)
        ->test(GuardianDashboard::class)
        ->assertSee('Target met and on pace')
        ->assertSee('Nothing to carry into next week')
        ->assertSee('Weekly guardian summary');
})->group('scenario:GD-03');
