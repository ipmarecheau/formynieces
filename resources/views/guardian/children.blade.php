<x-layouts::guardian>
    <style>
        .cl-head { margin-bottom: 22px; }
        .cl-h1 { font-family:'Fredoka','Nunito',sans-serif; font-weight:600; font-size:30px; color:var(--ink); margin:0 0 3px; }
        .cl-sub { font-size:14px; color:var(--ink-faint); font-weight:700; margin:0; }

        .cl-done { background:var(--good-tint,#e4f4ec); border:1px solid var(--good,#1a8f5f); color:var(--good,#1a8f5f); border-radius:11px; padding:12px 16px; margin-bottom:16px; font-weight:700; font-size:14px; }

        .cl-card { background:var(--paper-2); border:1px solid var(--line); border-radius:var(--radius); padding:20px 22px; box-shadow:var(--shadow-sm); margin-bottom:16px; }
        .cl-card h2 { font-family:'Fredoka','Nunito',sans-serif; font-weight:600; font-size:19px; color:var(--ink); margin:0 0 14px; }
        .cl-k { display:block; font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-faint); margin-bottom:5px; }
        .cl-email { font-weight:800; color:var(--ink); font-size:16px; word-break:break-all; margin-bottom:14px; }

        .cl-pw { background:var(--teal-tint); border:1px solid var(--teal); border-radius:12px; padding:12px 16px; margin-bottom:14px; }
        .cl-pw .v { font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:19px; font-weight:800; color:var(--teal-deep); }

        .cl-actions { display:flex; gap:10px; flex-wrap:wrap; }
        .cl-btn { font-family:'Nunito',sans-serif; font-weight:800; font-size:13.5px; border:0; border-radius:11px; padding:9px 16px; cursor:pointer; }
        .cl-btn-reveal { background:var(--paper-2); color:var(--teal-deep); border:1px solid var(--line); }
        .cl-btn-reveal:hover { border-color:var(--teal); color:var(--teal); }
        .cl-btn-reset { background:var(--amber); color:#5a3d00; }
        .cl-btn-reset:hover { filter:brightness(1.05); }
        .cl-note { font-size:12px; color:var(--ink-faint); margin-top:12px; line-height:1.5; }
        .cl-empty { font-size:14px; color:var(--ink-faint); font-style:italic; }
        .cl-empty a, .cl-foot a { color:var(--teal); font-weight:800; text-decoration:none; }
        .cl-foot { margin-top:6px; font-size:14px; }
    </style>

    <div class="cl-head">
        <h1 class="cl-h1">Children's logins</h1>
        <p class="cl-sub">Reveal or reset a password anytime — for safety, do it if a device is lost or shared.</p>
    </div>

    @if (session('reset_done'))
        <div class="cl-done">🔒 New password set for {{ session('reset_done') }} — write it down below.</div>
    @endif

    @php($revealed = session('revealed'))

    @forelse ($children as $child)
        <div class="cl-card">
            <h2>{{ $child->name }}</h2>
            <span class="cl-k">Login ID (email)</span>
            <div class="cl-email">{{ $child->email }}</div>

            @if ($revealed && $revealed['id'] === $child->id)
                <div class="cl-pw">
                    <span class="cl-k">Password</span>
                    @if ($revealed['password'])
                        <span class="v">{{ $revealed['password'] }}</span>
                    @else
                        <span class="v" style="font-size:14px;">No saved password yet — tap <strong>Reset password</strong> to generate one you can reveal.</span>
                    @endif
                </div>
            @endif

            <div class="cl-actions">
                <form method="POST" action="{{ route('guardian.children.reveal', $child) }}">
                    @csrf
                    <button type="submit" class="cl-btn cl-btn-reveal">👁 Reveal password</button>
                </form>
                <form method="POST" action="{{ route('guardian.children.reset', $child) }}"
                      onsubmit="return confirm('Reset {{ $child->name }}\'s password? Their current one will stop working.')">
                    @csrf
                    <button type="submit" class="cl-btn cl-btn-reset">🔄 Reset password</button>
                </form>
            </div>
            <p class="cl-note">We don't rotate passwords on a schedule (that weakens them); reset here only when you need to.</p>
        </div>
    @empty
        <div class="cl-card"><p class="cl-empty">No children yet. <a href="{{ route('child.setup') }}">Add a child →</a></p></div>
    @endforelse

    <p class="cl-foot">
        <a href="{{ route('child.setup') }}">＋ Add another child</a>
        &nbsp;·&nbsp;
        <a href="{{ route('guardian.dashboard') }}">Back to dashboard</a>
    </p>
</x-layouts::guardian>
