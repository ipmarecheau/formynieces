<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <x-seo title="SmoothSeas Resources — SEA prep, adaptive learning & AI, for T&T parents"
           description="Honest, practical guides for Trinidad & Tobago families preparing for the SEA: study plans, placement and composite scores, one-on-one adaptive learning, and what the evidence really says about AI in a child's education.">
        @php
            $k = '@';
            $home = url('/');
            $ld = [
                $k.'context' => 'https://schema.org',
                $k.'type' => 'Blog',
                'name' => 'SmoothSeas Resources',
                'url' => route('blog.index'),
                'description' => 'Guides on SEA preparation, adaptive learning and AI in education for Caribbean families.',
                'publisher' => [
                    $k.'type' => 'Organization',
                    'name' => 'SmoothSeas',
                    'url' => $home,
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    </x-seo>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#12222e; --ink-soft:#40566a; --ink-faint:#6b8199; --paper:#fbf8f2; --paper-2:#fff; --line:#e7ddcd; --teal:#0d7d8c; --teal-deep:#0a5c68; --teal-tint:#e6f3f4; --amber:#f2a900; --shadow-sm:0 1px 2px rgba(18,34,46,.06),0 4px 12px rgba(18,34,46,.05); --radius:18px; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--paper); color:var(--ink); font-family:'Nunito',system-ui,sans-serif; line-height:1.6; }
        img { max-width:100%; height:auto; display:block; }
        h1,h2,h3 { font-family:'Fredoka','Nunito',sans-serif; line-height:1.15; letter-spacing:-.01em; }
        a { color:var(--teal); }
        .wrap { max-width:1080px; margin:0 auto; padding:0 22px; }
        .top { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:22px 0; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .brand-mark { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--teal),var(--teal-deep)); display:flex; align-items:center; justify-content:center; font-size:18px; }
        .brand-name { font-family:'Fredoka',sans-serif; font-weight:700; font-size:19px; color:var(--ink); }
        .nav-cta { font-size:14px; font-weight:800; color:#fff; background:var(--teal); padding:9px 16px; border-radius:999px; text-decoration:none; }
        .hero { padding:26px 0 10px; }
        .eyebrow { font-size:12.5px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:var(--teal); }
        .hero h1 { font-size:34px; margin:8px 0 10px; max-width:20ch; }
        .hero p { font-size:17px; color:var(--ink-soft); max-width:62ch; }
        .filters { display:flex; flex-wrap:wrap; gap:8px; margin:26px 0 22px; }
        .chip { font-size:13px; font-weight:800; padding:7px 14px; border-radius:999px; text-decoration:none; border:1px solid var(--line); color:var(--ink-soft); background:var(--paper-2); }
        .chip.is-active { background:var(--teal); color:#fff; border-color:var(--teal); }
        .grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:20px; padding-bottom:60px; }
        .card { display:flex; flex-direction:column; background:var(--paper-2); border:1px solid var(--line); border-radius:var(--radius); padding:22px 22px 20px; box-shadow:var(--shadow-sm); text-decoration:none; color:inherit; transition:transform .12s ease, box-shadow .12s ease; }
        .card:hover { transform:translateY(-2px); box-shadow:0 10px 30px rgba(18,34,46,.09); }
        .card .cat { font-size:11.5px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--teal); }
        .card h2 { font-size:20px; margin:8px 0 8px; color:var(--ink); }
        .card p { font-size:14.5px; color:var(--ink-soft); flex:1; }
        .card .meta { font-size:12.5px; font-weight:700; color:var(--ink-faint); margin-top:14px; }
        .foot { border-top:1px solid var(--line); padding:22px 0 60px; font-size:13px; color:var(--ink-faint); }
        @media (max-width:560px){ .hero h1 { font-size:27px; } }
    </style>
</head>
<body>
    <header class="wrap">
        <div class="top">
            <a href="{{ url('/') }}" class="brand"><span class="brand-mark">⚓</span><span class="brand-name">SmoothSeas</span></a>
            <a href="{{ route('register') }}" class="nav-cta">Start free</a>
        </div>
    </header>

    <main class="wrap">
        <section class="hero">
            <p class="eyebrow">Resources</p>
            <h1>Straight talk on SEA prep, learning &amp; AI</h1>
            <p>Practical, honest guides for Trinidad &amp; Tobago parents — how placement really works, how children actually learn a topic to mastery, and what the evidence says (good and bad) about AI in your child's education.</p>
        </section>

        <nav class="filters" aria-label="Filter articles by category">
            <a href="{{ route('blog.index') }}" class="chip @if(! $activeCategory) is-active @endif">All</a>
            @foreach ($categories as $category)
                <a href="{{ route('blog.index', ['category' => $category]) }}"
                   class="chip @if($activeCategory === $category) is-active @endif">{{ $category }}</a>
            @endforeach
        </nav>

        <div class="grid">
            @foreach ($articles as $article)
                <a class="card" href="{{ route('blog.show', $article->slug) }}">
                    <span class="cat">{{ $article->category }}</span>
                    <h2>{{ $article->title }}</h2>
                    <p>{{ $article->excerpt }}</p>
                    <span class="meta">{{ $article->published_at->format('M j, Y') }} · {{ $article->read_minutes }} min read</span>
                </a>
            @endforeach
        </div>
    </main>

    <footer class="wrap foot">
        <div class="wrap" style="padding:0;">© {{ now()->year }} SmoothSeas · <a href="{{ route('about') }}">About</a> · <a href="{{ route('privacy') }}">Privacy</a> · <a href="{{ route('terms') }}">Terms</a></div>
    </footer>
</body>
</html>
