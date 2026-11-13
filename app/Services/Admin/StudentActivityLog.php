<?php

namespace App\Services\Admin;

use App\Models\DailyPlan;
use App\Models\PracticeAttempt;
use App\Models\StudentProgress;
use App\Models\SupportTicket;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WritingSubmission;
use Illuminate\Support\Collection;

/**
 * Assembles a student's recent activity into one reverse-chronological timeline, composed
 * from existing records (practice, mastery, essays, daily plan, help tickets). Read-only;
 * no new logging. Used by the admin "Student Logs" page to catch problems early (AL-01..03).
 */
class StudentActivityLog
{
    /**
     * @return list<ActivityEvent>
     */
    public function forStudent(User $student, int $limit = 60): array
    {
        $events = collect()
            ->merge($this->practice($student->id, $limit))
            ->merge($this->masteries($student->id, $limit))
            ->merge($this->essays($student->id, $limit))
            ->merge($this->dailyPlans($student->id, $limit))
            ->merge($this->tickets($student->id, $limit));

        return $events
            ->sortByDesc(fn (ActivityEvent $e) => $e->at->getTimestamp())
            ->take($limit)
            ->values()
            ->all();
    }

    /** @return Collection<int, ActivityEvent> */
    private function practice(int $studentId, int $limit): Collection
    {
        $attempts = PracticeAttempt::where('student_id', $studentId)
            ->latest()->limit($limit)->get();

        $topics = $this->topics($attempts->pluck('module_id'));

        return $attempts->map(fn (PracticeAttempt $a) => new ActivityEvent(
            $a->created_at,
            'practice',
            sprintf('Practice · %s · level %d · attempt %d — %s',
                $topics[$a->module_id] ?? 'module', $a->difficulty, $a->attempt,
                $a->is_correct ? 'correct' : 'incorrect'),
            $a->is_correct ? 'positive' : 'negative',
        ));
    }

    /** @return Collection<int, ActivityEvent> */
    private function masteries(int $studentId, int $limit): Collection
    {
        $mastered = StudentProgress::where('student_id', $studentId)
            ->where('status', 'mastered')->whereNotNull('mastered_at')
            ->latest('mastered_at')->limit($limit)->get();

        $topics = $this->topics($mastered->pluck('module_id'));

        return $mastered->map(fn (StudentProgress $p) => new ActivityEvent(
            $p->mastered_at,
            'mastery',
            sprintf('Mastered %s 🏆', $topics[$p->module_id] ?? 'a module'),
            'positive',
        ));
    }

    /** @return Collection<int, ActivityEvent> */
    private function essays(int $studentId, int $limit): Collection
    {
        return WritingSubmission::where('student_id', $studentId)
            ->latest()->limit($limit)->get()
            ->map(fn (WritingSubmission $s) => new ActivityEvent(
                $s->scored_at ?? $s->created_at,
                'essay',
                $s->scored_at ? 'Essay marked — feedback ready ✍️' : 'Essay submitted ✍️',
                'neutral',
            ));
    }

    /** @return Collection<int, ActivityEvent> */
    private function dailyPlans(int $studentId, int $limit): Collection
    {
        return DailyPlan::where('student_id', $studentId)
            ->whereNotNull('completed_at')
            ->latest('completed_at')->limit($limit)->get()
            ->map(fn (DailyPlan $p) => new ActivityEvent(
                $p->completed_at,
                'day_complete',
                "Completed the day's quota ⚓",
                'positive',
            ));
    }

    /** @return Collection<int, ActivityEvent> */
    private function tickets(int $studentId, int $limit): Collection
    {
        return SupportTicket::where('user_id', $studentId)
            ->orWhere('student_id', $studentId)
            ->latest()->limit($limit)->get()
            ->map(fn (SupportTicket $t) => new ActivityEvent(
                $t->created_at,
                'help',
                sprintf('Raised a help %s%s', $t->kind, $t->message ? ": \"{$t->message}\"" : ' (voice note)'),
                'negative',
            ));
    }

    /**
     * @param  Collection<int, int|null>  $moduleIds
     * @return array<int, string>
     */
    private function topics(Collection $moduleIds): array
    {
        $ids = $moduleIds->filter()->unique();

        if ($ids->isEmpty()) {
            return [];
        }

        return SyllabusModule::whereIn('id', $ids)->pluck('topic', 'id')->all();
    }
}
