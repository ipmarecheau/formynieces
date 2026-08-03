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

            /* Theme the Breeze form controls that render inside the slot. */
            .guest-card label { color: var(--ss-muted); font-weight: 800; font-size: 13px;
                letter-spacing: 0.04em; text-transform: uppercase; }
            .guest-card input:not([type=checkbox]):not([type=radio]) {
                width: 100%; background: rgba(6,24,46,0.55); border: 1.5px solid var(--ss-border);
                border-radius: 12px; padding: 12px 16px; color: var(--ss-ink);
                font-family: var(--ss-font-body); font-size: 15px; margin-top: 6px; }
            .guest-card input:focus { outline: none; border-color: var(--ss-cyan);
                box-shadow: 0 0 0 3px rgba(34,211,238,0.22); }
            .guest-card .text-gray-600, .guest-card .dark\:text-gray-400,
            .guest-card p, .guest-card div { color: var(--ss-ink); }
            .guest-card button {
                font-family: var(--ss-font-head) !important; font-size: 16px !important;
                background: var(--ss-gold-gradient) !important; color: #06182e !important;
                border: none !important; border-radius: 999px !important; padding: 12px 26px !important;
                letter-spacing: 0.02em !important; text-transform: none !important; box-shadow: 0 8px 22px rgba(217,119,6,0.35); }
            .guest-card a { color: var(--ss-cyan); font-weight: 700; }
            .guest-card a:hover { color: var(--ss-aqua); }
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
