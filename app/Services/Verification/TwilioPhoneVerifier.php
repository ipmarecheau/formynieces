<?php

declare(strict_types=1);

namespace App\Services\Verification;

use Twilio\Rest\Client;

/**
 * Twilio Verify implementation. Sends the code WhatsApp-first; the caller
 * re-invokes start() with channel 'sms' for the fallback. Twilio owns the code,
 * so we only ever ask it whether a submitted code is approved.
 */
class TwilioPhoneVerifier implements PhoneVerifier
{
    public function __construct(
        private string $accountSid,
        private string $authToken,
        private string $verifyServiceSid,
    ) {}

    public function start(string $phone, string $channel = 'whatsapp'): void
    {
        $this->client()->verify->v2->services($this->verifyServiceSid)
            ->verifications
            ->create($phone, $channel);
    }

    public function check(string $phone, string $code): bool
    {
        $check = $this->client()->verify->v2->services($this->verifyServiceSid)
            ->verificationChecks
            ->create(['to' => $phone, 'code' => $code]);

        return $check->status === 'approved';
    }

    private function client(): Client
    {
        return new Client($this->accountSid, $this->authToken);
    }
}
