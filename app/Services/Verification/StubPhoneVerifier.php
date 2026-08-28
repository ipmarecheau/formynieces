<?php

declare(strict_types=1);

namespace App\Services\Verification;

use Illuminate\Support\Facades\Cache;

/**
 * Off-production phone verifier used when Twilio is not configured. It "sends"
 * nothing; it accepts a fixed development code so the flow is fully exercisable
 * locally and in tests, and records the channel a code was last requested on so
 * the WhatsApp→SMS fallback can be asserted.
 */
class StubPhoneVerifier implements PhoneVerifier
{
    /** The code the stub always accepts off-production. */
    public const DEV_CODE = '123456';

    public function start(string $phone, string $channel = 'whatsapp'): void
    {
        Cache::put($this->key($phone), $channel, now()->addMinutes(10));
    }

    public function check(string $phone, string $code): bool
    {
        return $code === self::DEV_CODE;
    }

    /** The channel the last code was requested on — for tests/inspection. */
    public function lastChannel(string $phone): ?string
    {
        return Cache::get($this->key($phone));
    }

    private function key(string $phone): string
    {
        return 'stub-phone-verify:'.$phone;
    }
}
