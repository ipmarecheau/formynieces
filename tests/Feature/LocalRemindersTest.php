<?php

use App\Models\DailyPlan;
use App\Models\User;
use App\Services\Reminders\ReminderScheduleBuilder;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

/*
 * Behaviour coverage for formynieces-spec/features/local_reminders.feature (LR-01..07).
 * The builder is pure computation, so these run fully offline. Each test is tagged with
 * the scenario id it realises for `specs:trace`.
 */

function remindersFor(User $student, ?CarbonImmutable $from = null, int $days = 7): array
{
    return app(ReminderScheduleBuilder::class)->forStudent($student, $from, $days);
}

function typesOf(array $reminders): array
{
    return array_map(fn ($r) => $r->type, $reminders);
}

it('serves a self-contained reminder plan the app can schedule, with stable ids', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)
        ->getJson(route('reminders.feed'))
        ->assertOk()
        ->assertJsonStructure(['reminders' => [['id', 'type', 'at', 'title', 'body', 'conditional']]]);

    expect($response->json('reminders'))->not->toBeEmpty();

    // LR-01: building twice yields the same ids, so re-scheduling replaces rather than duplicates.
    $a = remindersFor($student);
    $b = remindersFor($student);
    expect(array_map(fn ($r) => $r->id(), $a))->toBe(array_map(fn ($r) => $r->id(), $b));

    // Guests cannot read a child's plan.
    auth()->logout();
    $this->get(route('reminders.feed'))->assertRedirect(route('login'));
})->group('scenario:LR-01');

it('schedules a Morning Muster every day', function () {
    $student = User::factory()->create(['role' => 'student']);

    $muster = array_filter(remindersFor($student, CarbonImmutable::parse('2026-11-16'), 7),
        fn ($r) => $r->type === 'morning_muster');

    expect($muster)->toHaveCount(7);
})->group('scenario:LR-02');

it('schedules a Writing Mission only on Mon, Wed and Fri', function () {
    $student = User::factory()->create(['role' => 'student']);

    // A full week starting Monday 2026-11-16.
    $writing = array_values(array_filter(remindersFor($student, CarbonImmutable::parse('2026-11-16'), 7),
        fn ($r) => $r->type === 'writing_mission'));

    expect($writing)->toHaveCount(3);
    foreach ($writing as $r) {
        expect(in_array($r->fireAt->dayOfWeekIso, [1, 3, 5], true))->toBeTrue();
        expect($r->fireAt->format('H:i'))->toBe('16:00');
    }
})->group('scenario:LR-03');

it('only nudges about outstanding tasks when the day is unfinished', function () {
    Carbon::setTestNow('2026-11-16 09:00:00'); // a Monday
    $student = User::factory()->create(['role' => 'student']);

    // Unfinished today -> today has the outstanding-tasks reminder.
    DailyPlan::create([
        'student_id' => $student->id,
        'date' => '2026-11-16',
        'is_writing_day' => true,
        'duties' => ['map' => false],
    ]);

    $todayStr = CarbonImmutable::parse('2026-11-16')->toDateString();
    $outstandingToday = array_filter(remindersFor($student, CarbonImmutable::parse('2026-11-16'), 2),
        fn ($r) => $r->type === 'outstanding_tasks' && $r->fireAt->toDateString() === $todayStr);
    expect($outstandingToday)->toHaveCount(1);

    // Finish the day -> today's outstanding-tasks reminder disappears (but tomorrow's remains).
    DailyPlan::where('student_id', $student->id)->update(['duties' => ['map' => true]]);
    $all = remindersFor($student, CarbonImmutable::parse('2026-11-16'), 2);
    $outstandingToday = array_filter($all, fn ($r) => $r->type === 'outstanding_tasks' && $r->fireAt->toDateString() === $todayStr);
    $outstandingTomorrow = array_filter($all, fn ($r) => $r->type === 'outstanding_tasks' && $r->fireAt->toDateString() !== $todayStr);
    expect($outstandingToday)->toBeEmpty();
    expect($outstandingTomorrow)->toHaveCount(1);
})->group('scenario:LR-04');

it('only schedules the Streak-Saver when the daily minimum is unmet', function () {
    Carbon::setTestNow('2026-11-16 09:00:00');
    $student = User::factory()->create(['role' => 'student']);
    $todayStr = CarbonImmutable::parse('2026-11-16')->toDateString();

    // Minimum met today -> no streak-saver today.
    DailyPlan::create([
        'student_id' => $student->id,
        'date' => '2026-11-16',
        'is_writing_day' => false,
        'duties' => ['map' => true],
    ]);

    $streakToday = array_filter(remindersFor($student, CarbonImmutable::parse('2026-11-16'), 1),
        fn ($r) => $r->type === 'streak_saver' && $r->fireAt->toDateString() === $todayStr);
    expect($streakToday)->toBeEmpty();
})->group('scenario:LR-05');

it('respects guardian opt-out and never schedules inside quiet hours', function () {
    // Opt-out -> nothing at all.
    $optedOut = User::factory()->create(['role' => 'student', 'reminders_opt_out' => true]);
    expect(remindersFor($optedOut))->toBeEmpty();

    // Quiet hours covering the evening -> the 19:00 and 20:30 reminders are suppressed.
    $quiet = User::factory()->create([
        'role' => 'student',
        'quiet_hours_start' => '18:30',
        'quiet_hours_end' => '07:00',
    ]);
    $types = typesOf(remindersFor($quiet, CarbonImmutable::parse('2026-11-16'), 1));
    expect($types)->not->toContain('outstanding_tasks');
    expect($types)->not->toContain('streak_saver');
    expect($types)->toContain('morning_muster'); // 15:30 is before quiet hours — kept
})->group('scenario:LR-06');

it('renders a native-gated bridge that reads the feed and schedules on the device', function () {
    $html = view('partials.reminders-bridge')->render();

    // Native-only and plugin-guarded, so it is a safe no-op on the web.
    expect($html)->toContain('isNativePlatform');
    expect($html)->toContain('LocalNotifications');
    // Reads the plan and replaces the prior set (schedule + cancel).
    expect($html)->toContain(route('reminders.feed'));
    expect($html)->toContain('.schedule(');
    expect($html)->toContain('.cancel(');

    // Wired into the surfaces a child lands on at launch.
    $surfaces = [
        'resources/views/student-splash.blade.php',
        'resources/views/voyage/overworld.blade.php',
        'resources/views/components/layouts/diagnostic.blade.php',
    ];
    foreach ($surfaces as $surface) {
        expect(file_get_contents(base_path($surface)))
            ->toContain("@include('partials.reminders-bridge')");
    }
})->group('scenario:LR-01', 'scenario:LR-07');

it('each reminder carries its own time and words so it fires offline', function () {
    $student = User::factory()->create(['role' => 'student']);

    foreach (remindersFor($student) as $r) {
        $row = $r->toArray();
        expect($row['at'])->toBeString()->not->toBeEmpty();   // its own fire time
        expect($row['title'])->not->toBeEmpty();              // its own words
        expect($row['body'])->not->toBeEmpty();
    }
})->group('scenario:LR-07');
