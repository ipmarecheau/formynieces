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
            --purple: #0d7d8c;
            --pink:   #f2a900;
            --bg:     #fbf8f2;
            --card:   #ffffff;
            --border: #e7ddcd;
            --text:   #12222e;
            --muted:  #40566a;
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
        .orb { display:none; position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
        .orb-1 { width: 400px; height: 400px; background: rgba(13,125,140,0.25); top: -100px; left: -100px; }
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
            box-shadow: 0 0 30px rgba(13,125,140,0.5);
        }
        .brand h1 {
            font-family: 'Fredoka One', cursive; font-size: 24px;
            background: linear-gradient(135deg, #0d7d8c, #f2a900);
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
            background: #f6faf9;
            border: 1.5px solid rgba(13,125,140,0.3);
            border-radius: 12px;
            padding: 12px 16px;
            color: var(--text); font-family: 'Nunito', sans-serif; font-size: 15px;
            transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        input:focus {
            border-color: var(--purple);
            box-shadow: 0 0 0 3px rgba(13,125,140,0.2);
        }
        input::placeholder { color: #9aabb5; }
        .hint { font-size: 12px; color: #6b8199; margin-top: 5px; }

        /* Generated-login highlight — the focal point of the form */
        .login-preview {
            position: relative; text-align: center; margin: 6px 0 22px;
            background: linear-gradient(160deg, rgba(13,125,140,0.12), rgba(246,183,30,0.10));
            border: 1.5px solid rgba(13,125,140,0.55); border-radius: 16px;
            padding: 18px 20px 16px; box-shadow: 0 0 34px rgba(13,125,140,0.18);
        }
        .login-preview .lp-label {
            display: inline-block; font-size: 11.5px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #0a6e60; margin-bottom: 8px;
        }
        .login-preview .lp-email {
            font-family: 'Fredoka One', cursive; font-size: clamp(20px, 5.4vw, 27px);
            line-height: 1.1; word-break: break-all;
        }
        .login-preview .lp-email #username-preview { color: #f2a900; }
        .login-preview .lp-email .lp-suffix { color: #0d7d8c; }
        .login-preview .lp-note { font-size: 12px; color: var(--muted); margin-top: 9px; }
        .login-preview .lp-note strong { color: #12222e; }

        .username-row { display: flex; align-items: center; gap: 0; }
        .username-row input { border-radius: 12px 0 0 12px; }
        .username-suffix {
            background: rgba(13,125,140,0.18);
            border: 1.5px solid rgba(13,125,140,0.3); border-left: none;
            border-radius: 0 12px 12px 0;
            padding: 12px 14px; font-size: 13px; color: var(--muted); white-space: nowrap;
        }

        .year-chips { display: flex; flex-wrap: wrap; gap: 10px; }
        .year-chip { position: relative; cursor: pointer; }
        .year-chip input { position: absolute; opacity: 0; pointer-events: none; }
        .year-chip span {
            display: block; min-width: 76px; text-align: center;
            background: #f6faf9; border: 1.5px solid rgba(13,125,140,0.3);
            border-radius: 12px; padding: 12px 16px; color: var(--text);
            font-family: 'Fredoka One', cursive; font-size: 17px; transition: all 0.15s;
        }
        .year-chip:hover span { border-color: rgba(13,125,140,0.6); }
        .year-chip input:checked + span {
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border-color: transparent; color: #fff; box-shadow: 0 0 20px rgba(13,125,140,0.35);
        }
        .year-chip input:focus-visible + span { outline: 2px solid #0d7d8c; outline-offset: 2px; }

        .strands { margin-bottom: 18px; }
        .strand-group { margin-bottom: 14px; }
        .strand-group h3 {
            font-family: 'Fredoka One', cursive; font-size: 14px; color: #12222e;
            margin-bottom: 8px;
        }
        .strand-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .strand-check {
            display: flex; align-items: center; gap: 8px;
            background: #f6faf9;
            border: 1.5px solid rgba(13,125,140,0.2);
            border-radius: 10px; padding: 9px 12px;
            font-size: 13px; color: var(--muted); cursor: pointer;
            transition: all 0.15s;
        }
        .strand-check:hover { border-color: rgba(13,125,140,0.5); }
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
        .creds h2 { font-family: 'Fredoka One', cursive; font-size: 20px; color: #0a6e60; margin-bottom: 8px; }
        .creds .warn { font-size: 13px; color: #fde68a; margin-bottom: 18px; }
        .cred-row {
            display: flex; justify-content: space-between;
            background: #f1f6f5; border-radius: 10px;
            padding: 12px 16px; margin-bottom: 10px; font-size: 15px;
        }
        .cred-row .k { color: var(--muted); }
        .cred-row .v { color: var(--text); font-weight: 700; font-family: monospace; }
        .cred-hero {
            text-align: left; margin-bottom: 14px; padding: 16px 18px;
            background: linear-gradient(160deg, rgba(13,125,140,0.16), rgba(246,183,30,0.10));
            border: 1.5px solid rgba(13,125,140,0.6); border-radius: 14px;
            box-shadow: 0 0 30px rgba(13,125,140,0.2);
        }
        .cred-hero .k {
            display: block; font-size: 11.5px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #0a6e60; margin-bottom: 6px;
        }
        .cred-hero .v {
            font-family: 'Fredoka One', cursive; font-size: clamp(18px, 4.8vw, 25px);
            color: #f2a900; word-break: break-all; line-height: 1.15;
        }
        .creds a {
            display: inline-block; margin-top: 12px;
            color: #0d7d8c; font-weight: 700; text-decoration: none; font-size: 14px;
        }
        .creds a:hover { color: #f2a900; }

        /* Step-by-step child setup — progressive enhancement (mirrors the parent signup). */
        .rw-progress { display: none; }
        .rw-step-nav { display: none; align-items: center; gap: 12px; margin-top: 16px; }
        .rw-back { background: none; border: 0; color: #93b2cc; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 14px; cursor: pointer; padding: 6px 4px; }
        .rw-back:hover { color: #cfe6ea; }
        form.stepper .rw-progress { display: flex; gap: 6px; margin-bottom: 22px; }
        form.stepper .rw-progress i { height: 5px; flex: 1; border-radius: 99px; background: rgba(255,255,255,0.16); transition: background .2s; }
        form.stepper .rw-progress i.on { background: #f2a900; }
        form.stepper .rw-step-nav { display: flex; }
        form.stepper .rw-step:not(.rw-active) { display: none; }
        form.stepper .rw-next { flex: 1; }

        /* Confirmation: "I've saved it" acknowledgement gating the dashboard CTA. */
        .ack { display: flex; gap: 10px; align-items: flex-start; text-align: left; margin: 18px 0 4px; font-size: 13.5px; font-weight: 700; color: #cbd5e1; cursor: pointer; }
        .ack input { width: 18px; height: 18px; margin-top: 2px; flex: none; accent-color: #34d399; }
        a.ack-btn { display: block; margin-top: 12px; text-align: center; opacity: 0.45; pointer-events: none; transition: opacity .2s; }
        a.ack-btn.on { opacity: 1; pointer-events: auto; }
        .creds-secondary { display: inline-block; margin-top: 14px; color: #93b2cc; font-weight: 700; text-decoration: none; font-size: 14px; }
        .creds-secondary:hover { color: #0d7d8c; }
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
            <h2>{{ $c['name'] }}'s login</h2>
            <p class="warn">✅ Save this now. We've also emailed the <strong>login ID</strong> to your inbox for your records — for safety, the password is only ever shown here and on your dashboard.</p>
            <div class="cred-hero"><span class="k">Login ID (email)</span><span class="v">{{ $c['login_id'] }}</span></div>
            <div class="cred-row"><span class="k">Username</span><span class="v">{{ $c['username'] }}</span></div>
            <div class="cred-row"><span class="k">Password</span><span class="v">{{ $c['password'] }}</span></div>
            <p class="warn" style="color:#93b2cc;margin-top:14px;">🔑 You can reveal or reset the password anytime from your dashboard — no need to write it down.</p>

            <label class="ack" for="saved-ack">
                <input type="checkbox" id="saved-ack">
                <span>I've saved {{ $c['name'] }}'s login (or I know it's on my dashboard).</span>
            </label>
            <a href="{{ route('guardian.dashboard') }}" id="to-dashboard" class="btn-submit ack-btn">Go to my dashboard →</a>
            <a href="{{ route('child.setup') }}" class="creds-secondary">Set up another child →</a>
        </div>
    @else
        <div class="brand">
            <div class="brand-icon">👧</div>
            <h1>Add your first child</h1>
            <p>You can add others later from your dashboard.</p>
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

        <form method="POST" action="{{ route('child.store') }}" id="child-form">
            @csrf

            {{-- Progress (shown only when the JS stepper is active). --}}
            <div class="rw-progress" aria-hidden="true"><i></i><i></i><i></i></div>

            {{-- Step 1 — name --}}
            <div class="rw-step" data-step="1">
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

                <div class="rw-step-nav"><button type="button" class="btn-submit rw-next">Next →</button></div>
            </div>

            {{-- Step 2 — SEA year --}}
            <div class="rw-step" data-step="2">
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
                <div class="rw-step-nav"><button type="button" class="rw-back">← Back</button><button type="button" class="btn-submit rw-next">Next →</button></div>
            </div>

            {{-- Step 3 — known weak areas (optional) + submit --}}
            <div class="rw-step" data-step="3">
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

                <div class="rw-step-nav" style="margin-bottom:12px;"><button type="button" class="rw-back">← Back</button></div>
                <button type="submit" class="btn-submit">Create the Account 🌟</button>
            </div>
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

    // Step-by-step stepper (progressive enhancement — without JS every step is visible).
    (function () {
        const form = document.getElementById('child-form');
        if (!form) return;
        const steps = Array.prototype.slice.call(form.querySelectorAll('.rw-step'));
        if (steps.length < 2) return;

        const dots = Array.prototype.slice.call(form.querySelectorAll('.rw-progress i'));
        let cur = 0;

        function show(i) {
            steps.forEach((s, n) => s.classList.toggle('rw-active', n === i));
            dots.forEach((d, n) => d.classList.toggle('on', n <= i));
            cur = i;
            const firstInput = steps[i].querySelector('input:not([type=checkbox]):not([type=radio])');
            if (firstInput) { try { firstInput.focus(); } catch (e) {} }
        }

        function stepValid(i) {
            let ok = true;
            steps[i].querySelectorAll('input').forEach(function (inp) {
                if (ok && !inp.checkValidity()) { inp.reportValidity(); ok = false; }
            });
            return ok;
        }

        form.classList.add('stepper');
        form.querySelectorAll('.rw-next').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (stepValid(cur) && cur < steps.length - 1) show(cur + 1);
            });
        });
        form.querySelectorAll('.rw-back').forEach(function (btn) {
            btn.addEventListener('click', function () { if (cur > 0) show(cur - 1); });
        });
        show(0);
    })();

    // Confirmation: the "Go to my dashboard" CTA unlocks once the parent acknowledges they've saved the login.
    (function () {
        const ack = document.getElementById('saved-ack');
        const cta = document.getElementById('to-dashboard');
        if (!ack || !cta) return;
        ack.addEventListener('change', function () { cta.classList.toggle('on', ack.checked); });
    })();
</script>
</body>
</html>