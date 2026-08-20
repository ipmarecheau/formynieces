<?php

namespace App\Console\Commands;

use App\Models\StudentGuidedTime;
use App\Models\User;
use App\Notifications\DailyTasksSummaryNotification;
use App\Services\Motivation\DailyPlanComposer;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * PN-01/PN-02 — email each guardian a summary of the day's paced tasks: once the
 * student finishes them all, or once she has gone inactive for a while with work
 * still open. At most one email per student per day. Schedule this to run every
 * few minutes; the once-a-day guard keeps it from repeating.
 */
class SendDailyTaskSummaries extends Command
{
    protected $signature = 'students:daily-summary
        {--inactive-minutes=30 : Minutes of no activity before an inactivity summary is sent}';

    protected $description = 'Email guardians a summary of the day\'s paced tasks (on completion or inactivity)';

    public function handle(DailyPlanComposer $composer): int
    {
        $today = Carbon::today();
        $inactiveMinutes = (int) $this->option('inactive-minutes');
        $sent = 0;

        $students = User::query()
            ->where('role', 'student')
            ->whereNotNull('parent_id')
            ->with('guardian')
            ->get();

        foreach ($students as $student) {
            $guardian = $student->guardian;
            if ($guardian === null) {
                continue;
            }

            $plan = $composer->forDay($student->id, $today);
            if ($plan->parent_summary_sent_at !== null) {
                continue; // already summarised today
            }

            $tasks = $composer->todaysLessonTasks($student->id, $today);
            $doneTopics = collect($tasks)->where('done', true)->pluck('topic')->all();
            $openTopics = collect($tasks)->where('done', false)->pluck('topic')->all();
            $minimumMet = $plan->isMinimumMet();
            $allDone = $minimumMet && count($openTopics) === 0;

            $reason = $this->reasonToSend($student->id, $today, $allDone, $inactiveMinutes);
            if ($reason === null) {
                continue;
            }

            $guardian->notify(new DailyTasksSummaryNotification(
                student: $student,
                minimumMet: $minimumMet,
                doneTopics: $doneTopics,
                openTopics: $openTopics,
                reason: $reason,
            ));

            $plan->forceFill(['parent_summary_sent_at' => now()])->save();
            $sent++;
        }

        $this->info("Sent {$sent} daily summary email(s).");

        return self::SUCCESS;
    }

    /**
     * Decide whether — and why — to summarise now: on full completion, or once
     * she has been active today but idle for a while with work still open.
     */
    private function reasonToSend(int $studentId, Carbon $today, bool $allDone, int $inactiveMinutes): ?string
    {
        if ($allDone) {
            return 'done';
        }

        $guided = StudentGuidedTime::where('student_id', $studentId)
            ->where('day', $today->toDateString())
            ->first();

        // Only nudge if she actually engaged today, then went idle past the window.
        if ($guided !== null
            && (int) $guided->active_seconds > 0
            && $guided->updated_at->lt(now()->subMinutes($inactiveMinutes))) {
            return 'inactive';
        }

        return null;
    }
}
