<?php

declare(strict_types=1);

namespace App\Services\Pacing;

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;

/**
 * Generates a student's pacing roadmap.
 *
 * Ensures the student has a StudentJourney (creating one from her target SEA
 * year if absent), resolves the starting week from the earliest pacing week
 * that still holds a not-yet-mastered module, then materialises the current
 * week's target via the weekly rollover.
 */
final class RoadmapGenerator
{
    public function __construct(
        private WeeklyRollover $rollover,
        private ExamDateResolver $examDateResolver,
    ) {}

    /**
     * Ensure the journey exists, resolve the starting pacing week, and seed the
     * current week's target.
     *
     * The starting week is the minimum `pacing_week` among syllabus modules the
     * student has not yet mastered, where "mastered" follows WeeklyRollover's
     * frontier: StudentProgress rows for this student whose status is exactly
     * 'mastered' (inferred_mastered and needs_work are NOT excluded). The current
     * week's WeeklyTarget rows are then built and persisted by the rollover.
     *
     * @param  User  $student  The student whose roadmap is being generated.
     * @return int The starting pacing week.
     */
    public function generate(User $student): int
    {
        StudentJourney::firstOrCreate(
            ['student_id' => $student->id],
            [
                'journey_start' => today(),
                'exam_date' => $this->examDateResolver->resolve((int) $student->target_sea_year),
            ],
        );

        $masteredModuleIds = StudentProgress::where('student_id', $student->id)
            ->where('status', 'mastered')
            ->pluck('module_id');

        $startingWeek = (int) SyllabusModule::whereNotIn('id', $masteredModuleIds)
            ->min('pacing_week');

        $this->rollover->runFor($student);

        return $startingWeek;
    }
}
