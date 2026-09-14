<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('re-seeding modules preserves student progress and module ids', function () {
    // Seed once, then attach a student's mastered progress to a real module.
    (new SyllabusModuleSeeder)->run();

    $module = SyllabusModule::where('code', 'MATH-006')->firstOrFail();
    $originalId = $module->id;

    $student = User::factory()->create(['role' => 'student']);
    StudentProgress::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'status' => 'mastered',
        'mastered_at' => now(),
    ]);

    // Re-seed (as a production deploy would). This must NOT truncate/cascade.
    (new SyllabusModuleSeeder)->run();

    // Module id is unchanged (upsert by code, not recreate)...
    expect(SyllabusModule::where('code', 'MATH-006')->value('id'))->toBe($originalId);

    // ...and the student's progress still exists and is still mastered.
    $progress = StudentProgress::where('student_id', $student->id)
        ->where('module_id', $originalId)
        ->first();
    expect($progress)->not->toBeNull();
    expect($progress->status)->toBe('mastered');
})->group('scenario:LB-03');

it('re-seeding does not duplicate modules', function () {
    (new SyllabusModuleSeeder)->run();
    $first = SyllabusModule::count();

    (new SyllabusModuleSeeder)->run();

    expect(SyllabusModule::count())->toBe($first);
})->group('scenario:LB-03');
