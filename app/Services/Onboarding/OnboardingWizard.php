<?php

declare(strict_types=1);

namespace App\Services\Onboarding;

use App\Models\ModuleStageCompletion;
use App\Models\User;

/**
 * OnboardingWizard — the brain of the guided onboarding wizard (WZ-01..10).
 *
 * Computes the guardian's first-run checklist from REAL application state — a step is complete only
 * when the underlying thing actually happened (child created, exam date set, diagnostic finished),
 * including things the CHILD did (WZ-03, WZ-06). It never stores its own progress and never blocks
 * (WZ-08); it only reads state the other features already record, and points at the single next step.
 */
class OnboardingWizard
{
    public function __construct(private readonly User $guardian) {}

    public static function for(User $guardian): self
    {
        return new self($guardian);
    }

    /**
     * The child this wizard is guiding the family through (the first child added), or null before one exists.
     */
    public function focusChild(): ?User
    {
        return $this->guardian->students()->orderBy('id')->first();
    }

    /**
     * The ordered checklist, each step resolved against live state.
     *
     * @return list<array{key:string,label:string,why:string,done:bool,actor:string,route:?string}>
     */
    public function steps(): array
    {
        $child = $this->focusChild();

        return [
            [
                'key' => 'account',
                'label' => 'Create your account',
                'why' => 'Your secure guardian account keeps your child’s progress in one place.',
                'done' => $this->guardian->email_verified_at !== null,
                'actor' => 'guardian',
                'route' => null,
            ],
            [
                'key' => 'child',
                'label' => 'Add your child',
                'why' => 'Set up her profile and SEA exam year — that pitches lessons at her level and lets pacing keep her ahead.',
                'done' => $child !== null,
                'actor' => 'guardian',
                'route' => 'child.setup',
            ],
            [
                'key' => 'diagnostic',
                'label' => 'Take the diagnostic',
                'why' => 'A short check finds her starting level so nothing is too easy or too hard.',
                'done' => $child !== null && $child->diagnosticSessions()->whereNotNull('completed_at')->exists(),
                'actor' => 'child',
                'route' => null,
            ],
            [
                'key' => 'first_lesson',
                'label' => 'See her first lesson',
                'why' => 'Watch how SmoothSeas teaches — an interactive lesson, then practice.',
                'done' => $child !== null && ModuleStageCompletion::query()->where('student_id', $child->id)->exists(),
                'actor' => 'child',
                'route' => null,
            ],
        ];
    }

    /** The single next step to nudge (the first incomplete one), or null when everything is done (WZ-02). */
    public function nextStep(): ?array
    {
        foreach ($this->steps() as $step) {
            if (! $step['done']) {
                return $step;
            }
        }

        return null;
    }

    /**
     * Overall progress across the checklist.
     *
     * @return array{done:int,total:int,percent:int}
     */
    public function progress(): array
    {
        $steps = $this->steps();
        $done = count(array_filter($steps, fn (array $s): bool => $s['done']));
        $total = count($steps);

        return ['done' => $done, 'total' => $total, 'percent' => $total > 0 ? (int) round($done / $total * 100) : 0];
    }

    /** Whether every onboarding step is complete (WZ-09). */
    public function isComplete(): bool
    {
        return $this->nextStep() === null;
    }

    /**
     * Retire the wizard once the whole lifecycle is done: stamp the guardian's onboarding as complete so
     * it does not reappear unless she reopens it (WZ-09). Idempotent.
     */
    public function retireIfComplete(): bool
    {
        if ($this->isComplete() && $this->guardian->onboarding_completed_at === null) {
            $this->guardian->forceFill(['onboarding_completed_at' => now()])->save();

            return true;
        }

        return false;
    }
}
