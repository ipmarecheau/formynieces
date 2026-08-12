<?php

use App\Livewire\LessonWalk;
use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function leStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-le-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function leModule(): SyllabusModule
{
    return SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Place Value', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'Understand place value.', 'resources' => [],
    ]);
}

/**
 * LE-01 — a module's lesson is an interactive page authored in advance, served from stored
 * lesson content (never generated in real time).
 */
it('serves the authored interactive lesson content from storage', function () {
    $student = leStudent('01a');
    $module = leModule();
    Lesson::create([
        'module_id' => $module->id,
        'title' => 'Place value, block by block',
        'blocks' => [
            ['type' => 'text', 'content' => 'Every digit has a home called its place.'],
        ],
    ]);

    actingAs($student)
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSeeText('Place value, block by block')
        ->assertSeeText('Every digit has a home called its place.');
})->group('scenario:LE-01');

it('shows an interactive placeholder when no lesson is authored yet', function () {
    $student = leStudent('01b');
    $module = leModule();   // no Lesson row

    actingAs($student)
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSeeText('interactive lesson');   // the placeholder, not an error
})->group('scenario:LE-01');

it('steps through the lesson, gates on checks, and unlocks practice on completion', function () {
    $student = leStudent('int');
    $module = leModule();
    Lesson::where('module_id', $module->id)->delete();
    Lesson::create(['module_id' => $module->id, 'title' => 'Spelling', 'blocks' => [
        ['type' => 'text', 'content' => 'i before e, except after c.'],
        ['type' => 'check', 'question' => 'Which is right?', 'options' => ['recieve', 'receive'], 'answer' => 1],
        ['type' => 'text', 'content' => 'Now you are ready to practise.'],
    ]]);

    Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])
        ->assertSet('revealed', 1)                  // starts on the first block only
        ->assertSet('lessonComplete', false)        // practice is gated
        ->call('next')                              // reveal the check
        ->assertSet('revealed', 2)
        ->call('answerCheck', 1, 0)                 // wrong choice
        ->assertSet('lessonComplete', false)
        ->call('next')                              // gated: cannot pass an unanswered check
        ->assertSet('revealed', 2)
        ->call('answerCheck', 1, 1)                 // correct choice
        ->call('next')                              // reveal the last block
        ->assertSet('revealed', 3)
        ->assertSet('lessonComplete', true);        // worked all the way through -> practice unlocks
})->group('scenario:LE-01');

it('renders the practice CTA once the lesson is complete', function () {
    $student = leStudent('done');
    $module = leModule();
    Lesson::where('module_id', $module->id)->delete();
    Lesson::create(['module_id' => $module->id, 'title' => 'Spelling', 'blocks' => [
        ['type' => 'text', 'content' => 'The whole lesson in one short block.'],
    ]]);

    // A single-block lesson is complete as soon as it is revealed.
    Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])
        ->assertSet('lessonComplete', true)
        ->assertSee('Start practising');
})->group('scenario:LE-01');
