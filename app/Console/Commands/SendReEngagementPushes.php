<?php

namespace App\Console\Commands;

use App\Models\StudentProgress;
use App\Models\User;
use App\Services\Push\Notifications\ReEngagementNotifier;
use Illuminate\Console\Command;

/**
 * Nightly check: for each student with no practice activity in the last N days, send the
 * guardian one re-engagement push (subject to the notifier's cooldown, opt-out, quiet hours).
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-06)
 */
class SendReEngagementPushes extends Command
{
    protected $signature = 'push:re-engage {--days=3 : Idle threshold in days} {--cooldown=3 : Minimum days between nudges}';

    protected $description = 'Push guardians of children who have drifted away, once, respecting quiet hours and cooldown.';

    public function handle(ReEngagementNotifier $notifier): int
    {
        $idleBefore = now()->subDays((int) $this->option('days'));
        $cooldown = (int) $this->option('cooldown');
        $nudged = 0;

        User::query()
            ->where('role', 'student')
            ->where(fn ($q) => $q->whereNull('is_test')->orWhere('is_test', false))
            ->chunkById(200, function ($students) use ($idleBefore, $cooldown, $notifier, &$nudged) {
                foreach ($students as $student) {
                    $lastActive = StudentProgress::where('student_id', $student->id)->max('updated_at');

                    if ($lastActive !== null && $lastActive >= $idleBefore) {
                        continue; // active recently — leave them be
                    }

                    if ($notifier->nudge($student, $cooldown)) {
                        $nudged++;
                    }
                }
            });

        $this->info("Re-engagement nudges sent: {$nudged}");

        return self::SUCCESS;
    }
}
