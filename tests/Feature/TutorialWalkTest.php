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

function tutorialModule(): SyllabusModule
{
    $module = SyllabusModule::factory()->create();
    PracticeQuestion::factory()->create([
        'module_id' => $module->id, 'difficulty' => 1,
        'prompt' => 'What is 2 + 2?', 'options' => ['4', '3', '5', '6'], 'correct_index' => 0,
        'explanation' => 'Add the two numbers. Two plus two is four.',
    ]);

    return $module;
}

it('the lesson offers a worked example that leads on to practice (TU-01)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    $this->actingAs($student)->get(route('practice.lesson', $module->id))
        ->assertOk()
        ->assertSee(route('practice.tutorial', $module->id))
        ->assertSee('worked example');

    Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])
        ->call('revealAll')                       // the practice CTA appears once the example is fully revealed
        ->assertSee(route('practice.walk', $module->id))
        ->assertSee('Start practising');
})->group('scenario:TU-01');

it('reveals the worked example one step at a time (TU-02)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    $c = Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])
        ->assertSet('revealed', 1);

    $stepCount = count($c->get('steps'));
    expect($stepCount)->toBeGreaterThan(1);

    $c->call('nextStep')->assertSet('revealed', 2);
    $c->call('revealAll')->assertSet('revealed', $stepCount);
})->group('scenario:TU-02');

it('never changes the student\'s progress (TU-03)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])
        ->call('nextStep')
        ->call('revealAll');

    expect(StudentProgress::count())->toBe(0);
    expect(PracticeAttempt::count())->toBe(0);
})->group('scenario:TU-03');

it('can be revisited freely with no penalty (TU-04)', function () {
    $student = User::factory()->create(['role' => 'student']);
    $module = tutorialModule();

    Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])->assertOk();
    // Revisit — still works, still no scoring.
    Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module])->assertOk();

    expect(PracticeAttempt::count())->toBe(0);
})->group('scenario:TU-04');

it('caches a generated worked example and reuses it across students', function () {
    config()->set('services.llm.key', 'test-key');
    $module = tutorialModule();
    $question = PracticeQuestion::where('module_id', $module->id)->first();

    // LLM is called at most once; the second student reuses the cache.
    $llm = Mockery::mock(LlmService::class);
    $llm->shouldReceive('complete')->once()->andReturn("First, add them.\nTwo plus two.\nThe answer is 4.");
    $this->instance(LlmService::class, $llm);

    $gen = app(WorkedExampleGenerator::class);
    $a = $gen->forQuestion($question);
    $b = $gen->forQuestion($question);   // cached — no second LLM call

    expect($a->id)->toBe($b->id);
    expect($a->source)->toBe('llm');
    expect($a->steps)->toBe(['First, add them.', 'Two plus two.', 'The answer is 4.']);
    expect(WorkedExample::count())->toBe(1);
})->group('scenario:TU-01');

it('falls back to the bank explanation when the LLM is unavailable', function () {
    config()->set('services.llm.key', '');   // no key → no LLM call
    $module = tutorialModule();
    $question = PracticeQuestion::where('module_id', $module->id)->first();

    $example = app(WorkedExampleGenerator::class)->forQuestion($question);

    expect($example->source)->toBe('explanation');
    expect($example->steps)->not->toBeEmpty();
    expect(collect($example->steps)->implode(' '))->toContain('four');
})->group('scenario:TU-01');
