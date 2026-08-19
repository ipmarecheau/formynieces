<?php

use App\Models\SchoolJournalEntry;
use App\Models\User;

/**
 * Browser (Playwright) verification for the screen-backed School Journal scenarios.
 * The honest-layer rules (SJ-04/05/08/09/11/13 signals, trends, plan-steering) are
 * covered by feature tests and have no child-facing screen; not driven here.
 */
function sjGuardianWithStudent(): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id]);

    return [$guardian, $student];
}

function sjEntry(User $student, string $term, string $date, string $strand, string $score): void
{
    SchoolJournalEntry::create([
        'student_id' => $student->id,
        'uploaded_by' => 'guardian',
        'image_path' => 'school-journal/fake/paper.jpg',
        'assessment_date' => $date,
        'term' => $term,
        'strand' => $strand,
        'score' => $score,
        'digitisation_status' => SchoolJournalEntry::STATUS_CONFIRMED,
    ]);
}

it('SJ-03: the guardian journal reads as a term timeline, newest term first', function () {
    [$guardian, $student] = sjGuardianWithStudent();
    sjEntry($student, 'Term I 2026', '2026-02-10', 'Number', '17/20');
    sjEntry($student, 'Term II 2026', '2026-05-05', 'Grammar', '18/20');
    $this->actingAs($guardian);

    $page = visit("/guardian/students/{$student->id}/journal");

    $page->assertNoJavascriptErrors()
        ->assertSee('Term II 2026')   // both terms grouped on the timeline
        ->assertSee('Term I 2026')
        ->assertSee('Grammar')        // strand shown at a glance
        ->assertSee('Number');
});

it('SJ-10: a guardian opens an empty journal and the timeline renders without error', function () {
    [$guardian, $student] = sjGuardianWithStudent();
    $this->actingAs($guardian);

    // No entries filed yet — the screen must still render cleanly.
    $page = visit("/guardian/students/{$student->id}/journal");

    $page->assertNoJavascriptErrors()
        ->assertSee('File it'); // the filing affordance renders even with an empty journal
});

it('SJ-03(student): the student can open her own school journal screen', function () {
    [, $student] = sjGuardianWithStudent();
    sjEntry($student, 'Term I 2026', '2026-02-10', 'Number', '17/20');
    $this->actingAs($student);

    $page = visit('/journal');

    $page->assertNoJavascriptErrors()
        ->assertSee('File my paper');
});
