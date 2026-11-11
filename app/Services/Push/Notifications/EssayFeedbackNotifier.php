<?php

namespace App\Services\Push\Notifications;

use App\Models\User;
use App\Services\Push\PushMessage;
use App\Services\Push\PushSender;

/**
 * The human-in-the-loop moment: a live teacher publishes feedback on a child's essay, and
 * the PARENT is pushed on the parent's own device — not the child's tablet (PN-04).
 */
class EssayFeedbackNotifier
{
    public function __construct(private readonly PushSender $sender) {}

    public function published(User $student, int $essayId): void
    {
        $guardian = $student->guardian;

        if ($guardian === null) {
            return;
        }

        $firstName = explode(' ', $student->name)[0];

        $this->sender->send(PushMessage::standard(
            userId: $guardian->id,
            type: 'essay.feedback',
            title: 'A teacher left feedback 📣',
            body: "{$firstName}'s essay has been marked — tap to read the teacher's notes.",
            data: ['deep_link' => "/essays/{$essayId}/feedback", 'student_id' => $student->id],
        ));
    }
}
