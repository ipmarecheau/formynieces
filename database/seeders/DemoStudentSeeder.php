<?php

namespace Database\Seeders;

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A fixed, known demo student for manual verification on the dev site.
 *
 *   Student login:  demo-student@smoothseas.test  /  smoothseas
 *
 * Idempotent — safe to run repeatedly. Creates a verified guardian parent, an
 * onboarded student with a study journey, and a little progress so the Voyage and
 * the learning loop are navigable. Does NOT touch the question bank.
 */
class DemoStudentSeeder extends Seeder
{
    public function run(): void
    {
        $guardian = User::updateOrCreate(
            ['email' => 'demo-guardian@smoothseas.test'],
            [
                'name' => 'Demo Guardian',
                'password' => 'smoothseas',          // hashed by the cast
                'role' => 'guardian',
                'email_verified_at' => now(),
                'age_attested_at' => now(),
            ],
        );

        $student = User::updateOrCreate(
            ['email' => 'demo-student@smoothseas.test'],
            [
                'name' => 'Demo Student',
                'password' => 'smoothseas',          // hashed by the cast
                'role' => 'student',
                'parent_id' => $guardian->id,
                'onboarding_completed_at' => now(),  // routed to the Voyage, not the diagnostic
                'target_sea_year' => now()->addYear()->year,
                'email_verified_at' => now(),
            ],
        );

        // A study journey so the Voyage / pacing works.
        StudentJourney::updateOrCreate(
            ['student_id' => $student->id],
            [
                'journey_start' => now()->toDateString(),
                'exam_date' => now()->addYear()->setDate(now()->year + 1, 4, 1)->toDateString(),
            ],
        );

        // A touch of progress: first two Math modules mastered, so the map shows a
        // start; everything else stays open to practise (e.g. module 55, Spelling).
        SyllabusModule::orderBy('sequence_order')->take(2)->get()->each(
            fn (SyllabusModule $m) => StudentProgress::updateOrCreate(
                ['student_id' => $student->id, 'module_id' => $m->id],
                ['status' => 'mastered', 'score' => 100, 'current_rung' => 5, 'current_streak' => 3],
            ),
        );

        $this->command?->info('Demo student ready — demo-student@smoothseas.test / smoothseas');
    }
}
