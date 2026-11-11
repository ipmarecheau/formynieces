<?php

namespace App\Services\Push\Transports;

use App\Services\Push\Contracts\PushTransport;
use App\Services\Push\PushMessage;
use App\Services\Push\PushResult;
use Illuminate\Support\Facades\Http;

/**
 * Sends to Apple directly over APNs with our own .p8 auth key — no Firebase.
 *
 * iOS payloads never pass through Google. The ES256 JWT is signed with our key and sent as
 * a bearer token; a dead token (410 / Unregistered / BadDeviceToken) is reported for pruning.
 *
 * Live path: exercised against APNs at device-verification time, not in the test suite,
 * which binds a FakePushTransport in its place.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-02, PN-08)
 */
class ApnsTransport implements PushTransport
{
    public function platform(): string
    {
        return 'ios';
    }

    public function send(string $token, PushMessage $message): PushResult
    {
        $config = config('services.apns');

        if (empty($config['key_id']) || empty($config['team_id']) || empty($config['private_key'])) {
            return PushResult::retryable($token, 'apns: not configured');
        }

        $host = ($config['production'] ?? false)
            ? 'https://api.push.apple.com'
            : 'https://api.sandbox.push.apple.com';

        try {
            $jwt = $this->authToken($config);
        } catch (\Throwable $e) {
            return PushResult::failed($token, 'apns: jwt signing failed');
        }

        $response = Http::withHeaders([
            'authorization' => "bearer {$jwt}",
            'apns-topic' => $config['bundle_id'] ?? '',
            'apns-push-type' => 'alert',
        ])->withOptions(['version' => 2.0])
            ->post("{$host}/3/device/{$token}", [
                'aps' => [
                    'alert' => ['title' => $message->title, 'body' => $message->body],
                    'sound' => 'default',
                ],
                'data' => $message->data,
                'type' => $message->type,
            ]);

        if ($response->successful()) {
            return PushResult::delivered($token);
        }

        $reason = (string) $response->json('reason', '');

        if ($response->status() === 410 || in_array($reason, ['Unregistered', 'BadDeviceToken', 'DeviceTokenNotForTopic'], true)) {
            return PushResult::unregistered($token, "apns: {$reason}");
        }

        if ($response->status() === 429 || $response->serverError()) {
            return PushResult::retryable($token, "apns: {$response->status()}");
        }

        return PushResult::failed($token, "apns: {$response->status()} {$reason}");
    }

    /**
     * ES256-signed APNs provider JWT.
     *
     * @param  array<string, mixed>  $config
     */
    private function authToken(array $config): string
    {
        $header = $this->base64url(json_encode(['alg' => 'ES256', 'kid' => $config['key_id']]));
        $claims = $this->base64url(json_encode(['iss' => $config['team_id'], 'iat' => time()]));
        $signingInput = "{$header}.{$claims}";

        $privateKey = str_contains($config['private_key'], 'BEGIN')
            ? $config['private_key']
            : (is_file($config['private_key']) ? file_get_contents($config['private_key']) : $config['private_key']);

        $derSignature = '';
        openssl_sign($signingInput, $derSignature, $privateKey, OPENSSL_ALGO_SHA256);

        return "{$signingInput}.".$this->base64url($this->derToRaw($derSignature));
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Convert an ECDSA DER signature to the raw fixed-width R||S form APNs expects.
     */
    private function derToRaw(string $der): string
    {
        $offset = 4; // skip SEQUENCE + length + INTEGER tag
        $rLength = ord($der[$offset - 1]);
        $r = ltrim(substr($der, $offset, $rLength), "\x00");
        $offset += $rLength + 2;
        $sLength = ord($der[$offset - 1]);
        $s = ltrim(substr($der, $offset, $sLength), "\x00");

        return str_pad($r, 32, "\x00", STR_PAD_LEFT).str_pad($s, 32, "\x00", STR_PAD_LEFT);
    }
}
