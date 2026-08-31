<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <x-seo :title="$article->title . ' — SmoothSeas'"
           :description="$article->meta_description"
           type="article">
        @php
            $k = '@';
            $home = url('/');
            $ld = [
                $k.'context' => 'https://schema.org',
                $k.'type' => 'Article',
                'headline' => $article->title,
                'description' => $article->meta_description,
                'datePublished' => $article->published_at->toIso8601String(),
                'dateModified' => $article->updated_at->toIso8601String(),
                'author' => [
                    $k.'type' => 'Organization',
                    'name' => $article->author,
                ],
                'publisher' => [
                    $k.'type' => 'Organization',
                    'name' => 'SmoothSeas',
                    'url' => $home,
                ],
                'mainEntityOfPage' => route('blog.show', $article->slug),
                'keywords' => implode(', ', $article->keywords ?? []),
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
        body { background:var(--paper); color:var(--ink); font-family:'Nunito',system-ui,sans-serif; line-height:1.7; }
        img { max-width:100%; height:auto; display:block; }
        h1,h2,h3 { font-family:'Fredoka','Nunito',sans-serif; line-height:1.2; letter-spacing:-.01em; }
        a { color:var(--teal); }
        .wrap { max-width:740px; margin:0 auto; padding:0 22px; }
        .top { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:22px 0; max-width:1080px; margin:0 auto; padding-left:22px; padding-right:22px; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .brand-mark { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--teal),var(--teal-deep)); display:flex; align-items:center; justify-content:center; font-size:18px; }
        .brand-name { font-family:'Fredoka',sans-serif; font-weight:700; font-size:19px; color:var(--ink); }
        .nav-cta { font-size:14px; font-weight:800; color:#fff; background:var(--teal); padding:9px 16px; border-radius:999px; text-decoration:none; }
        .eyebrow { font-size:12px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--teal); }
        article { padding:14px 0 10px; }
        .art-head { margin-bottom:26px; }
        .art-head h1 { font-size:35px; margin:10px 0 12px; }
        .art-meta { font-size:13px; font-weight:700; color:var(--ink-faint); }
        .art-body { font-size:17.5px; color:var(--ink-soft); }
        .art-body h2 { font-size:25px; color:var(--ink); margin:34px 0 10px; }
        .art-body h3 { font-size:20px; color:var(--ink); margin:26px 0 8px; }
        .art-body p { margin-bottom:16px; }
        .art-body ul, .art-body ol { margin:0 0 16px 22px; }
        .art-body li { margin-bottom:7px; }
        .art-body strong { color:var(--ink); }
        .art-body blockquote { border-left:3px solid var(--teal); background:var(--teal-tint); padding:12px 18px; border-radius:0 12px 12px 0; margin:0 0 18px; color:var(--ink); font-weight:600; }
        .art-body a { text-decoration:underline; }
        .art-body hr { border:none; border-top:1px solid var(--line); margin:26px 0; }
        .cta { background:var(--paper-2); border:1px solid var(--line); border-radius:var(--radius); padding:26px 26px; box-shadow:var(--shadow-sm); margin:40px 0 30px; text-align:center; }
        .cta h3 { font-size:22px; margin-bottom:8px; }
        .cta p { font-size:15px; color:var(--ink-soft); margin-bottom:16px; }
        .cta a.btn { display:inline-block; background:var(--teal); color:#fff; font-weight:800; padding:12px 24px; border-radius:999px; text-decoration:none; }
        .related { border-top:1px solid var(--line); padding:26px 0 60px; }
        .related h3 { font-size:14px; text-transform:uppercase; letter-spacing:.08em; color:var(--ink-faint); margin-bottom:14px; }
        .related ul { list-style:none; display:grid; gap:12px; }
        .related a { font-weight:700; font-size:16px; text-decoration:none; }
        .related .r-cat { font-size:11.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:var(--teal); display:block; }
        @media (max-width:560px){ .art-head h1 { font-size:28px; } .art-body { font-size:16.5px; } }
    </style>
</head>
<body>
    <header>
        <div class="top">
            <a href="{{ url('/') }}" class="brand"><span class="brand-mark">⚓</span><span class="brand-name">SmoothSeas</span></a>
            <a href="{{ route('register') }}" class="nav-cta">Start free</a>
        </div>
    </header>

    <main class="wrap">
        <article>
            <div class="art-head">
                <a class="eyebrow" href="{{ route('blog.index', ['category' => $article->category]) }}" style="text-decoration:none;">{{ $article->category }}</a>
                <h1>{{ $article->title }}</h1>
                <p class="art-meta">{{ $article->published_at->format('F j, Y') }} · {{ $article->read_minutes }} min read · {{ $article->author }}</p>
            </div>

            <div class="art-body">
                {!! $article->body_html !!}
            </div>

            <div class="cta">
                <h3>See your child's real progress — in one place</h3>
                <p>SmoothSeas builds an adaptive daily plan for Math, ELA and Writing, re-teaches what your child misses, and gives you an honest parent view. No overclaiming — just the data.</p>
                <a class="btn" href="{{ route('register') }}">Start free</a>
            </div>
        </article>

        @if ($related->isNotEmpty())
            <section class="related">
                <h3>Keep reading</h3>
                <ul>
                    @foreach ($related as $item)
                        <li>
                            <span class="r-cat">{{ $item->category }}</span>
                            <a href="{{ route('blog.show', $item->slug) }}">{{ $item->title }}</a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </main>
</body>
</html>
