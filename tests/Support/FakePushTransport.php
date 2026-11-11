<?php

namespace Tests\Support;

use App\Services\Push\Contracts\PushTransport;
use App\Services\Push\PushMessage;
use App\Services\Push\PushResult;

/**
 * An in-memory push gateway for tests.
 *
 * It records every (token, message) it is asked to send and returns a configurable
 * outcome per token, so specs can prove delivery, suppression, and token pruning without
 * ever contacting Apple or Google.
 *
 * Usage:
 *   $fake = (new FakePushTransport('ios'))->rejectAsUnregistered('dead-token');
 *   $this->app->instance(ApnsTransport::class, $fake); // bound in place of the real transport
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-02, PN-08)
 */
class FakePushTransport implements PushTransport
{
    /** @var array<int, array{token: string, message: PushMessage}> */
    public array $sent = [];

    /** @var array<string, string> token => outcome (unregistered|retryable|failed) */
    private array $tokenOutcomes = [];

    public function __construct(private readonly string $platform = 'ios') {}

    public function platform(): string
    {
        return $this->platform;
    }

    public function rejectAsUnregistered(string $token): self
    {
        $this->tokenOutcomes[$token] = 'unregistered';

        return $this;
    }

    public function rejectAsRetryable(string $token): self
    {
        $this->tokenOutcomes[$token] = 'retryable';

        return $this;
    }

    public function send(string $token, PushMessage $message): PushResult
    {
        $this->sent[] = ['token' => $token, 'message' => $message];

        return match ($this->tokenOutcomes[$token] ?? 'delivered') {
            'unregistered' => PushResult::unregistered($token, 'fake: unregistered'),
            'retryable' => PushResult::retryable($token, 'fake: rate limited'),
            'failed' => PushResult::failed($token, 'fake: failed'),
            default => PushResult::delivered($token),
        };
    }

    public function sentTokens(): array
    {
        return array_column($this->sent, 'token');
    }

    public function wasSentTo(string $token): bool
    {
        return in_array($token, $this->sentTokens(), true);
    }
}
