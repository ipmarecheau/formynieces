<?php

use App\Models\DailyPlan;
use App\Models\StudentGuidedTime;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Notifications\DailyTasksSummaryNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function pdsPair(): array
{
    $guardian = User::factory()->create(['role' => 'guardian', 'email_verified_at' => now()]);
    $student = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'onboarding_completed_at' => now(), 'welcomed_at' => now()]);

    return [$guardian, $student];
}

function pdsTarget(User $student, string $topic, string $status): void
{
    $module = SyllabusModule::factory()->create(['topic' => $topic, 'pacing_week' => 1]);
    WeeklyTarget::create(['student_id' => $student->id, 'module_id' => $module->id, 'week_start_date' => Carbon::today()->startOfWeek()->toDateString(), 'is_completed' => $status === 'mastered']);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $module->id, 'status' => $status]);
}

function pdsCompleteMinimum(User $student): void
{
    DailyPlan::create([
        'student_id' => $student->id,
        'date' => Carbon::today()->toDateString(),
        'is_writing_day' => false,
        'duties' => ['morning_tide' => true, 'map' => true],
    ]);
}

it('PN-01: emails the guardian when the student finishes the day\'s paced tasks', function () {
    Notification::fake();
    [$guardian, $student] = pdsPair();
    pdsTarget($student, 'Fractions', 'mastered');
    pdsCompleteMinimum($student);

    $this->artisan('students:daily-summary')->assertSuccessful();

    Notification::assertSentTo($guardian, DailyTasksSummaryNotification::class,
        fn ($n) => $n->reason === 'done' && $n->minimumMet === true && $n->openTopics === []);
})->group('scenario:PN-01');

it('PN-02: emails the guardian when the student goes inactive with work open', function () {
    Notification::fake();
    [$guardian, $student] = pdsPair();
    pdsTarget($student, 'Fractions', 'needs_work');

    // She engaged today, then went idle 40 minutes ago.
    $g = StudentGuidedTime::create(['student_id' => $student->id, 'day' => Carbon::today()->toDateString(), 'active_seconds' => 600]);
    $g->updated_at = now()->subMinutes(40);
    $g->save();

    $this->artisan('students:daily-summary --inactive-minutes=30')->assertSuccessful();

    Notification::assertSentTo($guardian, DailyTasksSummaryNotification::class,
        fn ($n) => $n->reason === 'inactive' && in_array('Fractions', $n->openTopics, true));
})->group('scenario:PN-02');

it('PN-01: does not email twice for the same day', function () {
    Notification::fake();
    [$guardian, $student] = pdsPair();
    pdsTarget($student, 'Fractions', 'mastered');
    pdsCompleteMinimum($student);

    $this->artisan('students:daily-summary')->assertSuccessful();
    $this->artisan('students:daily-summary')->assertSuccessful();

    Notification::assertSentToTimes($guardian, DailyTasksSummaryNotification::class, 1);
})->group('scenario:PN-01');

it('PN-02: stays quiet while the student is still active and not done', function () {
    Notification::fake();
    [$guardian, $student] = pdsPair();
    pdsTarget($student, 'Fractions', 'needs_work');
    StudentGuidedTime::create(['student_id' => $student->id, 'day' => Carbon::today()->toDateString(), 'active_seconds' => 600]); // updated just now

    $this->artisan('students:daily-summary --inactive-minutes=30')->assertSuccessful();

    Notification::assertNothingSent();
})->group('scenario:PN-02');
