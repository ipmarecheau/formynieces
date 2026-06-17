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