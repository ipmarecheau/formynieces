<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Children's Logins — SmoothSeas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        :root{--purple:#0e7490;--pink:#f6b71e;--bg:#06182e;--card:#0c2440;--border:rgba(34,211,238,.35);--text:#e6f2fb;--muted:#93b2cc}
        body{min-height:100vh;background:var(--bg);font-family:'Nunito',sans-serif;color:var(--text);padding:32px 16px}
        .wrap{max-width:620px;margin:0 auto}
        .head{text-align:center;margin-bottom:24px}
        .head h1{font-family:'Fredoka One',cursive;font-size:26px;background:linear-gradient(135deg,#67e8f9,#fcd34d);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .head p{color:var(--muted);font-size:14px;margin-top:6px}
        .child{background:var(--card);border:1.5px solid var(--border);border-radius:18px;padding:22px;margin-bottom:16px}
        .child h2{font-family:'Fredoka One',cursive;font-size:18px;margin-bottom:14px}
        .k{display:block;font-size:11px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#86efac;margin-bottom:5px}
        .email{font-family:'Fredoka One',cursive;font-size:clamp(17px,4.6vw,22px);color:#fcd34d;word-break:break-all;line-height:1.15;margin-bottom:16px}
        .pw{background:linear-gradient(160deg,rgba(34,211,238,.16),rgba(246,183,30,.10));border:1.5px solid rgba(34,211,238,.6);border-radius:12px;padding:14px 16px;margin-bottom:14px}
        .pw .v{font-family:monospace;font-size:20px;font-weight:700;color:#fff}
        .actions{display:flex;gap:10px;flex-wrap:wrap}
        button{font-family:'Nunito',sans-serif;font-weight:700;font-size:14px;border:0;border-radius:999px;padding:10px 18px;cursor:pointer}
        .btn-reveal{background:rgba(255,255,255,.08);color:var(--text);border:1.5px solid var(--border)}
        .btn-reveal:hover{border-color:#67e8f9}
        .btn-reset{background:linear-gradient(135deg,var(--purple),var(--pink));color:#fff}
        .btn-reset:hover{opacity:.9}
        .note{font-size:12px;color:var(--muted);margin-top:12px;line-height:1.5}
        .done{background:rgba(34,197,94,.12);border:1.5px solid rgba(34,197,94,.4);color:#86efac;border-radius:10px;padding:10px 14px;font-size:13px;margin-bottom:16px;text-align:center}
        .empty{text-align:center;color:var(--muted);padding:24px}
        .foot{text-align:center;margin-top:8px}
        .foot a{color:#67e8f9;font-weight:700;text-decoration:none;font-size:14px}
        .foot a:hover{color:#fcd34d}
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <h1>Your Children's Logins</h1>
        <p>Reveal or reset a password anytime — for safety, do it if a device is lost or shared.</p>
    </div>

    @if (session('reset_done'))
        <div class="done">🔒 New password set for {{ session('reset_done') }} — write it down below.</div>
    @endif

    @php($revealed = session('revealed'))

    @forelse ($children as $child)
        <div class="child">
            <h2>{{ $child->name }}</h2>
            <span class="k">Login ID (email)</span>
            <div class="email">{{ $child->email }}</div>

            @if ($revealed && $revealed['id'] === $child->id)
                <div class="pw">
                    <span class="k">Password</span>
                    <span class="v">{{ $revealed['password'] ?? '— set a new one below —' }}</span>
                </div>
            @endif

            <div class="actions">
                <form method="POST" action="{{ route('guardian.children.reveal', $child) }}">
                    @csrf
                    <button type="submit" class="btn-reveal">👁 Reveal password</button>
                </form>
                <form method="POST" action="{{ route('guardian.children.reset', $child) }}"
                      onsubmit="return confirm('Reset {{ $child->name }}\'s password? Their current one will stop working.')">
                    @csrf
                    <button type="submit" class="btn-reset">🔄 Reset password</button>
                </form>
            </div>
            <p class="note">We don't rotate passwords on a schedule (that weakens them); reset here only when you need to.</p>
        </div>
    @empty
        <div class="child"><p class="empty">No children yet. <a href="{{ route('child.setup') }}" style="color:#67e8f9">Add a child →</a></p></div>
    @endforelse

    <div class="foot">
        <a href="{{ route('child.setup') }}">＋ Add another child</a>&nbsp;&nbsp;·&nbsp;&nbsp;
        <a href="{{ route('dashboard') }}">Back to dashboard</a>
    </div>
</div>
</body>
</html>
