<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\MobilePracticeSession;
use App\Models\PracticeQuestion;
use App\Models\StreakReward;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Services\Motivation\DailyPlanComposer;
use App\Services\Motivation\StreakEconomyService;
use App\Services\Pacing\AdventureMapBuilder;
use App\Services\Practice\CompetencyCheck;
use App\Services\Practice\PracticeQuestions;
use App\Services\Practice\QuestionExposure;
use App\Services\Practice\RecordPracticeAttempt;
use App\Support\VoyageInteriors;
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

        // Adaptive climb (LL-13): serve one question at a time at her current rung; the
        // rung advances inside RecordPracticeAttempt as her streak clears, D1 → D3 → D5.
        $first = $this->nextPracticeQuestion($child, $module->id, []);
        abort_if($first === null, Response::HTTP_CONFLICT, 'No questions available for this mission yet.');

        $session = MobilePracticeSession::create([
            'student_id' => $child->id,
            'module_id' => $module->id,
            'question_ids' => [$first->id],
            'position' => 0,
            'answers' => [],
        ]);

        return response()->json([
            'session_id' => $session->id,
            'total_questions' => self::SESSION_SIZE, // a nominal target; the climb ends on mastery
            'current_question' => $this->questionPayload($first),
        ]);
    }

    /** One unseen question at the student's CURRENT rung (reloaded each call, so it climbs). */
    private function nextPracticeQuestion(User $child, int $moduleId, array $excludeIds): ?PracticeQuestion
    {
        $rung = (int) (StudentProgress::where('student_id', $child->id)
            ->where('module_id', $moduleId)->value('current_rung') ?? 1);
        $atRung = app(PracticeQuestions::class)->forModule($moduleId)->where('difficulty', $rung)->values();
        $seen = app(QuestionExposure::class)->seenHashes($child->id);
        $unseen = $atRung->reject(fn (PracticeQuestion $q) => in_array($q->content_hash, $seen, true) || in_array($q->id, $excludeIds, true))->values();
        $pool = $unseen->isNotEmpty() ? $unseen : $atRung->reject(fn (PracticeQuestion $q) => in_array($q->id, $excludeIds, true))->values();

        return $pool->first();
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

        // Second try (LL-14): a first-try miss on this same question earns one retry;
        // attempt 2 is a hard miss that pauses to reteach.
        $prior = collect($session->answers)->where('question_id', $question->id)->last();
        $attempt = ($prior !== null && $prior['attempt'] === 1 && $prior['correct'] === false) ? 2 : 1;

        // Same learning record + no-repeat ledger as the web loop.
        app(RecordPracticeAttempt::class)->handle($session->student_id, $question->id, $choiceIndex, $attempt);
        app(QuestionExposure::class)->record($session->student_id, $question->content_hash, 'practice');

        $correct = $choiceIndex === $question->correct_index;
        $answers = $session->answers;
        $answers[] = ['question_id' => $question->id, 'correct' => $correct, 'attempt' => $attempt];
        $session->answers = $answers;

        // First-try miss → keep the same question, let her try once more.
        if (! $correct && $attempt === 1) {
            $session->save();

            return response()->json([
                'correct' => false,
                'retry' => true,
                'feedback' => 'Not quite — take another look and try once more.',
                'next_question' => null,
            ]);
        }

        // Missed both tries → reteach (loopback to the lesson).
        if (! $correct && $attempt === 2) {
            $session->save();

            return response()->json([
                'correct' => false,
                'reteach' => true,
                'feedback' => 'No worries — let’s revisit this one together.',
                'next_question' => null,
            ]);
        }

        // Correct: has the streak just carried her to mastery?
        $progress = StudentProgress::where('student_id', $session->student_id)
            ->where('module_id', $session->module_id)->first();
        if ($progress?->status === 'mastered') {
            $session->update(['answers' => $answers, 'finished_at' => now()]);

            return response()->json(['correct' => true, 'done' => true, 'mastered' => true, 'feedback' => $question->explanation ?: 'Mastered! 🎉']);
        }

        // Serve the next question at her (possibly climbed) rung.
        $next = $this->nextPracticeQuestion($session->student, $session->module_id, $session->question_ids);
        if ($next === null) {
            $session->update(['answers' => $answers, 'finished_at' => now()]);

            return response()->json(['correct' => true, 'done' => true, 'mastered' => false, 'feedback' => $question->explanation ?: 'Nice work!']);
        }

        $ids = $session->question_ids;
        $ids[] = $next->id;
        $session->update(['answers' => $answers, 'question_ids' => $ids, 'position' => $session->position + 1]);

        return response()->json([
            'correct' => true,
            'feedback' => $question->explanation ?: 'Nice work!',
            'next_question' => $this->questionPayload($next),
            'rung' => $progress?->current_rung ?? 1,
            'streak' => $progress?->current_streak ?? 0,
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

    /** LL-20 test-out — serve the D1/D3/D5 competency check (six questions). */
    public function checkStart(Request $request, SyllabusModule $module): JsonResponse
    {
        $child = $request->user();
        $served = app(CompetencyCheck::class)->serve($child->id, $module->id);
        abort_if($served->isEmpty(), Response::HTTP_CONFLICT, 'No check questions available yet.');

        $session = MobilePracticeSession::create([
            'student_id' => $child->id,
            'module_id' => $module->id,
            'question_ids' => $served->pluck('id')->all(),
            'position' => 0,
            'answers' => [],
        ]);

        return response()->json([
            'session_id' => $session->id,
            'total_questions' => $served->count(),
            'current_question' => $this->questionPayload($served->first()),
        ]);
    }

    /** Record a check answer; on the last, grade the whole check via CompetencyCheck (test-out or miss). */
    public function checkAnswer(Request $request, MobilePracticeSession $session): JsonResponse
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
        $choiceIndex = array_search(strtoupper($data['choice_id']), self::CHOICE_LETTERS, true);
        abort_if($choiceIndex === false, Response::HTTP_UNPROCESSABLE_ENTITY, 'Invalid choice.');

        $answers = $session->answers;
        $answers[] = ['question_id' => $data['question_id'], 'choice_index' => $choiceIndex];
        $session->update(['answers' => $answers, 'position' => $session->position + 1]);

        $nextId = $session->question_ids[$session->position] ?? null;
        if ($nextId !== null) {
            return response()->json([
                'done' => false,
                'next_question' => $this->questionPayload(PracticeQuestion::findOrFail($nextId)),
                'progress' => ['answered' => $session->position, 'total' => count($session->question_ids)],
            ]);
        }

        // All answered — grade with the same CompetencyCheck seam the web uses.
        $served = PracticeQuestion::findMany($session->question_ids);
        $map = [];
        foreach ($session->answers as $a) {
            $map[$a['question_id']] = $a['choice_index'];
        }
        $mastered = app(CompetencyCheck::class)->grade($session->student_id, $session->module_id, $served, $map);
        if ($session->finished_at === null) {
            $session->update(['finished_at' => now()]);
        }

        return response()->json(['done' => true, 'mastered' => $mastered]);
    }

    /** The Voyage overworld — islands with progress + state. Mirrors the web /voyage (AM-01..04). */
    public function voyage(Request $request): JsonResponse
    {
        $child = $request->user();
        $islands = app(AdventureMapBuilder::class)->buildVoyage($child);

        $summary = array_map(fn (array $i) => [
            'slug' => $i['slug'],
            'name' => $i['name'],
            'icon' => $i['icon'],
            'x' => $i['x'],           // marker centre as % of the map image
            'y' => $i['y'],
            'conquered' => $i['conquered'],
            'total' => $i['total'],
            'state' => $i['state'],   // locked | playable | mastered
            'current' => $i['current'],
        ], $islands);

        return response()->json([
            'child' => ['id' => $child->id, 'name' => $child->name],
            'streak' => $this->streak($child),
            'islands' => $summary,
        ]);
    }

    /** An island's levels (its mini-voyage). A locked island cannot be entered. */
    public function island(Request $request, string $slug): JsonResponse
    {
        $child = $request->user();
        $islands = app(AdventureMapBuilder::class)->buildVoyage($child);
        $island = collect($islands)->firstWhere('slug', $slug);

        abort_if($island === null, Response::HTTP_NOT_FOUND, 'Unknown island.');
        abort_if($island['state'] === 'locked', Response::HTTP_FORBIDDEN, 'This island is still locked.');

        // Per-level stop coordinates along the island's painted interior path.
        $stops = VoyageInteriors::stopsFor($island['slug'], count($island['levels']));

        // Levels are sequential within an island: mastered ones are done, the first
        // not-yet-mastered is "current" (playable), and the rest wait (locked).
        $currentAssigned = false;
        $levels = [];
        foreach ($island['levels'] as $i => $l) {
            $mastered = (bool) $l['mastered'];
            $current = false;
            $locked = false;
            if ($mastered) {
                // done
            } elseif (! $currentAssigned) {
                $current = true;
                $currentAssigned = true;
            } else {
                $locked = true;
            }

            $levels[] = [
                'id' => $l['id'],
                'topic' => $l['topic'],
                'subject' => $l['subject'],
                'mastered' => $mastered,
                'review' => $l['review'],
                'current' => $current,
                'locked' => $locked,
                'mission_id' => "m{$l['id']}",
                'x' => $stops[$i]['x'] ?? null,
                'y' => $stops[$i]['y'] ?? null,
            ];
        }

        return response()->json([
            'island' => [
                'slug' => $island['slug'],
                'name' => $island['name'],
                'icon' => $island['icon'],
                'state' => $island['state'],
                'conquered' => $island['conquered'],
                'total' => $island['total'],
            ],
            'levels' => $levels,
        ]);
    }

    /** Captain's Orders — today's minimum duties (Captain's Brief), or shore leave on a rest day (CO). */
    public function captainsOrders(Request $request): JsonResponse
    {
        $child = $request->user();

        return response()->json([
            'is_evening' => now()->hour >= 17, // mirrors CaptainsOrders::isEvening (AST)
            'orders' => $this->ordersTab($child),
            'locker' => $this->lockerTab($child),
            'logs' => $this->logsTab($child),
            'journal' => $this->journalTab($child),
            'streak' => $this->streak($child),
        ]);
    }

    /** @return array<string, mixed> */
    private function ordersTab(User $child): array
    {
        $plan = app(DailyPlanComposer::class)->forDay($child->id);
        $labels = ['practice' => 'Practice a topic', 'reading' => 'Morning reading', 'vocabulary' => 'Daily vocabulary', 'writing' => 'Writer’s Log', 'map' => 'Sail the map'];

        $duties = [];
        foreach (($plan->duties ?? []) as $key => $done) {
            if ($done === null) {
                continue;
            }
            $duties[] = ['key' => $key, 'label' => $labels[$key] ?? ucfirst((string) $key), 'done' => (bool) $done];
        }
        $rest = $duties === [];

        $tasks = array_map(fn (array $t) => [
            'subject' => $t['subject'] ?? '',
            'topic' => $t['topic'] ?? '',
            'done' => (bool) ($t['done'] ?? false),
            'mission_id' => isset($t['module_id']) ? 'm'.$t['module_id'] : null,
        ], app(DailyPlanComposer::class)->todaysLessonTasks($child->id));

        return [
            'title' => $rest ? 'Shore Leave' : 'Captain’s Brief',
            'is_writing_day' => (bool) $plan->is_writing_day,
            'minimum_met' => $plan->isMinimumMet(),
            'rest' => $rest,
            'message' => $rest
                ? 'Shore leave, first mate! The seas are calm — rest and enjoy your weekend. Your streak sails on.'
                : 'Today’s orders, Captain. Clear them to keep the Voyage sailing.',
            'duties' => $duties,
            'lesson_tasks' => $tasks,
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function lockerTab(User $child): array
    {
        $economy = app(StreakEconomyService::class);
        $meta = [
            'shore_leave' => ['icon' => '🏝️', 'label' => 'Shore Leave', 'blurb' => 'Skip one duty without breaking your streak.', 'earn' => 'Reach a 7-day streak.'],
            'anchor' => ['icon' => '⚓', 'label' => 'Anchor', 'blurb' => 'Freeze your streak for a day off.', 'earn' => 'Master three islands.'],
            'tailwind' => ['icon' => '🌬️', 'label' => 'Tailwind', 'blurb' => 'Bank extra progress in a subject.', 'earn' => 'Get ahead of pace.'],
            'lifebuoy' => ['icon' => '🛟', 'label' => 'Lifebuoy', 'blurb' => 'Save a streak after a miss.', 'earn' => 'Finish a full week.'],
        ];

        $rewards = [];
        foreach (StreakReward::TYPES as $type) {
            $m = $meta[$type];
            $rewards[] = ['type' => $type, 'icon' => $m['icon'], 'label' => $m['label'], 'blurb' => $m['blurb'], 'earn' => $m['earn'], 'held' => $economy->balance($child->id, $type)];
        }

        return $rewards;
    }

    /** @return array<int, array<string, mixed>> */
    private function logsTab(User $child): array
    {
        $labels = ['voyage' => 'Voyage streak', 'login' => 'Daily login', 'practice' => 'Practice', 'reading' => 'Reading', 'vocabulary' => 'Vocabulary', 'writing' => 'Writing', 'mastery' => 'Mastery', 'pace_weeks' => 'On-pace weeks'];

        return StudentStreak::where('student_id', $child->id)
            ->orderByDesc('count')->get()
            ->map(fn (StudentStreak $s) => ['label' => $labels[$s->type] ?? ucfirst($s->type), 'count' => (int) $s->count])
            ->all();
    }

    /** @return array<int, array<string, mixed>> Recent conquests (mastered topics). */
    private function journalTab(User $child): array
    {
        return StudentProgress::where('student_id', $child->id)
            ->where('status', 'mastered')
            ->whereNotNull('mastered_at')
            ->orderByDesc('mastered_at')
            ->with('module:id,topic,subject')
            ->limit(15)->get()
            ->map(fn (StudentProgress $p) => [
                'topic' => $p->module?->topic ?? 'A topic',
                'subject' => $p->module?->subject ?? '',
                'at' => $p->mastered_at?->toIso8601String(),
            ])->all();
    }

    /** A module's lesson — the teaching stage before practice (LE-01/LE-03 gated sequence). */
    public function lesson(Request $request, SyllabusModule $module): JsonResponse
    {
        $lesson = Lesson::where('module_id', $module->id)->where('is_published', true)->first();

        return response()->json([
            'module' => ['id' => $module->id, 'topic' => $module->topic, 'subject' => $module->subject],
            'has_lesson' => $lesson !== null,
            'title' => $lesson?->title,
            'mission_id' => "m{$module->id}",
            'blocks' => $lesson ? $this->renderableBlocks($lesson->blocks ?? []) : [],
        ]);
    }

    /**
     * Trim authored blocks to the fields the mobile renderer shows (drops heavy
     * authoring-only data like practiceItems). Keeps the block order.
     *
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<int, array<string, mixed>>
     */
    private function renderableBlocks(array $blocks): array
    {
        $keep = ['type', 'content', 'steps', 'question', 'options', 'answer', 'prompt', 'instruction', 'text', 'items', 'pairs'];

        return array_values(array_map(
            fn (array $b) => array_filter(
                array_intersect_key($b, array_flip($keep)),
                fn ($v) => $v !== null,
            ),
            $blocks,
        ));
    }

    /** Welcome-back splash — streaks + any claimable milestone, before the Voyage (SH-06/CE-04). */
    public function welcomeBack(Request $request): JsonResponse
    {
        $child = $request->user();
        $count = fn (string $type): int => (int) (StudentStreak::where('student_id', $child->id)->where('type', $type)->value('count') ?? 0);
        $milestone = app(StreakEconomyService::class)->claimStreakMilestone($child->id);

        return response()->json([
            'child' => ['id' => $child->id, 'name' => $child->name],
            'streaks' => [
                'voyage' => $this->streak($child)['days'],
                'practice' => $count('practice'),
                'login' => $count('login'),
                'mastery' => $count('mastery'),
                'pace_weeks' => $count('pace_weeks'),
            ],
            'milestone' => $milestone,
            'message' => $milestone !== null
                ? "🎉 A {$milestone}-day milestone — you're on fire!"
                : "Welcome back, {$child->name}! Ready to set sail?",
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
