<?php

declare(strict_types=1);

namespace App\Services\Verification;

use Illuminate\Support\Facades\Http;

/**
 * Server-side verification of a Cloudflare Turnstile token.
 *
 * When no secret key is configured (local/dev/test), verification passes so the
 * registration form is never blocked off-production. In production the secret is
 * set and every token is checked against Cloudflare's siteverify endpoint.
 */
class TurnstileService
{
    public function isConfigured(): bool
    {
        return ! empty(config('services.turnstile.secret_key'));
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->isConfigured()) {
            return true;
        }

        if (empty($token)) {
            return false;
        }

        $response = Http::asForm()->post(config('services.turnstile.verify_url'), [
            'secret' => config('services.turnstile.secret_key'),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        return $response->successful() && $response->json('success') === true;
    }
}
