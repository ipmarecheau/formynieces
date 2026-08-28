<div wire:poll.6s="poll">
    <style>
        .va-brand { text-align:center; margin-bottom:22px; }
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
    </style>

    <div class="va-brand">
        <x-brand.logo />
        <h1 class="va-title" style="margin-top:14px;">Confirm it's you</h1>
        <p class="va-lede">Two quick checks, then you'll set up your child's voyage.</p>
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
        Need help? <a href="{{ route('contact') }}" style="color:var(--ss-cyan); font-weight:800;">Contact us</a>
        and a real person will sort it out.
    </p>

    <form method="POST" action="{{ route('logout') }}" style="text-align:center; margin-top:14px;">
        @csrf
        <button type="submit" class="va-link">Log out</button>
    </form>
</div>
