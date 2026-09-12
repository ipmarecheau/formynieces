<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\PracticeAttempt;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingSubmission;
use App\Services\ExamAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mobile parent app — the calm control room. Screen-shaped JSON that answers
 * "what should my child practise next, why, and are they getting ready?"
 * Contract: MOBILE_API_CONTRACT.md. Features: mobile_parent_app.feature (MP-01..07).
 *
 * Reuses the honest-layer logic the web guardian dashboard already trusts
 * (ExamAgentService::analyse, StudentProgress, WritingSubmission, StudentStreak)
 * rather than duplicating pace/readiness rules.
 */
class ParentController extends Controller
{
    /** Below this many practice answers, readiness is "building evidence", not a signal (MP-05). */
    private const READINESS_EVIDENCE_MIN = 10;

    public function __construct(private ExamAgentService $examAgent) {}

    /** MP-01 — child cards with latest activity + attention status. */
    public function children(Request $request): JsonResponse
    {
        $children = $request->user()->students()->get()->map(function (User $child) {
            $analysis = $this->examAgent->analyse($child);

            return [
                'id' => $child->id,
                'name' => $child->name,
                'standard' => $child->seaStandardLabel(),
                'latest_activity' => $this->latestActivityLabel($child),
                'status' => $this->status($analysis),
                'status_label' => $this->statusLabel($child, $analysis),
                'streak_days' => $this->streakDays($child),
            ];
        })->values();

        return response()->json(['children' => $children]);
    }

    /** MP-02 — the child overview: next action + why + activity + one action. */
    public function overview(Request $request, User $child): JsonResponse
    {
        $this->authorizeChild($request, $child);
        $analysis = $this->examAgent->analyse($child);
        $weak = $this->weakModules($child);
        $top = $weak->first();

        return response()->json([
            'child' => $this->childStub($child),
            'next_action' => [
                'title' => $top
                    ? "Practise {$top->topic} next"
                    : 'Keep the daily practice going',
                'why' => $top
                    ? "It's her weakest {$top->subject} topic right now."
                    : ($analysis['recommendation'] ?? 'She is keeping pace with the plan.'),
                'cta' => 'Assign a 10-minute practice',
            ],
            'weekly_summary' => [
                'sessions_completed' => $this->sessionsThisWeek($child),
                'streak_days' => $this->streakDays($child),
                'last_practised_at' => optional($this->lastAttempt($child))?->created_at?->toIso8601String(),
            ],
            'cards' => [
                'weak_topics' => $weak->count(),
                'writing_status' => $this->writingStatusLabel($child),
                'readiness_status' => $this->readinessVerdict($analysis)['headline'],
            ],
        ]);
    }

    /** MP-03 — weak topics, worst first, each actionable. */
    public function weakTopics(Request $request, User $child): JsonResponse
    {
        $this->authorizeChild($request, $child);

        $topics = $this->weakModules($child)->map(fn (SyllabusModule $m) => [
            'id' => $m->id,
            'subject' => $m->subject,
            'title' => $m->topic,
            'reason' => 'Not yet mastered against the syllabus pace',
            'suggested_action' => 'Do one 10-minute practice set today',
            'severity' => 'high',
        ])->values();

        return response()->json(['topics' => $topics]);
    }

    /** MP-06 — latest writing status in parent-friendly language. */
    public function writing(Request $request, User $child): JsonResponse
    {
        $this->authorizeChild($request, $child);

        $submission = WritingSubmission::where('student_id', $child->id)
            ->latest('created_at')->first();

        if ($submission === null) {
            return response()->json(['status' => 'none', 'latest' => null]);
        }

        return response()->json([
            'status' => $submission->scored_at !== null ? 'feedback_ready' : 'submitted',
            'latest' => [
                'prompt' => $submission->prompt?->prompt,
                'submitted_at' => $submission->created_at?->toIso8601String(),
                'summary' => trim(($submission->did_well ?? '').' '.($submission->try_next ?? '')) ?: null,
                'skills' => ['content', 'language', 'grammar', 'organisation'],
            ],
        ]);
    }

    /** MP-04/05 — readiness with context, never a bare alarming number. */
    public function readiness(Request $request, User $child): JsonResponse
    {
        $this->authorizeChild($request, $child);
        $analysis = $this->examAgent->analyse($child);
        $readiness = $this->readinessVerdict($analysis);
        $sessions = $this->sessionsThisWeek($child);
        $totalAttempts = PracticeAttempt::where('student_id', $child->id)->count();

        // MP-05: too little practice history for a reliable signal — don't overstate it.
        if ($totalAttempts < self::READINESS_EVIDENCE_MIN) {
            return response()->json([
                'status' => 'building_evidence',
                'headline' => "{$child->name} needs more practice before we estimate readiness.",
                'evidence' => ["{$totalAttempts} practice answers so far", 'No timed mock completed yet'],
                'next_action' => 'Complete a few short practices this week',
            ]);
        }

        return response()->json([
            'status' => $readiness['tone'],
            'headline' => $readiness['headline'],
            'evidence' => [$readiness['detail'], "{$sessions} practice sessions this week"],
            'next_action' => $analysis['recommendation'] ?? 'Keep to the weekly plan',
        ]);
    }

    // --- helpers -------------------------------------------------------------

    /** A parent may only read her own child (403 otherwise); the target must be a student. */
    private function authorizeChild(Request $request, User $child): void
    {
        abort_unless(
            $child->isStudent() && $child->parent_id === $request->user()->id,
            Response::HTTP_FORBIDDEN,
            'This child is not linked to your account.',
        );
    }

    /** @return array{id:int,name:string,standard:string} */
    private function childStub(User $child): array
    {
        return ['id' => $child->id, 'name' => $child->name, 'standard' => $child->seaStandardLabel()];
    }

    /** @return Collection<int, SyllabusModule> weakest-first */
    private function weakModules(User $child)
    {
        $weakIds = StudentProgress::where('student_id', $child->id)
            ->where('status', 'needs_work')
            ->pluck('module_id');

        return SyllabusModule::whereIn('id', $weakIds)
            ->orderBy('pacing_week')->orderBy('sequence_order')->get();
    }

    private function lastAttempt(User $child): ?PracticeAttempt
    {
        return PracticeAttempt::where('student_id', $child->id)->latest('created_at')->first();
    }

    private function latestActivityLabel(User $child): string
    {
        $last = $this->lastAttempt($child);

        return $last === null
            ? 'No practice yet'
            : 'Last practised '.$last->created_at->diffForHumans();
    }

    private function sessionsThisWeek(User $child): int
    {
        return PracticeAttempt::where('student_id', $child->id)
            ->where('created_at', '>=', now()->startOfWeek())
            ->distinct('module_id')->count('module_id');
    }

    private function streakDays(User $child): int
    {
        return (int) (StudentStreak::where('student_id', $child->id)->max('count') ?? 0);
    }

    private function writingStatusLabel(User $child): string
    {
        $s = WritingSubmission::where('student_id', $child->id)->latest('created_at')->first();
        if ($s === null) {
            return 'No writing yet';
        }

        return $s->scored_at !== null ? 'Feedback ready' : 'Awaiting feedback';
    }

    /** @param array<string,mixed> $analysis */
    private function status(array $analysis): string
    {
        return match ($analysis['overall_status'] ?? null) {
            'at_risk', 'slight_risk' => 'needs_attention',
            'on_track' => 'on_track',
            default => 'getting_started',
        };
    }

    /** @param array<string,mixed> $analysis */
    private function statusLabel(User $child, array $analysis): string
    {
        $top = $this->weakModules($child)->first();

        return match ($analysis['overall_status'] ?? null) {
            'at_risk', 'slight_risk' => $top ? "Practise {$top->topic} next" : 'A little to catch up',
            'on_track' => 'On track for SEA',
            default => 'Getting started',
        };
    }

    /**
     * @param  array<string,mixed>  $analysis
     * @return array{tone:string,headline:string,detail:string}
     */
    private function readinessVerdict(array $analysis): array
    {
        $status = $analysis['overall_status'] ?? null;
        $behind = (int) ($analysis['total_behind'] ?? 0);

        return match ($status) {
            'on_track' => ['tone' => 'on_track', 'headline' => 'On track for the exam', 'detail' => 'Every subject is keeping pace with the syllabus calendar.'],
            'slight_risk' => ['tone' => 'watch', 'headline' => 'Mostly on pace, a little to catch up', 'detail' => "{$behind} modules behind — well within reach this term."],
            'at_risk' => ['tone' => 'behind', 'headline' => 'Behind pace — a catch-up plan is in place', 'detail' => "{$behind} modules behind the calendar; the plan breaks it into weekly steps."],
            default => ['tone' => 'building_evidence', 'headline' => 'Getting started', 'detail' => 'Once the diagnostic and first modules are in, readiness will show here.'],
        };
    }
}
