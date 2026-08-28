<?php

namespace App\Providers;

use App\Services\Verification\PhoneVerifier;
use App\Services\Verification\StubPhoneVerifier;
use App\Services\Verification\TwilioPhoneVerifier;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Phone verification driver: Twilio Verify when fully configured,
        // otherwise the stub so dev/test exercise the flow with no live keys.
        $this->app->singleton(PhoneVerifier::class, function () {
            $sid = config('services.twilio.account_sid');
            $token = config('services.twilio.auth_token');
            $service = config('services.twilio.verify_service_sid');

            if ($sid && $token && $service) {
                return new TwilioPhoneVerifier($sid, $token, $service);
            }

            return new StubPhoneVerifier;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
