<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\Verification\TurnstileService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates the Cloudflare Turnstile response token submitted with a form.
 * Passes automatically when Turnstile is not configured (dev/test).
 */
class Turnstile implements ValidationRule
{
    public function __construct(private ?string $ip = null) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! app(TurnstileService::class)->verify(is_string($value) ? $value : null, $this->ip)) {
            $fail('The CAPTCHA verification failed. Please try again.');
        }
    }
}
