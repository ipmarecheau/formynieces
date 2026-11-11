<?php

namespace App\Services\Push;

/**
 * The outcome of handing a {@see PushMessage} to a {@see Contracts\PushTransport} for a
 * single token.
 *
 * - delivered: the gateway accepted it.
 * - unregistered: the gateway reported the token dead — the {@see PushSender} must prune it (PN-02).
 * - retryable: a transient failure (rate limit, gateway 5xx) — keep the token, try again later (PN-02).
 * - failed: a permanent non-token failure (malformed payload) — logged, token kept.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-02, PN-08)
 */
final class PushResult
{
    private function __construct(
        public readonly string $outcome,
        public readonly string $token,
        public readonly ?string $detail = null,
    ) {}

    public static function delivered(string $token): self
    {
        return new self('delivered', $token);
    }

    public static function unregistered(string $token, ?string $detail = null): self
    {
        return new self('unregistered', $token, $detail);
    }

    public static function retryable(string $token, ?string $detail = null): self
    {
        return new self('retryable', $token, $detail);
    }

    public static function failed(string $token, ?string $detail = null): self
    {
        return new self('failed', $token, $detail);
    }

    public function isDelivered(): bool
    {
        return $this->outcome === 'delivered';
    }

    public function shouldPruneToken(): bool
    {
        return $this->outcome === 'unregistered';
    }
}
