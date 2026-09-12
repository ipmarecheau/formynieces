<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\MobilePracticeSession;
use App\Models\PracticeQuestion;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\QuestionExposure;
use App\Services\Practice\RecordPracticeAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mobile child app — the daily SEA habit. Opens on Today's mission, one tap into a
 * short touch-first session, immediate feedback, a reason to return.
 * Contract: MOBILE_API_CONTRACT.md. Features: mobile_child_app.feature (MC-01..07).
 *
 * Practice attempts write to the SAME learning record as the web loop
 * (RecordPracticeAttempt → practice_attempts / student_progress), and exposures are
 * recorded on ANSWER (QuestionExposure), matching the serve-vs-answer fix.
 */
class ChildController extends Controller
{
    private const SESSION_SIZE = 5;

    private const CHOICE_LETTERS = ['A', 'B', 'C', 'D'];

    /** MC-01/MC-02 — Today: Smooth + the next mission, or a calm empty state. */
    public function today(Request $request): JsonResponse
    {
        $child = $request->user();
        $module = $this->nextMissionModule($child);

        if ($module === null) {
            return response()->json([
                'child' => ['id' => $child->id, 'name' => $child->name],
                'smooth_message' => "You're all caught up! Check back soon for your next mission.",
                'mission' => null,
                'streak' => $this->streak($child),
                'voyage' => ['current_island' => null, 'progress_label' => 'Ready for what’s next'],
            ]);
        }

        return response()->json([
            'child' => ['id' => $child->id, 'name' => $child->name],
            'smooth_message' => "Today we work on {$module->topic}, then you earn progress on your voyage.",
            'mission' => [
                'id' => "m{$module->id}",
                'subject' => $module->subject,
                'topic' => $module->topic,
                'estimated_minutes' => 10,
                'cta' => 'Start mission',
            ],
            'streak' => $this->streak($child),
            'voyage' => ['current_island' => $module->strand ?? $module->subject, 'progress_label' => 'Keep it up!'],
        ]);
    }

    /** MC-03 — start a short session and serve the first question. */
    public function start(Request $request): JsonResponse
    {
        $data = $request->validate(['mission_id' => ['required', 'string']]);
        $child = $request->user();

        $moduleId = (int) ltrim($data['mission_id'], 'm');
        $module = SyllabusModule::find($moduleId);
        abort_if($module === null, Response::HTTP_NOT_FOUND, 'Unknown mission.');

        $rung = (int) (StudentProgress::where('student_id', $child->id)
            ->where('module_id', $module->id)->value('current_rung') ?? 1);

        $questions = $this->pickQuestions($child, $module->id, $rung);
        abort_if($questions->isEmpty(), Response::HTTP_CONFLICT, 'No questions available for this mission yet.');

        $session = MobilePracticeSession::create([
            'student_id' => $child->id,
            'module_id' => $module->id,
            'question_ids' => $questions->pluck('id')->all(),
            'position' => 0,
            'answers' => [],
        ]);

        return response()->json([
            'session_id' => $session->id,
            'total_questions' => $questions->count(),
            'current_question' => $this->questionPayload($questions->first()),
        ]);
    }

    /** MC-03/MC-04 — record one answer, give feedback, hand back the next question. */
    public function answer(Request $request, MobilePracticeSession $session): JsonResponse
    {
        $this->authorizeSession($request, $session);
        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'choice_id' => ['required', 'string'],
        ]);

        abort_unless(
            ($session->question_ids[$session->position] ?? null) === $data['question_id'],
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'That is not the current question for this session.',
        );

        $question = PracticeQuestion::findOrFail($data['question_id']);
        $choiceIndex = array_search(strtoupper($data['choice_id']), self::CHOICE_LETTERS, true);
        abort_if($choiceIndex === false, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid choice.');

        // Same learning record as the web loop; exposure recorded on ANSWER.
        app(RecordPracticeAttempt::class)->handle($session->student_id, $question->id, $choiceIndex, 1);
        app(QuestionExposure::class)->record($session->student_id, $question->content_hash, 'practice');

        $correct = $choiceIndex === $question->correct_index;
        $answers = $session->answers;
        $answers[] = ['question_id' => $question->id, 'correct' => $correct];
        $session->update(['answers' => $answers, 'position' => $session->position + 1]);

        $nextId = $session->question_ids[$session->position] ?? null;
        $next = $nextId ? PracticeQuestion::find($nextId) : null;

        return response()->json([
            'correct' => $correct,
            'feedback' => $correct
                ? ($question->explanation ?: 'Nice work!')
                : ('Not quite. '.($question->explanation ?: 'Let’s look at this one again.')),
            'next_question' => $next ? $this->questionPayload($next) : null,
            'progress' => ['answered' => $session->position, 'total' => count($session->question_ids)],
        ]);
    }

    /** MC-05 — result: accuracy, a simple celebration, streak, next step. */
    public function finish(Request $request, MobilePracticeSession $session): JsonResponse
    {
        $this->authorizeSession($request, $session);

        $total = count($session->question_ids);
        $answered = collect($session->answers);
        $correct = $answered->where('correct', true)->count();
        $accuracy = $total > 0 ? (int) round($correct / $total * 100) : 0;

        if ($session->finished_at === null) {
            $session->update(['finished_at' => now()]);
        }

        $module = $session->module;

        return response()->json([
            'accuracy' => $accuracy,
            'celebration' => $accuracy >= 60
                ? "Great work — you strengthened {$module?->topic}!"
                : "Good effort — every try makes {$module?->topic} easier.",
            'streak' => $this->streak($session->student),
            'next_action' => 'Return tomorrow for your next mission',
        ]);
    }

    // --- helpers -------------------------------------------------------------

    private function authorizeSession(Request $request, MobilePracticeSession $session): void
    {
        abort_unless(
            $session->student_id === $request->user()->id,
            Response::HTTP_FORBIDDEN,
            'This is not your practice session.',
        );
    }

    /** The next topic worth practising: weakest first, else any not-yet-mastered. */
    private function nextMissionModule(User $child): ?SyllabusModule
    {
        $progress = StudentProgress::where('student_id', $child->id)->get()->keyBy('module_id');

        $needsWork = $progress->firstWhere('status', 'needs_work');
        if ($needsWork) {
            return SyllabusModule::find($needsWork->module_id);
        }

        // Otherwise the first module she has not mastered, by pacing order.
        $masteredIds = $progress->whereIn('status', ['mastered', 'diagnostic_passed'])->pluck('module_id');

        return SyllabusModule::whereNotIn('id', $masteredIds)
            ->orderBy('pacing_week')->orderBy('sequence_order')->first();
    }

    /** @return Collection<int, PracticeQuestion> */
    private function pickQuestions(User $child, int $moduleId, int $rung)
    {
        $atRung = app(PracticeQuestions::class)->forModule($moduleId)->where('difficulty', $rung)->values();
        $seen = app(QuestionExposure::class)->seenHashes($child->id);

        $unseen = $atRung->reject(fn (PracticeQuestion $q) => in_array($q->content_hash, $seen, true))->values();
        $pool = $unseen->isNotEmpty() ? $unseen : $atRung;

        return $pool->take(self::SESSION_SIZE);
    }

    /** @return array{id:int,prompt:string,choices:array<int,array{id:string,text:string}>} */
    private function questionPayload(PracticeQuestion $q): array
    {
        $choices = [];
        foreach (array_values($q->options) as $i => $text) {
            $choices[] = ['id' => self::CHOICE_LETTERS[$i] ?? (string) $i, 'text' => $text];
        }

        return ['id' => $q->id, 'prompt' => $q->prompt, 'choices' => $choices];
    }

    /** @return array{days:int,label:string} */
    private function streak(User $child): array
    {
        $days = (int) (StudentStreak::where('student_id', $child->id)->max('count') ?? 0);

        return ['days' => $days, 'label' => "{$days}-day streak"];
    }
}
