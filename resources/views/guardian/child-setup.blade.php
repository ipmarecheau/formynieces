<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Niece — ForMyNieces</title>
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
            width: 100%; max-width: 540px;
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
            font-family: 'Fredoka One', cursive; font-size: 24px;
            background: linear-gradient(135deg, #c084fc, #f472b6);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .brand p { color: var(--muted); font-size: 14px; margin-top: 4px; }

        .field { margin-bottom: 18px; }
        label.lbl {
            display: block; font-size: 13px; font-weight: 700;
            color: var(--muted); margin-bottom: 7px;
            letter-spacing: 0.04em; text-transform: uppercase;
        }
        input[type="text"],
        input[type="password"],
        input[type="number"] {
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
        .hint { font-size: 12px; color: rgba(196,181,253,0.7); margin-top: 5px; }

        .username-row { display: flex; align-items: center; gap: 0; }
        .username-row input { border-radius: 12px 0 0 12px; }
        .username-suffix {
            background: rgba(147,51,234,0.18);
            border: 1.5px solid rgba(147,51,234,0.3); border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 12px 14px; font-size: 13px; color: var(--muted); white-space: nowrap;
        }

        .strands { margin-bottom: 18px; }
        .strand-group { margin-bottom: 14px; }
        .strand-group h3 {
            font-family: 'Fredoka One', cursive; font-size: 14px; color: #e9d5ff;
            margin-bottom: 8px;
        }
        .strand-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .strand-check {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(147,51,234,0.2);
            border-radius: 10px; padding: 9px 12px;
            font-size: 13px; color: var(--muted); cursor: pointer;
            transition: all 0.15s;
        }
        .strand-check:hover { border-color: rgba(147,51,234,0.5); }
        .strand-check input { accent-color: var(--purple); width: 16px; height: 16px; cursor: pointer; }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border: none; border-radius: 999px; padding: 14px;
            color: white; font-family: 'Fredoka One', cursive; font-size: 16px;
            cursor: pointer; letter-spacing: 0.03em;
            transition: opacity 0.2s, transform 0.1s; margin-top: 6px;
        }
        .btn-submit:hover  { opacity: 0.9; }
        .btn-submit:active { transform: scale(0.98); }

        .errors {
            background: rgba(239,68,68,0.12);
            border: 1.5px solid rgba(239,68,68,0.35);
            border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;
            font-size: 13px; color: #fca5a5;
        }
        .errors ul { padding-left: 16px; }

        /* One-time credentials panel */
        .creds {
            background: rgba(34,197,94,0.1);
            border: 1.5px solid rgba(34,197,94,0.4);
            border-radius: 16px; padding: 24px; text-align: center;
        }
        .creds h2 { font-family: 'Fredoka One', cursive; font-size: 20px; color: #86efac; margin-bottom: 8px; }
        .creds .warn { font-size: 13px; color: #fde68a; margin-bottom: 18px; }
        .cred-row {
            display: flex; justify-content: space-between;
            background: rgba(0,0,0,0.25); border-radius: 10px;
            padding: 12px 16px; margin-bottom: 10px; font-size: 15px;
        }
        .cred-row .k { color: var(--muted); }
        .cred-row .v { color: var(--text); font-weight: 700; font-family: monospace; }
        .creds a {
            display: inline-block; margin-top: 12px;
            color: #c084fc; font-weight: 700; text-decoration: none; font-size: 14px;
        }
        .creds a:hover { color: #f472b6; }
    </style>
</head>
<body>

<div class="stars" id="stars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="card">

    @if (session('student_credentials'))
        @php $c = session('student_credentials'); @endphp
        <div class="brand">
            <div class="brand-icon">🎉</div>
            <h1>{{ $c['name'] }} is all set!</h1>
        </div>
        <div class="creds">
            <h2>Her Login Details</h2>
            <p class="warn">⚠️ Write these down now — they won't be shown again.</p>
            <div class="cred-row"><span class="k">Username</span><span class="v">{{ $c['username'] }}</span></div>
            <div class="cred-row"><span class="k">Login ID</span><span class="v">{{ $c['login_id'] }}</span></div>
            <div class="cred-row"><span class="k">Password</span><span class="v">{{ $c['password'] }}</span></div>
            <a href="{{ route('child.setup') }}">Set up another niece →</a>
        </div>
    @else
        <div class="brand">
            <div class="brand-icon">👧</div>
            <h1>Set Up A Niece Account</h1>
            <p>Create her account and start her SEA adventure</p>
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

        <form method="POST" action="{{ route('child.store') }}">
            @csrf

            <div class="field">
                <label class="lbl" for="name">Her Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Aaliyah" required autofocus>
            </div>

            <div class="field">
                <label class="lbl" for="username">Choose a Username</label>
                <div class="username-row">
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           placeholder="aaliyah" required>
                    <span class="username-suffix">@students.formynieces.com</span>
                </div>
                <p class="hint">Letters, numbers, dashes and underscores only. She'll use this to log in.</p>
            </div>

            <div class="field">
                <label class="lbl" for="password">Set a Password for Her</label>
                <input type="password" id="password" name="password"
                       placeholder="At least 8 characters" required>
            </div>

            <div class="field">
                <label class="lbl" for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       placeholder="Repeat the password" required>
            </div>

            <div class="field">
                <label class="lbl" for="target_sea_year">Target SEA Year</label>
                <input type="number" id="target_sea_year" name="target_sea_year"
                       value="{{ old('target_sea_year') }}"
                       min="2025" max="2035" placeholder="e.g. 2027" required>
            </div>

            <div class="strands">
                <label class="lbl">Known Weak Areas (optional)</label>
                <p class="hint" style="margin-bottom:12px;">Pick any you already know she struggles with. The diagnostic will check these too.</p>

                @foreach ($strandsBySubject as $subject => $strands)
                    <div class="strand-group">
                        <h3>{{ $subject }}</h3>
                        <div class="strand-grid">
                            @foreach ($strands as $strand)
                                <label class="strand-check">
                                    <input type="checkbox" name="known_weak_areas[]" value="{{ $strand }}"
                                        {{ in_array($strand, old('known_weak_areas', [])) ? 'checked' : '' }}>
                                    {{ $strand }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <button type="submit" class="btn-submit">Create Her Account 🌟</button>
        </form>
    @endif

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