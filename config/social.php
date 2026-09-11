<?php

/**
 * Social login provider registry.
 *
 * The onboarding funnel is provider-agnostic: it renders a button for each provider
 * listed here whose credentials are configured in config/services.php. To light up a
 * new provider, add its Socialite driver + a row here, and set its credentials in env.
 * Google ships first; Microsoft, Apple, and Facebook are stubbed for later.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    |
    | Keyed by the Socialite driver name. `label` and `icon` drive the button;
    | `services_key` names the config/services.php entry whose client_id gates
    | whether the provider is enabled (no credentials → the button is hidden).
    |
    */
    'providers' => [
        'google' => [
            'label' => 'Google',
            'icon' => 'google',
            'services_key' => 'google',
        ],
        'microsoft' => [
            'label' => 'Microsoft',
            'icon' => 'microsoft',
            'services_key' => 'microsoft',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'icon' => 'facebook',
            'services_key' => 'facebook',
        ],
        'linkedin-openid' => [
            'label' => 'LinkedIn',
            'icon' => 'linkedin',
            'services_key' => 'linkedin-openid',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Coming soon
    |--------------------------------------------------------------------------
    |
    | Providers advertised but not yet live — rendered as greyed-out buttons with
    | a "Coming soon!" hint, regardless of credentials. Move a row up into
    | `providers` (and add its driver + creds) to switch it on.
    |
    */
    'coming_soon' => [
        'apple' => [
            'label' => 'Apple',
            'icon' => 'apple',
        ],
    ],

];
