<?php

namespace App\Services\Push\Notifications;

use App\Models\PushDelivery;
use App\Models\User;
use App\Services\Push\PushMessage;
use App\Services\Push\PushSender;

/**
 * A child has drifted away; the guardian gets ONE gentle re-engagement push referencing what
 * is slipping (PN-06). It respects opt-out and quiet hours (standard criticality) and is not
 * repeated every night — a cooldown blocks a second nudge within the window.
 */
class ReEngagementNotifier
{
    public function __construct(private readonly PushSender $sender) {}

    public function nudge(User $student, int $cooldownDays = 3): bool
    {
        $guardian = $student->guardian;

        if ($guardian === null) {
            return false;
        }

        // Not repeated every night: skip if we already nudged this guardian recently.
        $recentlyNudged = PushDelivery::query()
            ->where('user_id', $guardian->id)
            ->where('type', 're-engagement')
            ->where('created_at', '>=', now()->subDays($cooldownDays))
            ->exists();

        if ($recentlyNudged) {
            return false;
        }

        $firstName = explode(' ', $student->name)[0];

        $this->sender->send(PushMessage::standard(
            userId: $guardian->id,
            type: 're-engagement',
            title: 'Your crew misses the sea 🌊',
            body: "{$firstName} hasn't practised in a few days — a quick voyage keeps the streak and the progress alive.",
            data: ['deep_link' => '/dashboard', 'student_id' => $student->id],
        ));

        return true;
    }
}
