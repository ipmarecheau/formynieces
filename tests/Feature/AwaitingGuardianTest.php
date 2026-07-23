<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * RR-11 — a student whose guardian decision is pending is held on a waiting
 * page across logins (naming the guardian's login + support), never sent back
 * into the diagnostic, until the guardian decides or the 3-day hold times out —
 * at which point her next login proceeds her to the map.
 */
function seedLoginPendingStudent(int $completedDaysAgo, string $password = 'secret'): array
{
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'rr11-guard-'.uniqid().'@formynieces.com',
        'password' => bcrypt($password),
        'role' => 'guardian',
        'email_verified_at' => now(),
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr11-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt($password),
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2027,
        'onboarding_completed_at' => null,
        'guardian_reconciled_at' => null,
        'known_weak_areas' => ['Fractions'],
        'email_verified_at' => now(),
    ]);

    // The diagnostic cleared the flagged Fractions strand — a decision is pending.
    $fractions = SyllabusModule::create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'sea_section' => 'Section I', 'sequence_order' => 1, 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $fractions->id, 'status' => 'mastered', 'score' => 3]);
    SyllabusModule::create(['subject' => 'Math', 'topic' => 'Geometry: Angles', 'sea_section' => 'Section I', 'sequence_order' => 2, 'pacing_week' => 1]);

    $when = now()->subDays($completedDaysAgo);
    DB::table('diagnostic_sessions')->insert([
        'student_id' => $student->id,
        'status' => 'completed',
        'item_plan' => '[]',
        'current_item' => 0,
        'completed_at' => $when,
        'created_at' => $when,
        'updated_at' => $when,
    ]);

    return [$guardian, $student];
}

it('routes a pending student to the waiting page on login, not back into the diagnostic', function () {
    [, $student] = seedLoginPendingStudent(1);

    $this->post('/login', ['email' => $student->email, 'password' => 'secret'])
        ->assertRedirect(route('student.awaiting-guardian'));
})->group('scenario:RR-11');

it('shows the guardian login and support details on the waiting page', function () {
    [$guardian, $student] = seedLoginPendingStudent(1);

    $this->actingAs($student)
        ->get(route('student.awaiting-guardian'))
        ->assertOk()
        ->assertSee($guardian->email)
        ->assertSee('support@formynieces.com')
        ->assertSee('Log Out');
})->group('scenario:RR-11');

it('proceeds a student past the waiting page when the hold has already timed out', function () {
    [, $student] = seedLoginPendingStudent(4);

    $response = $this->post('/login', ['email' => $student->email, 'password' => 'secret']);

    // She is proceeded into her onboarded experience, not held on the waiting page.
    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('awaiting-guardian');

    $student->refresh();

    expect($student->onboarding_completed_at)->not->toBeNull()
        ->and($student->guardian_reconciled_at)->not->toBeNull()
        ->and(StudentJourney::where('student_id', $student->id)->exists())->toBeTrue();
})->group('scenario:RR-11');
