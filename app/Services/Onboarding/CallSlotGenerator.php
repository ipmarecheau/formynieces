<?php

namespace App\Services\Onboarding;

use App\Models\OnboardingCall;
use Carbon\CarbonImmutable;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Collection;

/**
 * OC-01 — the open onboarding-call slots for the coming two weeks.
 *
 * The founder takes 15-minute calls weekdays 5:00pm–8:00pm and Saturdays
 * 8:00am–5:00pm, Trinidad & Tobago time. Slots are offered from tomorrow
 * onward, Sundays are closed, and already-booked (non-cancelled) slots are
 * excluded so two parents can never grab the same time.
 */
class CallSlotGenerator
{
    /** Weekday window: last start 7:45pm. */
    private const WEEKDAY_START = '17:00';

    private const WEEKDAY_END = '19:45';

    /** Saturday window: last start 4:45pm. */
    private const SATURDAY_START = '08:00';

    private const SATURDAY_END = '16:45';

    private const STEP_MINUTES = 15;

    /**
     * @return array<int, array{date: string, label: string, slots: array<int, string>}>
     */
    public function days(int $count = 14, ?\DateTimeInterface $now = null): array
    {
        $ast = new CarbonTimeZone('America/Barbados');
        $today = CarbonImmutable::instance(($now ?? now())->setTimezone($ast))->startOfDay();

        $booked = OnboardingCall::query()
            ->where('status', '!=', 'cancelled')
            ->whereDate('call_date', '>', $today)
            ->get()
            ->map(fn (OnboardingCall $call) => $call->call_date->format('Y-m-d').'|'.$call->call_time->format('H:i'))
            ->flip();

        $days = [];
        for ($i = 1; $i <= $count; $i++) {
            $date = $today->addDays($i);

            if ($date->isSunday()) {
                continue;
            }

            [$start, $end] = $date->isSaturday()
                ? [self::SATURDAY_START, self::SATURDAY_END]
                : [self::WEEKDAY_START, self::WEEKDAY_END];

            $slots = [];
            $cursor = $date->setTimeFromTimeString($start);
            $last = $date->setTimeFromTimeString($end);

            while ($cursor <= $last) {
                $key = $date->format('Y-m-d').'|'.$cursor->format('H:i');
                if (! $booked->has($key)) {
                    $slots[] = $cursor->format('H:i');
                }
                $cursor = $cursor->addMinutes(self::STEP_MINUTES);
            }

            $days[] = [
                'date' => $date->format('Y-m-d'),
                'label' => $date->format('l j F'),
                'slots' => $slots,
            ];
        }

        return $days;
    }

    /**
     * Every open "Y-m-d|H:i" key — used to validate a submitted choice (OC-03).
     *
     * @return Collection<int, string>
     */
    public function openKeys(int $count = 14, ?\DateTimeInterface $now = null): Collection
    {
        return Collection::make($this->days($count, $now))
            ->flatMap(fn (array $day) => array_map(
                fn (string $time) => $day['date'].'|'.$time,
                $day['slots'],
            ));
    }

    /** Human label for a stored date/time pair, e.g. "Monday 17 August · 5:15pm". */
    public function label(string $date, string $time): string
    {
        $when = CarbonImmutable::parse($date.' '.$time, new CarbonTimeZone('America/Barbados'));

        return $when->format('l j F').' · '.$when->format('g:ia');
    }
}
