<?php

use App\Livewire\LessonWalk;
use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function lessonStudent(): User
{
    return User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
}

it('shuffles a check\'s options and remaps the answer, so the correct choice is never a fixed default', function () {
    $module = SyllabusModule::factory()->create();
    Lesson::create([
        'module_id' => $module->id, 'title' => 'T', 'is_published' => true,
        'blocks' => [[
            'type' => 'check', 'question' => 'Q',
            'options' => ['CORRECT', 'wrong1', 'wrong2', 'wrong3'], 'answer' => 0,
        ]],
    ]);

    $seenAnswerPositions = [];
    for ($i = 0; $i < 10; $i++) {
        $component = Livewire::actingAs(lessonStudent())->test(LessonWalk::class, ['module' => $module]);
        $block = $component->get('lessonBlocks')[0];

        // The remap must keep the answer pointing at the genuinely-correct option.
        expect($block['options'][$block['answer']])->toBe('CORRECT');
        $seenAnswerPositions[$block['answer']] = true;
    }

    // Across mounts the correct option lands in more than one position (not pinned to index 0).
    expect(count($seenAnswerPositions))->toBeGreaterThan(1);
});

it('keeps a shuffled check answerable — the correct (remapped) index passes', function () {
    $module = SyllabusModule::factory()->create();
    Lesson::create([
        'module_id' => $module->id, 'title' => 'T', 'is_published' => true,
        'blocks' => [[
            'type' => 'check', 'question' => 'Q',
            'options' => ['CORRECT', 'wrong1', 'wrong2'], 'answer' => 0,
        ]],
    ]);

    $component = Livewire::actingAs(lessonStudent())->test(LessonWalk::class, ['module' => $module]);
    $answer = (int) $component->get('lessonBlocks')[0]['answer'];

    $component->call('answerCheck', 0, $answer);
    expect($component->get('checkResults')[0] ?? null)->toBeTrue();
});
