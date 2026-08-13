<?php

use App\Livewire\LessonWalk;
use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

function liStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya', 'email' => "maya-li-{$suffix}@test.com",
        'password' => bcrypt('secret'), 'role' => 'student', 'onboarding_completed_at' => now(),
    ]);
}

/** A published lesson whose first block is the interaction under test, followed by a closing text block. */
function liLesson(int $moduleId, array $interaction): void
{
    Lesson::create([
        'module_id' => $moduleId,
        'is_published' => true,
        'title' => 'Interactions',
        'blocks' => [$interaction, ['type' => 'text', 'content' => 'Well done!']],
    ]);
}

/**
 * LE-07 — a fill-in-the-blank block gates the lesson: it does not advance on the wrong word, and
 * accepts the right word regardless of case/whitespace.
 */
it('gates a fill-in-the-blank block until the right word', function () {
    $student = liStudent('07');
    $module = SyllabusModule::factory()->create();
    liLesson($module->id, ['type' => 'fillblank', 'prompt' => 'The cat ___ on the mat.', 'answer' => 'sat', 'options' => ['sat', 'sit']]);

    $c = Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])
        ->assertSet('revealed', 1)->assertSet('lessonComplete', false);

    $c->call('answerFillBlank', 0, 'sit')->call('next')->assertSet('revealed', 1);            // wrong -> gated
    $c->call('answerFillBlank', 0, ' SAT ')->call('next')
        ->assertSet('revealed', 2)->assertSet('lessonComplete', true);                          // right (trim + case)
})->group('scenario:LE-07');

/**
 * LE-08 — a mark-the-words block gates until she taps exactly the *asterisk-marked* target words.
 */
it('gates a mark-the-words block until the target words are tapped', function () {
    $student = liStudent('08');
    $module = SyllabusModule::factory()->create();
    liLesson($module->id, ['type' => 'markwords', 'instruction' => 'Tap the verb', 'text' => 'The dog *runs* home']);

    $c = Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])->assertSet('lessonComplete', false);

    $c->call('answerMarkWords', 0, [1])->call('next')->assertSet('revealed', 1);                // wrong token -> gated
    $c->call('answerMarkWords', 0, [2])->call('next')                                            // 'runs' is token index 2
        ->assertSet('revealed', 2)->assertSet('lessonComplete', true);
})->group('scenario:LE-08');

/**
 * LE-09 — a match-pairs block gates until every left is matched to its authored right value.
 */
it('gates a match-pairs block until every pair is matched', function () {
    $student = liStudent('09');
    $module = SyllabusModule::factory()->create();
    liLesson($module->id, ['type' => 'matchpairs', 'instruction' => 'Match the synonyms',
        'pairs' => [['left' => 'big', 'right' => 'large'], ['left' => 'fast', 'right' => 'quick']]]);

    $c = Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])->assertSet('lessonComplete', false);

    $c->call('answerMatchPairs', 0, ['quick', 'large'])->call('next')->assertSet('revealed', 1);   // swapped -> gated
    $c->call('answerMatchPairs', 0, ['large', 'quick'])->call('next')
        ->assertSet('revealed', 2)->assertSet('lessonComplete', true);
})->group('scenario:LE-09');

/**
 * LE-10 — an order-the-steps block gates until her sequence matches the authored order.
 */
it('gates an order-the-steps block until the sequence is right', function () {
    $student = liStudent('10');
    $module = SyllabusModule::factory()->create();
    liLesson($module->id, ['type' => 'ordersteps', 'instruction' => 'Put the steps in order',
        'items' => ['Add the ones', 'Carry the ten', 'Write the answer']]);

    $c = Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])->assertSet('lessonComplete', false);

    $c->call('answerOrderSteps', 0, ['Carry the ten', 'Add the ones', 'Write the answer'])->call('next')->assertSet('revealed', 1);
    $c->call('answerOrderSteps', 0, ['Add the ones', 'Carry the ten', 'Write the answer'])->call('next')
        ->assertSet('revealed', 2)->assertSet('lessonComplete', true);
})->group('scenario:LE-10');
