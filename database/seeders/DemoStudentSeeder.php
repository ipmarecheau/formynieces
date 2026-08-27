<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\ModuleStageCompletion;
use App\Models\StreakReward;
use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\Remediation;
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

        // A rich mid-journey: the first eight modules mastered so several islands
        // on the Voyage read as conquered, giving the demo reel a sense of momentum.
        SyllabusModule::orderBy('sequence_order')->take(8)->get()->each(
            fn (SyllabusModule $m) => StudentProgress::updateOrCreate(
                ['student_id' => $student->id, 'module_id' => $m->id],
                ['status' => 'mastered', 'score' => 100, 'current_rung' => 5, 'current_streak' => 3],
            ),
        );

        // A "needs work" module that HAS an authored lesson, so the reel can walk
        // the practise → miss → AI re-teach path live. Prefer a spelling module
        // (e.g. module 55) whose lesson is known to exist; fall back to any lesson.
        $needsWorkModuleId = Lesson::where('module_id', 55)->exists()
            ? 55
            : Lesson::orderByDesc('module_id')->value('module_id');

        if ($needsWorkModuleId !== null) {
            StudentProgress::updateOrCreate(
                ['student_id' => $student->id, 'module_id' => $needsWorkModuleId],
                ['status' => 'needs_work', 'score' => 55, 'previous_score' => 55, 'current_rung' => 3, 'current_streak' => 0],
            );
        }

        // The Captain's Locker: a spread of protective rewards so the perks panel
        // reads as earned, not empty.
        $lockerHoldings = [
            'shore_leave' => ['quantity' => 2, 'source' => 'milestone'],
            'anchor' => ['quantity' => 1, 'source' => 'ahead'],
            'tailwind' => ['quantity' => 3, 'source' => 'xp'],
            'lifebuoy' => ['quantity' => 1, 'source' => 'guardian'],
        ];
        foreach ($lockerHoldings as $type => $holding) {
            StreakReward::updateOrCreate(
                ['student_id' => $student->id, 'type' => $type],
                $holding,
            );
        }

        // Live streaks so the Captain's Orders milestones and sub-streaks light up.
        $streaks = [
            'voyage' => 12,
            'reading' => 5,
            'vocabulary' => 4,
            'writing' => 3,
            'mastery' => 8,
        ];
        foreach ($streaks as $type => $count) {
            StudentStreak::updateOrCreate(
                ['student_id' => $student->id, 'type' => $type],
                ['count' => $count, 'last_activity_date' => now()->toDateString()],
            );
        }

        // Make every screen of the demo walkthrough reachable on the needs_work
        // module: unlock the gated lesson → worked-examples → practice sequence, and
        // open a re-teach session so the AI re-teach screen renders on demand.
        if ($needsWorkModuleId !== null) {
            foreach ([ModuleStageCompletion::STAGE_LESSON, ModuleStageCompletion::STAGE_TUTORIAL] as $stage) {
                ModuleStageCompletion::firstOrCreate(
                    ['student_id' => $student->id, 'module_id' => $needsWorkModuleId, 'stage' => $stage],
                    ['completed_at' => now()],
                );
            }

            app(Remediation::class)->start($student->id, $needsWorkModuleId, 'demo');
        }

        $this->command?->info('Demo student ready — demo-student@smoothseas.test / smoothseas');
    }
}
