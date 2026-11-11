<?php

namespace App\Services\Push\Notifications;

use App\Models\User;
use App\Services\Push\PushMessage;
use App\Services\Push\PushSender;

/**
 * A child hits a milestone on their tablet — a streak, island mastery, a full day's quota —
 * and the PARENT is pushed on another device to celebrate it (PN-05). The child's own device
 * is never the target, so it is not spammed with a push it does not need.
 */
class MilestoneNotifier
{
    public function __construct(private readonly PushSender $sender) {}

    public function reached(User $student, string $headline, string $detail): void
    {
        if ($student->is_test) {
            return;
        }

        $guardian = $student->guardian;

        if ($guardian === null) {
            return;
        }

        $firstName = explode(' ', $student->name)[0];

        $this->sender->send(PushMessage::standard(
            userId: $guardian->id,
            type: 'milestone',
            title: $headline,
            body: "{$firstName} {$detail}",
            data: ['deep_link' => '/dashboard', 'student_id' => $student->id],
        ));
    }
}
