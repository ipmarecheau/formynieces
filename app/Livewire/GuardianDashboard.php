<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\ReconciliationResolver;
use App\Services\ExamAgentService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

class GuardianDashboard extends Component
{
    public bool $targetCompleted = false;

    public ?string $paceStatus = null;

    public int $weeksBehind = 0;

    public bool $onTrack = false;

    private const PAPER_WEIGHTS = ['Math' => 50, 'ELA' => 30, 'Writing' => 20];

    #[Layout('layouts.guardian')]
    public function render(ExamAgentService $examAgent)
    {
        $guardian = auth()->user();
        $student = $guardian->students()->first();

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
        ]);
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
