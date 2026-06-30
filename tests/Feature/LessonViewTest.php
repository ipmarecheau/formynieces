<?php

use App\Models\User;
use App\Models\SyllabusModule;
use App\Models\StudentProgress;

use function Pest\Laravel\actingAs;

it('shows the module description and human-vetted resources on the lesson page', function () {
    $student = User::factory()->create(['onboarding_completed_at' => now()]);

    $module = SyllabusModule::query()->create([
        'subject'        => 'Math',
        'topic'          => 'Number: Place Value',
        'sea_section'    => 'A',
        'sequence_order' => 1,
        'pacing_week'    => 1,
        'description'    => 'Understand the value of each digit in a whole number.',
        'resources'      => [
            ['label' => 'Place Value Video', 'url' => 'https://example.test/pv'],
        ],
    ]);

    StudentProgress::create([
        'student_id' => $student->id,
        'module_id'  => $module->id,
        'status'     => 'needs_work',
    ]);

    actingAs($student)
        ->get("/practice/{$module->id}/lesson")
        ->assertOk()
        ->assertSeeText('Understand the value of each digit in a whole number.')
        ->assertSeeText('Place Value Video');
});

it('offers a way to start practising from the lesson', function () {
    $student = User::factory()->create(['onboarding_completed_at' => now()]);

    $module = SyllabusModule::query()->create([
        'subject'        => 'Math',
        'topic'          => 'Number: Place Value',
        'sea_section'    => 'A',
        'sequence_order' => 1,
        'pacing_week'    => 1,
        'description'    => 'Understand place value.',
        'resources'      => [],
    ]);

    StudentProgress::create([
        'student_id' => $student->id,
        'module_id'  => $module->id,
        'status'     => 'needs_work',
    ]);

    actingAs($student)
        ->get("/practice/{$module->id}/lesson")
        ->assertOk()
        ->assertSee(route('practice.walk', $module));
});