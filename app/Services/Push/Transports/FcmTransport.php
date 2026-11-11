<?php

namespace App\Services\Push\Transports;

use App\Services\Push\Contracts\PushTransport;
use App\Services\Push\PushMessage;
use App\Services\Push\PushResult;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Sends to Android directly over the FCM HTTP v1 API with our own Google service-account
 * credential — the plain authenticated endpoint, NOT the Firebase SDK or console.
 *
 * A short-lived OAuth2 access token is minted from the service account and cached. A dead
 * token (UNREGISTERED / invalid) is reported for pruning.
 *
 * Live path: exercised against FCM at device-verification time, not in the test suite,
 * which binds a FakePushTransport in its place.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-02, PN-08)
 */
class FcmTransport implements PushTransport
{
    public function platform(): string
    {
        return 'android';
    }

    public function send(string $token, PushMessage $message): PushResult
    {
        $config = config('services.fcm');

        if (empty($config['project_id']) || empty($config['credentials'])) {
            return PushResult::retryable($token, 'fcm: not configured');
        }

        try {
            $accessToken = $this->accessToken($config);
        } catch (\Throwable $e) {
            return PushResult::failed($token, 'fcm: auth failed');
        }

        $endpoint = "https://fcm.googleapis.com/v1/projects/{$config['project_id']}/messages:send";

        $response = Http::withToken($accessToken)->post($endpoint, [
            'message' => [
                'token' => $token,
                'notification' => ['title' => $message->title, 'body' => $message->body],
                'data' => array_map('strval', $message->data + ['type' => $message->type]),
            ],
        ]);

        if ($response->successful()) {
            return PushResult::delivered($token);
        }

        $status = (string) $response->json('error.status', '');

        if (in_array($status, ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'], true)) {
            return PushResult::unregistered($token, "fcm: {$status}");
        }

        if ($response->status() === 429 || $response->serverError() || $status === 'UNAVAILABLE') {
            return PushResult::retryable($token, "fcm: {$response->status()}");
        }

        return PushResult::failed($token, "fcm: {$response->status()} {$status}");
    }

    /**
     * Mint (and briefly cache) an OAuth2 access token from the service-account JWT grant.
     *
     * @param  array<string, mixed>  $config
     */
    private function accessToken(array $config): string
    {
        return Cache::remember('push.fcm.access_token', now()->addMinutes(50), function () use ($config) {
            $credentials = json_decode(
                is_file($config['credentials']) ? file_get_contents($config['credentials']) : $config['credentials'],
                true
            );

            $now = time();
            $header = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claims = $this->base64url(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signingInput = "{$header}.{$claims}";
            $signature = '';
            openssl_sign($signingInput, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256);
            $assertion = "{$signingInput}.".$this->base64url($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            return (string) $response->json('access_token');
        });
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
