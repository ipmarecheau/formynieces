<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Terms &amp; Conditions — SmoothSeas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#12222e; --ink-soft:#40566a; --ink-faint:#6b8199; --paper:#fbf8f2; --paper-2:#fff; --line:#e7ddcd; --teal:#0d7d8c; --teal-deep:#0a5c68; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--paper); color:var(--ink); font-family:'Nunito',system-ui,sans-serif; line-height:1.6; }
        .wrap { max-width:760px; margin:0 auto; padding:40px 22px 80px; }
        .top { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .brand-mark { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--teal),var(--teal-deep)); display:flex; align-items:center; justify-content:center; font-size:18px; }
        .brand-name { font-family:'Fredoka',sans-serif; font-weight:700; font-size:19px; color:var(--ink); }
        .back { font-size:14px; font-weight:800; color:var(--teal); text-decoration:none; }
        h1 { font-family:'Fredoka',sans-serif; font-weight:700; font-size:30px; margin-bottom:6px; letter-spacing:-.01em; }
        .terms-meta { font-size:13px; font-weight:700; color:var(--ink-faint); margin-bottom:20px; }
        .terms-body h2 { font-family:'Fredoka',sans-serif; font-weight:600; font-size:19px; color:var(--ink); margin:26px 0 8px; }
        .terms-body p { font-size:15px; color:var(--ink-soft); margin-bottom:10px; }
        .terms-body ul { margin:0 0 12px 20px; }
        .terms-body li { font-size:15px; color:var(--ink-soft); margin-bottom:6px; }
        .terms-body strong { color:var(--ink); }
        .card { background:var(--paper-2); border:1px solid var(--line); border-radius:18px; padding:26px 28px; box-shadow:0 1px 2px rgba(18,34,46,.06),0 4px 12px rgba(18,34,46,.05); }
        a { color:var(--teal); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="top">
            <a href="{{ url('/') }}" class="brand"><span class="brand-mark">⚓</span><span class="brand-name">SmoothSeas</span></a>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}" class="back">← Back</a>
        </div>
        <div class="card">
            <h1>Terms &amp; Conditions</h1>
            @include('legal._terms-body')
        </div>
    </div>
</body>
</html>
