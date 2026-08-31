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
            box-shadow: 0 0 30px rgba(34,211,238,0.5);
        }
        .brand h1 {
            font-family: 'Fredoka One', cursive; font-size: 26px;
            background: linear-gradient(135deg, #67e8f9, #fcd34d);
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
        .foot a { color: #67e8f9; font-weight: 700; text-decoration: none; }
        .foot a:hover { color: #fcd34d; }

        .errors {
            background: rgba(239,68,68,0.12);
            border: 1.5px solid rgba(239,68,68,0.35);
            border-radius: 12px; padding: 12px 16px; margin-bottom: 20px;
            font-size: 13px; color: #fca5a5;
        }
        .errors ul { padding-left: 16px; }

        .terms-scroll {
            max-height: 190px; overflow-y: auto; margin-top: 6px;
            background: rgba(6,24,46,0.55); border: 1.5px solid rgba(34,211,238,0.35);
            border-radius: 12px; padding: 14px 16px;
            font-size: 12.5px; line-height: 1.55; color: #cde3f5;
        }
        .terms-scroll h2 { font-size: 13.5px; color: #e6f2fb; margin: 14px 0 4px; font-weight: 800; }
        .terms-scroll h2:first-child { margin-top: 0; }
        .terms-scroll p, .terms-scroll li { color: #a9c6de; margin-bottom: 6px; }
        .terms-scroll ul { margin: 0 0 8px 18px; }
        .terms-scroll strong { color: #e6f2fb; }
        .terms-scroll a { color: #67e8f9; }
        .terms-scroll .terms-meta { color: #7fa0bb; font-size: 11px; }
        .terms-scroll.is-read { border-color: rgba(16,185,129,0.55); }
        .terms-scroll-hint { font-size: 11.5px; font-weight: 700; color: #93b2cc; margin-top: 6px; }
        .terms-scroll-hint.is-done { color: #6ee7b7; }

        /* Existing-account notice — shown when the typed email already has an account. */
        .exists-notice {
            display: none;
            background: rgba(34,211,238,0.10);
            border: 1.5px solid rgba(34,211,238,0.45);
            border-radius: 12px; padding: 14px 16px; margin-bottom: 20px;
            font-size: 13.5px; line-height: 1.5; color: #cde3f5;
        }
        .exists-notice.is-shown { display: block; }
        .exists-notice strong { color: #e6f2fb; }
        .exists-notice a {
            display: inline-block; margin-top: 10px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            color: #fff; font-weight: 700; text-decoration: none;
            padding: 9px 18px; border-radius: 999px; font-size: 13.5px;
        }
        .exists-notice a:hover { opacity: 0.9; }
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

    <form method="POST" action="{{ route('register') }}" id="register-form">
        @csrf

        <div class="field">
            <label for="name">Your Name (Parent / Guardian)</label>
            <input type="text" id="name" name="name"
                   value="{{ old('name') }}"
                   placeholder="e.g. Maria Thomas"
                   required autofocus autocomplete="name">
            <p class="field-hint">This is your account. You'll add your child in the next step.</p>
        </div>

        <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email"
                   value="{{ old('email') }}"
                   placeholder="you@example.com"
                   required autocomplete="username">
        </div>

        <div class="field">
            <label for="phone">Mobile Number (WhatsApp)</label>
            <input type="tel" id="phone" name="phone"
                   value="{{ old('phone') }}"
                   placeholder="+1 868 555 1234"
                   required autocomplete="tel">
            <p class="field-hint">Full international format, e.g. +18685551234. We'll send a code by WhatsApp (or SMS).</p>
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

        <div class="field attestation">
            <label class="attestation-label" for="age_attestation">
                <input type="checkbox" id="age_attestation" name="age_attestation" value="1"
                       {{ old('age_attestation') ? 'checked' : '' }}>
                <span>I confirm that I am 18 years of age or older and am the parent or legal guardian setting up this account.</span>
            </label>
        </div>

        <div class="field">
            <label>Terms &amp; Conditions</label>
            <div class="terms-scroll" id="terms-scroll" tabindex="0">
                @include('legal._terms-body')
            </div>
            <p class="terms-scroll-hint" id="terms-scroll-hint">Please scroll to the end of the terms to continue.</p>
            <label class="attestation-label" for="terms" style="margin-top:10px;">
                <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }}>
                <span>I have read and agree to the
                    <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a>
                    and <a href="{{ route('privacy') }}" target="_blank" rel="noopener">Privacy Policy</a>.</span>
            </label>
        </div>

        @if (config('services.turnstile.site_key'))
            <div class="field">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}" data-theme="dark"></div>
            </div>
        @endif

        <button type="submit" id="submit" class="btn-submit">Create Account 🌟</button>
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
    // Progressive enhancement: require the guardian to scroll through the terms
    // before the acceptance box can be ticked. With JS off, the box stays usable
    // and the server still requires acceptance.
    (function () {
        const box = document.getElementById('terms-scroll');
        const check = document.getElementById('terms');
        const hint = document.getElementById('terms-scroll-hint');
        if (!box || !check || !hint) return;

        function markRead() {
            check.disabled = false;
            box.classList.add('is-read');
            hint.textContent = 'Thanks for reading. You can now accept below.';
            hint.classList.add('is-done');
        }

        // Only gate when the terms actually overflow (otherwise it's all visible).
        if (box.scrollHeight - box.clientHeight > 8 && !check.checked) {
            check.disabled = true;
            const onScroll = function () {
                if (box.scrollTop + box.clientHeight >= box.scrollHeight - 12) {
                    markRead();
                    box.removeEventListener('scroll', onScroll);
                }
            };
            box.addEventListener('scroll', onScroll);
        }
    })();
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
</body>
</html>