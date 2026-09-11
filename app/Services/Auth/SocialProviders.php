<?php

declare(strict_types=1);

namespace App\Services\Auth;

/**
 * Resolves which social-login providers are actually usable — a provider from
 * config/social.php is only "enabled" when its credentials exist in config/services.php.
 * Views use this to render buttons; the controller uses it to reject unknown/off providers.
 */
class SocialProviders
{
    /**
     * @return array<string, array{label:string, icon:string, services_key:string}>
     */
    public static function enabled(): array
    {
        return array_filter(
            config('social.providers', []),
            fn (array $config): bool => filled(config("services.{$config['services_key']}.client_id"))
                && filled(config("services.{$config['services_key']}.client_secret")),
        );
    }

    public static function isEnabled(string $provider): bool
    {
        return array_key_exists($provider, self::enabled());
    }

    /**
     * Providers advertised as "coming soon" — shown greyed-out, not yet usable.
     *
     * @return array<string, array{label:string, icon:string}>
     */
    public static function comingSoon(): array
    {
        return config('social.coming_soon', []);
    }
}
