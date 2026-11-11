<?php

namespace App\Services\Push\Notifications;

use App\Models\User;
use App\Services\Push\PushMessage;
use App\Services\Push\PushSender;

/**
 * Billing and security events reach the account owner even with nudges off and inside quiet
 * hours — a family cannot mute a failed payment into silence (PN-07). These are CRITICAL.
 */
class BillingNotifier
{
    public function __construct(private readonly PushSender $sender) {}

    public function renewalDue(User $owner, string $when): void
    {
        $this->sender->send(PushMessage::critical(
            userId: $owner->id,
            type: 'billing.renewal',
            title: 'Subscription renews soon',
            body: "Your SmoothSeas plan renews {$when}.",
            data: ['deep_link' => '/billing'],
        ));
    }

    public function paymentFailed(User $owner): void
    {
        $this->sender->send(PushMessage::critical(
            userId: $owner->id,
            type: 'billing.failed',
            title: 'Payment needs attention',
            body: 'We could not process your last payment — tap to fix it so your voyage continues.',
            data: ['deep_link' => '/billing'],
        ));
    }
}
