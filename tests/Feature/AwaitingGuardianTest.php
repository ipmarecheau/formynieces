<?php

use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * RR-11 (non-blocking) — the child is NEVER held. Even when the diagnostic cleared a
 * strand the guardian flagged (a decision the guardian may still make on her dashboard),
 * the child completes onboarding and logs straight into her own experience — never a
 * waiting page. The guardian's review is applied later, in the background.
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
        'onboarding_completed_at' => now(),   // completing the diagnostic finishes onboarding — child is never held
        'guardian_reconciled_at' => null,
        'known_weak_areas' => ['Fractions'],
        'email_verified_at' => now(),
    ]);

    // The diagnostic cleared the flagged Fractions strand — a decision is available to the guardian.
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

it('logs a reviewable student straight into her own experience, never a waiting page', function () {
    [, $student] = seedLoginPendingStudent(1);

    $response = $this->post('/login', ['email' => $student->email, 'password' => 'secret']);

    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('awaiting-guardian');
})->group('scenario:RR-11');

it('sends a freshly flagged student into the diagnostic, not the waiting page', function () {
    // The guardian flagged weak areas at setup, but the student has NOT taken
    // the diagnostic yet — there is nothing to reconcile, so she takes it.
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'rr11-fresh-guard-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
        'email_verified_at' => now(),
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr11-fresh-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2027,
        'onboarding_completed_at' => null,
        'guardian_reconciled_at' => null,
        'known_weak_areas' => ['Fractions'],
        'email_verified_at' => now(),
    ]);

    $this->post('/login', ['email' => $student->email, 'password' => 'secret'])
        ->assertRedirect(route('diagnostic.intro'));
})->group('scenario:RR-11');

it('gates a fresh student to the diagnostic even with a stale intended url', function () {
    $guardian = User::create([
        'name' => 'Guardian',
        'email' => 'rr11-stale-guard-'.uniqid().'@formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'guardian',
        'email_verified_at' => now(),
    ]);

    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'rr11-stale-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'parent_id' => $guardian->id,
        'target_sea_year' => 2027,
        'onboarding_completed_at' => null,
        'known_weak_areas' => ['Fractions'],
        'email_verified_at' => now(),
    ]);

    // A leftover intended URL from earlier navigation must not bypass the gate.
    $this->withSession(['url.intended' => url('/my-map')])
        ->post('/login', ['email' => $student->email, 'password' => 'secret'])
        ->assertRedirect(route('diagnostic.intro'));
})->group('scenario:RR-11');

it('never holds a reviewable student, even with a stale intended url', function () {
    [, $student] = seedLoginPendingStudent(1);

    $response = $this->withSession(['url.intended' => url('/my-map')])
        ->post('/login', ['email' => $student->email, 'password' => 'secret']);

    expect($response->headers->get('Location'))->not->toContain('awaiting-guardian');
})->group('scenario:RR-11');

it('shows the guardian login and support details on the waiting page', function () {
    [$guardian, $student] = seedLoginPendingStudent(1);

    $this->actingAs($student)
        ->get(route('student.awaiting-guardian'))
        ->assertOk()
        ->assertSee($guardian->email)
        ->assertSee('support@smoothseas.org')
        ->assertSee('Log Out');
})->group('scenario:RR-11');

it('logs an onboarded student into her experience regardless of any pending guardian review', function () {
    [, $student] = seedLoginPendingStudent(4);

    $response = $this->post('/login', ['email' => $student->email, 'password' => 'secret']);

    // She is in her onboarded experience, not held on the waiting page.
    $response->assertRedirect();
    expect($response->headers->get('Location'))->not->toContain('awaiting-guardian');

    $student->refresh();
    expect($student->onboarding_completed_at)->not->toBeNull();
})->group('scenario:RR-11');
