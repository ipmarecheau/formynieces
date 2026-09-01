<div>
    <style>
        .ac-head { margin-bottom: 22px; }
        .ac-h1 { font-family:'Fredoka','Nunito',sans-serif; font-weight:600; font-size:30px; color:var(--ink); margin:0 0 3px; }
        .ac-sub { font-size:14px; color:var(--ink-faint); font-weight:700; margin:0; }

        .ac-card { background:var(--paper-2); border:1px solid var(--line); border-radius:var(--radius); padding:20px 22px; box-shadow:var(--shadow-sm); margin-bottom:16px; }
        .ac-card h2 { font-family:'Fredoka','Nunito',sans-serif; font-weight:600; font-size:19px; color:var(--ink); margin:0 0 4px; }
        .ac-card .ac-note { font-size:13px; color:var(--ink-faint); margin:0 0 16px; }

        .ac-field { margin-bottom:14px; }
        .ac-field label { display:block; font-size:12px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; color:var(--ink-faint); margin-bottom:6px; }
        .ac-field input { width:100%; font-family:'Nunito',sans-serif; font-size:15px; color:var(--ink); background:var(--paper); border:1px solid var(--line); border-radius:11px; padding:11px 14px; }
        .ac-field input:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px var(--teal-tint); }
        .ac-err { color:var(--warn,#c0392b); font-size:12.5px; font-weight:700; margin:6px 0 0; }

        .ac-btn { font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; cursor:pointer; color:#5a3d00; background:var(--amber); border:none; border-radius:11px; padding:10px 18px; }
        .ac-btn:hover { filter:brightness(1.05); }
        .ac-btn-danger { color:#fff; background:var(--warn,#c0392b); }

        .ac-flash { background:var(--good-tint,#e6f6ee); border:1px solid var(--good,#2e9e6b); color:var(--good,#1f7a51); border-radius:11px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size:14px; }

        .ac-rows { display:flex; flex-direction:column; gap:2px; }
        .ac-row { display:flex; justify-content:space-between; gap:16px; padding:11px 0; border-bottom:1px solid var(--line); font-size:14px; }
        .ac-row:last-child { border-bottom:0; }
        .ac-row .k { color:var(--ink-faint); font-weight:700; }
        .ac-row .v { color:var(--ink); font-weight:800; text-align:right; }

        .ac-badge { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:800; }
        .ac-badge.free { background:var(--teal-tint); color:var(--teal-deep); }
        .ac-badge.paid { background:var(--good-tint,#e6f6ee); color:var(--good,#1f7a51); }
        .ac-badge.due  { background:var(--amber-tint,#fdf0d5); color:#8a5a00; }

        .ac-table { width:100%; border-collapse:collapse; font-size:13.5px; }
        .ac-table th { text-align:left; font-size:11px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:var(--ink-faint); padding:8px 10px; border-bottom:1px solid var(--line); }
        .ac-table td { padding:10px; border-bottom:1px solid var(--line); color:var(--ink); }
        .ac-table tr:last-child td { border-bottom:0; }
        .ac-empty { font-size:14px; color:var(--ink-faint); font-style:italic; padding:8px 0; }

        .ac-danger { border-color:#f0c9c2; }
        .ac-danger h2 { color:var(--warn,#c0392b); }
    </style>

    <div class="ac-head">
        <h1 class="ac-h1">Account</h1>
        <p class="ac-sub">Your profile, sign-in, and billing.</p>
    </div>

    @if ($flash)
        <div class="ac-flash" role="status">{{ $flash }}</div>
    @endif

    {{-- Profile --}}
    <div class="ac-card">
        <h2>Profile</h2>
        <p class="ac-note">Changing your email will ask you to confirm the new address.</p>
        <form wire:submit="updateProfile">
            <div class="ac-field">
                <label for="ac-name">Your name (parent / guardian)</label>
                <input id="ac-name" type="text" wire:model="name">
                @error('name') <p class="ac-err">{{ $message }}</p> @enderror
            </div>
            <div class="ac-field">
                <label for="ac-email">Email address</label>
                <input id="ac-email" type="email" wire:model="email">
                @error('email') <p class="ac-err">{{ $message }}</p> @enderror
                @unless ($user->hasVerifiedEmail())
                    <p class="ac-err" style="color:var(--ink-faint);">This email is not verified yet.</p>
                @endunless
            </div>
            <div class="ac-field">
                <label for="ac-phone">Mobile number (WhatsApp)</label>
                <input id="ac-phone" type="tel" wire:model="phone" placeholder="+18685551234">
                @error('phone') <p class="ac-err">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="ac-btn">Save profile</button>
        </form>
    </div>

    {{-- Password --}}
    <div class="ac-card">
        <h2>Password</h2>
        <p class="ac-note">Use a strong password you don't reuse elsewhere.</p>
        <form wire:submit="updatePassword">
            <div class="ac-field">
                <label for="ac-current">Current password</label>
                <input id="ac-current" type="password" wire:model="current_password" autocomplete="current-password">
                @error('current_password') <p class="ac-err">{{ $message }}</p> @enderror
            </div>
            <div class="ac-field">
                <label for="ac-new">New password</label>
                <input id="ac-new" type="password" wire:model="password" autocomplete="new-password">
                @error('password') <p class="ac-err">{{ $message }}</p> @enderror
            </div>
            <div class="ac-field">
                <label for="ac-confirm">Confirm new password</label>
                <input id="ac-confirm" type="password" wire:model="password_confirmation" autocomplete="new-password">
            </div>
            <button type="submit" class="ac-btn">Update password</button>
        </form>
    </div>

    {{-- Billing --}}
    <div class="ac-card">
        <h2>Billing</h2>
        <p class="ac-note">SmoothSeas is free during launch — you won't be charged until the first bill date below.</p>
        <div class="ac-rows">
            <div class="ac-row">
                <span class="k">Plan</span>
                <span class="v"><span class="ac-badge free">{{ ucfirst($user->plan ?? 'free') }}</span></span>
            </div>
            <div class="ac-row">
                <span class="k">Status</span>
                <span class="v">Active — no charges yet</span>
            </div>
            <div class="ac-row">
                <span class="k">First bill date</span>
                <span class="v">{{ $user->first_bill_at?->format('j M Y') ?? '—' }}</span>
            </div>
            <div class="ac-row">
                <span class="k">Payment method</span>
                <span class="v">None on file</span>
            </div>
        </div>
    </div>

    {{-- Invoices --}}
    <div class="ac-card">
        <h2>Billing history</h2>
        @if ($invoices->isEmpty())
            <p class="ac-empty">No invoices yet — SmoothSeas is free during launch.</p>
        @else
            <table class="ac-table">
                <thead>
                    <tr><th>Invoice</th><th>Date</th><th>Period</th><th>Amount</th><th>Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $invoice->issued_at?->format('j M Y') }}</td>
                            <td>
                                @if ($invoice->period_start && $invoice->period_end)
                                    {{ $invoice->period_start->format('j M') }} – {{ $invoice->period_end->format('j M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $invoice->formattedAmount() }}</td>
                            <td><span class="ac-badge {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Danger zone --}}
    <div class="ac-card ac-danger">
        <h2>Delete account</h2>
        <p class="ac-note">This permanently deletes your account and every linked child. This cannot be undone.</p>
        <form wire:submit="deleteAccount">
            <div class="ac-field">
                <label for="ac-del">Confirm your password</label>
                <input id="ac-del" type="password" wire:model="delete_password" autocomplete="current-password">
                @error('delete_password') <p class="ac-err">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="ac-btn ac-btn-danger"
                    wire:confirm="Permanently delete your account and all linked children? This cannot be undone.">
                Delete my account
            </button>
        </form>
    </div>
</div>
