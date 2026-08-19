<?php

use App\Models\StudentJourney;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use Illuminate\Support\Carbon;

/**
 * Browser (Playwright) verification for the screen-backed Guardian Dashboard
 * scenarios. GD-04/08 (triage weighting, drill-down buckets) render on the
 * separate progress screen and are covered by their own feature tests.
 */
function gdBrowserGuardianWithStudent(string $studentName = 'Amara Okafor'): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student = User::factory()->create([
        'role' => 'student',
        'parent_id' => $guardian->id,
        'name' => $studentName,
    ]);

    StudentJourney::create([
        'student_id' => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date' => Carbon::parse('2026-05-21')->toDateString(),
    ]);

    $math = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1]);
    SyllabusModule::factory()->create(['subject' => 'ELA', 'pacing_week' => 1]);

    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $math->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed' => false,
    ]);

    return compact('guardian', 'student');
}

it('GD-07: the dashboard is headed by the child it is about', function () {
    ['guardian' => $guardian] = gdBrowserGuardianWithStudent('Amara Okafor');
    $this->actingAs($guardian);

    $page = visit('/guardian/dashboard');

    $page->assertNoJavascriptErrors()
        ->assertSee('Amara Okafor')  // headed by the student's name
        ->assertSee('This week');    // and the week it covers
});

it('GD-09: the guardian can grant a reward from the dashboard', function () {
    ['guardian' => $guardian] = gdBrowserGuardianWithStudent();
    $this->actingAs($guardian);

    $page = visit('/guardian/dashboard');

    $page->assertNoJavascriptErrors()
        ->assertSee('Grant a reward');  // an honest-layer control lives here
});

it('GD-11: every pace figure is labelled so it cannot be misread', function () {
    ['guardian' => $guardian] = gdBrowserGuardianWithStudent();
    $this->actingAs($guardian);

    $page = visit('/guardian/dashboard');

    // Numbers state what they count and carry exam-weight labels, never bare.
    $page->assertNoJavascriptErrors()
        ->assertSee('% of the exam')
        ->assertSee("This week's target");
});
