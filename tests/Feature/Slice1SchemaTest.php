<?php

use App\Models\AnchorQuestion;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

/**
 * Slice 1 verification — scenario zero (schema only).
 *
 * These run under RefreshDatabase against a fresh test DB, so they assert
 * STRUCTURE, not seeded data. The subject remap against the real 90-module
 * data was verified directly (Math + ELA, correct section counts) and is not
 * re-asserted here, because RefreshDatabase has no seeded modules to check.
 */

it('creates all Slice 1 tables', function () {
    foreach ([
        'module_prerequisites',
        'anchor_questions',
        'anchor_question_module',
        'diagnostic_sessions',
        'diagnostic_responses',
    ] as $table) {
        expect(Schema::hasTable($table))->toBeTrue("missing table: {$table}");
    }
});

it('adds a nullable onboarding_completed_at column to users', function () {
    expect(Schema::hasColumn('users', 'onboarding_completed_at'))->toBeTrue();

    $user = User::create([
        'name' => 'Test Guardian',
        'email' => 'guardian@example.test',
        'password' => 'password',
        'role' => 'guardian',
    ]);

    expect($user->onboarding_completed_at)->toBeNull()
        ->and($user->hasCompletedOnboarding())->toBeFalse();
});

it('only allows Math and ELA as subject values', function () {
    // The CHECK constraint should reject anything outside the remapped set.
    $insert = fn (string $subject) => SyllabusModule::create([
        'subject' => $subject,
        'topic' => 'Probe',
        'sea_section' => 'Section I',
        'sequence_order' => 1,
        'pacing_week' => 1,
    ]);

    expect(fn () => $insert('Math'))->not->toThrow(Exception::class);
    expect(fn () => $insert('ELA'))->not->toThrow(Exception::class);
    expect(fn () => $insert('English Editing'))
        ->toThrow(Illuminate\Database\QueryException::class);
});

it('links anchor questions to modules many-to-many', function () {
    $module = SyllabusModule::create([
        'subject' => 'Math',
        'topic' => 'Place value',
        'sea_section' => 'Section I',
        'sequence_order' => 1,
        'pacing_week' => 1,
    ]);

    $anchor = AnchorQuestion::create([
        'subject' => 'Math',
        'sea_section' => 'Section I',
        'strand' => 'Number',
        'difficulty' => 2,
        'prompt' => 'What is 3 + 4?',
        'options' => ['6', '7', '8'],
        'correct_index' => 1,
        'distractor_notes' => ['0' => 'off by one low', '2' => 'off by one high'],
        'is_active' => true,
    ]);

    $anchor->modules()->attach($module->id);

    expect($anchor->fresh()->modules)->toHaveCount(1)
        ->and($module->fresh()->anchorQuestions)->toHaveCount(1)
        ->and($anchor->options)->toBe(['6', '7', '8'])
        ->and($anchor->correct_index)->toBe(1);
});

it('links module prerequisites as a directed self-relation', function () {
    $advanced = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Long division', 'sea_section' => 'Section I',
        'sequence_order' => 2, 'pacing_week' => 2,
    ]);
    $basic = SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Subtraction', 'sea_section' => 'Section I',
        'sequence_order' => 1, 'pacing_week' => 1,
    ]);

    $advanced->prerequisites()->attach($basic->id);

    expect($advanced->fresh()->prerequisites)->toHaveCount(1)
        ->and($advanced->fresh()->prerequisites->first()->id)->toBe($basic->id)
        ->and($basic->fresh()->dependents)->toHaveCount(1);
});
