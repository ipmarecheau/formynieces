<?php

use App\Filament\Resources\Lessons\LessonResource;
use App\Filament\Resources\Lessons\Pages\CreateLesson;
use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function laAdmin(): User
{
    return User::create([
        'name' => 'Admin', 'email' => 'admin-la@test.com',
        'password' => bcrypt('secret'), 'role' => 'admin',
    ]);
}

function laStudent(): User
{
    return User::create([
        'name' => 'Maya', 'email' => 'maya-la@test.com',
        'password' => bcrypt('secret'), 'role' => 'student', 'onboarding_completed_at' => now(),
    ]);
}

function laModule(): SyllabusModule
{
    return SyllabusModule::factory()->create(['topic' => 'Number: Rounding', 'subject' => 'Math']);
}

/**
 * LE-05 — lessons are authored from typed interaction blocks. The Filament Builder's {type,data}
 * items flatten to the runtime block shape the student renderer reads, and round-trip back for
 * editing.
 */
it('flattens builder blocks to the runtime shape and nests them back for editing', function () {
    $builder = [
        ['type' => 'text', 'data' => ['content' => 'Round to the nearest ten.']],
        ['type' => 'check', 'data' => ['question' => 'Round 47', 'options' => ['40', '50'], 'answer' => 1, 'explain' => 'Closer to 50.']],
    ];

    $flat = LessonResource::flattenBlocks($builder);

    expect($flat)->toBe([
        ['type' => 'text', 'content' => 'Round to the nearest ten.'],
        ['type' => 'check', 'question' => 'Round 47', 'options' => ['40', '50'], 'answer' => 1, 'explain' => 'Closer to 50.'],
    ]);
    expect(LessonResource::nestBlocks($flat))->toBe($builder);
})->group('scenario:LE-05');

it('authors a lesson from blocks and stores it in the runtime shape', function () {
    $admin = laAdmin();
    $module = laModule();

    Livewire::actingAs($admin)->test(CreateLesson::class)
        ->fillForm([
            'module_id' => $module->id,
            'title' => 'Rounding, block by block',
            'is_published' => true,
            'blocks' => [
                ['type' => 'text', 'data' => ['content' => 'Round to the nearest ten.']],
                ['type' => 'check', 'data' => ['question' => 'Round 47', 'options' => ['40', '50'], 'answer' => 1, 'explain' => 'Closer to 50.']],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $lesson = Lesson::where('title', 'Rounding, block by block')->first();
    expect($lesson)->not->toBeNull();
    expect($lesson->is_published)->toBeTrue();
    expect($lesson->blocks[0])->toMatchArray(['type' => 'text', 'content' => 'Round to the nearest ten.']);
    expect($lesson->blocks[1]['type'])->toBe('check');
    expect($lesson->blocks[1]['question'])->toBe('Round 47');
    expect($lesson->blocks[1]['options'])->toBe(['40', '50']);
})->group('scenario:LE-05');

it('serves an authored lesson to a student who opens it', function () {
    $module = laModule();
    Lesson::create([
        'module_id' => $module->id,
        'title' => 'Rounding, block by block',
        'is_published' => true,
        'blocks' => [['type' => 'text', 'content' => 'Round to the nearest ten.']],
    ]);

    actingAs(laStudent())
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSeeText('Rounding, block by block')
        ->assertSeeText('Round to the nearest ten.');
})->group('scenario:LE-05');
