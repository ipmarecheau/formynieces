<?php

namespace App\Services\Push;

/**
 * Whether a push can be silenced by the family's opt-out and quiet hours.
 *
 * Standard pushes (streaks, reminders, re-engagement, milestones) respect opt-out
 * and quiet hours. Critical pushes (billing, security) are always delivered — a family
 * cannot mute a failed payment into silence.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-03, PN-07)
 */
enum PushCriticality: string
{
    case Standard = 'standard';
    case Critical = 'critical';

    public function respectsQuietHours(): bool
    {
        return $this === self::Standard;
    }
}
