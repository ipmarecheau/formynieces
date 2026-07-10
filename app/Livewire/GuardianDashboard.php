<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\ExamAgentService;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\Attributes\Layout;

class GuardianDashboard extends Component
{
    public bool $targetCompleted = false;
    public ?string $paceStatus = null;
    public int $weeksBehind = 0;

    private const PAPER_WEIGHTS = ['Math' => 50, 'ELA' => 30, 'Writing' => 20];

    #[Layout('layouts.guardian')]
    public function render(ExamAgentService $examAgent)
    {
        $guardian = auth()->user();
        $student  = $guardian->students()->first();

        $this->targetCompleted = $this->resolveTargetCompleted($student);

        $journey = $student?->studentJourney;
        $this->paceStatus  = $journey?->pace_status;
        $this->weeksBehind = (int) ($journey?->weeks_behind ?? 0);

        $analysis = $student
            ? $examAgent->analyse($student)
            : ['subject_analysis' => [], 'recommendation' => ''];

        return view('livewire.guardian-dashboard', [
            'pace'            => $this->buildPace($analysis['subject_analysis'] ?? []),
            'recommendation'  => $analysis['recommendation'] ?? '',
            'writingFeedback' => $this->latestWritingFeedback($student),
        ]);
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
        $ela  = $subjectAnalysis['ELA'] ?? null;

        return [
            'Math' => [
                'weight'       => self::PAPER_WEIGHTS['Math'],
                'expected'     => $math['expected'] ?? 0,
                'completed'    => $math['completed'] ?? 0,
                'behind_count' => $math['behind_count'] ?? 0,
                'assessed'     => $math !== null,
            ],
            'ELA' => [
                'weight'       => self::PAPER_WEIGHTS['ELA'],
                'expected'     => $ela['expected'] ?? 0,
                'completed'    => $ela['completed'] ?? 0,
                'behind_count' => $ela['behind_count'] ?? 0,
                'assessed'     => $ela !== null,
            ],
            'Writing' => [
                'weight'       => self::PAPER_WEIGHTS['Writing'],
                'expected'     => 0,
                'completed'    => 0,
                'behind_count' => 0,
                'assessed'     => false,
            ],
        ];
    }

    private function latestWritingFeedback(?User $student): ?array
    {
        return null;
    }
}
