<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use App\Services\Practice\PracticeQuestions;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns a module\'s active questions easiest-first and excludes inactive ones', function () {
    $module = SyllabusModule::factory()->create();
    $other  = SyllabusModule::factory()->create();

    // Out of order on purpose: difficulty 3, 1, 2 — resolver must sort to 1,2,3.
    $hard   = PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 3]);
    $easy   = PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 1]);
    $medium = PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 2]);

    // Noise that must NOT appear: inactive question, and another module's question.
    PracticeQuestion::factory()->create(['module_id' => $module->id, 'difficulty' => 1, 'is_active' => false]);
    PracticeQuestion::factory()->create(['module_id' => $other->id,  'difficulty' => 1]);

    $resolved = (new PracticeQuestions())->forModule($module->id);

    expect($resolved->pluck('id')->all())
        ->toBe([$easy->id, $medium->id, $hard->id]);

    expect((new PracticeQuestions())->countForModule($module->id))->toBe(3);
});