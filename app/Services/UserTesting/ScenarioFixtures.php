<?php

namespace App\Services\UserTesting;

use App\Models\DailyPlan;
use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SupportTicket;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;

/**
 * Seeds the database for a scenario so a tester can actually exercise it. Each scenario gets
 * its OWN dedicated account(s), all flagged is_test (so they are isolated from real families
 * and trivially purged), built idempotently on a stable email. Returns the user the tester
 * should be signed in as — or null for a "guest" scenario, where they are signed out.
 *
 * The heavy lifting mirrors DemoStudentSeeder: a verified guardian and an onboarded student
 * with a journey and a little progress, so the dashboard and Voyage render rather than bounce.
 */
class ScenarioFixtures
{
    /** The single shared child all AL (activity-log) scenarios point the admin at. */
    private const AL_LOG_KEY = 'AL-LOG';

    /** The friendly, unambiguous name that child shows under in the admin picker. */
    private const AL_CHILD_NAME = 'Test Explorer (AL)';

    /**
     * Ensure the state for a scenario exists and return the account to sign in as.
     */
    public function prepare(string $scenarioId, string $persona): ?User
    {
        return match ($persona) {
            'guest' => null,
            'admin' => $this->admin($scenarioId),
            'parent' => $this->guardianWithChild($scenarioId)['guardian'],
            'child' => $this->guardianWithChild($scenarioId)['student'],
            default => null,
        };
    }

    /**
     * The stable test login a tester types into an installed native app for a device
     * scenario. A physical device has no in-browser auto-login, so the credentials must be
     * shown to them. The kids app signs in as the onboarded child; the parent app as the
     * guardian. Both authenticate with email + the shared test password. Seeded idempotently
     * and flagged is_test, like every other test-drive account.
     *
     * @return array{email: string, password: string, role: string}
     */
    public function deviceLogin(string $app): array
    {
        $pair = $this->guardianWithChild('DEVICE', 'Test Explorer (App)');
        $user = $app === 'parent' ? $pair['guardian'] : $pair['student'];

        return [
            'email' => $user->email,
            'password' => (string) config('user_testing.password'),
            'role' => $user->role,
        ];
    }

    /**
     * Mark an account's email verified. `email_verified_at` is intentionally not mass-assignable
     * on the User model, so it must be force-set — otherwise updateOrCreate silently drops it and
     * the account bounces to the email-verification gate.
     */
    private function markVerified(User $user): void
    {
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }
    }

    private function slug(string $scenarioId): string
    {
        return strtolower(str_replace([' ', '_'], '-', $scenarioId));
    }

    private function email(string $scenarioId, string $persona): string
    {
        return 'td-'.$this->slug($scenarioId).'-'.$persona.'@'.config('user_testing.email_domain');
    }

    private function admin(string $scenarioId): User
    {
        $admin = User::updateOrCreate(
            ['email' => $this->email($scenarioId, 'admin')],
            [
                'name' => 'Test Admin',
                'password' => config('user_testing.password'),
                'role' => 'admin',
                'is_test' => true,
            ],
        );
        $this->markVerified($admin);

        // The student log is only worth looking at if some child has a full, varied
        // history to show, so seed one active child the admin can pick. All AL scenarios
        // share this single child (stable key + name) so the picker shows exactly one
        // unambiguous "Test Explorer", not one per scenario.
        $this->seedActiveChild();

        return $admin;
    }

    /**
     * A child with one of every kind of activity — practised, mastered, wrote, finished a
     * day, and asked for help — so the activity log spans the whole journey (AL-02).
     */
    private function seedActiveChild(): User
    {
        $child = $this->guardianWithChild(self::AL_LOG_KEY, self::AL_CHILD_NAME)['student'];

        // Each kind of activity is seeded only if the child has none of it yet, so re-entering
        // the scenario tops up a partial seed rather than piling up duplicates.
        $module = SyllabusModule::orderBy('sequence_order')->first();

        if ($module !== null) {
            StudentProgress::updateOrCreate(
                ['student_id' => $child->id, 'module_id' => $module->id],
                ['status' => 'mastered', 'score' => 100, 'current_rung' => 5, 'current_streak' => 3, 'mastered_at' => now()->subDays(2)],
            );
        }

        // Reuse existing reference data where it exists (dev already has prompts/questions on
        // unique weekly keys), falling back to a factory only in a bare database.
        if (! PracticeAttempt::where('student_id', $child->id)->exists()) {
            $question = PracticeQuestion::query()->first();
            PracticeAttempt::factory()->create(array_filter([
                'student_id' => $child->id,
                'practice_question_id' => $question?->id,
                'module_id' => $question?->module_id,
            ]));
        }

        if (! WritingSubmission::where('student_id', $child->id)->exists()) {
            $prompt = WritingPrompt::query()->first() ?? WritingPrompt::factory()->create();
            WritingSubmission::factory()->create([
                'student_id' => $child->id,
                'writing_prompt_id' => $prompt->id,
            ]);
        }

        DailyPlan::updateOrCreate(
            ['student_id' => $child->id, 'date' => now()->subDay()->toDateString()],
            ['is_writing_day' => false, 'duties' => '[]', 'completed_at' => now()->subDay()],
        );

        if (! SupportTicket::where('student_id', $child->id)->exists()) {
            SupportTicket::factory()->create([
                'user_id' => $child->id,
                'student_id' => $child->id,
                'role' => 'student',
            ]);
        }

        return $child;
    }

    /**
     * A verified guardian with one onboarded, lightly-progressed child.
     *
     * @return array{guardian: User, student: User}
     */
    private function guardianWithChild(string $scenarioId, ?string $childName = null): array
    {
        $guardian = User::updateOrCreate(
            ['email' => $this->email($scenarioId, 'parent')],
            [
                'name' => 'Test Parent',
                'password' => config('user_testing.password'),
                'role' => 'guardian',
                'age_attested_at' => now(),
                'is_test' => true,
            ],
        );
        $this->markVerified($guardian);

        $student = User::updateOrCreate(
            ['email' => $this->email($scenarioId, 'child')],
            [
                // Non-AL children are scenario-qualified so they never collide with the
                // one canonical "Test Explorer" the admin picker targets.
                'name' => $childName ?? 'Test Explorer ('.$scenarioId.')',
                'password' => config('user_testing.password'),
                'role' => 'student',
                'parent_id' => $guardian->id,
                'onboarding_completed_at' => now(), // routes to the Voyage, not the diagnostic
                'target_sea_year' => now()->addYear()->year,
                'is_test' => true,
            ],
        );
        $this->markVerified($student);

        $this->giveStudentAStartingState($student);

        return ['guardian' => $guardian, 'student' => $student];
    }

    /**
     * A study journey plus a few mastered modules, so the Voyage and the guardian's
     * progress figures read as real rather than empty. Guarded so it is a no-op when the
     * syllabus has not been seeded (e.g. in a bare test database).
     */
    private function giveStudentAStartingState(User $student): void
    {
        StudentJourney::updateOrCreate(
            ['student_id' => $student->id],
            [
                'journey_start' => now()->toDateString(),
                'exam_date' => now()->addYear()->setDate(now()->year + 1, 4, 1)->toDateString(),
            ],
        );

        $modules = SyllabusModule::orderBy('sequence_order')->take(6)->get();

        foreach ($modules as $module) {
            StudentProgress::updateOrCreate(
                ['student_id' => $student->id, 'module_id' => $module->id],
                ['status' => 'mastered', 'score' => 100, 'current_rung' => 5, 'current_streak' => 3],
            );
        }
    }
}
