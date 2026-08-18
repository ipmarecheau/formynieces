<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\ReconciliationResolver;
use App\Services\Diagnostic\SessionLifecycle;
use App\Services\ExamAgentService;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Pacing\PauseService;
use App\Services\SchoolJournal\SchoolEvidenceService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GuardianDashboard extends Component
{
    public bool $targetCompleted = false;

    public ?string $paceStatus = null;

    public int $weeksBehind = 0;

    public bool $onTrack = false;

    /** GD-07 — the child this dashboard is about; null = the guardian's first student. */
    public ?int $studentId = null;

    private const PAPER_WEIGHTS = ['Math' => 50, 'ELA' => 30, 'Writing' => 20];

    #[Layout('layouts.guardian')]
    public function render(ExamAgentService $examAgent)
    {
        $guardian = auth()->user();

        $students = $guardian->students()->orderBy('name')->get();
        $student = $this->studentId
            ? $students->firstWhere('id', $this->studentId)
            : $students->first();
        $this->studentId = $student?->id;

        $this->targetCompleted = $this->resolveTargetCompleted($student);

        $journey = $student?->studentJourney;
        $this->paceStatus = $journey?->pace_status;
        $this->weeksBehind = (int) ($journey?->weeks_behind ?? 0);

        $this->onTrack = $this->targetCompleted
            && $this->weeksBehind === 0
            && $this->paceStatus === null;

        $analysis = $student
            ? $examAgent->analyse($student)
            : ['subject_analysis' => [], 'recommendation' => ''];

        $reconciliation = app(DiagnosticReconciliation::class);
        $reconciliationPending = $student !== null && $reconciliation->isPending($student);

        return view('livewire.guardian-dashboard', [
            'pace' => $this->buildPace($analysis['subject_analysis'] ?? []),
            'recommendation' => $analysis['recommendation'] ?? '',
            'writingFeedback' => $this->latestWritingFeedback($student),
            'triage' => $this->buildTriage($analysis['subject_analysis'] ?? []),
            'reconciliationPending' => $reconciliationPending,
            'clearedStrands' => $reconciliationPending ? $reconciliation->clearedStrands($student) : [],
            'studentName' => $student?->name,
            'student' => $student,
            'students' => $students,
            'weekLabel' => 'Week of '.Carbon::today()->startOfWeek()->format('j M Y'),
            'rewardTypes' => [
                'shore_leave' => 'Shore Leave',
                'anchor' => 'Anchor',
                'tailwind' => 'Tailwind',
                'lifebuoy' => 'Lifebuoy',
            ],
            'schoolThisWeek' => $student
                ? app(SchoolEvidenceService::class)->thisWeek($student->id)
                : collect(),
        ]);
    }

    /**
     * SE-15 — the guardian grants a streak reward, which lands in the student's
     * Captain's Locker. The one sanctioned crossing point between the honest
     * layer and the child's motivational world.
     */
    public function grantReward(string $type, StreakEconomyService $economy): void
    {
        $student = auth()->user()->students()->find($this->studentId)
            ?? auth()->user()->students()->first();

        if ($student === null) {
            return;
        }

        try {
            $economy->grantReward($student->id, $type, 'guardian');
        } catch (\InvalidArgumentException) {
            return;
        }

        $this->dispatch('reward-granted', type: $type);
    }

    /**
     * GD-09 — the guardian pauses her student's journey from the dashboard. A
     * pause freezes streaks and stops the pacing clock (WT-05); an honest-layer
     * control, never shown to the child.
     */
    public function pauseJourney(PauseService $pauses): void
    {
        $student = $this->resolveStudent();
        if ($student !== null) {
            $pauses->pause($student);
            $this->dispatch('journey-paused');
        }
    }

    /**
     * GD-09 — the guardian resumes a paused journey from the dashboard, bridging
     * frozen day-streaks so the pause never counts against the student (ML-03).
     */
    public function resumeJourney(PauseService $pauses): void
    {
        $student = $this->resolveStudent();
        if ($student !== null) {
            $pauses->resume($student);
            $this->dispatch('journey-resumed');
        }
    }

    /**
     * GD-09 — the guardian requests a diagnostic retake from the dashboard,
     * starting a fresh diagnostic session for the student. Honest-layer only.
     */
    public function requestRetake(SessionLifecycle $sessions): void
    {
        $student = $this->resolveStudent();
        if ($student !== null) {
            $sessions->startOrResume($student->id);
            $this->dispatch('retake-requested');
        }
    }

    /** The child this dashboard is acting on — the selected student, or the first. */
    private function resolveStudent(): ?User
    {
        return auth()->user()->students()->find($this->studentId)
            ?? auth()->user()->students()->first();
    }

    /**
     * The guardian accepts the diagnostic result over her stated weak areas,
     * unblocking the student's onboarding and roadmap. [RR-04]
     */
    public function proceedWithDiagnostic(ReconciliationResolver $resolver): void
    {
        $student = auth()->user()->students()->first();
        if ($student !== null) {
            $resolver->proceedWithDiagnostic($student);
        }
    }

    /**
     * The guardian keeps her stated weak areas over the diagnostic. [RR-04;
     * the strand remapping itself is RR-05.]
     */
    public function keepWeakAreas(ReconciliationResolver $resolver): void
    {
        $student = auth()->user()->students()->first();
        if ($student !== null) {
            $resolver->keepStatedWeakAreas($student);
        }
    }

    private function resolveTargetCompleted(?User $student): bool
    {
        if (! $student) {
            return false;
        }

        $weekStart = Carbon::today()->startOfWeek()->toDateString();

        $rows = WeeklyTarget::where('student_id', $student->id)
            ->where('week_start_date', $weekStart)
            ->get();

        return $rows->isNotEmpty() && $rows->every(fn ($r) => (bool) $r->is_completed);
    }

    private function buildPace(array $subjectAnalysis): array
    {
        $math = $subjectAnalysis['Math'] ?? null;
        $ela = $subjectAnalysis['ELA'] ?? null;

        return [
            'Math' => [
                'weight' => self::PAPER_WEIGHTS['Math'],
                'expected' => $math['expected'] ?? 0,
                'completed' => $math['completed'] ?? 0,
                'behind_count' => $math['behind_count'] ?? 0,
                'assessed' => $math !== null,
            ],
            'ELA' => [
                'weight' => self::PAPER_WEIGHTS['ELA'],
                'expected' => $ela['expected'] ?? 0,
                'completed' => $ela['completed'] ?? 0,
                'behind_count' => $ela['behind_count'] ?? 0,
                'assessed' => $ela !== null,
            ],
            'Writing' => [
                'weight' => self::PAPER_WEIGHTS['Writing'],
                'expected' => 0,
                'completed' => 0,
                'behind_count' => 0,
                'assessed' => false,
            ],
        ];
    }

    /**
     * Build a calm catch-up plan for a significantly-behind student
     * (4+ weeks behind the pacing calendar). Returns null when the
     * student is on pace, so the view renders nothing.
     *
     * Subjects are ordered by paper weight (Math first at 50%), and
     * each behind subject is expressed as feasible weekly steps —
     * modules per week derived from behind_count / weeks_lost — never
     * as a raw deficit total.
     *
     * @return array{name: string, weekly_step: string}[]
     */
    private function buildTriage(array $subjectAnalysis): ?array
    {
        if ($this->weeksBehind < 4) {
            return null;
        }

        $subjects = collect($subjectAnalysis)
            ->filter(fn (array $s): bool => (int) ($s['behind_count'] ?? 0) > 0)
            ->sortByDesc(fn (array $s): int => self::PAPER_WEIGHTS[$s['subject']] ?? 0)
            ->map(function (array $s): array {
                $behind = (int) ($s['behind_count'] ?? 0);
                $lost = max((int) ($s['weeks_lost'] ?? 0), 1);
                $perWeek = (int) round($behind / $lost);

                return [
                    'name' => $s['subject'] === 'Math' ? 'Mathematics' : $s['subject'],
                    'weekly_step' => "About {$perWeek} module".($perWeek === 1 ? '' : 's').' per week',
                ];
            })
            ->values()
            ->all();

        return [
            'weeks_behind' => $this->weeksBehind,
            'priority' => 'Start with Mathematics',
            'subjects' => $subjects,
        ];
    }

    private function latestWritingFeedback(?User $student): ?array
    {
        return null;
    }
}
