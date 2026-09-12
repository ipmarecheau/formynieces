<?php

use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Lessons\LessonImporter;

/**
 * Create the ELA-001 module and import the real edited bundle through the real importer,
 * so the walk renders exactly what shipped (including the new sample_answers field).
 */
function importEditedEla001(): SyllabusModule
{
    $module = SyllabusModule::query()->create([
        'code' => 'ELA-001',
        'subject' => 'ELA',
        'topic' => 'Making tricky plurals',
        'sea_section' => 'A',
        'sequence_order' => 1,
        'pacing_week' => 1,
        'description' => 'Plurals for words ending in y, f, and hissing sounds.',
    ]);

    app(LessonImporter::class)->import(
        (string) file_get_contents(database_path('data/lessons/ela-001.json'))
    );

    return $module;
}

/**
 * Browser (Playwright) regression check for the sample_answers authoring pass.
 *
 * sample_answers is authored onto every reasoning block but is not yet rendered by any
 * screen (it is the LLM-judge fallback). So this test does NOT assert the field appears;
 * it proves the edited + re-imported bundles still render in the real student renderer
 * with no JavaScript/console errors — i.e. the added field broke nothing in the walk.
 */
it('renders an edited lesson in admin preview with no console errors', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $module = importEditedEla001();

    $this->actingAs($admin);

    visit(route('admin.lessons.preview', $module))
        ->assertNoJavascriptErrors()
        ->assertSee('plural');
});

it('renders the re-teach preview of an edited lesson with no console errors', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $module = importEditedEla001();

    $this->actingAs($admin);

    visit(route('admin.lessons.preview-reteach', $module))
        ->assertNoJavascriptErrors();
});
