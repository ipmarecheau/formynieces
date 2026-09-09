<div wire:poll.6s="poll">
    <style>
        .va-brand { text-align:center; margin-bottom:22px; }
        .va-mail { position:relative; display:inline-flex; align-items:center; justify-content:center; width:88px; height:88px; margin-bottom:12px; }
        .va-mail::before { content:""; position:absolute; inset:0; border-radius:50%; background:rgba(13,125,140,0.12); animation:vaPulse 2.4s ease-in-out infinite; }
        .va-mail-icon { position:relative; font-size:52px; line-height:1; animation:vaBob 2.4s ease-in-out infinite; }
        @keyframes vaBob { 0%,100%{ transform:translateY(0) rotate(-3deg); } 50%{ transform:translateY(-8px) rotate(3deg); } }
        @keyframes vaPulse { 0%,100%{ transform:scale(0.82); opacity:0.55; } 50%{ transform:scale(1.12); opacity:0.15; } }
        .va-spam { font-size:12.5px; color:var(--ss-muted); font-weight:600; margin:10px 0 0; }
        @media (prefers-reduced-motion: reduce) { .va-mail-icon, .va-mail::before { animation:none; } }
        .va-title { font-family:var(--ss-font-head); font-size:24px; color:var(--ss-foam); margin:0 0 6px; }
        .va-lede { font-size:14px; color:var(--ss-muted); font-weight:600; margin:0; }
        .va-panel { border:1.5px solid var(--ss-border); border-radius:16px; padding:18px; margin-top:16px; background:rgba(6,24,46,0.4); }
        .va-panel-head { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:6px; }
        .va-panel-name { font-family:var(--ss-font-head); font-size:16px; color:var(--ss-foam); }
        .va-target { font-size:12.5px; color:var(--ss-muted); font-weight:700; margin:0 0 12px; word-break:break-all; }
        .va-check { display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:800; color:#6ee7b7; background:rgba(16,185,129,0.14); border:1.5px solid rgba(16,185,129,0.4); border-radius:999px; padding:4px 12px; }
        .va-pending { font-size:12px; font-weight:800; color:var(--ss-gold); }
        .va-row { display:flex; gap:8px; flex-wrap:wrap; align-items:flex-start; }
        .va-code { flex:1 1 130px; letter-spacing:0.3em; text-align:center; font-weight:800; }
        .va-actions { display:flex; gap:14px; flex-wrap:wrap; margin-top:10px; }
        .va-link { background:none; border:0; padding:0; cursor:pointer; font-family:var(--ss-font-body); font-size:12.5px; font-weight:800; color:var(--ss-cyan); }
        .va-link:hover { color:var(--ss-aqua); }
        .va-err { color:#fca5a5; font-size:12px; font-weight:700; margin-top:6px; }
        .va-note { font-size:12px; color:var(--ss-muted); margin-top:10px; }
        .va-status { background:rgba(16,185,129,0.14); border:1.5px solid rgba(16,185,129,0.35); color:#6ee7b7; border-radius:10px; padding:9px 12px; font-size:12.5px; font-weight:700; margin-bottom:6px; }
        .va-progress { text-align:center; font-size:13px; font-weight:800; color:var(--ss-muted); margin-top:18px; }
        /* Parent verify screen — light landing palette (dark sea theme is for students). */
        .ss-sea { display:none !important; }
        body.ss-body { background:#fbf8f2; color:#0f172a; }
        .ss-card { background:#fff; border-color:#e7ddcd; box-shadow:0 18px 40px -22px rgba(18,34,46,.26); }
        .ss-input { background:#f6faf9; border-color:rgba(13,125,140,.35); color:#0f172a; }
        .ss-input::placeholder { color:#9aabb5; }
        .ss-input:focus { border-color:#0d9488; box-shadow:0 0 0 3px rgba(13,125,140,.2); }
        .va-title, .va-panel-name { color:#0f172a; }
        .va-lede, .va-target, .va-note, .va-progress { color:#475569; }
        .va-panel { background:#fbfdfc; border-color:#e7ddcd; }
        .va-link { color:#0d9488; }
        .va-link:hover { color:#b45309; }
        .va-err { color:#9a2b1e; }
        /* Status colours — readable green / amber on the light card. */
        .va-check, .va-status { color:#15803d; background:rgba(21,128,61,0.10); border-color:rgba(21,128,61,0.35); }
        .va-pending { color:#a05a00; }
    </style>

    <div class="va-brand">
        <div class="va-mail" aria-hidden="true"><span class="va-mail-icon">📬</span></div>
        <h1 class="va-title">Check your email 📬</h1>
        <p class="va-lede">We just sent you a link — tap it to confirm, then set up your child.</p>
        <p class="va-spam">Don't see it? Have a peek in your <strong>spam</strong> or <strong>junk</strong> folder.</p>
    </div>

    @if ($status === 'email-sent')
        <div class="va-status">A fresh email is on its way.</div>
    @elseif ($status === 'phase-whatsapp-sent' || $status === 'phone-whatsapp-sent')
        <div class="va-status">Sent a new code on WhatsApp.</div>
    @elseif ($status === 'phone-sms-sent')
        <div class="va-status">Sent the code by SMS instead.</div>
    @endif

    {{-- ===== Email ===== --}}
    <div class="va-panel">
        <div class="va-panel-head">
            <span class="va-panel-name">1 · Email</span>
            @if ($user->hasVerifiedEmail())
                <span class="va-check">✓ Verified</span>
            @else
                <span class="va-pending">Waiting</span>
            @endif
        </div>
        <p class="va-target">{{ $user->email }}</p>

        @unless ($user->hasVerifiedEmail())
            <p class="va-note" style="margin-top:0;">Tap the link in the email, <strong>or</strong> enter the 6-digit code:</p>
            <form wire:submit="submitEmailCode" class="va-row" style="margin-top:10px;">
                <input type="text" inputmode="numeric" maxlength="6" class="ss-input va-code"
                       wire:model="emailCode" placeholder="••••••" autocomplete="one-time-code">
                <button type="submit" class="ss-btn-accent" style="padding:12px 18px;">Verify</button>
            </form>
            @error('emailCode') <p class="va-err">{{ $message }}</p> @enderror
            <div class="va-actions">
                <button type="button" class="va-link" wire:click="resendEmail">Resend email</button>
            </div>
        @endunless
    </div>

    {{-- ===== Phone ===== --}}
    @if ($phoneRequired)
    <div class="va-panel">
        <div class="va-panel-head">
            <span class="va-panel-name">2 · Phone</span>
            @if ($user->hasVerifiedPhone())
                <span class="va-check">✓ Verified</span>
            @else
                <span class="va-pending">Waiting</span>
            @endif
        </div>
        <p class="va-target">{{ $user->phone }}</p>

        @unless ($user->hasVerifiedPhone())
            <p class="va-note" style="margin-top:0;">We sent a code on WhatsApp. Enter it here:</p>
            <form wire:submit="submitPhoneCode" class="va-row" style="margin-top:10px;">
                <input type="text" inputmode="numeric" maxlength="6" class="ss-input va-code"
                       wire:model="phoneCode" placeholder="••••••" autocomplete="one-time-code">
                <button type="submit" class="ss-btn-accent" style="padding:12px 18px;">Verify</button>
            </form>
            @error('phoneCode') <p class="va-err">{{ $message }}</p> @enderror
            <div class="va-actions">
                <button type="button" class="va-link" wire:click="resendPhone('whatsapp')">Resend on WhatsApp</button>
                <button type="button" class="va-link" wire:click="resendPhone('sms')">Didn't get it? Send by SMS</button>
            </div>
        @endunless
    </div>
    @endif

    <p class="va-progress">
        @if ($user->isFullyVerified())
            All set — taking you to onboarding…
        @elseif ($phoneRequired)
            Verify both to continue and set up your child.
        @else
            Verify your email to continue and set up your child.
        @endif
    </p>

    <p class="va-note" style="text-align:center;">
        Need help? <a href="{{ route('contact') }}" style="color:#0d9488; font-weight:800;">Contact us</a>
        and a real person will sort it out.
    </p>

    <form method="POST" action="{{ route('logout') }}" style="text-align:center; margin-top:14px;">
        @csrf
        <button type="submit" class="va-link">Log out</button>
    </form>
</div>
