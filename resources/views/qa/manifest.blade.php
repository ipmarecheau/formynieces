<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QA Manifest · SmoothSeas</title>
    <style>
        :root{--ink:#0c2a2e;--soft:#40595c;--faint:#6b8386;--line:#d5e0e0;--teal:#0e7c86;--sand:#c9862b;--bg:#eef3f3;--card:#fff}
        *{box-sizing:border-box}
        body{margin:0;background:var(--bg);color:var(--ink);font:16px/1.5 system-ui,sans-serif}
        .wrap{max-width:900px;margin:0 auto;padding:32px 20px 80px}
        h1{font-size:28px;margin:0 0 6px}
        .intent{color:var(--soft);max-width:65ch}
        .box{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px 20px;margin:18px 0}
        .box h2{font-size:15px;text-transform:uppercase;letter-spacing:.06em;color:var(--teal);margin:0 0 10px}
        ol,ul{margin:0;padding-left:20px}li{margin:4px 0;color:var(--soft)}
        .feat{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:14px 18px;margin:12px 0}
        .feat h3{margin:0 0 2px;font-size:17px}
        .tag{font:600 11px system-ui;text-transform:uppercase;letter-spacing:.05em;padding:2px 8px;border-radius:999px;margin-left:8px;vertical-align:middle}
        .mvp{background:#dceee7;color:#2f8f6b}.road{background:#f3e6cd;color:#8a6100}
        .feat .desc{color:var(--faint);font-size:13.5px;margin:2px 0 8px}
        .story{font-size:13.5px;color:var(--soft);padding:3px 0;border-top:1px solid var(--line)}
        .sid{font-family:ui-monospace,monospace;color:var(--teal);font-weight:600;margin-right:8px}
        code{font-family:ui-monospace,monospace;background:#e7f5f4;padding:1px 5px;border-radius:5px;font-size:13px}
    </style>
</head>
<body>
<div class="wrap">
    <h1>SmoothSeas — QA Manifest</h1>
    <p class="intent">{{ $payload['intent'] }}</p>

    <div class="box">
        <h2>How to test</h2>
        <ol>@foreach($payload['how_to_test'] as $step)<li>{{ $step }}</li>@endforeach</ol>
        <p style="color:var(--soft);font-size:13.5px;margin:12px 0 0">
            Report each finding: <code>POST /qa/report</code> with your token (header <code>X-QA-Token</code> or <code>?token=</code>)
            and JSON <code>{ scenario, outcome, summary, detail }</code>. Outcomes:
            <code>pass</code> · <code>fail</code> · <code>blocked</code> · <code>gap</code> · <code>note</code>.
            See the live feed at <code>/qa/reports</code>.
        </p>
    </div>

    <p style="color:var(--faint);font-size:13px">{{ $payload['feature_count'] }} features · generated {{ $payload['generated_at'] }} · actors: {{ implode(', ', $payload['actors']) }}</p>

    @foreach($payload['features'] as $f)
        <div class="feat">
            <h3>{{ $f['name'] }}<span class="tag {{ $f['mvp'] ? 'mvp' : 'road' }}">{{ $f['mvp'] ? 'MVP' : 'roadmap' }}</span></h3>
            @if($f['intent'])<p class="desc">{{ \Illuminate\Support\Str::limit($f['intent'], 260) }}</p>@endif
            @foreach($f['stories'] as $s)
                <div class="story">@if($s['id'])<span class="sid">{{ $s['id'] }}</span>@endif{{ $s['title'] }}</div>
            @endforeach
        </div>
    @endforeach
</div>
</body>
</html>
