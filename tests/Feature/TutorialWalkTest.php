<?php

use App\Livewire\TutorialWalk;
use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WorkedExample;
use App\Services\LlmService;
use App\Services\Practice\WorkedExampleGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/** Three D1 questions so the tutorial builds three distinct worked examples. Multi-sentence
 *  explanations give 3 fallback steps each (no LLM), so step-reveal is exercised. */
function tutorialModule(): SyllabusModule
{
    config()->set('services.llm.key', '');   // no LLM → deterministic fallback steps + remark
    $module = SyllabusModule::factory()->create();

    foreach ([['2 + 2', ['4', '3', '5'], 0], ['3 + 1', ['4', '5', '2'], 0], ['5 + 0', ['5', '6', '4'], 0]] as [$q, $opts, $ci]) {
        PracticeQuestion::factory()->create([
            'module_id' => $module->id, 'difficulty' => 1,
            'prompt' => "What is {$q}?", 'options' => $opts, 'correct_index' => $ci,
            'explanation' => 'Line up the numbers. Add the ones together. That gives the answer.',
        ]);
    }

    return $module;
}

/** Walk the current example: reveal its steps, then predict the correct answer. */
function tutorialAnswerCurrent($component): void
{
    $ex = $component->instance()->currentExample();
    while ($component->get('phase') === 'walk') {
        $component->call('nextStep');
    }
    $component->call('predict', $ex['correctIndex']);
}

it('walks three worked examples through to practice (TU-01)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    $c = Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])
        ->assertSet('exampleIndex', 0);

    expect(count($c->get('examples')))->toBe(3);

    // Example 1 and 2 lead to the next example; the third leads to practice.
    tutorialAnswerCurrent($c);
    $c->assertSet('phase', 'reveal')->assertSee('Next example')->call('continueExample')->assertSet('exampleIndex', 1);
    tutorialAnswerCurrent($c);
    $c->call('continueExample')->assertSet('exampleIndex', 2);
    tutorialAnswerCurrent($c);
    $c->assertSee('On to practice')->call('continueExample')
        ->assertSet('phase', 'done')
        ->assertSee('Start practising')
        ->assertSee(route('practice.walk', $module->id));
})->group('scenario:TU-01');

it('reveals a worked example one step at a time, then hands her the wheel (TU-02)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    $c = Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])
        ->assertSet('revealed', 1)
        ->assertSet('phase', 'walk');

    $steps = count($c->instance()->currentExample()['steps']);
    expect($steps)->toBeGreaterThan(1);

    $c->call('nextStep')->assertSet('revealed', 2);
    // Once only the final step remains, the next tap hands her the wheel (predict).
    while ($c->get('phase') === 'walk') {
        $c->call('nextStep');
    }
    $c->assertSet('phase', 'predict')->assertSee('Your turn');
})->group('scenario:TU-02');

it('reacts warmly to her guess and can be got wrong without penalty (TU-02)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    $c = Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module]);
    while ($c->get('phase') === 'walk') {
        $c->call('nextStep');
    }
    $ex = $c->instance()->currentExample();
    $wrong = $ex['correctIndex'] === 0 ? 1 : 0;

    $c->call('predict', $wrong)
        ->assertSet('phase', 'reveal')
        ->assertSet('pickedCorrect', false)
        ->assertSee('Let\'s see');     // Smooth's kind fallback reaction

    expect(PracticeAttempt::count())->toBe(0);   // never scored
})->group('scenario:TU-02');

it('never changes the student\'s progress (TU-03)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    $c = Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module]);
    tutorialAnswerCurrent($c);
    $c->call('continueExample');
    tutorialAnswerCurrent($c);

    expect(StudentProgress::count())->toBe(0);
    expect(PracticeAttempt::count())->toBe(0);
})->group('scenario:TU-03');

it('can be revisited freely with no penalty (TU-04)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])->assertOk();
    Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])->assertOk();

    expect(PracticeAttempt::count())->toBe(0);
})->group('scenario:TU-04');

it('caches a generated worked example and reuses it across students', function () {
    config()->set('services.llm.key', 'test-key');
    $module = tutorialModule();
    config()->set('services.llm.key', 'test-key');
    $question = PracticeQuestion::where('module_id', $module->id)->first();

    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('complete')->once()->andReturn("First, add them.\nTwo plus two.\nThe answer is 4.");
    $this->instance(LlmService::class, $llm);

    $gen = app(WorkedExampleGenerator::class);
    $a = $gen->forQuestion($question);
    $b = $gen->forQuestion($question);

    expect($a->id)->toBe($b->id);
    expect($a->source)->toBe('llm');
    expect($a->steps)->toBe(['First, add them.', 'Two plus two.', 'The answer is 4.']);
    expect(WorkedExample::count())->toBe(1);
})->group('scenario:TU-01');

it('falls back to the bank explanation when the LLM is unavailable', function () {
    config()->set('services.llm.key', '');
    $module = tutorialModule();
    $question = PracticeQuestion::where('module_id', $module->id)->first();

    $example = app(WorkedExampleGenerator::class)->forQuestion($question);

    expect($example->source)->toBe('explanation');
    expect($example->steps)->not->toBeEmpty();
})->group('scenario:TU-01');
