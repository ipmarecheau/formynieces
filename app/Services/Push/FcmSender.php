<?php

namespace App\Services\Push;

use App\Models\DeviceToken;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends push notifications to the native apps via Firebase Cloud Messaging (HTTP v1).
 *
 * FCM fans out to both Android (directly) and iOS (via APNs, once the APNs key is
 * uploaded to the Firebase project), so one sender covers both platforms.
 *
 * Configuration (prod .env — see config/services.php `fcm`):
 *   FCM_PROJECT_ID        the Firebase project id
 *   FCM_CREDENTIALS       absolute path to the service-account JSON
 * Until both are set, {@see isConfigured()} is false and {@see send()} is a logged no-op,
 * so streak reminders still work on-device (local notifications) with no server push.
 *
 * NOTE: acquiring the OAuth2 bearer token from the service account is the one remaining
 * wire-up — do it with google/auth (composer require google/auth) or a signed JWT. It is
 * intentionally left as {@see accessToken()} returning null so this ships without a new
 * dependency or live credentials; the payload construction + delivery below are complete.
 */
class FcmSender
{
    public function isConfigured(): bool
    {
        return ! empty(config('services.fcm.project_id'))
            && ! empty(config('services.fcm.credentials'))
            && is_file((string) config('services.fcm.credentials'));
    }

    /**
     * Send one notification to many device tokens. Returns the number delivered.
     *
     * @param  Collection<int, DeviceToken>|array<int, DeviceToken>  $tokens
     * @param  array<string, string>  $data
     */
    public function send(iterable $tokens, string $title, string $body, array $data = []): int
    {
        if (! $this->isConfigured()) {
            Log::info('FcmSender: push not configured — skipping', ['title' => $title]);

            return 0;
        }

        $accessToken = $this->accessToken();
        if ($accessToken === null) {
            Log::warning('FcmSender: no access token (service-account OAuth not wired)');

            return 0;
        }

        $url = 'https://fcm.googleapis.com/v1/projects/'.config('services.fcm.project_id').'/messages:send';
        $sent = 0;

        foreach ($tokens as $device) {
            $response = Http::withToken($accessToken)->post($url, [
                'message' => [
                    'token' => $device->token,
                    'notification' => ['title' => $title, 'body' => $body],
                    'data' => array_map('strval', $data),
                    'android' => ['priority' => 'high'],
                    'apns' => ['payload' => ['aps' => ['sound' => 'default']]],
                ],
            ]);

            if ($response->successful()) {
                $sent++;
            } elseif ($response->status() === 404) {
                // Token no longer valid — prune it.
                $device->delete();
            }
        }

        return $sent;
    }

    /**
     * OAuth2 bearer for the FCM v1 API, from the service-account JSON. Returns null until
     * wired (see class note) so the sender ships without a new dependency or live creds.
     */
    private function accessToken(): ?string
    {
        return null;
    }
}
