<?php

namespace App\Livewire;

use App\Models\DailyReadingAssignment;
use App\Models\StudentPause;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Models\WritingPrompt;
use App\Models\WritingSubmission;
use App\Services\Diagnostic\DiagnosticReconciliation;
use App\Services\Diagnostic\ReconciliationResolver;
use App\Services\Diagnostic\SessionLifecycle;
use App\Services\Estimator\PerformanceEstimator;
use App\Services\ExamAgentService;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Pacing\PauseService;
use App\Services\SchoolJournal\SchoolEvidenceService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

class GuardianDashboard extends Component
{
    public bool $targetCompleted = false;

    public ?string $paceStatus = null;

    public int $weeksBehind = 0;

    public bool $onTrack = false;

    /** GD-07 — the child this dashboard is about; null = the guardian's first student. */
    #[Url]
    public ?int $studentId = null;

    /** Which sidebar section is showing: overview | this-week | pace | estimator | rewards. */
    #[Url]
    public string $section = 'overview';

    /**
     * GD-01 — the exam agent's plain-English guardian briefing. Loaded lazily
     * (wire:init) so the LLM round-trip never blocks first paint, and cached
     * per student per teaching-week so we call the model at most once a week.
     */
    public ?string $aiSummary = null;

    public bool $aiSummaryLoaded = false;

    private const PAPER_WEIGHTS = ['Math' => 50, 'ELA' => 30, 'Writing' => 20];

    private const TOTAL_TEACHING_WEEKS = 30;

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
            : ['subject_analysis' => [], 'recommendation' => '', 'overall_status' => null];

        $reconciliation = app(DiagnosticReconciliation::class);
        $reconciliationPending = $student !== null && $reconciliation->isPending($student);

        $subjectAnalysis = $analysis['subject_analysis'] ?? [];

        return view('livewire.guardian-dashboard', [
            'pace' => $this->buildPace($subjectAnalysis),
            'recommendation' => $analysis['recommendation'] ?? '',
            'writingFeedback' => $this->latestWritingFeedback($student),
            'triage' => $this->buildTriage($subjectAnalysis),
            'reconciliationPending' => $reconciliationPending,
            'clearedStrands' => $reconciliationPending ? $reconciliation->clearedStrands($student) : [],
            'studentName' => $student?->name,
            'student' => $student,
            'students' => $students,
            'weekLabel' => 'Week of '.Carbon::today()->startOfWeek()->format('j M Y'),
            'examDate' => $this->resolveExamDate($journey, $analysis),
            'daysToExam' => $this->daysToExam($journey, $analysis),
            'currentWeek' => (int) ($analysis['current_week'] ?? 0),
            'readiness' => $this->buildReadiness($analysis),
            'trajectory' => $this->buildTrajectory($subjectAnalysis, (int) ($analysis['current_week'] ?? 0)),
            'excelling' => $this->buildExcelling($subjectAnalysis, $student),
            'streaks' => $this->buildStreaks($student),
            'perks' => $this->buildPerks($student),
            'rewardTypes' => [
                'shore_leave' => 'Shore Leave',
                'anchor' => 'Anchor',
                'tailwind' => 'Tailwind',
                'lifebuoy' => 'Lifebuoy',
            ],
            'schoolThisWeek' => $student
                ? app(SchoolEvidenceService::class)->thisWeek($student->id)
                : collect(),
            'pauseHistory' => $student
                ? StudentPause::where('student_id', $student->id)->orderByDesc('paused_at')->get()
                : collect(),
            'isPaused' => (bool) ($student?->isPaused()),
            'estimate' => $student
                ? app(PerformanceEstimator::class)->estimate($student, $subjectAnalysis)
                : null,
            'weeklyPlan' => $this->buildWeeklyPlan($student),
            'paceCalendar' => $this->buildPaceCalendar($student, $journey, (int) ($analysis['current_week'] ?? 0)),
            'paceUpdatedAt' => $journey?->pace_recalculated_at,
        ]);
    }

    /**
     * GD-01 — lazily generate the exam agent's plain-English guardian briefing.
     * Cached for a week per student so the model is called at most once a week;
     * degrades to the LlmService fallback string on any error.
     */
    public function loadAiSummary(ExamAgentService $examAgent): void
    {
        $this->aiSummaryLoaded = true;

        $student = $this->resolveStudent();
        if ($student === null) {
            $this->aiSummary = null;

            return;
        }

        $analysis = $examAgent->analyse($student);
        $week = (int) ($analysis['current_week'] ?? 0);

        $this->aiSummary = Cache::remember(
            "guardian-ai-summary:{$student->id}:{$week}",
            now()->addWeek(),
            fn (): string => $examAgent->generateSummary($analysis, 'guardian'),
        );
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
     * The exam date shown in the header — the student's own journey date when
     * seeded, otherwise the syllabus-wide date the exam agent computes against.
     */
    private function resolveExamDate($journey, array $analysis): ?string
    {
        $date = $journey?->exam_date;
        if ($date) {
            return Carbon::parse($date)->format('j M Y');
        }

        return $analysis['exam_date'] ?? null;
    }

    private function daysToExam($journey, array $analysis): ?int
    {
        $date = $journey?->exam_date ?? ($analysis['exam_date'] ?? null);
        if (! $date) {
            return null;
        }

        return max(0, (int) Carbon::today()->diffInDays(Carbon::parse($date), false));
    }

    /**
     * GD-01/GD-11 — a single plain-language readiness verdict that leads the
     * screen before any raw count. Direct, honest, and never alarming.
     *
     * @return array{tone: string, headline: string, detail: string}
     */
    private function buildReadiness(array $analysis): array
    {
        $status = $analysis['overall_status'] ?? null;
        $behind = (int) ($analysis['total_behind'] ?? 0);

        return match ($status) {
            'on_track' => [
                'tone' => 'good',
                'headline' => 'On track for the exam',
                'detail' => 'Every subject is keeping pace with the syllabus calendar.',
            ],
            'slight_risk' => [
                'tone' => 'watch',
                'headline' => 'Mostly on pace, a little to catch up',
                'detail' => $behind.' '.($behind === 1 ? 'module' : 'modules').' behind across all subjects — well within reach this term.',
            ],
            'at_risk' => [
                'tone' => 'warn',
                'headline' => 'Behind pace — a catch-up plan is in place',
                'detail' => $behind.' modules behind the calendar. The plan below breaks this into feasible weekly steps.',
            ],
            default => [
                'tone' => 'neutral',
                'headline' => 'Getting started',
                'detail' => 'Once the diagnostic and first modules are in, readiness will show here.',
            ],
        };
    }

    /**
     * The pace trajectory: the required-pace line (the plan, rising to the full
     * syllabus by the exam) against where the student actually is now. Truthful
     * as a snapshot — the plan is the line, the marker is the child's position.
     *
     * @return array{
     *   required_pct: int, actual_pct: int, current_week: int, total_weeks: int,
     *   completed: int, expected: int, total: int, on_or_ahead: bool
     * }
     */
    private function buildTrajectory(array $subjectAnalysis, int $currentWeek): array
    {
        $completed = 0;
        $expected = 0;
        $total = 0;

        foreach ($subjectAnalysis as $s) {
            $completed += (int) ($s['completed'] ?? 0);
            $expected += (int) ($s['expected'] ?? 0);
            $total += (int) ($s['total'] ?? 0);
        }

        $week = max(0, min($currentWeek, self::TOTAL_TEACHING_WEEKS));

        return [
            'required_pct' => (int) round(($week / self::TOTAL_TEACHING_WEEKS) * 100),
            'actual_pct' => $total > 0 ? (int) round(($completed / $total) * 100) : 0,
            'current_week' => $week,
            'total_weeks' => self::TOTAL_TEACHING_WEEKS,
            'completed' => $completed,
            'expected' => $expected,
            'total' => $total,
            'on_or_ahead' => $completed >= $expected,
        ];
    }

    /**
     * Where the child is excelling — the honest, visible reason for a proud
     * parent moment: subjects ahead of schedule, and a strong recent essay.
     *
     * @return array<int, array{facet: string, detail: string}>
     */
    private function buildExcelling(array $subjectAnalysis, ?User $student): array
    {
        $highlights = [];

        foreach ($subjectAnalysis as $s) {
            $ahead = count($s['ahead_modules'] ?? []);
            if ($ahead > 0) {
                $name = $s['subject'] === 'Math' ? 'Mathematics' : $s['subject'];
                $highlights[] = [
                    'facet' => $name,
                    'detail' => $ahead.' '.($ahead === 1 ? 'module' : 'modules').' mastered ahead of schedule',
                ];
            }
        }

        if ($student !== null) {
            $writing = WritingSubmission::where('student_id', $student->id)
                ->whereNotNull('scored_at')
                ->latest('scored_at')
                ->first();

            if ($writing !== null) {
                $avg = ($writing->content_score + $writing->language_score
                    + $writing->grammar_score + $writing->organisation_score) / 4;
                if ($avg >= 7) {
                    $highlights[] = [
                        'facet' => 'Writing',
                        'detail' => 'Strong recent essay — averaging '.round($avg, 1).'/10 across the four marks',
                    ];
                }
            }
        }

        return $highlights;
    }

    /**
     * Streaks shown to the parent as data — how consistently the child shows up.
     * This is reporting, not the child's celebration styling (GD-05): plain
     * counts, no fire/party iconography.
     *
     * @return array<int, array{label: string, count: int}>
     */
    private function buildStreaks(?User $student): array
    {
        if ($student === null) {
            return [];
        }

        $rows = StudentStreak::where('student_id', $student->id)->get()->keyBy('type');

        $labels = [
            StreakEconomyService::MASTER_STREAK => 'Overall',
            'reading' => 'Reading',
            'vocabulary' => 'Vocabulary',
            'writing' => 'Writing',
            'map' => 'Lessons',
        ];

        $out = [];
        foreach ($labels as $type => $label) {
            $count = (int) ($rows->get($type)?->count ?? 0);
            if ($count > 0 || $type === StreakEconomyService::MASTER_STREAK) {
                $out[] = ['label' => $label, 'count' => $count];
            }
        }

        return $out;
    }

    /**
     * Perks the child currently holds in the Locker — visibility for the parent
     * of the rewards their child has earned or been granted.
     *
     * @return array<int, array{label: string, count: int}>
     */
    private function buildPerks(?User $student): array
    {
        if ($student === null) {
            return [];
        }

        $economy = app(StreakEconomyService::class);
        $labels = [
            'shore_leave' => 'Shore Leave',
            'anchor' => 'Anchor',
            'tailwind' => 'Tailwind',
            'lifebuoy' => 'Lifebuoy',
        ];

        $out = [];
        foreach ($labels as $type => $label) {
            $out[] = ['label' => $label, 'count' => $economy->balance($student->id, $type)];
        }

        return $out;
    }

    /**
     * Calm triage for a significantly-behind student (4+ weeks behind the pacing
     * calendar). Returns null when on pace, so the view renders nothing.
     *
     * Subjects are ordered by paper weight (Math first at 50%), and each behind
     * subject is expressed as feasible weekly steps — never a raw deficit total.
     *
     * @return array{weeks_behind: int, priority: string, subjects: array<int, array{name: string, weekly_step: string}>}|null
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

    /**
     * The latest scored writing submission, surfaced as the Q4 pointer and the
     * writing-facet detail.
     *
     * @return array{did_well: ?string, try_next: ?string, average: float, scored_at: ?string}|null
     */
    /**
     * This week's concrete plan for the parent: the topics set for the week, the
     * shared weekly writing assignment, and the reading passages scheduled for
     * the student this week — the "what is she actually doing this week" detail.
     *
     * @return array{topics: array<int, array{topic: ?string, subject: ?string, done: bool}>, writing: ?WritingPrompt, reading: array<int, array{title: string, done: bool}>}
     */
    private function buildWeeklyPlan(?User $student): array
    {
        if ($student === null) {
            return ['topics' => [], 'writing' => null, 'reading' => []];
        }

        $weekStart = Carbon::today()->startOfWeek();

        $topics = WeeklyTarget::with('module')
            ->where('student_id', $student->id)
            ->where('week_start_date', $weekStart->toDateString())
            ->get()
            ->map(fn (WeeklyTarget $t): array => [
                'topic' => $t->module?->topic,
                'subject' => $t->module?->subject,
                'done' => (bool) $t->is_completed,
            ])
            ->values()
            ->all();

        $writing = WritingPrompt::where('week_start_date', $weekStart->toDateString())->first();

        $reading = DailyReadingAssignment::with('passage')
            ->where('student_id', $student->id)
            ->whereBetween('date', [$weekStart->toDateString(), $weekStart->copy()->endOfWeek()->toDateString()])
            ->get()
            ->map(fn (DailyReadingAssignment $a): array => [
                'title' => $a->passage?->title ?? 'Reading passage',
                'done' => $a->completed_at !== null,
            ])
            ->values()
            ->all();

        return ['topics' => $topics, 'writing' => $writing, 'reading' => $reading];
    }

    /**
     * The whole syllabus as a collapsible calendar: month → week → topics, each
     * topic carrying her mastery status. Months and weeks are dated from her
     * journey_start so "this week" lands on the right row. Feeds the year/month/
     * week drill-down on the dashboard.
     *
     * @return array<int, array{key: string, label: string, is_current: bool, mastered: int, total: int, weeks: array<int, array{week_no: int, date: string, is_current: bool, mastered: int, total: int, topics: array<int, array{topic: string, subject: string, status: string}>}>}>
     */
    private function buildPaceCalendar(?User $student, $journey, int $currentWeek): array
    {
        $modules = SyllabusModule::orderBy('pacing_week')->orderBy('sequence_order')->get();

        $progress = $student
            ? StudentProgress::where('student_id', $student->id)->get()->keyBy('module_id')
            : collect();

        $anchor = $journey?->journey_start
            ? Carbon::parse($journey->journey_start)->startOfWeek()
            : Carbon::today()->startOfWeek()->subWeeks(max($currentWeek - 1, 0));

        $months = [];

        foreach ($modules as $module) {
            $weekNo = (int) $module->pacing_week;
            $weekDate = $anchor->copy()->addWeeks($weekNo - 1);
            $monthKey = $weekDate->format('Y-m');

            $status = match ($progress->get($module->id)?->status) {
                'mastered', 'diagnostic_passed' => 'mastered',
                'needs_work' => 'working',
                'inferred_mastered' => 'review',
                default => 'upcoming',
            };

            $months[$monthKey] ??= ['key' => $monthKey, 'label' => $weekDate->format('F Y'), 'weeks' => []];
            $months[$monthKey]['weeks'][$weekNo] ??= [
                'week_no' => $weekNo,
                'date' => $weekDate->format('j M'),
                'is_current' => $weekNo === $currentWeek,
                'topics' => [],
            ];
            $months[$monthKey]['weeks'][$weekNo]['topics'][] = [
                'topic' => (string) $module->topic,
                'subject' => (string) $module->subject,
                'status' => $status,
            ];
        }

        // Flatten, tally, and flag the current month/weeks.
        $out = [];
        foreach ($months as $month) {
            $weeks = array_values($month['weeks']);
            $monthMastered = 0;
            $monthTotal = 0;
            $monthCurrent = false;

            foreach ($weeks as $i => $week) {
                $mastered = count(array_filter($week['topics'], fn ($t) => $t['status'] === 'mastered'));
                $weeks[$i]['mastered'] = $mastered;
                $weeks[$i]['total'] = count($week['topics']);
                $monthMastered += $mastered;
                $monthTotal += count($week['topics']);
                $monthCurrent = $monthCurrent || $week['is_current'];
            }

            $out[] = [
                'key' => $month['key'],
                'label' => $month['label'],
                'is_current' => $monthCurrent,
                'mastered' => $monthMastered,
                'total' => $monthTotal,
                'weeks' => $weeks,
            ];
        }

        return $out;
    }

    private function latestWritingFeedback(?User $student): ?array
    {
        if ($student === null) {
            return null;
        }

        $writing = WritingSubmission::where('student_id', $student->id)
            ->whereNotNull('scored_at')
            ->latest('scored_at')
            ->first();

        if ($writing === null) {
            return null;
        }

        return [
            'did_well' => $writing->did_well,
            'try_next' => $writing->try_next,
            'average' => round((
                $writing->content_score + $writing->language_score
                + $writing->grammar_score + $writing->organisation_score
            ) / 4, 1),
            'scored_at' => $writing->scored_at?->format('j M Y'),
        ];
    }
}
