<?php

namespace App\Providers;

use App\Services\Persona\BrowserPersonaSimulator;
use App\Services\Persona\Contracts\PersonaSimulator;
use App\Services\Push\PushTransportRegistry;
use App\Services\Push\Transports\ApnsTransport;
use App\Services\Push\Transports\FcmTransport;
use App\Services\Verification\PhoneVerifier;
use App\Services\Verification\StubPhoneVerifier;
use App\Services\Verification\TwilioPhoneVerifier;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\Provider;

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

        // Self-hosted push: resolve a transport per platform. Real transports call Apple/
        // Google directly; tests re-register fakes on this same singleton.
        $this->app->singleton(PushTransportRegistry::class, function () {
            return (new PushTransportRegistry)
                ->register(new ApnsTransport)
                ->register(new FcmTransport);
        });

        // Persona-simulation agent: the live engine by default; tests bind a fake in its place.
        $this->app->bind(
            PersonaSimulator::class,
            BrowserPersonaSimulator::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Allow Blade views in resources/views/layouts to be used as components,
        // so controller-returned pages can wrap themselves in <x-layouts.guardian>
        // (the same chrome Livewire pages get via #[Layout('layouts.guardian')]).
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        // Register the community Socialite driver (Microsoft covers Hotmail/Outlook/Live).
        // Google, Facebook and LinkedIn (OpenID) are built into Socialite core.
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', Provider::class);
        });
    }
}
