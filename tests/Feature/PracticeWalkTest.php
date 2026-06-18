<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\PracticeWalk;

uses(RefreshDatabase::class);

it('shows the first rung-1 question when a student opens a module to practise', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create(['topic' => 'Fractions: Adding Like Denominators']);

    PracticeQuestion::factory()->create([
        'module_id'   => $module->id,
        'difficulty'  => 1,
        'prompt'      => 'What is one half plus one half?',
        'options'     => ['One quarter', 'One whole', 'Two halves stay', 'Three quarters'],
        'correct_index' => 1,
    ]);
    // A harder question that should NOT be the one shown first.
    PracticeQuestion::factory()->create([
        'module_id'  => $module->id,
        'difficulty' => 3,
        'prompt'     => 'A rung-3 question',
    ]);

    Livewire::actingAs($student)
        ->test(PracticeWalk::class, ['module' => $module])
        ->assertSee('Fractions: Adding Like Denominators')
        ->assertSee('Level 1 of 3')
        ->assertSee('What is one half plus one half?')
        ->assertDontSee('A rung-3 question');
});

it('shows the explanation and advances after a correct answer', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();

    PracticeQuestion::factory()->create([
        'module_id'    => $module->id,
        'difficulty'   => 1,
        'prompt'       => 'Pick B',
        'options'      => ['A', 'B', 'C', 'D'],
        'correct_index'=> 1,
        'explanation'  => 'B is right because it is the second option.',
    ]);

    $component = Livewire::actingAs($student)
        ->test(PracticeWalk::class, ['module' => $module])
        ->call('choose', 1)               // correct
        ->assertSee('Nice work!')
        ->assertSee('B is right because it is the second option.')
        ->assertSet('currentStreak', 1);  // streak advanced

    $component->call('next')->assertSet('feedback', null);  // back to answering
});

it('frames a wrong answer as not-yet, never failure, and resets the streak', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();

    PracticeQuestion::factory()->create([
        'module_id'    => $module->id,
        'difficulty'   => 1,
        'prompt'       => 'Pick B',
        'options'      => ['A', 'B', 'C', 'D'],
        'correct_index'=> 1,
        'explanation'  => 'The answer is B.',
    ]);

    Livewire::actingAs($student)
        ->test(PracticeWalk::class, ['module' => $module])
        ->call('choose', 0)               // wrong
        ->assertSee('Not yet')
        ->assertSee('The answer is B.')
        ->assertDontSee('Wrong')
        ->assertDontSee('Incorrect')
        ->assertSet('currentStreak', 0);  // stayed/reset at 0
});

it('serves a different question after answering, not the same one again', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();

    $q1 = PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'First question', 'options' => ['A','B','C','D'], 'correct_index' => 1,
        'explanation' => 'x',
    ]);
    $q2 = PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'Second question', 'options' => ['A','B','C','D'], 'correct_index' => 1,
        'explanation' => 'y',
    ]);

    $component = Livewire::actingAs($student)
        ->test(PracticeWalk::class, ['module' => $module]);

    // Whichever question is shown first, answer it correctly, then Next.
    $firstId = $component->get('question')['id'];
    $component->call('choose', 1)->call('next');

    // The next question must be a DIFFERENT id (not the one just answered in this streak).
    $secondId = $component->get('question')['id'];
    expect($secondId)->not->toBe($firstId);
});

it('shows a mastery celebration, not coming-soon, once the module is mastered', function () {
    $student = User::factory()->create();
    $module  = SyllabusModule::factory()->create();

    // Pre-set the student to mastered on this module.
    \App\Models\StudentProgress::create([
        'student_id'    => $student->id,
        'module_id'     => $module->id,
        'status'        => 'mastered',
        'score'         => 100,
        'current_rung'  => 3,
        'current_streak'=> 3,
    ]);

    // Some questions exist, but mastery should short-circuit before serving them.
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 3]);

    Livewire::actingAs($student)
        ->test(PracticeWalk::class, ['module' => $module])
        ->assertSet('isMastered', true)
        ->assertSee('mastered')
        ->assertDontSee('coming soon');
});