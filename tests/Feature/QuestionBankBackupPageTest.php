<?php

use App\Filament\Resources\PracticeQuestions\Pages\ListPracticeQuestions;
use App\Filament\Resources\QuestionBankBackups\Pages\ListQuestionBankBackups;
use App\Models\PracticeQuestion;
use App\Models\QuestionBankBackup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

it('offers back-up and delete-all actions on the question bank when questions exist', function () {
    PracticeQuestion::factory()->create();

    Livewire::test(ListPracticeQuestions::class)
        ->assertActionExists('backupNow')
        ->assertActionExists('deleteAll')
        ->assertActionExists('export');
});

it('lists backups with a restore action for an admin', function () {
    $backup = QuestionBankBackup::create(['reason' => 'daily', 'question_count' => 12, 'path' => 'backups/question-bank/x.json']);

    Livewire::test(ListQuestionBankBackups::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$backup])
        ->assertTableActionExists('restore');
});
