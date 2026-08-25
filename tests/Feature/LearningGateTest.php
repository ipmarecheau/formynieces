<?php

use App\Livewire\LessonWalk;
use App\Livewire\ModuleEntry;
use App\Livewire\TutorialWalk;
use App\Models\Lesson;
use App\Models\ModuleStageCompletion;
use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\LearningGate;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

function lgStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-lg-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

/** A module that runs the gate: it has BOTH an authored lesson and worked-example (D1) content. */
function lgGatedModule(): SyllabusModule
{
    $module = SyllabusModule::factory()->create(['topic' => 'Number: Rounding']);
    Lesson::create([
        'module_id' => $module->id,
        'is_published' => true,
        'title' => 'Rounding',
        'blocks' => [['type' => 'text', 'content' => 'Round to the nearest ten.']],
    ]);
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 1]);

    return $module;
}

/**
 * LE-03 — the gated learning sequence (lesson -> worked examples -> practice). The gate applies
 * only to a module that has both a lesson and worked examples; each stage unlocks the next, and
 * completion is permanent.
 */
it('gates only a module that has both a lesson and worked examples', function () {
    $gate = app(LearningGate::class);

    expect($gate->gated(lgGatedModule()->id))->toBeTrue();

    // Lesson but no worked-example (D1) content.
    $lessonOnly = SyllabusModule::factory()->create();
    Lesson::create(['module_id' => $lessonOnly->id, 'is_published' => true, 'title' => 'x',
        'blocks' => [['type' => 'text', 'content' => 'hi']]]);
    expect($gate->gated($lessonOnly->id))->toBeFalse();

    // Worked-example content but no lesson.
    $questionOnly = SyllabusModule::factory()->create();
    PracticeQuestion::factory()->create(['module_id' => $questionOnly->id, 'difficulty' => 1]);
    expect($gate->gated($questionOnly->id))->toBeFalse();
})->group('scenario:LE-03');

it('locks worked examples until the lesson is done, and practice until worked examples are done', function () {
    $gate = app(LearningGate::class);
    $student = lgStudent('lock');
    $module = lgGatedModule();

    // Nothing completed: both later stages are locked.
    expect($gate->workedExamplesUnlocked($student->id, $module->id))->toBeFalse();
    expect($gate->practiceUnlocked($student->id, $module->id))->toBeFalse();

    // Lesson done -> worked examples open, practice still locked.
    $gate->markCompleted($student->id, $module->id, ModuleStageCompletion::STAGE_LESSON);
    expect($gate->workedExamplesUnlocked($student->id, $module->id))->toBeTrue();
    expect($gate->practiceUnlocked($student->id, $module->id))->toBeFalse();

    // Worked examples done -> practice open.
    $gate->markCompleted($student->id, $module->id, ModuleStageCompletion::STAGE_TUTORIAL);
    expect($gate->practiceUnlocked($student->id, $module->id))->toBeTrue();
})->group('scenario:LE-03');

it('never gates a module that is missing a stage — every way in stays open', function () {
    $gate = app(LearningGate::class);
    $student = lgStudent('open');

    // Worked-example content but no lesson: ungated, practice open with no completions.
    $module = SyllabusModule::factory()->create();
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 1]);

    expect($gate->workedExamplesUnlocked($student->id, $module->id))->toBeTrue();
    expect($gate->practiceUnlocked($student->id, $module->id))->toBeTrue();
})->group('scenario:LE-03');

it('records the lesson stage on lesson completion, unlocking worked examples permanently', function () {
    $student = lgStudent('lw');
    $module = lgGatedModule();

    // A single-block authored lesson completes on mount.
    Livewire::actingAs($student)->test(LessonWalk::class, ['module' => $module])
        ->assertSet('lessonComplete', true);

    expect(app(LearningGate::class)->workedExamplesUnlocked($student->id, $module->id))->toBeTrue();
    $this->assertDatabaseHas('module_stage_completions', [
        'student_id' => $student->id, 'module_id' => $module->id, 'stage' => 'lesson',
    ]);
})->group('scenario:LE-03');

it('records the tutorial stage when the worked example is fully revealed, unlocking practice', function () {
    $student = lgStudent('tw');
    $module = lgGatedModule();
    // Lesson first, or the worked-examples guard would send her back out.
    app(LearningGate::class)->markCompleted($student->id, $module->id, ModuleStageCompletion::STAGE_LESSON);

    // Walk the whole tutorial: reveal each worked example's steps, predict, then continue —
    // through all three examples until it's done and the tutorial stage is recorded.
    $tutorial = Livewire::actingAs($student)->test(TutorialWalk::class, ['module' => $module]);
    $guard = 0;
    while ($tutorial->get('phase') !== 'done' && $guard++ < 60) {
        match ($tutorial->get('phase')) {
            'walk' => $tutorial->call('nextStep'),
            'predict' => $tutorial->call('predict', 0),
            default => $tutorial->call('continueExample'), // 'reveal'
        };
    }
    $tutorial->assertSet('phase', 'done');

    expect(app(LearningGate::class)->practiceUnlocked($student->id, $module->id))->toBeTrue();
    $this->assertDatabaseHas('module_stage_completions', [
        'student_id' => $student->id, 'module_id' => $module->id, 'stage' => 'tutorial',
    ]);
})->group('scenario:LE-03');

it('exposes the lock states to the module entry', function () {
    $student = lgStudent('me');
    $module = lgGatedModule();

    Livewire::actingAs($student)->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('workedExamplesLocked', true)
        ->assertSet('practiceLocked', true);
})->group('scenario:LE-03');

/**
 * LE-06 — tapping a locked stage (or opening its link directly) never takes her into it; she is
 * sent back to the module entry with a friendly, child-language message to finish the earlier part.
 */
it('redirects a direct visit to locked practice back to the module entry with a kind message', function () {
    $student = lgStudent('06p');
    $module = lgGatedModule();   // nothing completed -> practice locked

    actingAs($student)->get(route('practice.walk', $module))
        ->assertRedirect(route('practice.enter', $module))
        ->assertSessionHas('lockMessage');
})->group('scenario:LE-06');

it('redirects a direct visit to locked worked examples back with a kind message', function () {
    $student = lgStudent('06t');
    $module = lgGatedModule();   // lesson not done -> worked examples locked

    actingAs($student)->get(route('practice.tutorial', $module))
        ->assertRedirect(route('practice.enter', $module))
        ->assertSessionHas('lockMessage');
})->group('scenario:LE-06');

it('carries the kind lock message onto the module entry after a locked-link redirect', function () {
    $student = lgStudent('06m');
    $module = lgGatedModule();

    session()->flash('lockMessage', 'Finish the lesson and worked examples first!');

    Livewire::actingAs($student)->test(ModuleEntry::class, ['module' => $module])
        ->assertSet('lockMessage', 'Finish the lesson and worked examples first!');
})->group('scenario:LE-06');

it('lets practice open once both earlier stages are done — no redirect', function () {
    $student = lgStudent('06ok');
    $module = lgGatedModule();
    $gate = app(LearningGate::class);
    $gate->markCompleted($student->id, $module->id, ModuleStageCompletion::STAGE_LESSON);
    $gate->markCompleted($student->id, $module->id, ModuleStageCompletion::STAGE_TUTORIAL);

    actingAs($student)->get(route('practice.walk', $module))->assertOk();
})->group('scenario:LE-06');
