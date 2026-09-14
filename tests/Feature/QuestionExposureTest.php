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

it('does not burn a question on serve — only once it is answered', function () {
    // Regression for the production dead-end: a student was SERVED every D1 question of a
    // topic (each serve recorded an exposure) but never answered one, so the global no-repeat
    // rule excluded them all and practice showed "more practice coming soon" with 20 available.
    $student = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create();
    PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'Only D1', 'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'x',
    ]);

    $c = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module]);

    // Served — but NOT answered — records nothing, and re-loading still serves it.
    expect($c->get('question'))->not->toBeNull();
    expect(StudentQuestionExposure::where('student_id', $student->id)->count())->toBe(0);

    $c->call('next'); // navigate away / re-mount without answering
    expect($c->get('question'))->not->toBeNull();
    expect(StudentQuestionExposure::where('student_id', $student->id)->count())->toBe(0);

    // Answering it is what marks it seen.
    $c->call('choose', 1);
    expect(StudentQuestionExposure::where('student_id', $student->id)->count())->toBe(1);
})->group('scenario:LL-18');

it('does not let tutorial exposures gate practice selection', function () {
    // The tutorial shows D1 practice questions as worked examples and records them with
    // context 'tutorial'. Those teaching views must never consume the practice pool
    // (the bug that dead-ended a student on "more practice coming soon").
    $student = User::factory()->create(['role' => 'student']);
    $module = SyllabusModule::factory()->create();

    $q = PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'Only D1', 'options' => ['A', 'B', 'C', 'D'], 'correct_index' => 1, 'explanation' => 'x',
    ]);

    // The whole D1 pool has been shown in the tutorial.
    app(QuestionExposure::class)->record($student->id, $q->content_hash, 'tutorial');

    // Practice must still serve it — a tutorial view is not a practice repeat.
    $c = Livewire::actingAs($student)->test(PracticeWalk::class, ['module' => $module]);
    expect($c->get('question'))->not->toBeNull();
    expect($c->get('question')['id'])->toBe($q->id);
})->group('scenario:LL-18');

it('seenHashes can exclude a context', function () {
    $student = User::factory()->create(['role' => 'student']);
    $exposure = app(QuestionExposure::class);

    $exposure->record($student->id, 'hash-practice', 'practice');
    $exposure->record($student->id, 'hash-tutorial', 'tutorial');

    expect($exposure->seenHashes($student->id))->toContain('hash-practice', 'hash-tutorial');
    expect($exposure->seenHashes($student->id, excludeContexts: ['tutorial']))
        ->toBe(['hash-practice']);
})->group('scenario:LL-18');
