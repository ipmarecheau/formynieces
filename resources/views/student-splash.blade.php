<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome back! ✨ ForMyNieces</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka+One&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #fdf4ff 0%, #ede9fe 100%);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .fmn-splash {
            max-width: 560px;
            width: 100%;
            text-align: center;
        }

        .fmn-splash-confetti {
            font-size: 2rem;
            letter-spacing: 6px;
            margin-bottom: 0.5rem;
        }

        .fmn-splash-hero {
            background: linear-gradient(135deg, #9333ea 0%, #db2777 100%);
            border-radius: 26px;
            padding: 2rem 1.75rem;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 12px 32px rgba(168, 85, 247, 0.28);
        }
        .fmn-splash-hero::before {
            content: '✦';
            position: absolute;
            left: 1.25rem;
            top: 0.5rem;
            font-size: 2.5rem;
            opacity: 0.18;
            pointer-events: none;
        }
        .fmn-splash-hero::after {
            content: '✦';
            position: absolute;
            right: 1.25rem;
            bottom: 0.75rem;
            font-size: 3.5rem;
            opacity: 0.18;
            pointer-events: none;
        }

        .fmn-splash-title {
            font-family: 'Fredoka One', cursive;
            font-size: 2rem;
            margin: 0 0 0.4rem;
            line-height: 1.15;
        }
        .fmn-splash-name {
            color: #fde68a;
        }
        .fmn-splash-sub {
            font-size: 0.95rem;
            opacity: 0.92;
            margin: 0;
        }

        .fmn-splash-streaks {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 1.5rem 0 2rem;
        }

        .fmn-streak {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: white;
            border: 1.5px solid #f3e8ff;
            border-radius: 999px;
            padding: 12px 22px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 1rem;
            color: #c2410c;
            box-shadow: 0 3px 10px rgba(124, 58, 237, 0.06);
        }
        .fmn-streak-emoji {
            font-size: 1.3rem;
        }
        .fmn-streak-login    { color: #7c3aed; }
        .fmn-streak-mastery  { color: #b45309; }
        .fmn-streak-pace     { color: #166534; }

        .fmn-splash-continue {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            padding: 16px 38px;
            border-radius: 999px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 8px 22px rgba(236, 72, 153, 0.32);
        }
        .fmn-splash-continue:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(236, 72, 153, 0.4);
        }
        .fmn-splash-hint {
            display: block;
            margin-top: 0.85rem;
            font-size: 0.78rem;
            color: #a78bfa;
            font-weight: 700;
        }
    </style>
</head>
<body>

    <main class="fmn-splash">

        <div class="fmn-splash-confetti">🎉 ✨ 🎊 ✨ 🎉</div>

        <div class="fmn-splash-hero">
            <h1 class="fmn-splash-title">
                Welcome back, <span class="fmn-splash-name">{{ explode(' ', $user->name)[0] }}</span>! 🌸
            </h1>
            <p class="fmn-splash-sub">Look at you go — your streaks are on fire! 🔥</p>
        </div>

        {{-- Celebrate her current streaks (only the ones she's actually built). --}}
        <div class="fmn-splash-streaks">
            @if ($practiceStreak > 0)
                <span class="fmn-streak">
                    <span class="fmn-streak-emoji">🔥</span> {{ $practiceStreak }} day practice streak
                </span>
            @endif

            @if ($loginStreak > 0)
                <span class="fmn-streak fmn-streak-login">
                    <span class="fmn-streak-emoji">🔑</span> {{ $loginStreak }} day login streak
                </span>
            @endif

            @if ($masteryStreak > 0)
                <span class="fmn-streak fmn-streak-mastery">
                    <span class="fmn-streak-emoji">🏆</span> {{ $masteryStreak }} day mastery streak
                </span>
            @endif

            @if ($paceStreak > 0)
                <span class="fmn-streak fmn-streak-pace">
                    <span class="fmn-streak-emoji">🌷</span> {{ $paceStreak }} week on-pace streak
                </span>
            @endif
        </div>

        <a href="{{ route('student.map') }}" class="fmn-splash-continue">
            Continue to my map →
        </a>
        <span class="fmn-splash-hint">Keep the streak alive today! 🌟</span>

    </main>

</body>
</html>
