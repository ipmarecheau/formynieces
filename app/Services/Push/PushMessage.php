<?php

namespace App\Services\Push;

/**
 * An immutable description of a single notification to deliver to one user's devices.
 *
 * It carries no transport detail — the {@see PushSender} resolves the target user's tokens
 * and hands each one to the right {@see Contracts\PushTransport}. This keeps triggers
 * (essay feedback, milestones, re-engagement, billing) ignorant of Apple/Google entirely.
 *
 * @see formynieces-spec/features/push_notifications.feature
 */
final class PushMessage
{
    /**
     * @param  string  $type  A stable key for the kind of push, e.g. "essay.feedback" — used for logging and de-duplication.
     * @param  array<string, mixed>  $data  Payload data, typically including a deep link the app opens on tap.
     */
    public function __construct(
        public readonly int $userId,
        public readonly string $type,
        public readonly string $title,
        public readonly string $body,
        public readonly PushCriticality $criticality = PushCriticality::Standard,
        public readonly array $data = [],
    ) {}

    public static function standard(int $userId, string $type, string $title, string $body, array $data = []): self
    {
        return new self($userId, $type, $title, $body, PushCriticality::Standard, $data);
    }

    public static function critical(int $userId, string $type, string $title, string $body, array $data = []): self
    {
        return new self($userId, $type, $title, $body, PushCriticality::Critical, $data);
    }
}
