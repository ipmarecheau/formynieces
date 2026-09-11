<?php

use App\Livewire\GuardianDashboard;
use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows an exam date in the child\'s chosen target year even before a journey exists', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    User::factory()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2029,   // far year, no journey yet
    ]);
    SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);

    Livewire::actingAs($guardian)->test(GuardianDashboard::class)
        ->assertViewHas('examDate', fn ($d) => str_contains((string) $d, '2029'));
})->group('scenario:GO-10');

it('prefers the journey exam date once the roadmap has generated one', function () {
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $student = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'target_sea_year' => 2029]);
    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->toDateString(),
        'exam_date' => '2028-04-01',
    ]);
    SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);

    Livewire::actingAs($guardian)->test(GuardianDashboard::class)
        ->assertViewHas('examDate', fn ($d) => str_contains((string) $d, '2028'));
})->group('scenario:GO-10');
