<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PracticeAttempt;
use App\Models\StudentQuestionExposure;
use Illuminate\Console\Command;

/**
 * One-time repair for the serve-time exposure bug (fixed in PracticeWalk): practice
 * questions used to be recorded as "seen" the moment they were SERVED, so a question a
 * student merely viewed and never answered was permanently excluded by the no-repeat
 * rule — dead-ending practice on "more practice coming soon".
 *
 * This removes only those stale, serve-only PRACTICE exposures — rows whose question the
 * student never actually ANSWERED (no matching practice_attempt). It never touches
 * check/tutorial/maintenance exposures, and never touches student_progress or
 * practice_attempts. Dry-run by default; pass --apply to delete.
 */
class HealPracticeExposures extends Command
{
    protected $signature = 'practice:heal-exposures {--apply : Actually delete the stale rows (default is a dry run)}';

    protected $description = 'Remove stale serve-only practice exposures (never-answered questions) left by the serve-time exposure bug';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        // The content hashes each student has genuinely ANSWERED (a practice_attempt exists).
        $answered = PracticeAttempt::query()
            ->join('practice_questions', 'practice_questions.id', '=', 'practice_attempts.practice_question_id')
            ->select('practice_attempts.student_id', 'practice_questions.content_hash')
            ->distinct()
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->pluck('content_hash')->all());

        // Candidate stale rows: practice-context exposures NOT backed by an answer.
        $stale = StudentQuestionExposure::query()
            ->where('context', 'practice')
            ->get()
            ->filter(fn (StudentQuestionExposure $e) => ! in_array(
                $e->content_hash,
                $answered->get($e->student_id, []),
                true,
            ));

        $byStudent = $stale->groupBy('student_id');

        $this->info(($apply ? 'APPLYING' : 'DRY RUN').' — stale serve-only practice exposures');
        $this->line('  practice exposures total: '.StudentQuestionExposure::where('context', 'practice')->count());
        $this->line('  stale (never-answered):   '.$stale->count().' across '.$byStudent->count().' student(s)');

        if ($byStudent->isNotEmpty()) {
            $this->table(
                ['student_id', 'stale rows'],
                $byStudent->map(fn ($rows, $id) => [$id, $rows->count()])->values()->all(),
            );
        }

        if (! $apply) {
            $this->comment('No changes made. Re-run with --apply to delete these rows.');

            return self::SUCCESS;
        }

        if ($stale->isEmpty()) {
            $this->info('Nothing to delete.');

            return self::SUCCESS;
        }

        StudentQuestionExposure::whereIn('id', $stale->pluck('id'))->delete();
        $this->info('Deleted '.$stale->count().' stale practice exposure row(s). Progress and attempts untouched.');

        return self::SUCCESS;
    }
}
