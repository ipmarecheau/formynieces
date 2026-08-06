<?php

use App\Filament\Resources\PracticeQuestions\Pages\CreatePracticeQuestion;
use App\Filament\Resources\PracticeQuestions\Pages\EditPracticeQuestion;
use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

it('creates a practice question against its module and rung', function () {
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'sea_section' => 'Section I']);

    Livewire::test(CreatePracticeQuestion::class)
        ->fillForm([
            'module_id' => $module->id,
            'difficulty' => 2,
            'prompt' => 'What is 1/2 + 1/2?',
            'option_1' => '1', 'option_2' => '1/4', 'option_3' => '2', 'option_4' => '0',
            'correct_option' => 1,
            'explanation' => 'Add the numerators.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $q = PracticeQuestion::where('prompt', 'What is 1/2 + 1/2?')->firstOrFail();
    expect($q->module_id)->toBe($module->id)
        ->and($q->difficulty)->toBe(2)
        ->and($q->options)->toBe(['1', '1/4', '2', '0'])
        ->and($q->correct_index)->toBe(0)
        ->and($q->subject)->toBe('Math')
        ->and($q->sea_section)->toBe('Section I')
        ->and($q->is_active)->toBeTrue();
})->group('scenario:QB-06');

it('edits an existing question', function () {
    $q = PracticeQuestion::factory()->create(['prompt' => 'Old prompt']);

    Livewire::test(EditPracticeQuestion::class, ['record' => $q->id])
        ->fillForm(['prompt' => 'New prompt'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($q->fresh()->prompt)->toBe('New prompt')
        ->and($q->fresh()->options)->toBe(['3', '4', '5', '6']); // untouched options survive the round-trip
})->group('scenario:QB-07');

it('rejects a question without four options and saves nothing', function () {
    $module = SyllabusModule::factory()->create();

    Livewire::test(CreatePracticeQuestion::class)
        ->fillForm([
            'module_id' => $module->id,
            'difficulty' => 1,
            'prompt' => 'Incomplete',
            'option_1' => 'a', 'option_2' => 'b', 'option_3' => 'c', 'option_4' => null,
            'correct_option' => 1,
        ])
        ->call('create')
        ->assertHasFormErrors(['option_4']);

    expect(PracticeQuestion::count())->toBe(0);
})->group('scenario:QB-08');
