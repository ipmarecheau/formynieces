<?php

namespace App\Services\Push;

use App\Services\Push\Contracts\PushTransport;

/**
 * Resolves the right {@see PushTransport} for a device platform. The real transports
 * (APNs, FCM) are registered in the service provider; tests register fakes in their place.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-08)
 */
class PushTransportRegistry
{
    /** @var array<string, PushTransport> */
    private array $byPlatform = [];

    public function register(PushTransport $transport): self
    {
        $this->byPlatform[$transport->platform()] = $transport;

        return $this;
    }

    public function for(string $platform): ?PushTransport
    {
        return $this->byPlatform[$platform] ?? null;
    }
}
