<?php

use App\Models\PracticeQuestion;
use App\Models\SyllabusModule;
use Database\Seeders\PracticeQuestionSeeder;
use Database\Seeders\SyllabusModuleSeeder;

/**
 * The practice bank seeder is NON-DESTRUCTIVE and idempotent: re-seeding never deletes existing
 * questions (so a separately-imported bank survives) and never duplicates the yaml's own content.
 */
beforeEach(function () {
    $this->seed(SyllabusModuleSeeder::class);
});

it('never deletes questions it did not author when re-seeding', function () {
    $module = SyllabusModule::first();

    // A question that is NOT in the yaml bank (e.g. a Moodle admin import).
    $sentinel = PracticeQuestion::create([
        'module_id' => $module->id,
        'subject' => $module->subject,
        'sea_section' => 'Section I',
        'difficulty' => 1,
        'prompt' => 'A uniquely-worded imported question that the yaml never contains.',
        'options' => ['a', 'b', 'c', 'd'],
        'correct_index' => 0,
        'explanation' => 'Imported elsewhere.',
        'is_active' => true,
    ]);

    $this->seed(PracticeQuestionSeeder::class);

    // The imported question is untouched by the reseed.
    expect(PracticeQuestion::whereKey($sentinel->id)->exists())->toBeTrue();
});

it('is idempotent — re-seeding does not change the question count', function () {
    $this->seed(PracticeQuestionSeeder::class);
    $afterFirst = PracticeQuestion::count();

    $this->seed(PracticeQuestionSeeder::class);
    $afterSecond = PracticeQuestion::count();

    expect($afterSecond)->toBe($afterFirst)
        ->and($afterFirst)->toBeGreaterThan(0);
});
