<?php

use App\Livewire\ModuleEntry;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

/**
 * LL-19 — opening a level greets her with a student-language explanation of the
 * loop, and that explanation leads her into the competency check.
 */
it('greets her with a student-language explanation that leads into the competency check', function () {
    $student = User::create([
        'name' => 'Maya',
        'email' => 'maya-ll19@test.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    $module = SyllabusModule::create([
        'subject' => 'Math',
        'topic' => 'Number: Place Value',
        'sea_section' => 'A',
        'sequence_order' => 1,
        'pacing_week' => 1,
        'description' => 'Understand place value.',
        'resources' => [],
    ]);

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('phase', 'explainer')
        ->assertSeeText('How this level works')
        ->call('beginCheck')
        ->assertSet('phase', 'check')
        // TR-07 — the first-run lesson tour follows the student's real progress.
        ->assertDispatched('lesson-phase', phase: 'check');
})->group('scenario:LL-19');

it('opens the entry page from the map route', function () {
    $student = User::create([
        'name' => 'Maya',
        'email' => 'maya-ll19b@test.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    $module = SyllabusModule::create([
        'subject' => 'Math',
        'topic' => 'Number: Place Value',
        'sea_section' => 'A',
        'sequence_order' => 1,
        'pacing_week' => 1,
        'description' => 'Understand place value.',
        'resources' => [],
    ]);

    actingAs($student)
        ->get(route('practice.enter', $module))
        ->assertOk()
        ->assertSeeText('How this level works');
})->group('scenario:LL-19');
