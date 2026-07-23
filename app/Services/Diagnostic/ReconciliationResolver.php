<?php

declare(strict_types=1);

namespace App\Services\Diagnostic;

use App\Models\User;
use App\Services\Pacing\RoadmapGenerator;

/**
 * Resolves a guardian's reconciliation decision (RR-04).
 *
 * Once the guardian chooses how to reconcile the diagnostic against her stated
 * weak areas, her decision is recorded and the student's onboarding and roadmap
 * are unblocked.
 */
final class ReconciliationResolver
{
    public function __construct(
        private RoadmapGenerator $generator,
    ) {}

    /**
     * The guardian accepts the diagnostic result.
     *
     * @param  User  $student  The student whose guardian has reconciled.
     */
    public function proceedWithDiagnostic(User $student): void
    {
        $this->finalize($student);
    }

    /**
     * The guardian keeps her stated weak areas.
     *
     * RR-05 will additionally mark the kept strands' modules not_started before generation.
     *
     * @param  User  $student  The student whose guardian has reconciled.
     */
    public function keepStatedWeakAreas(User $student): void
    {
        $this->finalize($student);
    }

    /**
     * Record the guardian's decision and unblock the student's onboarding and roadmap.
     *
     * Idempotent: a student whose guardian has already reconciled is left
     * untouched. Otherwise the decision and any missing onboarding completion
     * are timestamped, persisted, and the roadmap is generated when the student
     * has a target SEA year.
     *
     * @param  User  $student  The student whose guardian has reconciled.
     */
    private function finalize(User $student): void
    {
        if ($student->guardian_reconciled_at !== null) {
            return;
        }

        $student->guardian_reconciled_at = now();

        if ($student->onboarding_completed_at === null) {
            $student->onboarding_completed_at = now();
        }

        $student->save();

        if ($student->target_sea_year !== null) {
            $this->generator->generate($student);
        }
    }
}
