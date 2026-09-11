<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — SmoothSeas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple: #0d9488;
            --pink:   #b45309;
            --bg:     #fbf8f2;
            --card:   #ffffff;
            --border: #e7ddcd;
            --text:   #0f172a;
            --muted:  #475569;
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
            background: var(--purple);
            border-radius: 18px;
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 30px; margin-bottom: 14px;
            box-shadow: 0 8px 20px rgba(13,125,140,0.28);
        }
        .brand h1 {
            font-family: 'Fredoka One', cursive; font-size: 26px;
            color: var(--purple);
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
        input[type="tel"],
        input[type="text"] {
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
        .field-hint {
            margin-top: 6px; font-size: 12px; color: var(--muted);
            text-transform: none; letter-spacing: 0; font-weight: 500;
        }

        /* 18+ attestation checkbox */
        .attestation { margin-bottom: 18px; }
        .attestation-label {
            display: flex; align-items: flex-start; gap: 10px;
            text-transform: none; letter-spacing: 0; font-weight: 500;
            font-size: 13px; color: var(--muted); cursor: pointer; margin-bottom: 0;
        }
        .attestation-label input[type="checkbox"] {
            width: 18px; height: 18px; margin-top: 1px; flex-shrink: 0;
            accent-color: var(--purple); cursor: pointer;
        }
        .attestation-label a { color: var(--purple); font-weight: 700; text-decoration: underline; }
        .attestation-label a:hover { color: #0f766e; }

        .btn-submit {
            width: 100%;
            background: var(--purple);
            border: none; border-radius: 999px; padding: 14px;
            color: white; font-family: 'Fredoka One', cursive; font-size: 17px;
            cursor: pointer; letter-spacing: 0.03em;
            box-shadow: 0 6px 16px rgba(13,125,140,0.25);
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            margin-top: 6px;
        }
        .btn-submit:hover  { background: #0f766e; box-shadow: 0 8px 20px rgba(13,125,140,0.32); }
        .btn-submit:active { transform: scale(0.98); }

        .foot { text-align: center; margin-top: 24px; font-size: 14px; color: var(--muted); }
        .foot a { color: #0d9488; font-weight: 700; text-decoration: none; }
        .foot a:hover { color: #b45309; }

        .errors {
            background: rgba(239,68,68,0.12);
            border: 1.5px solid rgba(239,68,68,0.35);
            border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;
            font-size: 13px; color: #b91c1c;
        }
        .errors ul { padding-left: 16px; }

        .terms-scroll {
            max-height: 190px; overflow-y: auto; margin-top: 6px;
            background: rgba(6,24,46,0.55); border: 1.5px solid rgba(13,125,140,0.35);
            border-radius: 12px; padding: 14px 16px;
            font-size: 12.5px; line-height: 1.55; color: #475569;
        }
        .terms-scroll h2 { font-size: 13.5px; color: #e6f2fb; margin: 14px 0 4px; font-weight: 800; }
        .terms-scroll h2:first-child { margin-top: 0; }
        .terms-scroll p, .terms-scroll li { color: #a9c6de; margin-bottom: 6px; }
        .terms-scroll ul { margin: 0 0 8px 18px; }
        .terms-scroll strong { color: #e6f2fb; }
        .terms-scroll a { color: #0d9488; }
        .terms-scroll .terms-meta { color: #7fa0bb; font-size: 11px; }
        .terms-scroll.is-read { border-color: rgba(16,185,129,0.55); }
        .terms-scroll-hint { font-size: 11.5px; font-weight: 700; color: #475569; margin-top: 6px; }
        .terms-scroll-hint.is-done { color: #6ee7b7; }

        /* Existing-account notice — shown when the typed email already has an account. */
        .exists-notice {
            display: none;
            background: rgba(13,125,140,0.10);
            border: 1.5px solid rgba(13,125,140,0.45);
            border-radius: 12px; padding: 14px 16px; margin-bottom: 20px;
            font-size: 13.5px; line-height: 1.5; color: #475569;
        }
        .exists-notice.is-shown { display: block; }
        .exists-notice strong { color: #0f172a; }
        .exists-notice a {
            display: inline-block; margin-top: 10px;
            background: var(--purple);
            color: #fff; font-weight: 700; text-decoration: none;
            padding: 9px 18px; border-radius: 999px; font-size: 13.5px;
        }
        .exists-notice a:hover { background: #0f766e; }
        form.is-locked { opacity: 0.45; pointer-events: none; }
    </style>
    @if (config('services.turnstile.site_key'))
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endif
</head>
<body>

<div class="stars" id="stars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="card">
    <div class="brand">
        <div class="brand-icon">🌟</div>
        <h1>SmoothSeas</h1>
        <p>Join your SEA journey today</p>
    </div>

    @include('partials.setup-stepper', ['current' => 1])

    @if ($errors->any())
        <div class="errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="exists-notice" id="exists-notice" role="alert" aria-live="polite">
        <strong>You already have an account.</strong><br>
        An account with this email already exists. Please sign in to your dashboard to continue.
        <br><a href="{{ route('login') }}" id="exists-login-link">Sign in to your dashboard →</a>
    </div>

    <style>
        .reg-lead { text-align:center; font-size:14.5px; color:var(--muted); margin:-8px 0 18px; line-height:1.5; }
        /* One clear consent that gates every path (social + email). */
        .consent { display:flex; gap:11px; align-items:flex-start; text-align:left;
            background:#f6faf9; border:1.5px solid rgba(13,125,140,0.3); border-radius:14px;
            padding:13px 15px; margin-bottom:16px; cursor:pointer; transition:border-color .15s, background .15s; }
        .consent input { width:20px; height:20px; margin-top:1px; flex:none; accent-color:var(--purple); cursor:pointer; }
        .consent span { font-size:13px; line-height:1.5; color:var(--text); font-weight:600; text-transform:none; letter-spacing:normal; }
        .consent a { color:var(--purple); font-weight:700; text-decoration:underline; }
        .consent.needs { border-color:#e11d48; background:#fef2f2; animation:regShake .3s; }
        .consent-hint { display:none; color:#e11d48; font-size:12.5px; font-weight:700; margin:-8px 0 14px; text-align:center; }
        .consent-hint.show { display:block; }
        @keyframes regShake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }
        .or-line { display:flex; align-items:center; gap:12px; margin:18px 0; color:var(--muted); font-size:13px; font-weight:700; }
        .or-line::before, .or-line::after { content:""; flex:1; height:1px; background:currentColor; opacity:.25; }
        .email-toggle { display:block; width:100%; text-align:center; background:none; border:0; cursor:pointer;
            color:var(--purple); font-weight:800; font-size:14.5px; padding:6px; }
        .email-toggle:hover { color:#0f766e; }
        .email-form[hidden] { display:none; }
        .email-form { margin-top:8px; }
    </style>

    <p class="reg-lead">Create your parent account. The quickest way in is an account you already have.</p>

    {{-- One clear consent, gating BOTH the providers and the email form. Kept as two
         hidden fields (age + terms) so the server contract is unchanged. --}}
    <label class="consent" id="consent-label" for="consent">
        <input type="checkbox" id="consent" {{ old('age_attestation') && old('terms') ? 'checked' : '' }}>
        <span>I'm 18 or older and the parent or legal guardian, and I agree to the
            <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms</a>
            &amp; <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</span>
    </label>
    <p class="consent-hint" id="consent-hint">Please confirm the box above to continue.</p>

    {{-- Social-first: continue with an existing account. Gated on the consent above. --}}
    @include('auth.partials.social-buttons', ['consent' => false])

    <div class="or-line">or</div>

    <button type="button" class="email-toggle" id="email-toggle" aria-expanded="false" aria-controls="register-form">
        Sign up with an email address instead
    </button>

    <form method="POST" action="{{ route('register') }}" id="register-form" class="email-form" hidden>
        @csrf
        {{-- The single consent above drives these (kept for the unchanged server contract). --}}
        <input type="hidden" name="age_attestation" id="hid-age" value="{{ old('age_attestation') }}">
        <input type="hidden" name="terms" id="hid-terms" value="{{ old('terms') }}">

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

        @if (config('services.turnstile.site_key'))
            <div class="field">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="dark"></div>
            </div>
        @endif

        <button type="submit" id="submit" class="btn-submit">Create account</button>
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
<script>
    // When the typed email already has an account, stop here and point the
    // guardian at sign-in — they shouldn't fill out the whole form again.
    (function () {
        const emailInput = document.getElementById('email');
        const form = document.getElementById('register-form');
        const notice = document.getElementById('exists-notice');
        const loginLink = document.getElementById('exists-login-link');
        if (!emailInput || !form || !notice) return;

        const checkUrl = @json(route('register.check-email'));
        const loginUrl = @json(route('login'));
        const token = document.querySelector('input[name="_token"]').value;
        let lastChecked = null;

        function lock() {
            notice.classList.add('is-shown');
            form.classList.add('is-locked');
            notice.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function unlock() {
            notice.classList.remove('is-shown');
            form.classList.remove('is-locked');
        }

        async function checkEmail() {
            const email = emailInput.value.trim();
            if (!email || !emailInput.checkValidity() || email === lastChecked) return;
            lastChecked = email;
            try {
                const res = await fetch(checkUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ email }),
                });
                if (!res.ok) { return; }
                const data = await res.json();
                if (data.exists) {
                    loginLink.href = loginUrl + '?email=' + encodeURIComponent(email);
                    lock();
                } else {
                    unlock();
                }
            } catch (e) { /* network error — the server-side unique rule still catches it */ }
        }

        emailInput.addEventListener('blur', checkEmail);
        emailInput.addEventListener('input', function () {
            if (emailInput.value.trim() !== lastChecked) { unlock(); }
        });
    })();
</script>
<script>
    // One consent drives every path. Provider buttons stay clickable but only proceed
    // once consent is given (a gentle nudge otherwise, never a dead greyed button); the
    // email form mirrors consent into its two hidden fields so the server contract holds.
    (function () {
        const consent = document.getElementById('consent');
        const label = document.getElementById('consent-label');
        const hint = document.getElementById('consent-hint');
        const hidAge = document.getElementById('hid-age');
        const hidTerms = document.getElementById('hid-terms');
        const btns = document.querySelectorAll('.soc-btn[data-base]');
        const form = document.getElementById('register-form');
        const toggle = document.getElementById('email-toggle');
        if (!consent) return;

        function sync() {
            const ok = consent.checked;
            if (hidAge) { hidAge.value = ok ? '1' : ''; }
            if (hidTerms) { hidTerms.value = ok ? '1' : ''; }
            btns.forEach(function (b) {
                b.setAttribute('href', ok ? b.dataset.base + '?agree=1' : b.dataset.base);
            });
            if (ok && hint) { hint.classList.remove('show'); label.classList.remove('needs'); }
        }
        function nudge() {
            hint.classList.add('show');
            label.classList.add('needs');
            label.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(function () { label.classList.remove('needs'); }, 500);
        }

        consent.addEventListener('change', sync);
        sync();

        // Providers: block navigation until consent is given.
        btns.forEach(function (b) {
            b.addEventListener('click', function (e) {
                if (!consent.checked) { e.preventDefault(); nudge(); }
            });
        });

        // Reveal the email form on demand (social-first keeps it tucked away).
        if (toggle && form) {
            toggle.addEventListener('click', function () {
                const show = form.hasAttribute('hidden');
                if (show) { form.removeAttribute('hidden'); toggle.setAttribute('aria-expanded', 'true'); toggle.textContent = 'Hide the email form'; document.getElementById('email').focus(); }
                else { form.setAttribute('hidden', ''); toggle.setAttribute('aria-expanded', 'false'); toggle.textContent = 'Sign up with an email address instead'; }
            });
            form.addEventListener('submit', function (e) {
                if (!consent.checked) { e.preventDefault(); nudge(); }
            });
        }
    })();
</script>
</body>
</html>