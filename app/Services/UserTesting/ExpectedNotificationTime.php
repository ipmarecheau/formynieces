<?php

namespace App\Services\UserTesting;

use Carbon\CarbonImmutable;

/**
 * Works out when a delayed-feedback scenario's notification is next expected to fire, in the
 * tester's own timezone, so the two reminder emails can be scheduled around it. The scenario
 * declares a daily local clock time (e.g. "18:30"); this returns the next occurrence as a UTC
 * instant. If that time today has already passed (or is so close the "watch for it" reminder
 * could not lead it), it rolls to tomorrow — there is no point reminding someone to watch for
 * an event that is already behind them.
 */
class ExpectedNotificationTime
{
    public static function nextOccurrence(string $dailyClockTime, ?string $timezone, int $leadMinutes = 0, ?CarbonImmutable $now = null): CarbonImmutable
    {
        $timezone = $timezone && $timezone !== '' ? $timezone : config('app.timezone');

        try {
            $local = ($now ?? CarbonImmutable::now())->setTimezone($timezone);
        } catch (\Throwable) {
            // An unrecognised timezone falls back to the app default rather than throwing.
            $timezone = config('app.timezone');
            $local = ($now ?? CarbonImmutable::now())->setTimezone($timezone);
        }

        [$hour, $minute] = array_map('intval', explode(':', $dailyClockTime));
        $candidate = $local->setTime($hour, $minute, 0);

        // Roll to tomorrow unless there is still enough lead time to send the pre-event reminder.
        if ($candidate->lessThanOrEqualTo($local->addMinutes($leadMinutes))) {
            $candidate = $candidate->addDay();
        }

        return $candidate->utc();
    }
}
