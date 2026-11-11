<?php

namespace App\Services\Push;

use App\Models\PushDelivery;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * The one orchestrator for sending a {@see PushMessage} to a user's devices.
 *
 * It applies the family's opt-out and quiet hours by message criticality, dispatches each
 * device token through the transport for its platform, prunes tokens a gateway reports dead,
 * and writes a {@see PushDelivery} audit row per attempt for the admin panel.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-02, PN-03, PN-07, PN-08)
 */
class PushSender
{
    public function __construct(private readonly PushTransportRegistry $transports) {}

    /**
     * @return array<int, PushDelivery> the audit rows written for this message
     */
    public function send(PushMessage $message): array
    {
        $user = User::find($message->userId);

        if ($user === null) {
            return [$this->log($message, null, PushDelivery::OUTCOME_FAILED, 'unknown-user')];
        }

        // PN-03 / PN-07: standard pushes bow to opt-out + quiet hours; critical ones never do.
        if ($message->criticality->respectsQuietHours() && $user->suppressesStandardPush()) {
            return [$this->log($message, null, PushDelivery::OUTCOME_SUPPRESSED, 'opt-out-or-quiet-hours')];
        }

        $tokens = $user->deviceTokens()->get();

        if ($tokens->isEmpty()) {
            return [$this->log($message, null, PushDelivery::OUTCOME_FAILED, 'no-tokens')];
        }

        $deliveries = [];

        foreach ($tokens as $device) {
            $transport = $this->transports->for($device->platform);

            if ($transport === null) {
                $deliveries[] = $this->log($message, $device->platform, PushDelivery::OUTCOME_FAILED, 'no-transport');

                continue;
            }

            $result = $transport->send($device->token, $message);

            if ($result->shouldPruneToken()) {
                // PN-02: the gateway says this token is dead — never send to it again.
                $device->delete();
                $deliveries[] = $this->log($message, $device->platform, PushDelivery::OUTCOME_FAILED, 'token-pruned', $result->detail);

                continue;
            }

            $outcome = $result->isDelivered()
                ? PushDelivery::OUTCOME_DELIVERED
                : PushDelivery::OUTCOME_RETRYABLE;

            $deliveries[] = $this->log($message, $device->platform, $outcome, null, $result->detail);
        }

        return $deliveries;
    }

    private function log(PushMessage $message, ?string $platform, string $outcome, ?string $reason = null, ?string $detail = null): PushDelivery
    {
        if ($outcome === PushDelivery::OUTCOME_FAILED) {
            Log::warning('push.failed', ['type' => $message->type, 'user' => $message->userId, 'reason' => $reason]);
        }

        return PushDelivery::create([
            'user_id' => $message->userId,
            'type' => $message->type,
            'criticality' => $message->criticality->value,
            'platform' => $platform,
            'outcome' => $outcome,
            'reason' => $reason,
            'detail' => $detail,
        ]);
    }
}
