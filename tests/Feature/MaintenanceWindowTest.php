<?php

use App\Livewire\ModuleEntry;
use App\Models\PracticeQuestion;
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

function mwD5(int $moduleId, int $correctIndex, string $prompt): PracticeQuestion
{
    return PracticeQuestion::create([
        'module_id' => $moduleId,
        'subject' => 'Math',
        'sea_section' => 'A',
        'difficulty' => 5,
        'prompt' => $prompt,
        'options' => ['a', 'b', 'c', 'd'],
        'correct_index' => $correctIndex,
        'explanation' => 'Because.',
        'is_active' => true,
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

it('unlocks the re-mastery check on the due day, and re-mastering resets the window', function () {
    Carbon::setTestNow('2026-08-12 10:00:00');

    $student = mwStudent('ll24');
    $module = mwModule();
    mwD5($module->id, 0, 'D5 one');
    mwD5($module->id, 1, 'D5 two');
    mwD5($module->id, 2, 'D5 three');

    $oldMastered = now()->subDays(15);   // due was day 14; now day 15 → inside the grace
    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'mastered',
        'mastered_at' => $oldMastered,
    ]);

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('phase', 'maintenance_due')
        ->call('beginCheck')
        ->assertSet('phase', 'check')
        ->call('answerCheck', 0)   // three D5, all first-try correct
        ->call('answerCheck', 1)
        ->call('answerCheck', 2)
        ->assertSet('phase', 'outcome')
        ->assertSet('mastered', true);

    $progress = StudentProgress::where('student_id', $student->id)
        ->where('module_id', $module->id)->first();

    expect($progress->status)->toBe('mastered')
        ->and($progress->mastered_at->gt($oldMastered))->toBeTrue();   // window reset

    Carbon::setTestNow();
})->group('scenario:LL-24');

it('does not offer the re-mastery check before the due day', function () {
    Carbon::setTestNow('2026-08-12 10:00:00');

    $student = mwStudent('ll24b');
    $module = mwModule();
    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'mastered',
        'mastered_at' => now()->subDays(5),   // due in 9 days — still locked
    ]);

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('phase', 'maintained')
        ->assertSet('isMaintenance', false);

    Carbon::setTestNow();
})->group('scenario:LL-24');
