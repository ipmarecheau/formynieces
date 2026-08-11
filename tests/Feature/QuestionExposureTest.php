<?php

use App\Livewire\PracticeWalk;
use App\Models\PracticeQuestion;
use App\Models\StudentQuestionExposure;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\QuestionExposure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('computes a stable content hash independent of option order', function () {
    $a = PracticeQuestion::hashFor('What is 2+2?', ['4', '3', '5', '6']);
    $b = PracticeQuestion::hashFor('What is 2+2?', ['6', '5', '4', '3']); // same set, shuffled

    expect($a)->toBe($b);
    expect($a)->not->toBe(PracticeQuestion::hashFor('What is 2+3?', ['4', '3', '5', '6']));
});

it('records and excludes seen questions', function () {
    $student = User::factory()->create(['role' => 'student']);
    $exposure = app(QuestionExposure::class);

    $exposure->record($student->id, 'hash-abc', 'practice');
    $exposure->record($student->id, 'hash-abc', 'practice'); // idempotent per hash

    expect($exposure->seenHashes($student->id))->toBe(['hash-abc']);
    expect(StudentQuestionExposure::where('student_id', $student->id)->count())->toBe(1);
    expect(StudentQuestionExposure::first()->seen_count)->toBe(2);
})->group('scenario:LL-18');

it('never serves the same question twice in practice', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create();

    // Two distinct D1 questions.
    PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'First', 'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'x',
    ]);
    PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'Second', 'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'y',
    ]);

    $c = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module]);

    $firstId = $c->get('question')['id'];
    $c->call('choose', 1)->call('next');       // answer + advance
    $secondId = $c->get('question')['id'];

    expect($secondId)->not->toBe($firstId);

    // Both are now recorded as seen; a further load has nothing new to serve.
    $c->call('choose', 1)->call('next');
    expect($c->get('question'))->toBeNull();

    expect(StudentQuestionExposure::where('student_id', $student->id)->count())->toBe(2);
})->group('scenario:LL-18');
