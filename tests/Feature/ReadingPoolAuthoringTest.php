<?php

use App\Filament\Resources\ReadingPassages\Pages\CreateReadingPassage;
use App\Models\ReadingPassage;
use App\Models\User;
use App\Services\Reading\DailyReadingService;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

function rpAdmin(): User
{
    return User::create([
        'name' => 'Admin', 'email' => 'admin-rp@test.com',
        'password' => bcrypt('secret'), 'role' => 'admin',
    ]);
}

function rpStudent(int $level): User
{
    return User::create([
        'name' => 'Maya', 'email' => 'maya-rp@test.com',
        'password' => bcrypt('secret'), 'role' => 'student',
        'reading_level' => $level, 'onboarding_completed_at' => now(),
    ]);
}

/**
 * DR-06 — an admin stocks the level-keyed reading pool in advance: each passage
 * is stored with its reading level and its comprehension questions, and becomes
 * available to be served on future mornings.
 */
it('stores an authored passage with its level and questions', function () {
    $admin = rpAdmin();

    Livewire::actingAs($admin)->test(CreateReadingPassage::class)
        ->fillForm([
            'title' => 'The Lighthouse Keeper',
            'reading_level' => 4,
            'is_active' => true,
            'body' => 'The keeper climbed the winding stairs each night to light the great lamp.',
            'word_count' => 13,
            'questions' => [
                ['prompt' => 'Where did the keeper climb?', 'type' => 'mc', 'options' => ['stairs', 'a hill'], 'correct_index' => 0],
                ['prompt' => 'Why does the lighthouse matter?', 'type' => 'written'],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(ReadingPassage::class, [
        'title' => 'The Lighthouse Keeper',
        'reading_level' => 4,
        'is_active' => true,
    ]);

    $passage = ReadingPassage::where('title', 'The Lighthouse Keeper')->first();
    expect($passage->questions)->toHaveCount(2)
        ->and($passage->questions[0]['prompt'])->toBe('Where did the keeper climb?')
        ->and($passage->questions[0]['options'])->toBe(['stairs', 'a hill']);
})->group('scenario:DR-06');

it('makes an authored passage available to be served on future mornings', function () {
    $admin = rpAdmin();

    Livewire::actingAs($admin)->test(CreateReadingPassage::class)
        ->fillForm([
            'title' => 'A Morning at Sea',
            'reading_level' => 4,
            'is_active' => true,
            'body' => 'Gulls wheeled over the harbour as the fishing boats returned with the dawn catch.',
            'word_count' => 14,
            'questions' => [
                ['prompt' => 'What returned at dawn?', 'type' => 'mc', 'options' => ['boats', 'trains'], 'correct_index' => 0],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $student = rpStudent(level: 4);
    $assignment = app(DailyReadingService::class)->serve($student);

    expect($assignment)->not->toBeNull()
        ->and($assignment->passage->title)->toBe('A Morning at Sea');
})->group('scenario:DR-06');
