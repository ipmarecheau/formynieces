<?php

use App\Livewire\SyllabusMap;
use App\Models\Lesson;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Lessons\LessonImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows objective coverage, lesson status, questions and the student\'s own progress', function () {
    $student = User::factory()->create(['role' => 'student', 'onboarding_completed_at' => now()]);
    $module = SyllabusModule::factory()->create([
        'subject' => 'Math', 'topic' => 'Measurement: Area of Squares and Rectangles',
        'code' => 'MATH-900', 'sequence_order' => 1,
    ]);
    Lesson::create([
        'module_id' => $module->id, 'title' => 'Area', 'is_published' => true,
        'blocks' => [['type' => 'text', 'content' => 'x']],
        'objectives_direct' => ['MATH-900'], 'objectives_indirect' => ['MATH-901'],
    ]);
    PracticeQuestion::factory()->count(3)->create(['module_id' => $module->id, 'difficulty' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => 'mastered']);

    Livewire::actingAs($student)->test(SyllabusMap::class)
        ->assertSee('Syllabus coverage')
        ->assertSee('Measurement')                 // strand
        ->assertSee('Area of Squares and Rectangles')
        ->assertSee('MATH-900')                     // objective code
        ->assertSee('lesson')                       // has a lesson
        ->assertSee('Mastered')                     // this student's progress
        ->assertSee('On your Voyage');              // deep-link for the student's own view
});

it('shows a guardian their child\'s progress with no launch link', function () {
    $guardian = User::factory()->create(['role' => 'guardian']);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);
    $module = SyllabusModule::factory()->create(['subject' => 'ELA', 'topic' => 'Grammar: Parts of Speech', 'code' => 'ELA-900', 'sequence_order' => 1]);
    StudentProgress::create(['student_id' => $child->id, 'module_id' => $module->id, 'status' => 'needs_work']);

    Livewire::actingAs($guardian)->test(SyllabusMap::class)
        ->assertSee($child->name)
        ->assertSee('Working on')
        ->assertDontSee('On your Voyage');          // guardians view only, never launch
});

it('imports objective codes onto a lesson bundle', function () {
    $module = SyllabusModule::factory()->create(['code' => 'MATH-777']);
    $json = json_encode([[
        'module' => 'MATH-777', 'title' => 'T', 'is_published' => true,
        'objectives_direct' => ['MATH-777'], 'objectives_indirect' => ['MATH-776'],
        'blocks' => [['type' => 'text', 'content' => 'hi']],
    ]]);

    app(LessonImporter::class)->import($json);

    $lesson = Lesson::where('module_id', $module->id)->first();
    expect($lesson->objectives_direct)->toBe(['MATH-777'])
        ->and($lesson->objectives_indirect)->toBe(['MATH-776']);
});

it('defaults a lesson\'s direct objective to its own module code when none is given', function () {
    $module = SyllabusModule::factory()->create(['code' => 'MATH-778']);
    $json = json_encode([[
        'module' => 'MATH-778', 'title' => 'T', 'is_published' => true,
        'blocks' => [['type' => 'text', 'content' => 'hi']],
    ]]);

    app(LessonImporter::class)->import($json);

    expect(Lesson::where('module_id', $module->id)->first()->objectives_direct)->toBe(['MATH-778']);
});
