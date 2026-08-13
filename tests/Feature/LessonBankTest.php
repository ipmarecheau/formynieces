<?php

use App\Filament\Pages\LessonImportGuide;
use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Lessons\LessonExporter;
use App\Services\Lessons\LessonImporter;
use App\Support\LessonBlockSchema;
use Database\Seeders\LessonSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Livewire\Livewire;

function lbBundle(array $lessons): string
{
    return (string) json_encode($lessons);
}

/** Foundation — every module gets a unique, stable, per-subject code the import binds to. */
it('assigns stable per-subject module codes when seeding', function () {
    (new SyllabusModuleSeeder)->run();

    expect(SyllabusModule::where('subject', 'Math')->orderBy('sequence_order')->first()->code)->toBe('MATH-001');
    expect(SyllabusModule::where('subject', 'ELA')->orderBy('sequence_order')->first()->code)->toBe('ELA-001');
    expect(SyllabusModule::whereNotNull('code')->distinct()->count('code'))->toBe(SyllabusModule::count());
})->group('scenario:LB-01');

/**
 * LB-01 — import a JSON bundle: upsert by module code, blocks intact, re-import updates not
 * duplicates, invalid entries skipped + reported, preview writes nothing.
 */
it('imports a bundle, storing each lesson for its module by code', function () {
    $module = SyllabusModule::factory()->create(['code' => 'MATH-001']);
    $json = lbBundle([[
        'module' => 'MATH-001', 'title' => 'Rounding', 'is_published' => true,
        'blocks' => [['type' => 'text', 'content' => 'Hello'], ['type' => 'check', 'question' => 'Q', 'options' => ['a', 'b'], 'answer' => 1]],
    ]]);

    $result = app(LessonImporter::class)->import($json);

    expect($result['created'])->toBe(1);
    $lesson = Lesson::where('module_id', $module->id)->first();
    expect($lesson->title)->toBe('Rounding');
    expect($lesson->blocks[0]['content'])->toBe('Hello');
})->group('scenario:LB-01');

it('re-imports as an update, never a duplicate', function () {
    $module = SyllabusModule::factory()->create(['code' => 'MATH-001']);
    $json = lbBundle([['module' => 'MATH-001', 'title' => 'v1', 'blocks' => [['type' => 'text', 'content' => 'a']]]]);
    app(LessonImporter::class)->import($json);

    $json2 = lbBundle([['module' => 'MATH-001', 'title' => 'v2', 'blocks' => [['type' => 'text', 'content' => 'b']]]]);
    $result = app(LessonImporter::class)->import($json2);

    expect($result['updated'])->toBe(1);
    expect(Lesson::where('module_id', $module->id)->count())->toBe(1);
    expect(Lesson::where('module_id', $module->id)->first()->title)->toBe('v2');
})->group('scenario:LB-01');

it('skips and reports unknown module codes and invalid blocks', function () {
    SyllabusModule::factory()->create(['code' => 'MATH-001']);
    $json = lbBundle([
        ['module' => 'NOPE-999', 'title' => 'x', 'blocks' => [['type' => 'text', 'content' => 'hi']]],
        ['module' => 'MATH-001', 'title' => 'y', 'blocks' => [['type' => 'check', 'question' => 'Q', 'options' => ['a'], 'answer' => 5]]],
    ]);

    $result = app(LessonImporter::class)->import($json);

    expect($result['skipped'])->toBe(2);
    expect($result['created'])->toBe(0);
    expect(Lesson::count())->toBe(0);
    expect(collect($result['lessons'])->flatMap(fn ($l) => $l['errors']))->not->toBeEmpty();
})->group('scenario:LB-01');

it('previews an import without saving', function () {
    SyllabusModule::factory()->create(['code' => 'MATH-001']);
    $json = lbBundle([['module' => 'MATH-001', 'title' => 'Rounding', 'blocks' => [['type' => 'text', 'content' => 'Hi']]]]);

    $result = app(LessonImporter::class)->preview($json);

    expect($result['created'])->toBe(1);   // would create
    expect(Lesson::count())->toBe(0);        // but wrote nothing
})->group('scenario:LB-01');

/** LB-02 — export dumps the importer format and round-trips. */
it('exports lessons in the importer format and re-imports identically', function () {
    $module = SyllabusModule::factory()->create(['code' => 'ELA-001']);
    Lesson::create(['module_id' => $module->id, 'title' => 'Plurals', 'is_published' => true, 'blocks' => [['type' => 'text', 'content' => 'Add s']]]);

    $json = app(LessonExporter::class)->exportAll();
    $decoded = json_decode($json, true);
    expect($decoded[0]['module'])->toBe('ELA-001');
    expect($decoded[0]['title'])->toBe('Plurals');

    Lesson::query()->delete();
    app(LessonImporter::class)->import($json);

    $lesson = Lesson::where('module_id', $module->id)->first();
    expect($lesson->title)->toBe('Plurals');
    expect($lesson->blocks[0]['content'])->toBe('Add s');
})->group('scenario:LB-02');

/** LB-03 — seed the version-controlled bundles idempotently. */
it('seeds repository lessons and is idempotent', function () {
    SyllabusModule::factory()->create(['code' => 'MATH-003']);   // the shipped example binds to MATH-003

    (new LessonSeeder(app(LessonImporter::class)))->run();
    $count = Lesson::whereHas('module', fn ($q) => $q->where('code', 'MATH-003'))->count();
    expect($count)->toBe(1);

    (new LessonSeeder(app(LessonImporter::class)))->run();
    expect(Lesson::whereHas('module', fn ($q) => $q->where('code', 'MATH-003'))->count())->toBe(1);
})->group('scenario:LB-03');

/** LB-04 — the guide lists block types, and its template is itself a valid importable bundle. */
it('the import guide lists every block type', function () {
    $admin = User::create(['name' => 'Admin', 'email' => 'admin-lb@test.com', 'password' => bcrypt('x'), 'role' => 'admin']);

    Livewire::actingAs($admin)->test(LessonImportGuide::class)
        ->assertOk()
        ->assertSee('fillblank')
        ->assertSee('matchpairs')
        ->assertSee('ordersteps');
})->group('scenario:LB-04');

it('offers a template that is a valid importable bundle covering every block type', function () {
    SyllabusModule::factory()->create(['code' => 'MATH-001']);   // the template binds to MATH-001
    $template = (new LessonImportGuide)->sampleJson();

    // Every block type appears in the template.
    foreach (LessonBlockSchema::types() as $type) {
        expect($template)->toContain('"type": "'.$type.'"');
    }

    $result = app(LessonImporter::class)->import($template);
    expect($result['created'])->toBe(1);
    expect($result['skipped'])->toBe(0);
})->group('scenario:LB-04');
