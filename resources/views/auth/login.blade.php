<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — ForMyNieces</title>
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
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Nunito', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* --- Stars --- */
        .stars {
            position: fixed; inset: 0; pointer-events: none; z-index: 0;
        }
        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle var(--d, 3s) ease-in-out infinite var(--delay, 0s);
        }
        @keyframes twinkle {
            0%,100% { opacity: 0.15; transform: scale(1); }
            50%      { opacity: 0.9;  transform: scale(1.4); }
        }

        /* --- Glowing orbs --- */
        .orb {
            position: fixed; border-radius: 50%; filter: blur(80px);
            pointer-events: none; z-index: 0;
        }
        .orb-1 { width: 400px; height: 400px; background: rgba(147,51,234,0.25); top: -100px; left: -100px; }
        .orb-2 { width: 300px; height: 300px; background: rgba(219,39,119,0.2); bottom: -80px; right: -80px; }

        /* --- Card --- */
        .card {
            position: relative; z-index: 1;
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 24px;
            padding: 44px 40px;
            width: 100%; max-width: 420px;
            margin: 20px;
            animation: fadeUp 0.5s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* --- Logo / brand --- */
        .brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 30px;
            margin-bottom: 14px;
            box-shadow: 0 0 30px rgba(147,51,234,0.5);
        }
        .brand h1 {
            font-family: 'Fredoka One', cursive;
            font-size: 26px;
            background: linear-gradient(135deg, #c084fc, #f472b6);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand p {
            color: var(--muted); font-size: 14px; margin-top: 4px;
        }

        /* --- Form --- */
        .field { margin-bottom: 20px; }
        label {
            display: block;
            font-size: 13px; font-weight: 700;
            color: var(--muted);
            margin-bottom: 7px;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(147,51,234,0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text);
            font-family: 'Nunito', sans-serif;
            font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }
        input:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(147,51,234,0.2);
        }
        input::placeholder { color: rgba(196,181,253,0.4); }

        /* remember + forgot row */
        .meta-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 24px; font-size: 14px;
        }
        .remember { display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--muted); }
        .remember input[type="checkbox"] {
            width: 16px; height: 16px; border-radius: 5px;
            accent-color: var(--purple);
        }
        .forgot { color: #c084fc; text-decoration: none; font-weight: 600; }
        .forgot:hover { color: #f472b6; }

        /* submit */
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border: none; border-radius: 999px;
            padding: 14px;
            color: white;
            font-family: 'Fredoka One', cursive;
            font-size: 17px;
            cursor: pointer;
            letter-spacing: 0.03em;
            transition: opacity 0.2s, transform 0.1s;
        }
        .btn-submit:hover  { opacity: 0.9; }
        .btn-submit:active { transform: scale(0.98); }

        /* footer link */
        .foot {
            text-align: center; margin-top: 24px;
            font-size: 14px; color: var(--muted);
        }
        .foot a { color: #c084fc; font-weight: 700; text-decoration: none; }
        .foot a:hover { color: #f472b6; }

        /* error messages */
        .errors {
            background: rgba(239,68,68,0.12);
            border: 1.5px solid rgba(239,68,68,0.35);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #fca5a5;
        }
        .errors ul { padding-left: 16px; }
        .status-msg {
            background: rgba(34,197,94,0.12);
            border: 1.5px solid rgba(34,197,94,0.3);
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #86efac;
        }
    </style>
</head>
<body>

<!-- Stars -->
<div class="stars" id="stars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="card">
    <div class="brand">
        <div class="brand-icon">✨</div>
        <h1>ForMyNieces</h1>
        <p>Your SEA study companion</p>
    </div>

    @if (session('status'))
        <div class="status-msg">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="you@example.com"
                   required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="••••••••"
                   required autocomplete="current-password">
        </div>

        <div class="meta-row">
            <label class="remember">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a class="forgot" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="btn-submit">Sign In ✨</button>
    </form>

    @if (Route::has('register'))
        <p class="foot">
            New here? <a href="{{ route('register') }}">Create an account</a>
        </p>
    @endif
</div>

<script>
    // Generate stars
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