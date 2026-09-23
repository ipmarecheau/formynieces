<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QA Reports · SmoothSeas</title>
    <style>
        :root{--ink:#0c2a2e;--soft:#40595c;--faint:#6b8386;--line:#d5e0e0;--teal:#0e7c86;--bg:#eef3f3;--card:#fff;
            --pass:#2f8f6b;--fail:#cc5240;--blocked:#c9862b;--gap:#8a5cd0;--note:#6b8386}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.5 system-ui,sans-serif}
        .wrap{max-width:960px;margin:0 auto;padding:32px 20px 80px}
        h1{font-size:26px;margin:0 0 14px}
        .counts{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:20px}
        .pill{font:600 13px system-ui;padding:6px 12px;border-radius:999px;background:var(--card);border:1px solid var(--line)}
        .pill .n{font-variant-numeric:tabular-nums}
        .r{background:var(--card);border:1px solid var(--line);border-left-width:4px;border-radius:10px;padding:12px 16px;margin:10px 0}
        .r.pass{border-left-color:var(--pass)}.r.fail{border-left-color:var(--fail)}
        .r.blocked{border-left-color:var(--blocked)}.r.gap{border-left-color:var(--gap)}.r.note{border-left-color:var(--note)}
        .r .top{display:flex;gap:10px;align-items:baseline;flex-wrap:wrap}
        .oc{font:700 11px system-ui;text-transform:uppercase;letter-spacing:.05em;padding:2px 8px;border-radius:6px;color:#fff}
        .oc.pass{background:var(--pass)}.oc.fail{background:var(--fail)}.oc.blocked{background:var(--blocked)}.oc.gap{background:var(--gap)}.oc.note{background:var(--note)}
        .sid{font-family:ui-monospace,monospace;color:var(--teal);font-weight:600}
        .when{color:var(--faint);font-size:12.5px;margin-left:auto;font-family:ui-monospace,monospace}
        .sum{margin:6px 0 0;font-weight:600}
        .detail{margin:6px 0 0;color:var(--soft);font-size:14px;white-space:pre-wrap}
        .actor{color:var(--faint);font-size:12.5px;margin-top:6px}
        .empty{color:var(--faint);text-align:center;padding:40px}
        a{color:var(--teal)}
    </style>
</head>
<body>
<div class="wrap">
    <h1>SmoothSeas — QA Report Feed</h1>
    <div class="counts">
        @foreach(['pass','fail','blocked','gap','note'] as $o)
            <span class="pill"><span class="oc {{ $o }}" style="padding:1px 6px">{{ $o }}</span> <span class="n">{{ $counts[$o] ?? 0 }}</span></span>
        @endforeach
        <span class="pill">total <span class="n">{{ $counts->sum() }}</span></span>
    </div>

    @forelse($reports as $r)
        <div class="r {{ $r->outcome }}">
            <div class="top">
                <span class="oc {{ $r->outcome }}">{{ $r->outcome }}</span>
                @if($r->scenario)<span class="sid">{{ $r->scenario }}</span>@endif
                <span class="when">{{ $r->created_at->diffForHumans() }} · {{ $r->created_at->format('d M H:i') }}</span>
            </div>
            <p class="sum">{{ $r->summary }}</p>
            @if($r->detail)<p class="detail">{{ $r->detail }}</p>@endif
            @if($r->screenshot_url)<p class="detail"><a href="{{ $r->screenshot_url }}">screenshot ↗</a></p>@endif
            @if($r->actor)<p class="actor">— {{ $r->actor }}</p>@endif
        </div>
    @empty
        <p class="empty">No reports yet. The agent posts findings to <code>/qa/report</code>.</p>
    @endforelse
</div>
</body>
</html>
