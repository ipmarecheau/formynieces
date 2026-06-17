{{-- resources/views/student/diagnostic-intro.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Expedition Awaits — ForMyNieces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple: #9333ea;
            --pink:   #db2777;
            --bg:     #0f0720;
            --card:   #1a0d30;
            --border: rgba(147,51,234,0.35);
            --text:   #f3e8ff;
            --muted:  #c4b5fd;
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

        .stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .star {
            position: absolute; background: white; border-radius: 50%;
            animation: twinkle var(--d, 3s) ease-in-out infinite var(--delay, 0s);
        }
        @keyframes twinkle {
            0%,100% { opacity: 0.15; transform: scale(1); }
            50%      { opacity: 0.9;  transform: scale(1.4); }
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
        .orb-1 { width: 400px; height: 400px; background: rgba(147,51,234,0.25); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(219,39,119,0.2);  bottom: -80px; right: -80px; }
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

        .crest {
            width: 76px; height: 76px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border-radius: 22px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 36px; margin-bottom: 20px;
            box-shadow: 0 0 36px rgba(147,51,234,0.5);
            animation: bob 4s ease-in-out infinite;
        }
        @keyframes bob {
            0%,100% { transform: translateY(0) rotate(-3deg); }
            50%      { transform: translateY(-8px) rotate(3deg); }
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
            background: linear-gradient(135deg, #e9d5ff 0%, #f472b6 60%, #fde68a 100%);
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
        .island-2 { background: rgba(219,39,119,.16);  border: 1.5px solid rgba(219,39,119,.45); color: #f472b6; }
        .island-3 { background: rgba(147,51,234,.18);  border: 1.5px solid rgba(147,51,234,.45); color: #c084fc; }

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
            box-shadow: 0 0 32px rgba(147,51,234,0.45);
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-sail:hover  { opacity: 0.92; }
        .btn-sail:active { transform: scale(0.98); }
    </style>
</head>
<body>

<div class="stars" id="stars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="card">
    <div class="crest">🧭</div>

    <p class="eyebrow">Your expedition awaits</p>

    <h1>Ready to explore, {{ explode(' ', auth()->user()->name)[0] }}? 🌊</h1>

    <p class="lead">
        This isn't a test to pass or fail — it's how we find out everything you
        already know. Some questions will feel easy, and some will really make
        you think. The tricky ones mean you're doing brilliantly, and we're
        seeing how far you can go. 🌟
    </p>

    <div class="islands">
        <span class="island island-1">🔢 Number Isle</span>
        <span class="island island-2">✏️ Word Harbour</span>
        <span class="island island-3">📖 Story Cove</span>
    </div>



    <p class="reassure">
        Just pick the answer you think is best. The questions climb as you go —
        that's exactly what's meant to happen. Take all the time you like. 🌊
    </p>

    <a href="{{ route('diagnostic.start') }}" class="btn-sail">Set sail ⛵</a>
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
</script>
</body>
</html>