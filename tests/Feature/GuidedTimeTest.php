<?php

use App\Models\StudentGuidedTime;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\GuidedTime;

use function Pest\Laravel\actingAs;

function gtStudent(string $suffix): User
{
    return User::create([
        'name' => 'Maya',
        'email' => "maya-gt-{$suffix}@test.com",
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function gtModule(): SyllabusModule
{
    return SyllabusModule::create([
        'subject' => 'Math', 'topic' => 'Number: Place Value', 'sea_section' => 'A',
        'sequence_order' => 1, 'pacing_week' => 1, 'description' => 'Understand place value.', 'resources' => [],
    ]);
}

/**
 * AG-05 — guided, LLM-tailored learning draws active time from the daily 2-hour pool;
 * practice never counts.
 */
it('records guided active time when a student beats the endpoint', function () {
    $student = gtStudent('05a');

    actingAs($student)
        ->post(route('guided-time.beat'))
        ->assertOk()
        ->assertJson(['remaining' => 7200 - GuidedTime::BEAT_SECONDS]);

    expect(app(GuidedTime::class)->usedSecondsToday($student->id))->toBe(GuidedTime::BEAT_SECONDS);
})->group('scenario:AG-05');

it('beats from guided pages but never from practice', function () {
    $student = gtStudent('05b');
    $module = gtModule();

    actingAs($student)
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSee('visibilityState', false);      // the lesson heartbeats

    actingAs($student)
        ->get(route('practice.walk', $module))
        ->assertOk()
        ->assertDontSee('visibilityState', false);   // practice never counts
})->group('scenario:AG-05');

it('locks guided pages once the daily pool is spent, but leaves practice open', function () {
    $student = gtStudent('06');
    $module = gtModule();
    StudentGuidedTime::create([
        'student_id' => $student->id, 'day' => now()->toDateString(), 'active_seconds' => 7200,
    ]);

    // Lesson is kindly locked...
    actingAs($student)
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSeeText('Practice is always open')
        ->assertDontSeeText('About this skill');   // the lesson content is hidden

    // ...but practice stays open.
    actingAs($student)
        ->get(route('practice.walk', $module))
        ->assertOk()
        ->assertDontSeeText('Practice is always open');
})->group('scenario:AG-06');

it('gates the heartbeat on visibility and recent activity, so idle time does not count', function () {
    $student = gtStudent('07');
    $module = gtModule();

    actingAs($student)
        ->get(route('practice.lesson', $module))
        ->assertOk()
        ->assertSee('visibilityState', false)   // only beats while the tab is visible
        ->assertSee('lastActive', false);        // and only while recently active (idle guard)
})->group('scenario:AG-07');
