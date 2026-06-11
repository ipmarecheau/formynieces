<?php

namespace App\Services;

use App\Models\User;
use App\Models\SyllabusModule;
use App\Models\StudentProgress;
use Carbon\Carbon;

class ExamAgentService
{

    public function __construct(private GroqService $groq) {}
    /*
    |------------------------------------------------------------------
    | SEA EXAM CONFIGURATION
    |------------------------------------------------------------------
    | Term 1: Sep 1 – Dec 19 (16 weeks)
    | Term 2: Jan 5 – Mar 27 (12 weeks)
    | Term 3: Mar 30 – Apr 9  (2 teaching weeks)
    | Total teaching weeks: 30
    | Revision buffer: 6 weeks before exam
    | Exam date: May 21
    |------------------------------------------------------------------
    */

    const TERM_1_START    = '2025-09-01';
    const EXAM_DATE       = '2026-05-21';
    const TOTAL_WEEKS     = 30;
    const REVISION_WEEKS  = 6;

    // Term break ranges to skip when calculating current week
    const TERM_BREAKS = [
        ['2025-12-20', '2026-01-04'], // Christmas break
        ['2026-03-28', '2026-03-29'], // Easter break
    ];

    public function generateSummary(array $analysis, string $audience = 'student'): string
    {
        $behind  = $analysis['total_behind'];
        $status  = $analysis['overall_status'];
        $week    = $analysis['current_week'];
        $weeks   = $analysis['weeks_to_exam'];

        $subjects = collect($analysis['subject_analysis'])
            ->map(fn($s) => "{$s['subject']}: {$s['status']}, {$s['behind_count']} modules behind")
            ->implode('. ');

        if ($audience === 'guardian') {
            $system = <<<PROMPT
    You are a clear, warm, and direct assistant writing weekly progress briefings for guardians
    of primary school students preparing for the SEA exam in Trinidad and Tobago.
    Write in plain English a parent or guardian can understand easily.
    Never use jargon. Be specific. Be encouraging but honest.
    Always end with exactly 3 numbered actions the guardian can take this week.
    Maximum 150 words.
    PROMPT;

            $user = "Student is in week {$week} of 30. SEA is in {$weeks} weeks. "
                . "Overall status: {$status}. {$subjects}. "
                . "Total weeks behind: {$behind}. "
                . "Write the weekly guardian briefing.";
        } else {
            $system = <<<PROMPT
    You are a warm, encouraging study coach writing directly to a 10-11 year old girl
    preparing for the SEA exam in Trinidad and Tobago.
    Use simple, friendly language. Be specific about what to do next.
    Never be alarming. Focus on what she CAN do.
    Always end with exactly 3 numbered actions she can take today.
    Maximum 120 words.
    PROMPT;

            $user = "Student is in week {$week} of 30. SEA is in {$weeks} weeks. "
                . "Overall status: {$status}. {$subjects}. "
                . "Total weeks behind: {$behind}. "
                . "Write the student summary.";
        }

        return $this->groq->complete($system, $user, 512);
    }

    public function generateWritingFeedback(string $submission, string $prompt, string $type): array
    {
        $system = <<<PROMPT
    You are an experienced primary school English teacher in Trinidad and Tobago
    marking SEA writing practice. Score each dimension out of 10 and give one specific,
    actionable improvement tip per dimension.
    You must respond with valid JSON only. No preamble, no markdown, no backticks.
    Return exactly this structure:
    {
    "content": {"score": 7, "feedback": "..."},
    "language": {"score": 7, "feedback": "..."},
    "grammar": {"score": 8, "feedback": "..."},
    "organisation": {"score": 6, "feedback": "..."},
    "overall": "One sentence overall comment."
    }
    PROMPT;

        $user = "Writing type: {$type}.\nPrompt given: {$prompt}.\nStudent submission:\n{$submission}";

        $result = $this->groq->completeJson($system, $user, 800);

        return empty($result) ? [
            'content'      => ['score' => 0, 'feedback' => 'Unable to generate feedback.'],
            'language'     => ['score' => 0, 'feedback' => 'Unable to generate feedback.'],
            'grammar'      => ['score' => 0, 'feedback' => 'Unable to generate feedback.'],
            'organisation' => ['score' => 0, 'feedback' => 'Unable to generate feedback.'],
            'overall'      => 'Feedback unavailable. Please try again.',
        ] : $result;
    }
    public function analyse(User $student): array
    {
        $currentWeek    = $this->getCurrentTeachingWeek();
        $examDate       = Carbon::parse(self::EXAM_DATE);
        $weeksToExam    = max(0, Carbon::now()->diffInWeeks($examDate, false));
        $inRevision     = $currentWeek > self::TOTAL_WEEKS;

        // All modules ordered by pacing week
        $allModules = SyllabusModule::orderBy('pacing_week')
                                    ->orderBy('sequence_order')
                                    ->get();

        // Student's progress keyed by module_id
        $progressMap = StudentProgress::where('student_id', $student->id)
                                      ->get()
                                      ->keyBy('module_id');

        // Modules expected to be done by current week
        $expectedModules = $allModules->where('pacing_week', '<=', $currentWeek);

        // Analyse each subject separately
        $subjectAnalysis = [];
        foreach (['Math', 'English Editing', 'English Comprehension'] as $subject) {

            $expected = $expectedModules->where('subject', $subject);
            $total    = $allModules->where('subject', $subject)->count();

            $completed  = 0;
            $behind     = [];
            $onTrack    = [];
            $ahead      = [];

            foreach ($expected as $module) {
                $progress = $progressMap->get($module->id);
                $status   = $progress?->status ?? 'not_started';

                if (in_array($status, ['mastered', 'diagnostic_passed'])) {
                    $completed++;
                    $onTrack[] = $module;
                } else {
                    $behind[] = $module;
                }
            }

            // Modules ahead of schedule
            $aheadModules = $allModules
                ->where('subject', $subject)
                ->where('pacing_week', '>', $currentWeek);

            foreach ($aheadModules as $module) {
                $progress = $progressMap->get($module->id);
                if (in_array($progress?->status, ['mastered', 'diagnostic_passed'])) {
                    $ahead[] = $module;
                }
            }

            $expectedCount  = $expected->count();
            $behindCount    = count($behind);
            $weeksLost      = $behindCount > 0
                ? ceil($behindCount / $this->modulesPerWeek($subject))
                : 0;

            $subjectAnalysis[$subject] = [
                'subject'        => $subject,
                'expected'       => $expectedCount,
                'completed'      => $completed,
                'behind_modules' => $behind,
                'ahead_modules'  => $ahead,
                'behind_count'   => $behindCount,
                'weeks_lost'     => $weeksLost,
                'total'          => $total,
                'status'         => $this->subjectStatus($behindCount, $expectedCount),
            ];
        }

        // Overall recommendation
        $totalBehind = collect($subjectAnalysis)->sum('behind_count');
        $recommendation = $this->buildRecommendation(
            $subjectAnalysis,
            $currentWeek,
            $weeksToExam,
            $inRevision
        );

        return [
            'current_week'    => $currentWeek,
            'weeks_to_exam'   => $weeksToExam,
            'exam_date'       => $examDate->format('M d, Y'),
            'in_revision'     => $inRevision,
            'total_behind'    => $totalBehind,
            'subject_analysis'=> $subjectAnalysis,
            'recommendation'  => $recommendation,
            'overall_status'  => $totalBehind === 0 ? 'on_track' :
                                 ($totalBehind <= 3  ? 'slight_risk' : 'at_risk'),
        ];
    }

    private function getCurrentTeachingWeek(): int
    {
        $start   = Carbon::parse(self::TERM_1_START);
        $today   = Carbon::now();

        if ($today->lt($start)) {
            return 0;
        }

        $totalDays    = $start->diffInDays($today);
        $breakDays    = $this->countBreakDays($start, $today);
        $teachingDays = $totalDays - $breakDays;
        $week         = (int) ceil($teachingDays / 5);

        return min($week, self::TOTAL_WEEKS + self::REVISION_WEEKS);
    }

    private function countBreakDays(Carbon $start, Carbon $today): int
    {
        $breakDays = 0;
        foreach (self::TERM_BREAKS as $break) {
            $breakStart = Carbon::parse($break[0]);
            $breakEnd   = Carbon::parse($break[1]);

            if ($today->gt($breakStart)) {
                $overlapStart = $breakStart->max($start);
                $overlapEnd   = $breakEnd->min($today);
                if ($overlapEnd->gte($overlapStart)) {
                    $breakDays += $overlapStart->diffInDays($overlapEnd) + 1;
                }
            }
        }
        return $breakDays;
    }

    private function modulesPerWeek(string $subject): float
    {
        return match($subject) {
            'Math'                   => 3.0,
            'English Editing'        => 1.0,
            'English Comprehension'  => 1.0,
            default                  => 1.0,
        };
    }

    private function subjectStatus(int $behindCount, int $expectedCount): string
    {
        if ($expectedCount === 0) return 'not_started';
        if ($behindCount === 0)   return 'on_track';
        $ratio = $behindCount / max($expectedCount, 1);
        return $ratio <= 0.2 ? 'slight_risk' : 'at_risk';
    }

    private function buildRecommendation(
        array $analysis,
        int $currentWeek,
        int $weeksToExam,
        bool $inRevision
    ): string {
        if ($inRevision) {
            return "You're in the revision period — focus on past papers and weak areas before the exam on " . Carbon::parse(self::EXAM_DATE)->format('M d, Y') . ".";
        }

        $atRisk = collect($analysis)->filter(fn($s) => $s['status'] === 'at_risk');
        $slight = collect($analysis)->filter(fn($s) => $s['status'] === 'slight_risk');

        if ($atRisk->isEmpty() && $slight->isEmpty()) {
            return "Great work! You're on track across all subjects. Keep up the weekly pace to stay ahead for your revision period.";
        }

        $parts = [];
        foreach ($atRisk as $subject => $data) {
            $next = collect($data['behind_modules'])->first();
            $parts[] = "You are {$data['behind_count']} topic(s) behind in {$subject}" .
                       ($next ? " — start with \"{$next->topic}\"" : "") . ".";
        }
        foreach ($slight as $subject => $data) {
            $parts[] = "You are slightly behind in {$subject} ({$data['behind_count']} topic(s)).";
        }

        $parts[] = "You have {$weeksToExam} weeks until the exam. Stay focused!";

        return implode(' ', $parts);
    }
}