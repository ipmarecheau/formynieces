<?php

use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Support\Carbon;

/**
 * Repro for the reported bug: on the Voyage, Smooth's "How to sail" guide's
 * "Got it!" button does nothing. Checks the guide dismisses both when the tour
 * is present (welcomed, tour unseen) and when it is not (tour already seen).
 */
function vgStudent(bool $tourSeen): User
{
    $student = User::create([
        'name' => 'Amara',
        'email' => 'vg-'.uniqid().'@students.local',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
        'welcomed_at' => now(),
        'seen_guides' => $tourSeen ? ['tour'] : [],
        'tour_stage' => $tourSeen ? 'done' : 'overworld',
    ]);
    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(2)->toDateString(),
        'exam_date' => '2026-05-21',
    ]);
    SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);

    return $student;
}

it('dismisses the voyage guide with Got it! when the tour is NOT on the page', function () {
    $this->actingAs(vgStudent(tourSeen: true));

    $page = visit('/voyage');
    $page->assertSee('How to sail your Voyage')
        ->click('Got it! 🐢')
        ->assertDontSee('How to sail your Voyage')
        ->assertNoJavascriptErrors();
});

it('does not stack the how-to-sail guide over the tour on a first visit', function () {
    $this->actingAs(vgStudent(tourSeen: false));

    // While the tour is still unseen it supersedes the guide, so the guide's
    // overlay is not on the page to collide with (the reported "Got it! does
    // nothing" was the tour overlay intercepting the guide button underneath).
    $page = visit('/voyage');
    $page->assertSee('your turtle first mate')      // the tour (chapter 1) is what shows
        ->assertDontSee('How to sail your Voyage')  // the guide is not stacked under it
        ->assertNoJavascriptErrors();
});
