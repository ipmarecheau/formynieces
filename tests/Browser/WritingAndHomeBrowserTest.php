<?php

use App\Models\User;
use App\Models\WritingPrompt;
use Illuminate\Support\Carbon;

/**
 * Browser (Playwright) verification for the screen-backed Writing Track (WR) and
 * Student Home (SH) scenarios. The schedule/gate logic (WR-06/07) is covered by
 * feature tests; here we verify the child-facing screens render and connect.
 */
beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-08-17 09:00')); // Monday — a writing day
});

function wrhStudent(): User
{
    return User::create([
        'name' => 'Maya',
        'email' => 'maya-'.uniqid().'@students.local',
        'password' => 'secret-password',
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

function wrhPrompt(): void
{
    WritingPrompt::create([
        'week_start_date' => Carbon::now()->startOfWeek()->toDateString(),
        'title' => 'The Mystery Door',
        'prompt' => 'Write a story about a door that should not have been opened.',
        'type' => 'narrative',
    ]);
}

it('WR-01/SH-05: opening the writing duty shows today\'s prompt', function () {
    $this->actingAs(wrhStudent());
    wrhPrompt();

    $page = visit('/writing');

    $page->assertNoJavascriptErrors()
        ->assertSee('The Mystery Door')
        ->assertSee('Write a story about a door that should not have been opened.');
});

it('WR-08: the writing screen renders gracefully when no prompt is available', function () {
    $this->actingAs(wrhStudent());
    // No prompt seeded — she must not be stranded on a broken screen.

    $page = visit('/writing');

    $page->assertNoJavascriptErrors()
        ->assertSee('Back to my voyage'); // she can always sail home
});

it('SH-08: a child-layer screen offers a clear way back to the Voyage', function () {
    $this->actingAs(wrhStudent());
    wrhPrompt();

    $page = visit('/writing');

    $page->assertNoJavascriptErrors()
        ->assertSee('Back to my voyage');
});
