<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — ForMyNieces</title>
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

        .card {
            position: relative; z-index: 1;
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 24px;
            padding: 44px 40px;
            width: 100%; max-width: 440px;
            margin: 20px;
            animation: fadeUp 0.5s ease both;
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand { text-align: center; margin-bottom: 28px; }
        .brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 30px; margin-bottom: 14px;
            box-shadow: 0 0 30px rgba(147,51,234,0.5);
        }
        .brand h1 {
            font-family: 'Fredoka One', cursive; font-size: 26px;
            background: linear-gradient(135deg, #c084fc, #f472b6);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .brand p { color: var(--muted); font-size: 14px; margin-top: 4px; }

        .field { margin-bottom: 18px; }
        label {
            display: block; font-size: 13px; font-weight: 700;
            color: var(--muted); margin-bottom: 7px;
            letter-spacing: 0.04em; text-transform: uppercase;
        }
        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(147,51,234,0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text); font-family: 'Nunito', sans-serif; font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        input:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(147,51,234,0.2);
        }
        input::placeholder { color: rgba(196,181,253,0.4); }

        /* Role selector */
        .role-group {
            display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
            margin-bottom: 18px;
        }
        .role-option { display: none; }
        .role-label {
            display: flex; flex-direction: column; align-items: center;
            gap: 6px; padding: 14px 10px;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(147,51,234,0.25);
            border-radius: 14px; cursor: pointer;
            transition: all 0.2s; text-transform: none;
            letter-spacing: 0; font-size: 14px; font-weight: 600;
            color: var(--muted);
        }
        .role-label .icon { font-size: 24px; }
        .role-option:checked + .role-label {
            border-color: var(--purple);
            background: rgba(147,51,234,0.15);
            color: #e9d5ff;
            box-shadow: 0 0 0 3px rgba(147,51,234,0.2);
        }
        .role-section-label {
            font-size: 13px; font-weight: 700; color: var(--muted);
            letter-spacing: 0.04em; text-transform: uppercase;
            margin-bottom: 10px; display: block;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border: none; border-radius: 999px; padding: 14px;
            color: white; font-family: 'Fredoka One', cursive; font-size: 17px;
            cursor: pointer; letter-spacing: 0.03em;
            transition: opacity 0.2s, transform 0.1s;
            margin-top: 6px;
        }
        .btn-submit:hover  { opacity: 0.9; }
        .btn-submit:active { transform: scale(0.98); }

        .foot { text-align: center; margin-top: 24px; font-size: 14px; color: var(--muted); }
        .foot a { color: #c084fc; font-weight: 700; text-decoration: none; }
        .foot a:hover { color: #f472b6; }

        .errors {
            background: rgba(239,68,68,0.12);
            border: 1.5px solid rgba(239,68,68,0.35);
            border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;
            font-size: 13px; color: #fca5a5;
        }
        .errors ul { padding-left: 16px; }
    </style>
</head>
<body>

<div class="stars" id="stars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="card">
    <div class="brand">
        <div class="brand-icon">🌟</div>
        <h1>ForMyNieces</h1>
        <p>Join your SEA journey today</p>
    </div>

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name"
                   value="{{ old('name') }}"
                   placeholder="e.g. Aaliyah Thomas"
                   required autofocus autocomplete="name">
        </div>

        <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="you@example.com"
                   required autocomplete="username">
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   placeholder="At least 8 characters"
                   required autocomplete="new-password">
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   placeholder="Repeat your password"
                   required autocomplete="new-password">
        </div>

        <span class="role-section-label">I am a…</span>
        <div class="role-group">
            <input type="radio" name="role" id="role-student" value="student" class="role-option"
                   {{ old('role', 'student') === 'student' ? 'checked' : '' }}>
            <label for="role-student" class="role-label">
                <span class="icon">🎒</span>
                Student
            </label>

            <input type="radio" name="role" id="role-parent" value="parent" class="role-option"
                   {{ old('role') === 'parent' ? 'checked' : '' }}>
            <label for="role-parent" class="role-label">
                <span class="icon">👩‍👧</span>
                Parent
            </label>
        </div>

        <button type="submit" class="btn-submit">Create Account 🌟</button>
    </form>

    <p class="foot">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </p>
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