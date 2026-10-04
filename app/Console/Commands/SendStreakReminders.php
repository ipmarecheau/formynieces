<?php

namespace App\Console\Commands;

use App\Models\DeviceToken;
use App\Models\PracticeAttempt;
use App\Models\User;
use App\Services\Push\FcmSender;
use Illuminate\Console\Command;

/**
 * Evening nudge: push a "keep your streak alive" reminder to students who have the app
 * installed but have not practised yet today. Server-side complement to the on-device
 * daily local reminder (which fires regardless of connectivity).
 *
 * No-ops cleanly when FCM is not configured (see FcmSender). Schedule it in the evening,
 * e.g. ->dailyAt('18:00') in routes/console.php.
 */
class SendStreakReminders extends Command
{
    protected $signature = 'notify:streak-reminders {--dry-run : List who would be notified without sending}';

    protected $description = 'Push a streak reminder to students who have not practised today';

    public function handle(FcmSender $fcm): int
    {
        // Students with a registered device who have no practice attempt today.
        $practisedTodayIds = PracticeAttempt::whereDate('created_at', today())
            ->distinct()->pluck('student_id');

        $targets = User::query()
            ->where('role', 'student')
            ->whereIn('id', DeviceToken::query()->select('user_id'))
            ->whereNotIn('id', $practisedTodayIds)
            ->get();

        $this->info("Students to remind: {$targets->count()}");

        if ($this->option('dry-run')) {
            $targets->each(fn (User $u) => $this->line("  - {$u->name} (#{$u->id})"));

            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($targets as $student) {
            $tokens = DeviceToken::where('user_id', $student->id)->get();
            $sent += $fcm->send(
                $tokens,
                'Keep your streak alive! 🔥',
                "Set sail for a few minutes today, {$student->name}, to keep your voyage going.",
                ['type' => 'streak_reminder'],
            );
        }

        $this->info("Push notifications sent: {$sent}");

        return self::SUCCESS;
    }
}
