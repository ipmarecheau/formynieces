{{-- resources/views/student/diagnostic-intro.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Expedition Awaits — SmoothSeas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple: #0e7490;
            --pink:   #f6b71e;
            --bg:     #06182e;
            --card:   #0c2440;
            --border: rgba(34,211,238,0.35);
            --text:   #e6f2fb;
            --muted:  #93b2cc;
            --teal:   #0d9488;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Nunito', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
            padding: 24px 0;
        }

        .stars { display: none; position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .star {
            position: absolute; background: white; border-radius: 50%;
            animation: twinkle var(--d, 3s) ease-in-out infinite var(--delay, 0s);
        }
        @keyframes twinkle {
            0%,100% { opacity: 0.15; transform: scale(1); }
            50%      { opacity: 0.9;  transform: scale(1.4); }
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
        .orb-1 { width: 400px; height: 400px; background: rgba(34,211,238,0.25); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(246,183,30,0.2);  bottom: -80px; right: -80px; }
        .orb-3 { width: 280px; height: 280px; background: rgba(13,148,136,0.16); top: 45%; left: 60%; }

        .card {
            position: relative; z-index: 1;
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 24px;
            padding: 48px 40px;
            width: 100%; max-width: 560px;
            margin: 20px;
            text-align: center;
            animation: fadeUp 0.6s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .smooth-intro {
            width: 132px; height: 132px; object-fit: contain;
            margin: 0 auto 6px; display: block;
            filter: drop-shadow(0 10px 22px rgba(0,0,0,0.45));
            animation: swimIn 0.9s cubic-bezier(.2,.8,.2,1) both, bob 4s ease-in-out 0.9s infinite;
        }
        @keyframes swimIn {
            from { opacity: 0; transform: translateX(-60px) translateY(10px) rotate(-8deg); }
            to   { opacity: 1; transform: translateX(0) translateY(0) rotate(0); }
        }
        @keyframes bob {
            0%,100% { transform: translateY(0) rotate(-2deg); }
            50%      { transform: translateY(-9px) rotate(2deg); }
        }
        .smooth-says {
            font-size: 12px; font-weight: 800; letter-spacing: 0.12em;
            text-transform: uppercase; color: #67e8f9; margin-bottom: 6px;
        }
        @media (prefers-reduced-motion: reduce) {
            .smooth-intro { animation: none; }
            .card { animation: none; }
        }

        .eyebrow {
            font-size: 12px; font-weight: 800;
            letter-spacing: 0.14em; text-transform: uppercase;
            color: var(--muted); margin-bottom: 14px;
        }

        h1 {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(28px, 7vw, 40px);
            line-height: 1.15;
            background: linear-gradient(135deg, #cbe4f0 0%, #fcd34d 60%, #fde68a 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 18px;
        }

        .lead {
            font-size: 16px; line-height: 1.7;
            color: var(--muted);
            max-width: 420px; margin: 0 auto 28px;
        }

        .islands {
            display: flex; justify-content: center; gap: 10px;
            flex-wrap: wrap; margin-bottom: 32px;
        }
        .island {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 8px 16px; border-radius: 999px;
            font-size: 13px; font-weight: 700;
        }
        .island-1 { background: rgba(13,148,136,.18);  border: 1.5px solid rgba(13,148,136,.45); color: #5eead4; }
        .island-2 { background: rgba(246,183,30,.16);  border: 1.5px solid rgba(246,183,30,.45); color: #fcd34d; }
        .island-3 { background: rgba(34,211,238,.18);  border: 1.5px solid rgba(34,211,238,.45); color: #67e8f9; }

        .reassure {
            font-size: 14px; line-height: 1.65;
            color: rgba(196,181,253,0.75);
            max-width: 400px; margin: 0 auto 30px;
        }

        .btn-sail {
            display: inline-block;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border: none; border-radius: 999px;
            padding: 16px 40px;
            color: white; font-family: 'Fredoka One', cursive; font-size: 18px;
            cursor: pointer; letter-spacing: 0.03em; text-decoration: none;
            box-shadow: 0 0 32px rgba(34,211,238,0.45);
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-sail:hover  { opacity: 0.92; }
        .btn-sail:active { transform: scale(0.98); }

        /* Click-through steps: only one shows at a time, so no wall of text on mobile. */
        .di-step { display: none; animation: fadeUp 0.4s ease both; }
        .di-step.is-active { display: block; }
        .di-dots { display: flex; gap: 8px; justify-content: center; margin: 0 0 22px; }
        .di-dot { width: 9px; height: 9px; border-radius: 999px; background: rgba(147,178,204,0.3); transition: background 0.2s; }
        .di-dot.is-on { background: #f6b71e; }
        .btn-next {
            display: inline-block; cursor: pointer;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border: none; border-radius: 999px; padding: 14px 38px;
            color: white; font-family: 'Fredoka One', cursive; font-size: 17px;
            letter-spacing: 0.03em; box-shadow: 0 0 28px rgba(34,211,238,0.4);
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-next:hover { opacity: 0.92; }
        .btn-next:active { transform: scale(0.98); }
    </style>
</head>
<body>

<div class="stars" id="stars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="card">
    <div class="di-dots" aria-hidden="true">
        <span class="di-dot is-on" data-dot="0"></span>
        <span class="di-dot" data-dot="1"></span>
        <span class="di-dot" data-dot="2"></span>
        <span class="di-dot" data-dot="3"></span>
    </div>

    {{-- Step 1 — Smooth says hi --}}
    <div class="di-step is-active" data-step="0">
        <img class="smooth-intro" src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle, waving hello">
        <p class="smooth-says">Smooth says hi 👋</p>
        <p class="eyebrow">Your expedition awaits</p>
        <h1>Ahoy, {{ explode(' ', auth()->user()->name)[0] }}! I'm Smooth 🐢</h1>
        <p class="lead" style="margin-bottom:24px;">
            I'm your guide on every voyage from here on — and any time you're stuck,
            just tap me to ask <em>anything</em>. 🐢
        </p>
        <button type="button" class="btn-next" data-next>Nice to meet you →</button>
    </div>

    {{-- Step 2 — what this is --}}
    <div class="di-step" data-step="1">
        <h1>Let's build your map 🗺️</h1>
        <p class="lead" style="margin-bottom:24px;">
            First, we'll explore together so I can build your very own map. This isn't
            a test to pass or fail — no grades, no wrong answers. It's just how I find
            out everything you already know. 🌟
        </p>
        <button type="button" class="btn-next" data-next>Okay! →</button>
    </div>

    {{-- Step 3 — the islands --}}
    <div class="di-step" data-step="2">
        <h1>Three places to explore 🌊</h1>
        <div class="islands" style="margin-bottom:24px;">
            <span class="island island-1">🔢 Number Isle</span>
            <span class="island island-2">✏️ Word Harbour</span>
            <span class="island island-3">📖 Story Cove</span>
        </div>
        <p class="lead" style="margin-bottom:24px;">
            We'll visit each one together. Some questions feel easy; some really make
            you think — the tricky ones mean you're doing brilliantly, and I'm seeing
            how far you can go. 🌟
        </p>
        <button type="button" class="btn-next" data-next>Got it →</button>
    </div>

    {{-- Step 4 — how it works + start --}}
    <div class="di-step" data-step="3">
        <h1>Ready when you are ⛵</h1>
        <p class="reassure" style="margin-bottom:26px;">
            Just pick the answer you think is best. The questions climb as you go —
            that's exactly what's meant to happen. Take all the time you like. 🌊
        </p>
        <a href="{{ route('diagnostic.start') }}" class="btn-sail">Set sail ⛵</a>
    </div>
</div>

<script>
    const container = document.getElementById('stars');
    for (let i = 0; i < 120; i++) {
        const s = document.createElement('div');
        s.className = 'star';
        const size = Math.random() * 2.5 + 1;
        s.style.cssText = `
            width:${size}px; height:${size}px;
            top:${Math.random()*100}%;
            left:${Math.random()*100}%;
            --d:${(Math.random()*4+2).toFixed(1)}s;
            --delay:-${(Math.random()*5).toFixed(1)}s;
        `;
        container.appendChild(s);
    }

    // Click-through stepper — one panel at a time, kind on small screens.
    (function () {
        const steps = Array.from(document.querySelectorAll('.di-step'));
        const dots  = Array.from(document.querySelectorAll('.di-dot'));
        let current = 0;
        function show(i) {
            steps.forEach((s, n) => s.classList.toggle('is-active', n === i));
            dots.forEach((d, n) => d.classList.toggle('is-on', n <= i));
            current = i;
            window.scrollTo({ top: 0, behavior: 'auto' });
        }
        document.querySelectorAll('[data-next]').forEach((btn) => {
            btn.addEventListener('click', () => { if (current < steps.length - 1) { show(current + 1); } });
        });
    })();
</script>
</body>
</html>