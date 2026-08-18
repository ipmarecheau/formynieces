<?php

use App\Livewire\ModuleEntry;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\StudentQuestionExposure;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

/**
 * LL-20 — the competency check is a fast test-out: one question at each of D1, D3, D5.
 * Clear all three first-try and the module is mastered, with no lesson or tutorial, and
 * the check only ever serves questions she has not seen before.
 */
function ll20Student(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function ll20Module(): SyllabusModule
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

function ll20Question(int $moduleId, int $difficulty, int $correctIndex, string $prompt): PracticeQuestion
{
    return PracticeQuestion::create([
        'module_id' => $moduleId,
        'subject' => 'Math',
        'sea_section' => 'A',
        'difficulty' => $difficulty,
        'prompt' => $prompt,
        'options' => ['a', 'b', 'c', 'd'],
        'correct_index' => $correctIndex,
        'explanation' => 'Because.',
        'is_active' => true,
    ]);
}

it('masters the module when she clears one D1, D3 and D5 question on the first try', function () {
    $student = ll20Student('ll20a');
    $module = ll20Module();
    ll20Question($module->id, 1, 0, 'D1 place value');
    ll20Question($module->id, 3, 1, 'D3 place value');
    ll20Question($module->id, 5, 2, 'D5 place value');

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->call('beginCheck')
        ->assertSet('phase', 'check')
        ->call('answerCheck', 0)   // D1 correct
        ->call('answerCheck', 1)   // D3 correct
        ->call('answerCheck', 2)   // D5 correct
        ->assertSet('phase', 'outcome')
        ->assertSet('mastered', true);

    expect(
        StudentProgress::where('student_id', $student->id)
            ->where('module_id', $module->id)
            ->value('status')
    )->toBe('mastered');
})->group('scenario:LL-20');

it('masters the module at the check stage of the loop, completing the check', function () {
    $student = ll20Student('ll11');
    $module = ll20Module();
    ll20Question($module->id, 1, 0, 'D1 place value');
    ll20Question($module->id, 3, 1, 'D3 place value');
    ll20Question($module->id, 5, 2, 'D5 place value');

    // The check stage of the loop: one at each of D1/D3/D5, all first-try correct.
    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->call('beginCheck')
        ->assertSet('phase', 'check')
        ->call('answerCheck', 0)
        ->call('answerCheck', 1)
        ->call('answerCheck', 2)
        ->assertSet('phase', 'outcome') // the check is complete
        ->assertSet('mastered', true);

    expect(
        StudentProgress::where('student_id', $student->id)
            ->where('module_id', $module->id)
            ->value('status')
    )->toBe('mastered');
})->group('scenario:LL-11');

it('does not master the module if any of the three is missed on the first try', function () {
    $student = ll20Student('ll20b');
    $module = ll20Module();
    ll20Question($module->id, 1, 0, 'D1 place value');
    ll20Question($module->id, 3, 1, 'D3 place value');
    ll20Question($module->id, 5, 2, 'D5 place value');

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->call('beginCheck')
        ->call('answerCheck', 0)   // D1 correct
        ->call('answerCheck', 3)   // D3 WRONG
        ->call('answerCheck', 2)   // D5 correct
        ->assertSet('phase', 'outcome')
        ->assertSet('mastered', false);

    expect(
        StudentProgress::where('student_id', $student->id)
            ->where('module_id', $module->id)
            ->value('status')
    )->not->toBe('mastered');
})->group('scenario:LL-20');

it('offers a choice of lesson, tutorial or practice when she does not test out', function () {
    $student = ll20Student('ll21');
    $module = ll20Module();
    ll20Question($module->id, 1, 0, 'D1 place value');
    ll20Question($module->id, 3, 1, 'D3 place value');
    ll20Question($module->id, 5, 2, 'D5 place value');

    Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->call('beginCheck')
        ->call('answerCheck', 0)   // D1 correct
        ->call('answerCheck', 3)   // D3 WRONG — no test-out
        ->call('answerCheck', 2)   // D5 correct
        ->assertSet('phase', 'outcome')
        ->assertSet('mastered', false)
        ->assertSee(route('practice.lesson', $module->id), false)
        ->assertSee(route('practice.tutorial', $module->id), false)
        ->assertSee(route('practice.walk', $module->id), false)
        ->assertSeeText('learn it together');
})->group('scenario:LL-21');

it('only serves questions she has not seen before', function () {
    $student = ll20Student('ll20c');
    $module = ll20Module();
    ll20Question($module->id, 1, 0, 'D1 place value');
    $seenD3 = ll20Question($module->id, 3, 1, 'D3 already seen');
    $freshD3 = ll20Question($module->id, 3, 1, 'D3 fresh');
    ll20Question($module->id, 5, 2, 'D5 place value');

    // She has already seen the first D3 question elsewhere in the loop.
    StudentQuestionExposure::create([
        'student_id' => $student->id,
        'content_hash' => $seenD3->content_hash,
        'context' => 'practice',
    ]);

    $component = Livewire::actingAs($student)
        ->test(ModuleEntry::class, ['module' => $module])
        ->call('beginCheck');

    $servedIds = collect($component->get('checkQuestions'))->pluck('id')->all();

    expect($servedIds)->toContain($freshD3->id)
        ->and($servedIds)->not->toContain($seenD3->id);
})->group('scenario:LL-20');
