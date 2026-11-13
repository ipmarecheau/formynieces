<?php

namespace App\Services\Reminders;

use Carbon\CarbonImmutable;

/**
 * One reminder to be scheduled on the child's device. It is fully self-contained — its own
 * fire time and words — so the device can fire it offline with the app closed (LR-07).
 *
 * @see formynieces-spec/features/local_reminders.feature
 */
final class ScheduledReminder
{
    public function __construct(
        public readonly string $type,
        public readonly CarbonImmutable $fireAt,
        public readonly string $title,
        public readonly string $body,
        public readonly bool $conditional = false,
    ) {}

    /**
     * A stable id per (type, day) so re-scheduling replaces the prior one rather than
     * stacking a duplicate (LR-01). Local-notification ids must be integers.
     */
    public function id(): int
    {
        $key = $this->type.':'.$this->fireAt->toDateString();

        return (int) sprintf('%u', crc32($key));
    }

    /**
     * @return array{id:int, type:string, at:string, title:string, body:string, conditional:bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'type' => $this->type,
            'at' => $this->fireAt->toIso8601String(),
            'title' => $this->title,
            'body' => $this->body,
            'conditional' => $this->conditional,
        ];
    }
}
