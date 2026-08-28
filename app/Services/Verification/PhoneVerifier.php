<?php

declare(strict_types=1);

namespace App\Services\Verification;

/**
 * A phone-number verifier. The concrete driver is chosen by configuration:
 * Twilio Verify in production, a stub off-production. WhatsApp-first with an
 * SMS fallback is expressed through the $channel argument on start().
 */
interface PhoneVerifier
{
    /**
     * Begin (or re-send) a verification to the number on the given channel.
     * $channel is 'whatsapp' (default) or 'sms' (the fallback).
     */
    public function start(string $phone, string $channel = 'whatsapp'): void;

    /** Check a code the user entered. Returns true when approved. */
    public function check(string $phone, string $code): bool;
}
