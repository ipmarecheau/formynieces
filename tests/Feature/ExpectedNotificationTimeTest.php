<?php

use App\Services\UserTesting\ExpectedNotificationTime;
use Carbon\CarbonImmutable;

/*
 * Works out when a delayed scenario's notification is next expected, in the tester's own
 * timezone, so the two reminder emails land around it.
 */

it('returns today\'s occurrence when it is still ahead in the tester\'s timezone', function () {
    // 09:00 London; an 18:30 event is still ahead today.
    $now = CarbonImmutable::parse('2026-01-15 09:00', 'Europe/London');

    $when = ExpectedNotificationTime::nextOccurrence('18:30', 'Europe/London', 5, $now);

    expect($when->setTimezone('Europe/London')->format('Y-m-d H:i'))->toBe('2026-01-15 18:30');
    expect($when->timezone->getName())->toBe('UTC');
});

it('rolls to tomorrow when the time has already passed today', function () {
    $now = CarbonImmutable::parse('2026-01-15 20:00', 'Europe/London'); // past 18:30

    $when = ExpectedNotificationTime::nextOccurrence('18:30', 'Europe/London', 5, $now);

    expect($when->setTimezone('Europe/London')->format('Y-m-d H:i'))->toBe('2026-01-16 18:30');
});

it('rolls to tomorrow when there is no longer enough lead time for the watch reminder', function () {
    // 18:27 with a 5-minute lead: the pre-event reminder could not lead 18:30, so use tomorrow.
    $now = CarbonImmutable::parse('2026-01-15 18:27', 'Europe/London');

    $when = ExpectedNotificationTime::nextOccurrence('18:30', 'Europe/London', 5, $now);

    expect($when->setTimezone('Europe/London')->format('Y-m-d'))->toBe('2026-01-16');
});

it('resolves the time in the tester\'s own timezone, not the server\'s', function () {
    $now = CarbonImmutable::parse('2026-01-15 09:00', 'UTC');

    $london = ExpectedNotificationTime::nextOccurrence('18:30', 'Europe/London', 0, $now);
    $newYork = ExpectedNotificationTime::nextOccurrence('18:30', 'America/New_York', 0, $now);

    // 18:30 is a different absolute instant in each zone.
    expect($london->ne($newYork))->toBeTrue();
    expect($newYork->isAfter($london))->toBeTrue(); // New York is behind London
});

it('falls back to the app timezone for an unknown or empty timezone', function () {
    $now = CarbonImmutable::parse('2026-01-15 09:00', config('app.timezone'));

    $when = ExpectedNotificationTime::nextOccurrence('18:30', 'Not/AZone', 0, $now);

    expect($when)->toBeInstanceOf(CarbonImmutable::class);
    expect($when->setTimezone(config('app.timezone'))->format('H:i'))->toBe('18:30');
});
