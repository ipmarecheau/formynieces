<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\ModuleStageCompletion;
use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\SchoolJournalEntry;
use App\Models\StreakReward;
use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
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

        // A rich mid-journey: a healthy spread of modules mastered across both
        // subjects, so the Voyage reads as conquered AND the guardian's mastery and
        // pace figures show real momentum rather than "early days".
        $masteredModules = SyllabusModule::where('subject', 'Math')->orderBy('sequence_order')->take(10)->get()
            ->concat(SyllabusModule::where('subject', 'ELA')->orderBy('sequence_order')->take(6)->get());
        $masteredModules->each(
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

        // Practice history so the guardian Estimator projects from real evidence
        // (per-subject accuracy) instead of reading as "few attempts yet".
        $this->seedPracticeHistory($student->id, 'Math', 22, 0.82);
        $this->seedPracticeHistory($student->id, 'ELA', 18, 0.83);

        // One scored writing submission so the Estimator's Writing paper and the
        // Overview's "latest writing feedback" pointer have something honest to show.
        $promptId = WritingPrompt::orderBy('id')->value('id');
        if ($promptId !== null && WritingSubmission::where('student_id', $student->id)->doesntExist()) {
            WritingSubmission::create([
                'student_id' => $student->id,
                'writing_prompt_id' => $promptId,
                'body' => 'The old door creaked open. Behind it, a narrow stair spiralled down into a warm, golden light, and the smell of salt drifted up to meet me...',
                'status' => 'scored',
                'content_score' => 8,
                'language_score' => 9,
                'grammar_score' => 8,
                'organisation_score' => 8,
                'did_well' => 'A vivid opening image and strong sensory detail — the salt smell really places the reader there.',
                'try_next' => 'Try varying your sentence lengths in the next paragraph to build a little tension.',
                'scored_at' => now()->subDays(2),
            ]);
        }

        // A couple of digitised school papers so the guardian's School Journal shows
        // a real term timeline (classroom evidence beside the platform's own picture).
        SchoolJournalEntry::where('student_id', $student->id)->delete();
        foreach ([
            ['subject' => 'Mathematics', 'strand' => 'Number — Place Value', 'assessment_type' => 'Class Test',
                'score' => '82%', 'teacher_comment' => 'Strong on place value; watch careless errors when regrouping.', 'days' => 9],
            ['subject' => 'English Language Arts', 'strand' => 'Comprehension', 'assessment_type' => 'Quiz',
                'score' => '78%', 'teacher_comment' => 'Good literal answers — push for more inference next time.', 'days' => 20],
        ] as $paper) {
            SchoolJournalEntry::create([
                'student_id' => $student->id,
                'uploaded_by' => 'guardian',
                'image_path' => "school-journal/{$student->id}/demo-paper.jpg",
                'assessment_date' => now()->subDays($paper['days'])->toDateString(),
                'term' => 'Term 1',
                'subject' => $paper['subject'],
                'strand' => $paper['strand'],
                'assessment_type' => $paper['assessment_type'],
                'score' => $paper['score'],
                'teacher_comment' => $paper['teacher_comment'],
                'digitisation_status' => 'confirmed',
            ]);
        }

        $this->command?->info('Demo student ready — demo-student@smoothseas.test / smoothseas');
    }

    /**
     * Seed a run of practice attempts for one subject at a target accuracy, using
     * real questions from the student's mastered modules in that subject.
     */
    private function seedPracticeHistory(int $studentId, string $subject, int $count, float $accuracy): void
    {
        $subjectModuleIds = SyllabusModule::where('subject', $subject)->pluck('id');

        // Idempotent, and immune to stray attempts left by demo walkthroughs: clear
        // this subject's attempts for the demo student, then lay down a clean run.
        PracticeAttempt::where('student_id', $studentId)
            ->whereIn('module_id', $subjectModuleIds)
            ->delete();

        $questions = PracticeQuestion::whereIn(
            'module_id',
            SyllabusModule::where('subject', $subject)->orderBy('sequence_order')->take(6)->pluck('id'),
        )->get(['id', 'module_id']);

        if ($questions->isEmpty()) {
            return;
        }

        $correctTarget = (int) round($count * $accuracy);
        for ($i = 0; $i < $count; $i++) {
            $q = $questions[$i % $questions->count()];
            PracticeAttempt::create([
                'student_id' => $studentId,
                'practice_question_id' => $q->id,
                'module_id' => $q->module_id,
                'difficulty' => 1 + ($i % 3),
                'attempt' => 1,
                'is_correct' => $i < $correctTarget,
                'created_at' => now()->subDays(14)->addHours($i),
            ]);
        }
    }
}
