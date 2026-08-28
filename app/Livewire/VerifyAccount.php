<?php

namespace App\Livewire;

use App\Services\Verification\PhoneVerifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The post-registration verification screen. Email (link OR code) and phone
 * (WhatsApp code, with an SMS fallback) are confirmed here; once BOTH are done
 * the parent is sent straight into onboarding (/child-setup). Polls so that
 * verifying the email via the link in another tab advances this page too.
 */
class VerifyAccount extends Component
{
    public string $emailCode = '';

    public string $phoneCode = '';

    public ?string $status = null;

    public function mount(): RedirectResponse|Redirector|null
    {
        return $this->redirectIfDone();
    }

    /** Polled: advances the page when the email was verified via the link elsewhere. */
    public function poll()
    {
        return $this->redirectIfDone();
    }

    public function submitEmailCode()
    {
        $this->validate(['emailCode' => ['required', 'digits:6']]);

        if (! auth()->user()->verifyEmailCode($this->emailCode)) {
            $this->addError('emailCode', 'That code is incorrect or has expired.');

            return null;
        }

        $this->emailCode = '';
        $this->status = 'email-verified';

        return $this->redirectIfDone();
    }

    public function resendEmail(): void
    {
        auth()->user()->sendEmailVerificationNotification();
        $this->status = 'email-sent';
    }

    public function submitPhoneCode(PhoneVerifier $verifier)
    {
        $this->validate(['phoneCode' => ['required', 'digits:6']]);

        if (! $verifier->check(auth()->user()->phone, $this->phoneCode)) {
            $this->addError('phoneCode', 'That code is incorrect or has expired.');

            return null;
        }

        auth()->user()->markPhoneAsVerified();
        $this->phoneCode = '';
        $this->status = 'phone-verified';

        return $this->redirectIfDone();
    }

    /** Resend the phone code — 'whatsapp' (default) or 'sms' (the fallback). */
    public function resendPhone(string $channel, PhoneVerifier $verifier): void
    {
        $channel = $channel === 'sms' ? 'sms' : 'whatsapp';

        try {
            $verifier->start(auth()->user()->phone, $channel);
            $this->status = $channel === 'sms' ? 'phone-sms-sent' : 'phone-whatsapp-sent';
        } catch (\Throwable) {
            $this->addError('phoneCode', 'We could not send the code just now. Please try again.');
        }
    }

    /** Both verified → into onboarding; otherwise stay on the page. */
    private function redirectIfDone(): RedirectResponse|Redirector|null
    {
        if (auth()->user()->isFullyVerified()) {
            return redirect()->intended(route('child.setup'));
        }

        return null;
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.verify-account', [
            'user' => auth()->user(),
            'phoneRequired' => (bool) config('services.phone_verification.enabled') && auth()->user()->phone !== null,
        ]);
    }
}
