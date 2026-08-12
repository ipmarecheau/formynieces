<?php

use App\Models\Lesson;
use App\Models\SyllabusModule;
use App\Models\User;

use function Pest\Laravel\actingAs;

function leStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-le-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function leModule(): SyllabusModule
{
    return SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Place Value', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'Understand place value.', 'resources' => [],
    ]);
}

/**
 * LE-01 — a module's lesson is an interactive page authored in advance, served from stored
 * lesson content (never generated in real time).
 */
it('serves the authored interactive lesson content from storage', function () {
    $student = leStudent('01a');
    $module = leModule();
    Lesson::create([
        'module_id' => $module->id,
        'title' => 'Place value, block by block',
        'blocks' => [
            ['type' => 'text', 'content' => 'Every digit has a home called its place.'],
        ],
    ]);

    actingAs($student)
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSeeText('Place value, block by block')
        ->assertSeeText('Every digit has a home called its place.');
})->group('scenario:LE-01');

it('shows an interactive placeholder when no lesson is authored yet', function () {
    $student = leStudent('01b');
    $module = leModule();   // no Lesson row

    actingAs($student)
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSeeText('interactive lesson');   // the placeholder, not an error
})->group('scenario:LE-01');
