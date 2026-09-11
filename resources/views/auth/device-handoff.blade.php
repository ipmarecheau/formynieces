<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handing this device to {{ $child->name }} · SmoothSeas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
            font-family:'Nunito',system-ui,sans-serif;color:#0b2a31;
            background:radial-gradient(1200px 600px at 50% -10%, #1aa7c0, #0d7d8c 45%, #0a5c68 100%);}
        .card{width:100%;max-width:380px;background:#fff;border-radius:26px;padding:32px 26px 26px;box-shadow:0 30px 70px -30px rgba(0,0,0,.5);text-align:center}
        .turtle{font-size:56px;line-height:1;margin-bottom:6px}
        h1{font-family:'Fredoka',sans-serif;font-weight:600;font-size:23px;margin:0 0 6px;color:#0a5c68}
        .sub{font-size:14px;color:#4b6670;font-weight:700;margin:0 0 22px;line-height:1.5}
        .count{font-family:'Fredoka';font-weight:600;font-size:15px;color:#0d7d8c;background:#e7f5f4;border-radius:999px;padding:9px 16px;display:inline-block;margin-bottom:20px}
        .btn{width:100%;background:linear-gradient(160deg,#f2a900,#d99400);color:#3a2600;border:0;border-radius:15px;
            padding:15px;font-family:'Fredoka';font-weight:600;font-size:18px;cursor:pointer}
        .btn:hover{filter:brightness(1.04)}
        .foot{margin-top:18px;font-size:13px;color:#6e8890;font-weight:700}
        .foot a{color:#0d7d8c;font-weight:800;text-decoration:none}
    </style>
</head>
<body>
    <div class="card">
        <div class="turtle">🐢</div>
        <h1>Handing this device to {{ $child->name }}</h1>
        <p class="sub">You'll be signed out so {{ $child->name }} can sign in. Their login ID will already be filled in — they just type their password.</p>

        <p class="count">Signing you out in <span id="handoff-count">5</span>s…</p>

        <a href="{{ $commitUrl }}" class="btn" id="handoff-go">Hand it over now →</a>

        <p class="foot" style="margin-top:14px;">This hand-off link works until {{ $expiresAt->timezone(config('app.timezone'))->format('g:i A') }}. If it expires, just come back and tap the button again.</p>

        <div class="foot" style="margin-top:16px; display:flex; gap:16px; justify-content:center;">
            <a href="{{ route('guardian.dashboard') }}">← Back to dashboard</a>
            <a href="{{ route('guardian.children') }}">Use child login manually</a>
        </div>
    </div>

    <script>
        (function () {
            var n = 5;
            var el = document.getElementById('handoff-count');
            var go = document.getElementById('handoff-go');
            var tick = setInterval(function () {
                n -= 1;
                if (el) { el.textContent = n; }
                if (n <= 0) { clearInterval(tick); window.location.assign(go.href); }
            }, 1000);
        })();
    </script>
</body>
</html>
