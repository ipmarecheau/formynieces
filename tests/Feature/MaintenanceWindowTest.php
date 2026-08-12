<?php

use App\Livewire\ModuleEntry;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

/**
 * LL-23 — once mastered, a level is locked for a two-week maintenance window. Opening it
 * shows a "come back in N days" confirmation, never the loop explainer or the check.
 */
function mwStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-mw-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function mwModule(): SyllabusModule
{
    return SyllabusModule::create([
        'subject' => 'Math',
        'topic' => 'Number: Place Value',
        'sea_section' => 'A',
        'sequence_order' => 1,
        'pacing_week' => 1,
        'description' => 'Understand place value.',
        'resources' => [],
    ]);
}

it('greets a mastered level within its window with a come-back confirmation, not the loop', function () {
    Carbon::setTestNow('2026-08-12 10:00:00');

    $student = mwStudent('ll23');
    $module = mwModule();
    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'mastered',
        'mastered_at' => now()->subDays(3),   // due in 11 days
    ]);

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('phase', 'maintained')
        ->assertSet('daysToDue', 11)
        ->assertSeeText('mastered')
        ->assertSeeText('11')
        ->assertDontSeeText('How this level works');

    Carbon::setTestNow();
})->group('scenario:LL-23');
