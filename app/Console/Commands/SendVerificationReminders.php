<?php

namespace App\Console\Commands;

use App\Mail\DeferredCheckReminder;
use App\Models\VerificationRun;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Sweeps deferred test-drive checks and sends their two reminder emails: the "watch your
 * device" nudge just before the expected notification, and the "did it arrive?" follow-up
 * after. Each email is stamped so it goes out exactly once, so the sweep is safe to run on a
 * schedule (every few minutes) and idempotent across overlapping runs.
 */
class SendVerificationReminders extends Command
{
    protected $signature = 'verification:send-reminders';

    protected $description = 'Email testers the before/after reminders for deferred (waiting) test-drive checks';

    public function handle(): int
    {
        $now = now();
        $sent = 0;

        $waiting = VerificationRun::where('result', VerificationRun::RESULT_WAITING)
            ->whereNotNull('expected_at')
            ->get();

        foreach ($waiting as $run) {
            // "Watch for it" — once the lead window before the event has opened.
            if ($run->first_reminder_sent_at === null && $run->firstReminderAt()?->lte($now)) {
                Mail::to($run->tester_email)->send(new DeferredCheckReminder($run, DeferredCheckReminder::PHASE_BEFORE));
                $run->forceFill(['first_reminder_sent_at' => $now])->save();
                $sent++;
            }

            // "Did it arrive?" — once the event plus its grace period has passed.
            if ($run->second_reminder_sent_at === null && $run->secondReminderAt()?->lte($now)) {
                Mail::to($run->tester_email)->send(new DeferredCheckReminder($run, DeferredCheckReminder::PHASE_AFTER));
                $run->forceFill(['second_reminder_sent_at' => $now])->save();
                $sent++;
            }
        }

        $this->info("Sent {$sent} deferred-check reminder(s).");

        return self::SUCCESS;
    }
}
