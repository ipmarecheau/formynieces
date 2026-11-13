<?php

use App\Filament\Pages\StudentLogs;
use App\Models\DailyPlan;
use App\Models\PracticeAttempt;
use App\Models\StudentProgress;
use App\Models\SupportTicket;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingSubmission;
use App\Services\Admin\StudentActivityLog;
use Livewire\Livewire;

/*
 * Per-student activity log (AL-01..03): a read-only timeline composed from existing records.
 */

function seedJourney(User $student): SyllabusModule
{
    $module = SyllabusModule::factory()->create(['topic' => 'Fractions']);

    PracticeAttempt::factory()->create([
        'student_id' => $student->id, 'module_id' => $module->id,
        'difficulty' => 1, 'attempt' => 1, 'is_correct' => true,
        'created_at' => now()->subHours(5),
    ]);
    StudentProgress::create([
        'student_id' => $student->id, 'module_id' => $module->id,
        'status' => 'mastered', 'mastered_at' => now()->subHours(4),
    ]);
    WritingSubmission::factory()->create([
        'student_id' => $student->id, 'created_at' => now()->subHours(3), 'scored_at' => null,
    ]);
    DailyPlan::create([
        'student_id' => $student->id, 'date' => today()->toDateString(),
        'duties' => ['map' => true], 'completed_at' => now()->subHours(2),
    ]);
    SupportTicket::factory()->create([
        'user_id' => $student->id, 'role' => 'student', 'kind' => 'issue',
        'message' => 'The bank said coming soon', 'created_at' => now()->subHour(),
    ]);

    return $module;
}

it('composes a student timeline newest first', function () {
    $student = User::factory()->create(['role' => 'student']);
    seedJourney($student);

    $events = app(StudentActivityLog::class)->forStudent($student);

    // Newest-first ordering.
    $times = array_map(fn ($e) => $e->at->getTimestamp(), $events);
    $sorted = $times;
    rsort($sorted);
    expect($times)->toBe($sorted);

    // The most recent is the help ticket (1h ago); the oldest the practice attempt (5h ago).
    expect($events[0]->type)->toBe('help');
    expect(end($events)->type)->toBe('practice');
})->group('scenario:AL-01');

it('spans practice, mastery, essay, day-completion and help', function () {
    $student = User::factory()->create(['role' => 'student']);
    seedJourney($student);

    $types = array_map(fn ($e) => $e->type, app(StudentActivityLog::class)->forStudent($student));

    expect($types)->toContain('practice', 'mastery', 'essay', 'day_complete', 'help');
})->group('scenario:AL-02');

it('writes nothing to produce the log', function () {
    $student = User::factory()->create(['role' => 'student']);
    seedJourney($student);

    // Composing the log must not create rows anywhere.
    DB::enableQueryLog();
    app(StudentActivityLog::class)->forStudent($student);
    $writes = collect(DB::getQueryLog())->filter(
        fn ($q) => (bool) preg_match('/^\s*(insert|update|delete)/i', $q['query']),
    );
    expect($writes)->toBeEmpty();
})->group('scenario:AL-03');

it('renders the admin page and shows a chosen student\'s activity', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $student = User::factory()->create(['role' => 'student', 'name' => 'Maya Khan']);
    seedJourney($student);

    Livewire::test(StudentLogs::class)
        ->assertOk()
        ->set('studentId', $student->id)
        ->assertSee('Mastered Fractions')
        ->assertSee("Completed the day's quota");
})->group('scenario:AL-01');

it('keeps the log out of a non-admin\'s reach', function () {
    // The admin panel (and every page on it, including Student Logs) is gated to admins.
    $panel = Filament\Facades\Filament::getDefaultPanel();

    expect(User::factory()->create(['role' => 'admin'])->canAccessPanel($panel))->toBeTrue();
    expect(User::factory()->create(['role' => 'guardian'])->canAccessPanel($panel))->toBeFalse();
    expect(User::factory()->create(['role' => 'student'])->canAccessPanel($panel))->toBeFalse();
})->group('scenario:AL-03');
