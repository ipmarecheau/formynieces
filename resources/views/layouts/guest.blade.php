<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'SmoothSeas') }}</title>
        <x-brand.head />
        <style>
            body { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 24px; }
            .guest-wrap { position: relative; z-index: 2; width: 100%; max-width: 440px; }
            .guest-brand { text-align: center; margin-bottom: 22px; }
            .guest-brand .ss-logo { justify-content: center; }
            .guest-card { padding: 34px 32px; }

            /* Parent auth pages use the light landing palette (the dark sea theme is for students). */
            .ss-sea { display: none !important; }
            body.ss-body { background: #fbf8f2; color: #0f172a; }
            .guest-card.ss-card { background: #fff; border: 1.5px solid #e7ddcd;
                box-shadow: 0 18px 40px -22px rgba(18,34,46,.26); backdrop-filter: none; }
            /* Brand wordmark + mark recolored for the cream background. */
            .ss-logo-word { color: #0d9488; }
            .ss-logo-word b { background: none; -webkit-text-fill-color: #0d9488; color: #0d9488; }
            .ss-logo-mark { background: #0d9488; box-shadow: 0 8px 20px rgba(13,125,140,0.28); }

            /* Theme the Breeze form controls that render inside the slot. */
            .guest-card label { color: #475569; font-weight: 800; font-size: 13px;
                letter-spacing: 0.04em; text-transform: uppercase; }
            .guest-card input:not([type=checkbox]):not([type=radio]) {
                width: 100%; background: #f6faf9; border: 1.5px solid rgba(13,125,140,0.3);
                border-radius: 12px; padding: 12px 16px; color: #0f172a;
                font-family: var(--ss-font-body); font-size: 15px; margin-top: 6px; }
            .guest-card input:not([type=checkbox]):not([type=radio])::placeholder { color: #9aabb5; }
            .guest-card input:focus { outline: none; border-color: #0d9488;
                box-shadow: 0 0 0 3px rgba(13,125,140,0.2); }
            .guest-card .text-gray-600, .guest-card .dark\:text-gray-400,
            .guest-card p, .guest-card div { color: #475569; }
            .guest-card button {
                font-family: var(--ss-font-head) !important; font-size: 16px !important;
                background: #0d9488 !important; color: #fff !important;
                border: none !important; border-radius: 999px !important; padding: 12px 26px !important;
                letter-spacing: 0.02em !important; text-transform: none !important; box-shadow: 0 6px 16px rgba(13,125,140,0.25); }
            .guest-card button:hover { background: #0f766e !important; }
            .guest-card a { color: #0d9488; font-weight: 700; }
            .guest-card a:hover { color: #b45309; }
        </style>
    </head>
    <body class="ss-body">
        <x-brand.sea :compass="false" />

        <div class="guest-wrap">
            <div class="guest-brand">
                <a href="/"><x-brand.logo /></a>
            </div>
            <div class="ss-card guest-card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
