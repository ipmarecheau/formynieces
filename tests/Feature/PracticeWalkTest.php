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