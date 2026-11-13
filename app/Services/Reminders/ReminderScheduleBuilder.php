<?php

namespace App\Services\Reminders;

use App\Models\DailyPlan;
use App\Models\User;
use App\Services\Motivation\DailyPlanComposer;
use Carbon\CarbonImmutable;

/**
 * Builds the next N days of on-device reminders for a child, from the real daily-plan rules
 * and the guardian's controls. Pure computation — no gateway, no device — so it is fully
 * unit-tested; the native app writes the result to the device and fires it offline.
 *
 * @see formynieces-spec/features/local_reminders.feature
 */
class ReminderScheduleBuilder
{
    /** Fixed local times (24h) for each reminder. */
    private const TIMES = [
        'morning_muster' => '15:30',
        'writing_mission' => '16:00',
        'outstanding_tasks' => '19:00',
        'streak_saver' => '20:30',
    ];

    /**
     * @return list<ScheduledReminder>
     */
    public function forStudent(User $student, ?CarbonImmutable $from = null, int $days = 7): array
    {
        // LR-06: a guardian can switch reminders off entirely.
        if ($student->reminders_opt_out) {
            return [];
        }

        $from ??= CarbonImmutable::today();
        $today = CarbonImmutable::today();
        $minimumMetToday = $this->minimumMetToday($student->id, $today);

        $reminders = [];

        for ($i = 0; $i < $days; $i++) {
            $day = $from->addDays($i)->startOfDay();
            $isToday = $day->isSameDay($today);

            // LR-02: Morning Muster every day.
            $this->push($reminders, $student, $day, 'morning_muster',
                'Captain, your orders are ready ⚓',
                'Today\'s voyage is plotted — hop aboard and get started!');

            // LR-03: Writing Mission only on writing days (Mon/Wed/Fri).
            if (in_array($day->dayOfWeekIso, DailyPlanComposer::WRITING_DAYS, true)) {
                $this->push($reminders, $student, $day, 'writing_mission',
                    'Today\'s writing mission ✍️',
                    'A fresh essay prompt is waiting — a few minutes keeps your writing sharp.');
            }

            // LR-04 / LR-05: conditional reminders — skipped for a day already finished.
            if ($isToday && $minimumMetToday) {
                continue;
            }

            $this->push($reminders, $student, $day, 'outstanding_tasks',
                'Still a little left today 🌙',
                'You have tasks waiting — finish them off before the day is done.',
                conditional: true);

            $this->push($reminders, $student, $day, 'streak_saver',
                'Keep your streak alive! 🔥',
                'A quick task now protects your streak — you\'ve got this, Captain.',
                conditional: true);
        }

        return $reminders;
    }

    /**
     * @param  list<ScheduledReminder>  $reminders
     */
    private function push(array &$reminders, User $student, CarbonImmutable $day, string $type, string $title, string $body, bool $conditional = false): void
    {
        [$h, $m] = explode(':', self::TIMES[$type]);
        $fireAt = $day->setTime((int) $h, (int) $m);

        // LR-06: never place a reminder inside the child's quiet hours.
        if ($student->isWithinQuietHours($fireAt)) {
            return;
        }

        $reminders[] = new ScheduledReminder($type, $fireAt, $title, $body, $conditional);
    }

    private function minimumMetToday(int $studentId, CarbonImmutable $today): bool
    {
        // Read-only: do not compose/persist a plan here.
        $plan = DailyPlan::query()
            ->where('student_id', $studentId)
            ->whereDate('date', $today->toDateString())
            ->first();

        return $plan?->isMinimumMet() ?? false;
    }
}
