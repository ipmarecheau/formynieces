<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Up Your Child — SmoothSeas</title>
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
            box-shadow: 0 0 30px rgba(34,211,238,0.5);
        }
        .brand h1 {
            font-family: 'Fredoka One', cursive; font-size: 24px;
            background: linear-gradient(135deg, #67e8f9, #fcd34d);
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
            border: 1.5px solid rgba(34,211,238,0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text); font-family: 'Nunito', sans-serif; font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        input:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(34,211,238,0.2);
        }
        input::placeholder { color: rgba(196,181,253,0.4); }
        .hint { font-size: 12px; color: rgba(196,181,253,0.7); margin-top: 5px; }

        /* Generated-login highlight — the focal point of the form */
        .login-preview {
            position: relative; text-align: center; margin: 6px 0 22px;
            background: linear-gradient(160deg, rgba(34,211,238,0.12), rgba(246,183,30,0.10));
            border: 1.5px solid rgba(34,211,238,0.55); border-radius: 16px;
            padding: 18px 20px 16px; box-shadow: 0 0 34px rgba(34,211,238,0.18);
        }
        .login-preview .lp-label {
            display: inline-block; font-size: 11.5px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #86efac; margin-bottom: 8px;
        }
        .login-preview .lp-email {
            font-family: 'Fredoka One', cursive; font-size: clamp(20px, 5.4vw, 27px);
            line-height: 1.1; word-break: break-all;
        }
        .login-preview .lp-email #username-preview { color: #fcd34d; }
        .login-preview .lp-email .lp-suffix { color: #67e8f9; }
        .login-preview .lp-note { font-size: 12px; color: var(--muted); margin-top: 9px; }
        .login-preview .lp-note strong { color: #cbe4f0; }

        .username-row { display: flex; align-items: center; gap: 0; }
        .username-row input { border-radius: 12px 0 0 12px; }
        .username-suffix {
            background: rgba(34,211,238,0.18);
            border: 1.5px solid rgba(34,211,238,0.3); border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 12px 14px; font-size: 13px; color: var(--muted); white-space: nowrap;
        }

        .year-chips { display: flex; flex-wrap: wrap; gap: 10px; }
        .year-chip { position: relative; cursor: pointer; }
        .year-chip input { position: absolute; opacity: 0; pointer-events: none; }
        .year-chip span {
            display: block; min-width: 76px; text-align: center;
            background: rgba(255,255,255,0.06); border: 1.5px solid rgba(34,211,238,0.3);
            border-radius: 12px; padding: 12px 16px; color: var(--text);
            font-family: 'Fredoka One', cursive; font-size: 17px; transition: all 0.15s;
        }
        .year-chip:hover span { border-color: rgba(34,211,238,0.6); }
        .year-chip input:checked + span {
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border-color: transparent; color: #fff; box-shadow: 0 0 20px rgba(34,211,238,0.35);
        }
        .year-chip input:focus-visible + span { outline: 2px solid #67e8f9; outline-offset: 2px; }

        .strands { margin-bottom: 18px; }
        .strand-group { margin-bottom: 14px; }
        .strand-group h3 {
            font-family: 'Fredoka One', cursive; font-size: 14px; color: #cbe4f0;
            margin-bottom: 8px;
        }
        .strand-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .strand-check {
            display: flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,0.04);
            border: 1.5px solid rgba(34,211,238,0.2);
            border-radius: 10px; padding: 9px 12px;
            font-size: 13px; color: var(--muted); cursor: pointer;
            transition: all 0.15s;
        }
        .strand-check:hover { border-color: rgba(34,211,238,0.5); }
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
        .cred-hero {
            text-align: left; margin-bottom: 14px; padding: 16px 18px;
            background: linear-gradient(160deg, rgba(34,211,238,0.16), rgba(246,183,30,0.10));
            border: 1.5px solid rgba(34,211,238,0.6); border-radius: 14px;
            box-shadow: 0 0 30px rgba(34,211,238,0.2);
        }
        .cred-hero .k {
            display: block; font-size: 11.5px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #86efac; margin-bottom: 6px;
        }
        .cred-hero .v {
            font-family: 'Fredoka One', cursive; font-size: clamp(18px, 4.8vw, 25px);
            color: #fcd34d; word-break: break-all; line-height: 1.15;
        }
        .creds a {
            display: inline-block; margin-top: 12px;
            color: #67e8f9; font-weight: 700; text-decoration: none; font-size: 14px;
        }
        .creds a:hover { color: #fcd34d; }
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
            <h2>Child's Login Details</h2>
            <p class="warn">✅ All set! We've emailed these credentials to you for your records.</p>
            <div class="cred-hero"><span class="k">Login ID (email)</span><span class="v">{{ $c['login_id'] }}</span></div>
            <div class="cred-row"><span class="k">Username</span><span class="v">{{ $c['username'] }}</span></div>
            <div class="cred-row"><span class="k">Password</span><span class="v">{{ $c['password'] }}</span></div>
            <p class="warn" style="color:#93b2cc;margin-top:14px;">🔑 You can view or reset the password anytime from <strong>Children's logins</strong> in your dashboard — no need to write it down.</p>
            <a href="{{ route('guardian.children') }}">Manage children's logins →</a><br>
            <a href="{{ route('child.setup') }}">Set up another child →</a>
        </div>
    @else
        <div class="brand">
            <div class="brand-icon">👧</div>
            <h1>Set Up A Child Account</h1>
            <p>Create the account and start the SEA adventure</p>
        </div>

        @include('partials.setup-stepper', ['current' => 2])

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
                <label class="lbl" for="name">Child's Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       placeholder="e.g. Aaliyah Thomas" required autofocus>
            </div>

            <div class="login-preview" aria-live="polite">
                <span class="lp-label">✨ Your child's login</span>
                <div class="lp-email"><span id="username-preview">…</span><span class="lp-suffix">@smoothseas.org</span></div>
                <p class="lp-note">Auto-created from the first initial + last name — <strong>save it, it's how they sign in</strong>. A number is added if it's already taken.</p>
            </div>

            <div class="field">
                <label class="lbl">Target SEA Year</label>
                @php($years = range(now()->year, now()->year + 4))
                <div class="year-chips">
                    @foreach ($years as $y)
                        <label class="year-chip">
                            <input type="radio" name="target_sea_year" value="{{ $y }}"
                                   {{ (int) old('target_sea_year') === $y ? 'checked' : '' }} required>
                            <span>{{ $y }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="hint" style="margin-top:8px;">A strong password is generated automatically — you can reveal or reset it anytime in your Parent Portal.</p>
            </div>

            <div class="strands">
                <label class="lbl">Known Weak Areas (optional)</label>
                <p class="hint" style="margin-bottom:12px;">Pick any you already know they struggle with. The diagnostic will check these too.</p>

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

            <button type="submit" class="btn-submit">Create the Account 🌟</button>
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

    // Live username preview — mirrors the server rule (first initial + first 4 of
    // last name, lowercased, a–z0–9). The final login may gain a number if taken.
    (function () {
        const nameInput = document.getElementById('name');
        const preview = document.getElementById('username-preview');
        if (!nameInput || !preview) { return; }

        function derive(name) {
            const parts = name.trim().split(/\s+/).filter(Boolean);
            if (parts.length === 0) { return ''; }
            const first = parts[0];
            const last = parts.length > 1 ? parts[parts.length - 1] : first;
            return (first.charAt(0) + last.slice(0, 4))
                .toLowerCase()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9]/g, '');
        }

        function update() {
            preview.textContent = derive(nameInput.value) || '…';
        }

        nameInput.addEventListener('input', update);
        update();
    })();
</script>
</body>
</html>