<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome back aboard! ⛵ {{ config('app.name', 'SmoothSeas') }}</title>
    <x-brand.head />
    <style>
        body { display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .splash { position: relative; z-index: 2; max-width: 560px; width: 100%; text-align: center; }
        .splash-flags { font-size: 1.8rem; letter-spacing: 8px; margin-bottom: 0.6rem; }

        .splash-hero {
            background: linear-gradient(135deg, #0e7490 0%, #0d9488 55%, #0e4d6e 100%);
            border: 1.5px solid var(--ss-border);
            border-radius: 26px; padding: 2rem 1.75rem; color: var(--ss-foam);
            position: relative; overflow: hidden;
            box-shadow: 0 18px 44px rgba(0,0,0,0.4);
        }
        .splash-hero::before { content: '⚓'; position: absolute; left: 1.1rem; top: 0.5rem; font-size: 2.4rem; opacity: 0.16; }
        .splash-hero::after  { content: '🧭'; position: absolute; right: 1.1rem; bottom: 0.6rem; font-size: 3.2rem; opacity: 0.16; }
        .splash-title { font-family: var(--ss-font-head); font-size: 2rem; margin: 0 0 0.4rem; line-height: 1.15; }
        .splash-name { color: var(--ss-gold); }
        .splash-sub { font-size: 0.98rem; opacity: 0.94; margin: 0; }

        .splash-streaks { display: flex; flex-direction: column; gap: 12px; margin: 1.5rem 0 2rem; }
        .streak {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            background: var(--ss-card); border: 1.5px solid var(--ss-border);
            border-radius: 999px; padding: 12px 22px; backdrop-filter: blur(8px);
            font-weight: 800; font-size: 1rem; color: var(--ss-gold);
        }
        .streak-emoji { font-size: 1.3rem; }
        .streak-login   { color: var(--ss-cyan); }
        .streak-mastery { color: var(--ss-gold); }
        .streak-pace    { color: #6ee7b7; }

        .splash-hint { display: block; margin-top: 0.9rem; font-size: 0.78rem; color: var(--ss-muted); font-weight: 700; }
    </style>
</head>
<body class="ss-body">

<x-brand.sea />

<main class="splash">

    <div class="splash-flags">🎉 ⛵ 🌊 ⛵ 🎉</div>

    <div class="splash-hero">
        <h1 class="splash-title">
            Welcome back aboard, <span class="splash-name">{{ explode(' ', $user->name)[0] }}</span>! ⛵
        </h1>
        <p class="splash-sub">Fair winds and a following sea — your streaks are on fire! 🔥</p>
    </div>

    {{-- Celebrate her current streaks (only the ones she's actually built). --}}
    <div class="splash-streaks">
        @if ($practiceStreak > 0)
            <span class="streak"><span class="streak-emoji">🔥</span> {{ $practiceStreak }} day practice streak</span>
        @endif
        @if ($loginStreak > 0)
            <span class="streak streak-login"><span class="streak-emoji">🧭</span> {{ $loginStreak }} day login streak</span>
        @endif
        @if ($masteryStreak > 0)
            <span class="streak streak-mastery"><span class="streak-emoji">🏆</span> {{ $masteryStreak }} day mastery streak</span>
        @endif
        @if ($paceStreak > 0)
            <span class="streak streak-pace"><span class="streak-emoji">🗺️</span> {{ $paceStreak }} week on-pace streak</span>
        @endif
    </div>

    <a href="{{ route('student.voyage') }}" class="ss-btn">Continue to my voyage ⛵</a>
    <span class="splash-hint">Keep the streak alive today! 🌟</span>

</main>

</body>
</html>
