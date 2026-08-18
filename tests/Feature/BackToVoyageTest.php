<?php

use App\Livewire\ModuleEntry;
use App\Livewire\MorningTide;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

// SH-08 — every child-layer screen offers a consistent way back to the Voyage.
it('offers a back-to-Voyage control on the module entry screen', function () {
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertSee('Back to my Voyage')
        ->assertSee(route('student.voyage'), false);
})->group('scenario:SH-08');

it('offers a back-to-Voyage control on the Morning Tide', function () {
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);

    Livewire::actingAs($student)
        ->test(MorningTide::class)
        ->assertSee('Back to my Voyage');
})->group('scenario:SH-08');
