<?php

use App\Livewire\LessonWalk;
use App\Models\Lesson;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;

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

/**
 * LE-02 — the lesson is scaffolding, never an assessment: working all the way through it
 * (including answering its inline checks) leaves her module progress and mastery status
 * exactly as they were.
 */
it('leaves her module progress and mastery status unchanged when she completes the lesson', function () {
    $student = leStudent('02a');
    $module = leModule();
    Lesson::where('module_id', $module->id)->delete();
    Lesson::create(['module_id' => $module->id, 'title' => 'Spelling', 'blocks' => [
        ['type' => 'text', 'content' => 'i before e, except after c.'],
        ['type' => 'check', 'question' => 'Which is right?', 'options' => ['recieve', 'receive'], 'answer' => 1],
        ['type' => 'text', 'content' => 'Now you are ready to practise.'],
    ]]);

    // She is mid-climb before opening the lesson: a real, non-trivial progress state.
    $progress = StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'needs_work',
        'score' => 33,
        'previous_score' => 11,
        'current_rung' => 2,
        'current_streak' => 1,
        'streak_question_ids' => [7],
    ]);
    // Snapshot every column as stored (a fresh DB read, so all columns are present).
    $before = $progress->fresh()->getAttributes();

    // Work all the way through the lesson, answering the inline check correctly.
    Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])
        ->call('next')                  // reveal the check
        ->call('answerCheck', 1, 1)     // answer it correctly
        ->call('next')                  // reveal the last block
        ->assertSet('lessonComplete', true);

    // Every persisted attribute is byte-for-byte what it was before the lesson.
    expect(StudentProgress::where('student_id', $student->id)
        ->where('module_id', $module->id)
        ->first()
        ->getAttributes())->toEqual($before);
})->group('scenario:LE-02');

it('creates no progress row when she completes a lesson for a module she has not started', function () {
    $student = leStudent('02b');
    $module = leModule();   // no StudentProgress row for this student+module
    Lesson::where('module_id', $module->id)->delete();
    Lesson::create(['module_id' => $module->id, 'title' => 'One block', 'blocks' => [
        ['type' => 'text', 'content' => 'The whole lesson in one short block.'],
    ]]);

    // A single-block lesson completes on mount; completing it must seed nothing.
    Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])
        ->assertSet('lessonComplete', true);

    assertDatabaseMissing('student_progress', [
        'student_id' => $student->id,
        'module_id' => $module->id,
    ]);
})->group('scenario:LE-02');
