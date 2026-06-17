<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('stores a practice question with teaching fields and casts options to an array', function () {
    $module = SyllabusModule::factory()->create();

    $question = PracticeQuestion::factory()->create([
        'module_id'   => $module->id,
        'options'     => ['Apple', 'Banana', 'Cherry', 'Date'],
        'correct_index' => 2,
        'hint'        => 'It rhymes with merry.',
        'explanation' => 'Cherry is the correct fruit here.',
    ]);

    $fresh = $question->fresh();

    expect($fresh->options)->toBeArray()
        ->and($fresh->options[2])->toBe('Cherry')
        ->and($fresh->correct_index)->toBe(2)
        ->and($fresh->hint)->toBe('It rhymes with merry.')
        ->and($fresh->explanation)->toBe('Cherry is the correct fruit here.')
        ->and($fresh->is_active)->toBeTrue()
        ->and($fresh->module->id)->toBe($module->id);
});