<?php

namespace App\Services\Push\Contracts;

use App\Services\Push\PushMessage;
use App\Services\Push\PushResult;

/**
 * The single seam between SmoothSeas and a push gateway.
 *
 * Real implementations talk directly to a gateway — ApnsTransport (.p8 JWT, no Firebase)
 * and FcmTransport (FCM HTTP v1, no SDK). The test suite binds FakePushTransport instead,
 * so no test ever contacts Apple or Google.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-08)
 */
interface PushTransport
{
    /**
     * The platform this transport serves: "ios" or "android".
     */
    public function platform(): string;

    /**
     * Deliver one message to one device token and report what the gateway said.
     */
    public function send(string $token, PushMessage $message): PushResult;
}
