@php
    $homeUrl = auth()->check()
        ? (auth()->user()->isStudent() ? route('student.voyage') : route('dashboard'))
        : route('login');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $seoTitle = 'SmoothSeas — SEA exam prep, sailed with a turtle named Smooth';
        $seoDesc = 'SmoothSeas turns SEA prep into a voyage Caribbean children love: Math, ELA and Writing in one adaptive daily plan, with an AI that re-teaches what they miss and weekly reports for parents.';
        $seoImage = asset('reels/child-reel-poster.png');
        $canonical = url()->current();
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SmoothSeas">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta property="og:locale" content="en_TT">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDesc }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
        @php
            // Build @-prefixed schema.org keys via a variable so Blade never sees
            // a literal "@context"/"@type" token (it would treat them as directives).
            $k = '@';
            $home = url('/');
            $ld = [
                $k.'context' => 'https://schema.org',
                $k.'graph' => [
                    [
                        $k.'type' => 'Organization',
                        $k.'id' => $home.'#organization',
                        'name' => 'SmoothSeas',
                        'url' => $home,
                        'logo' => asset('images/voyage/companion/smooth.webp'),
                        'description' => 'SEA exam preparation for Caribbean primary-school children.',
                        'areaServed' => 'TT',
                        'email' => config('legal.contact_email'),
                    ],
                    [
                        $k.'type' => 'WebSite',
                        $k.'id' => $home.'#website',
                        'name' => 'SmoothSeas',
                        'url' => $home,
                        'publisher' => [$k.'id' => $home.'#organization'],
                        'inLanguage' => 'en',
                    ],
                    [
                        $k.'type' => 'EducationalOrganization',
                        'name' => 'SmoothSeas',
                        'url' => $home,
                        'description' => 'An adaptive SEA (Secondary Entrance Assessment) preparation platform for Trinidad and Tobago, covering Mathematics, English Language Arts and Creative Writing.',
                    ],
                ],
            ];
        @endphp
        <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── SmoothSeas — light editorial system ──
           A calm, trustworthy canvas for parents; teal is the single action colour. */
        :root {
            --ink:        #12222e;
            --ink-soft:   #40566a;
            --ink-faint:  #6b8199;
            --paper:      #fbf8f2;
            --paper-2:    #ffffff;
            --line:       #e7ddcd;
            --teal:       #0d7d8c;
            --teal-deep:  #0a5c68;
            --teal-tint:  #e6f3f4;
            --amber:      #f2a900;
            --amber-tint: #fdf1d6;
            --shadow-sm:  0 1px 2px rgba(18,34,46,.06), 0 4px 12px rgba(18,34,46,.05);
            --shadow-md:  0 10px 30px rgba(18,34,46,.09);
            --shadow-lg:  0 24px 60px rgba(10,60,72,.16);
            --radius:     18px;
        }

        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
        body {
            background: var(--paper);
            font-family: 'Nunito', system-ui, sans-serif;
            color: var(--ink);
            line-height: 1.6;
            overflow-x: hidden;
        }
        img { max-width: 100%; height: auto; display: block; }
        h1, h2, h3, h4 { font-family: 'Fredoka', 'Nunito', sans-serif; line-height: 1.15; letter-spacing: -.01em; }

        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 24px; }
        .wrap-narrow { max-width: 760px; }
        .eyebrow {
            font-size: 12.5px; font-weight: 800; letter-spacing: .12em;
            text-transform: uppercase; color: var(--teal);
        }

        /* ── BUTTONS ── */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 16px;
            padding: 14px 26px; border-radius: 999px; border: 1.5px solid transparent;
            text-decoration: none; cursor: pointer; white-space: nowrap;
            transition: transform .12s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
        }
        .btn-primary {
            background: var(--teal); color: #fff;
            box-shadow: 0 8px 20px rgba(13,125,140,.28);
        }
        .btn-primary:hover  { background: var(--teal-deep); transform: translateY(-2px); box-shadow: 0 12px 26px rgba(13,125,140,.34); }
        .btn-primary:active { transform: translateY(0); }
        .btn-secondary {
            background: var(--paper-2); color: var(--teal-deep); border-color: var(--line);
            box-shadow: var(--shadow-sm);
        }
        .btn-secondary:hover { border-color: var(--teal); color: var(--teal); transform: translateY(-2px); }
        .btn-lg { font-size: 17px; padding: 16px 32px; }
        .link-quiet {
            color: var(--ink-soft); font-weight: 700; font-size: 15px; text-decoration: none;
            border-bottom: 2px solid var(--line); padding-bottom: 1px; transition: color .2s, border-color .2s;
        }
        .link-quiet:hover { color: var(--teal); border-color: var(--teal); }

        /* ── NAV ── */
        header.nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(251,248,242,.85);
            backdrop-filter: saturate(140%) blur(12px);
            border-bottom: 1px solid var(--line);
        }
        .nav-inner {
            max-width: 1120px; margin: 0 auto; padding: 0 24px; height: 68px;
            display: flex; align-items: center; gap: 16px;
        }
        .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; flex-shrink: 0; }
        .brand-mark {
            width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--teal), var(--teal-deep));
            display: flex; align-items: center; justify-content: center; font-size: 19px;
            box-shadow: var(--shadow-sm);
        }
        .brand-name { font-family: 'Fredoka', sans-serif; font-weight: 700; font-size: 21px; color: var(--ink); }
        .nav-menu { display: flex; align-items: center; gap: 2px; margin-left: auto; }
        .nav-link {
            display: inline-flex; align-items: center; line-height: 1;
            color: var(--ink-soft); font-weight: 700; font-size: 15px; text-decoration: none;
            padding: 9px 14px; border-radius: 999px; transition: color .2s, background .2s;
        }
        .nav-link:hover { color: var(--teal); background: var(--teal-tint); }
        .nav-actions {
            display: flex; align-items: center; gap: 14px;
            margin-left: 18px; padding-left: 18px; border-left: 1px solid var(--line);
        }
        .nav-actions .link-quiet { border-bottom: none; padding-bottom: 0; }
        .nav-user { font-weight: 800; font-size: 15px; color: var(--ink); }
        .nav-logout { display: inline; margin: 0; }

        /* mobile menu toggle */
        .nav-toggle { display: none; background: none; border: 0; padding: 8px; cursor: pointer; color: var(--ink); }
        .nav-toggle svg { display: block; }

        /* ── HERO ── */
        .hero { padding: 72px 0 40px; }
        .hero-grid { display: grid; grid-template-columns: 1.05fr .95fr; gap: 56px; align-items: center; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--amber-tint); color: #8a5a00;
            border: 1px solid #f2d69a; border-radius: 999px;
            padding: 6px 14px; font-size: 13px; font-weight: 800; margin-bottom: 22px;
        }
        .hero h1 {
            font-size: clamp(34px, 5.4vw, 56px); font-weight: 700; color: var(--ink);
            margin-bottom: 20px;
        }
        .hero h1 .accent {
            color: var(--teal);
            background: linear-gradient(180deg, transparent 62%, var(--amber-tint) 62%);
            padding: 0 2px;
        }
        .hero-lede { font-size: clamp(17px, 2.1vw, 20px); color: var(--ink-soft); max-width: 520px; margin-bottom: 30px; }
        .hero-lede strong { color: var(--ink); font-weight: 800; }
        .hero-cta { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; }
        .hero-reassure { margin-top: 18px; font-size: 14px; color: var(--ink-faint); font-weight: 600; display: flex; align-items: center; gap: 8px; }
        .hero-reassure svg { flex-shrink: 0; }
        .hero-captain {
            margin-top: 22px; display: inline-flex; align-items: center; gap: 12px;
            background: var(--teal-tint); border: 1px solid #cfe6e8; border-radius: 999px;
            padding: 8px 16px 8px 8px; font-size: 14px; color: var(--ink-soft); max-width: 100%;
        }
        .hero-captain img { width: 44px; height: 44px; border-radius: 50%; background: #fff; flex-shrink: 0; }
        .hero-captain strong { color: var(--teal-deep); }

        /* ── HERO: centred, with the animated SVG as the focal point ── */
        .hero-lead { max-width: 730px; margin: 0 auto; text-align: center; }
        .hero-lead .hero-lede { margin-left: auto; margin-right: auto; }
        .hero-lead .hero-cta,
        .hero-lead .hero-reassure { justify-content: center; }
        .hero-anim { margin: clamp(30px, 5vw, 48px) auto 0; max-width: 920px; }
        .hero-anim .stage {
            border-radius: 22px; overflow: hidden; border: 1px solid var(--line);
            background: #08152c; aspect-ratio: 16 / 10; box-shadow: var(--shadow-lg);
        }
        .hero-anim .screen { display: block; width: 100%; height: 100%; }
        .hero-anim .dots { display: flex; gap: 9px; justify-content: center; margin: 18px 0 0; }
        .hero-anim .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--line); position: relative; }
        .hero-anim .dot i {
            position: absolute; inset: 0; border-radius: 50%; background: var(--teal);
            opacity: 0; transform: scale(.6); transform-origin: center; animation-fill-mode: both;
        }
        /* scene crossfade timeline (24s loop, 6 scenes) */
        .hero-anim .scene { opacity: 0; }
        .hero-anim .s1 { animation: sv-sc1 24s infinite; } .hero-anim .s2 { animation: sv-sc2 24s infinite; }
        .hero-anim .s3 { animation: sv-sc3 24s infinite; } .hero-anim .s4 { animation: sv-sc4 24s infinite; }
        .hero-anim .s5 { animation: sv-sc5 24s infinite; } .hero-anim .s6 { animation: sv-sc6 24s infinite; }
        .hero-anim .d1 i { animation: sv-sc1 24s infinite; } .hero-anim .d2 i { animation: sv-sc2 24s infinite; }
        .hero-anim .d3 i { animation: sv-sc3 24s infinite; } .hero-anim .d4 i { animation: sv-sc4 24s infinite; }
        .hero-anim .d5 i { animation: sv-sc5 24s infinite; } .hero-anim .d6 i { animation: sv-sc6 24s infinite; }
        .hero-anim .boat { animation: sv-boat 24s infinite; }
        .hero-anim .tick-draw { stroke-dasharray: 46; stroke-dashoffset: 46; animation: sv-draw 24s infinite; }
        .hero-anim .star { transform-box: fill-box; transform-origin: center; animation: sv-pop 24s infinite; }
        .hero-anim .wave { animation: sv-bob 5s ease-in-out infinite; }
        .hero-anim .rt-x { animation: sv-rtx 24s infinite; }
        .hero-anim .rt-help { animation: sv-rth 24s infinite; }
        .hero-anim .rt-ok { animation: sv-rtok 24s infinite; }
        .hero-anim .rt-x, .hero-anim .rt-ok { transform-box: fill-box; transform-origin: center; }
        @keyframes sv-sc1 { 0%{opacity:1} 14.6%{opacity:1} 16.6%{opacity:0} 97.9%{opacity:0} 100%{opacity:1} }
        @keyframes sv-sc2 { 0%,16.6%{opacity:0} 18%{opacity:1} 31.5%{opacity:1} 33.3%{opacity:0} 100%{opacity:0} }
        @keyframes sv-sc3 { 0%,33.3%{opacity:0} 34.7%{opacity:1} 48.2%{opacity:1} 50%{opacity:0} 100%{opacity:0} }
        @keyframes sv-sc4 { 0%,50%{opacity:0} 51.4%{opacity:1} 64.9%{opacity:1} 66.6%{opacity:0} 100%{opacity:0} }
        @keyframes sv-sc5 { 0%,66.6%{opacity:0} 68%{opacity:1} 81.5%{opacity:1} 83.3%{opacity:0} 100%{opacity:0} }
        @keyframes sv-sc6 { 0%,83.3%{opacity:0} 84.7%{opacity:1} 98%{opacity:1} 100%{opacity:0} }
        @keyframes sv-boat { 0%,18%{transform:translate(0,0)} 30%,100%{transform:translate(348px,-34px)} }
        @keyframes sv-draw { 0%,52%{stroke-dashoffset:46} 58%,100%{stroke-dashoffset:0} }
        @keyframes sv-pop { 0%,84.6%{transform:scale(0) rotate(-18deg)} 90%{transform:scale(1.15) rotate(4deg)} 93%,100%{transform:scale(1) rotate(0)} }
        @keyframes sv-bob { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
        @keyframes sv-rtx  { 0%,66.6%{opacity:0;transform:scale(.4)} 68.4%{opacity:1;transform:scale(1)} 71.5%{opacity:1} 73%,100%{opacity:0} }
        @keyframes sv-rth  { 0%,72.5%{opacity:0} 74%{opacity:1} 79%{opacity:1} 80.5%,100%{opacity:0} }
        @keyframes sv-rtok { 0%,79.5%{opacity:0;transform:scale(.4)} 81%{opacity:1;transform:scale(1.05)} 82%{transform:scale(1)} 83.3%{opacity:1} 83.4%,100%{opacity:0} }
        @media (prefers-reduced-motion: reduce) { .hero-anim .s2 { opacity: 1; } .hero-anim .tick-draw { stroke-dashoffset: 0; } }

        /* ── TWO ENGINES (the differentiator) ── */
        .engine-grid { display: grid; grid-template-columns: 1fr auto 1fr; gap: 22px; align-items: stretch; }
        .engine-card {
            border-radius: 22px; padding: 30px 28px; box-shadow: var(--shadow-md);
            border: 1px solid var(--line); position: relative; overflow: hidden;
        }
        .engine-card.game { background: linear-gradient(165deg, #073a52, #0a5c68); color: #eaf6f8; border-color: transparent; }
        .engine-card.ai   { background: linear-gradient(165deg, #4a3410, #7a5510); color: #fdf3dd; border-color: transparent; }
        .engine-tag {
            display: inline-flex; align-items: center; gap: 7px; font-size: 11.5px; font-weight: 800;
            letter-spacing: .12em; text-transform: uppercase; padding: 5px 12px; border-radius: 999px;
            background: rgba(255,255,255,.14); margin-bottom: 16px;
        }
        .engine-card h3 { font-size: clamp(20px, 3vw, 25px); margin-bottom: 12px; color: inherit; }
        .engine-card p { font-size: 15.5px; line-height: 1.6; color: inherit; opacity: .95; }
        .engine-card p strong { color: #fff; }
        .engine-card.ai p strong { color: #fff; }
        .engine-list { list-style: none; margin-top: 18px; display: flex; flex-wrap: wrap; gap: 8px; }
        .engine-list li { font-size: 12.5px; font-weight: 700; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.14); }
        .engine-icon { font-size: 30px; margin-bottom: 6px; }
        .engine-fuse {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 8px; width: 132px; text-align: center;
        }
        .engine-fuse img { width: 84px; height: 84px; border-radius: 50%; background: var(--teal-tint); box-shadow: var(--shadow-md); }
        .engine-fuse .fuse-arrow { font-size: 22px; color: var(--teal); line-height: 1; }
        .engine-fuse .fuse-label { font-size: 12px; font-weight: 800; color: var(--ink-faint); text-transform: uppercase; letter-spacing: .08em; }
        .engine-fuse-line {
            text-align: center; margin: 34px auto 0; max-width: 640px;
            font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: clamp(18px, 2.6vw, 24px); color: var(--ink);
        }
        .engine-fuse-line b { color: var(--teal); }

        /* the tandem loop — how the two layers hand off to each other */
        .tandem { margin-top: 56px; }
        .tandem-head { text-align: center; margin-bottom: 28px; }
        .tandem-head h3 { font-size: clamp(20px, 3vw, 26px); color: var(--ink); }
        .tandem-head p { font-size: 15.5px; color: var(--ink-soft); margin-top: 6px; }
        .tandem-flow { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; align-items: stretch; }
        .tandem-step {
            position: relative; background: var(--paper-2); border: 1px solid var(--line);
            border-radius: 18px; padding: 22px 20px; box-shadow: var(--shadow-sm);
        }
        .tandem-step[data-layer="game"] { border-top: 3px solid var(--teal); }
        .tandem-step[data-layer="ai"]   { border-top: 3px solid var(--amber); }
        .tandem-badge {
            display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 800;
            letter-spacing: .1em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; margin-bottom: 12px;
        }
        .tandem-step[data-layer="game"] .tandem-badge { background: var(--teal-tint); color: var(--teal-deep); }
        .tandem-step[data-layer="ai"] .tandem-badge   { background: var(--amber-tint); color: #8a5a00; }
        .tandem-step .tn { font-family: 'Fredoka', sans-serif; font-weight: 700; color: var(--ink-faint); font-size: 13px; }
        .tandem-step h4 { font-size: 16px; color: var(--ink); margin: 4px 0 7px; }
        .tandem-step p { font-size: 13.5px; color: var(--ink-soft); line-height: 1.5; }
        .tandem-step:not(:last-child)::after {
            content: '→'; position: absolute; right: -12px; top: 50%; transform: translateY(-50%);
            font-size: 20px; color: var(--teal); font-weight: 900; z-index: 2;
        }
        .tandem-loop {
            text-align: center; margin-top: 20px; font-weight: 800; font-size: 14px; color: var(--teal-deep);
        }
        @media (max-width: 940px) {
            .tandem-flow { grid-template-columns: 1fr 1fr; gap: 22px 26px; }
            .tandem-step:not(:last-child)::after { display: none; }
        }
        @media (max-width: 560px) {
            .tandem-flow { grid-template-columns: 1fr; }
        }
        @media (max-width: 940px) {
            .engine-grid { grid-template-columns: 1fr; gap: 16px; }
            .engine-fuse { width: auto; flex-direction: row; }
            .engine-fuse img { width: 56px; height: 56px; }
            .engine-fuse .fuse-arrow { transform: rotate(90deg); }
        }

        /* hero figure */
        .hero-figure { position: relative; display: flex; justify-content: center; }
        .hero-panel {
            position: relative; width: 100%; max-width: 440px;
            background: var(--paper-2); border: 1px solid var(--line); border-radius: 28px;
            padding: 28px 28px 24px; box-shadow: var(--shadow-lg);
        }
        .hero-panel-top { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
        .hero-avatar {
            width: 62px; height: 62px; border-radius: 16px; flex-shrink: 0;
            background: var(--teal-tint); display: flex; align-items: flex-end; justify-content: center; overflow: hidden;
        }
        .hero-avatar img { width: 54px; }
        .hero-panel-top h3 { font-size: 17px; color: var(--ink); }
        .hero-panel-top p { font-size: 13.5px; color: var(--ink-faint); font-weight: 700; }
        .bar-row { display: grid; grid-template-columns: 92px 1fr 44px; gap: 12px; align-items: center; margin-bottom: 13px; font-size: 14px; font-weight: 700; color: var(--ink-soft); }
        .bar { height: 9px; border-radius: 999px; background: var(--paper); overflow: hidden; border: 1px solid var(--line); }
        .bar i { display: block; height: 100%; width: 0; border-radius: 999px; background: linear-gradient(90deg, var(--teal), var(--amber)); transition: width 1.1s cubic-bezier(.2,.8,.2,1) .3s; }
        .hero-panel.in .bar i { width: var(--w); }
        .bar-row b { font-family: 'Fredoka', sans-serif; color: var(--teal-deep); text-align: right; font-size: 14px; }
        .hero-panel-note {
            margin-top: 16px; display: flex; gap: 9px; align-items: flex-start;
            background: var(--teal-tint); border-radius: 12px; padding: 11px 14px;
            font-size: 13px; font-weight: 700; color: var(--teal-deep); line-height: 1.45;
        }
        .hero-float {
            position: absolute; bottom: -18px; right: -10px;
            background: var(--paper-2); border: 1px solid var(--line); border-radius: 14px;
            padding: 10px 16px; font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 14px;
            color: var(--ink); box-shadow: var(--shadow-md); display: flex; align-items: center; gap: 8px;
        }

        /* ── TRUST BAR ── */
        .trust { border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); background: var(--paper-2); }
        .trust-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; padding: 30px 0; text-align: center; }
        .trust-num { font-family: 'Fredoka', sans-serif; font-weight: 700; font-size: 30px; color: var(--teal); display: block; }
        .trust-label { font-size: 13px; font-weight: 700; color: var(--ink-faint); margin-top: 2px; }

        /* ── SECTION SHELL ── */
        section.band { padding: 84px 0; }
        .section-head { text-align: center; max-width: 620px; margin: 0 auto 52px; }
        .section-head h2 { font-size: clamp(27px, 4.4vw, 40px); font-weight: 700; color: var(--ink); margin: 12px 0 14px; }
        .section-head p { font-size: 17px; color: var(--ink-soft); }

        /* ── MEET SMOOTH ── */
        .meet-grid { display: grid; grid-template-columns: .85fr 1.15fr; gap: 52px; align-items: center; }
        .meet-figure {
            background: linear-gradient(160deg, var(--teal-tint), #fff);
            border: 1px solid var(--line); border-radius: 26px; padding: 32px;
            display: flex; justify-content: center; box-shadow: var(--shadow-md);
        }
        .meet-figure img { width: 260px; }
        .meet-quote {
            position: relative; background: var(--paper-2); border: 1px solid var(--line);
            border-radius: var(--radius); padding: 22px 24px; margin-bottom: 22px;
            font-size: 17px; color: var(--ink); box-shadow: var(--shadow-sm);
        }
        .meet-quote strong { color: var(--teal-deep); }
        .meet-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .meet-card { background: var(--paper-2); border: 1px solid var(--line); border-radius: 16px; padding: 18px 16px; box-shadow: var(--shadow-sm); }
        .meet-card .m-icon { font-size: 24px; }
        .meet-card h4 { font-size: 15px; color: var(--ink); margin: 8px 0 5px; }
        .meet-card p { font-size: 13px; color: var(--ink-soft); line-height: 1.5; }

        /* ── FEATURES (For parents) ── */
        .features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .feature {
            background: var(--paper-2); border: 1px solid var(--line); border-radius: var(--radius);
            padding: 26px 24px; box-shadow: var(--shadow-sm);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .feature:hover { transform: translateY(-4px); box-shadow: var(--shadow-md); border-color: #d9ead9; }
        .feature-icon {
            width: 50px; height: 50px; border-radius: 14px; margin-bottom: 16px;
            display: flex; align-items: center; justify-content: center; font-size: 24px;
            background: var(--teal-tint);
        }
        .feature h3 { font-size: 18px; color: var(--ink); margin-bottom: 9px; }
        .feature p { font-size: 14.5px; color: var(--ink-soft); }
        .feature.wide { grid-column: span 3; display: grid; grid-template-columns: 1fr 1fr; gap: 32px; align-items: center; }
        .feature.wide .feature-icon { margin-bottom: 14px; }
        .feature.wide h3 { font-size: clamp(20px, 3vw, 26px); }
        .feature.wide p { font-size: 15.5px; }
        .feature-list { list-style: none; margin-top: 14px; display: flex; flex-direction: column; gap: 8px; }
        .feature-list li { display: flex; gap: 10px; align-items: baseline; font-size: 14.5px; font-weight: 700; color: var(--ink); }
        .feature-list li::before { content: '✓'; color: var(--teal); font-weight: 900; }
        .report-mini { background: var(--paper); border: 1px solid var(--line); border-radius: 16px; padding: 20px; }
        .report-mini .rm-head { font-family: 'Fredoka', sans-serif; font-size: 14px; color: var(--teal-deep); margin-bottom: 14px; }
        .report-mini .bar i { width: var(--w); transition: none; }

        /* ── HOW IT WORKS ── */
        .steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; counter-reset: step; }
        .step { background: var(--paper-2); border: 1px solid var(--line); border-radius: var(--radius); padding: 26px 24px; position: relative; box-shadow: var(--shadow-sm); }
        .step-num {
            width: 40px; height: 40px; border-radius: 12px; margin-bottom: 16px;
            background: linear-gradient(135deg, var(--teal), var(--teal-deep)); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Fredoka', sans-serif; font-weight: 700; font-size: 18px;
        }
        .step h3 { font-size: 17px; color: var(--ink); margin-bottom: 7px; }
        .step p { font-size: 14.5px; color: var(--ink-soft); }

        /* ── PRICING ── */
        .pricing-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 24px; align-items: stretch; }
        .price-card {
            background: var(--paper-2); border: 2px solid var(--teal); border-radius: 24px;
            padding: 38px 34px; box-shadow: var(--shadow-lg); position: relative;
        }
        .price-flag {
            position: absolute; top: -14px; left: 34px;
            background: var(--amber); color: #4a3400; font-family: 'Fredoka', sans-serif; font-weight: 600;
            font-size: 12.5px; letter-spacing: .04em; text-transform: uppercase;
            padding: 5px 14px; border-radius: 999px;
        }
        .price { font-family: 'Fredoka', sans-serif; font-weight: 700; font-size: clamp(46px, 7vw, 60px); color: var(--ink); line-height: 1; margin: 8px 0 4px; }
        .price span { font-family: 'Nunito', sans-serif; font-size: 18px; font-weight: 700; color: var(--ink-faint); }
        .price-note { font-size: 14px; color: var(--ink-faint); font-weight: 700; }
        .price-feats { list-style: none; margin: 24px 0 28px; display: flex; flex-direction: column; gap: 12px; }
        .price-feats li { display: flex; gap: 11px; align-items: baseline; font-size: 15px; font-weight: 700; color: var(--ink); }
        .price-feats li::before { content: '✓'; color: var(--teal); font-weight: 900; }
        .price-card .btn { width: 100%; }
        .guarantees { display: flex; flex-direction: column; gap: 20px; }
        .guarantee { background: var(--paper-2); border: 1px solid var(--line); border-radius: 20px; padding: 26px 26px; box-shadow: var(--shadow-sm); flex: 1; }
        .guarantee .g-icon { font-size: 26px; }
        .guarantee h3 { font-size: 17px; color: var(--teal-deep); margin: 10px 0 8px; }
        .guarantee p { font-size: 14.5px; color: var(--ink-soft); }
        .guarantee strong { color: var(--ink); }

        /* ── FINAL CTA ── */
        .final-cta {
            background: linear-gradient(140deg, var(--teal-deep), var(--teal));
            border-radius: 28px; padding: 60px 40px; text-align: center; color: #fff;
            position: relative; overflow: hidden;
        }
        .final-cta::after {
            content: ''; position: absolute; right: -40px; bottom: -40px; width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(242,169,0,.4), transparent 70%);
        }
        .final-cta h2 { font-size: clamp(26px, 4.6vw, 40px); font-weight: 700; margin-bottom: 14px; position: relative; }
        .final-cta p { font-size: 17px; color: rgba(255,255,255,.9); max-width: 540px; margin: 0 auto 28px; position: relative; }
        .final-cta .btn-primary { background: var(--amber); color: #3a2900; box-shadow: 0 10px 26px rgba(0,0,0,.22); position: relative; }
        .final-cta .btn-primary:hover { background: #ffbc1f; }
        .final-cta .link-quiet { color: rgba(255,255,255,.85); border-color: rgba(255,255,255,.4); position: relative; }
        .final-cta .link-quiet:hover { color: #fff; border-color: #fff; }

        /* ── FOOTER ── */
        footer.site { border-top: 1px solid var(--line); padding: 34px 0; }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; font-size: 14px; color: var(--ink-faint); }
        .footer-links { display: flex; gap: 18px; flex-wrap: wrap; }
        .footer-links a { color: var(--ink-soft); text-decoration: none; font-weight: 700; }
        .footer-links a:hover { color: var(--teal); }

        /* ── REVEAL ── */
        [data-reveal] { opacity: 0; transform: translateY(20px); transition: opacity .6s ease var(--rd,0s), transform .6s ease var(--rd,0s); }
        [data-reveal].in { opacity: 1; transform: none; }

        /* ── RESPONSIVE ── */
        @media (max-width: 940px) {
            .hero-grid, .meet-grid { grid-template-columns: 1fr; gap: 40px; }
            .hero-figure { order: -1; }
            .features { grid-template-columns: repeat(2, 1fr); }
            .feature.wide { grid-column: span 2; }
            .pricing-grid { grid-template-columns: 1fr; }
            .steps { grid-template-columns: 1fr; }
        }
        @media (max-width: 760px) {
            .nav-menu, .nav-actions.desktop { display: none; }
            .nav-toggle { display: inline-flex; }
            /* mobile drawer */
            #nav-open:checked ~ .nav-drawer { display: block; }
            .nav-drawer {
                display: none; position: absolute; left: 0; right: 0; top: 68px;
                background: var(--paper-2); border-bottom: 1px solid var(--line);
                padding: 14px 24px 22px; box-shadow: var(--shadow-md);
            }
            .nav-drawer a.nav-link { display: block; padding: 12px 8px; border-radius: 12px; font-size: 16px; }
            .nav-drawer .drawer-actions { display: flex; flex-direction: column; gap: 10px; margin-top: 12px; padding-top: 14px; border-top: 1px solid var(--line); }
            .nav-drawer .drawer-actions .btn { width: 100%; }
            .hero { padding: 48px 0 30px; }
            section.band { padding: 60px 0; }
            .trust-row { grid-template-columns: repeat(2, 1fr); gap: 26px 20px; }
            .features { grid-template-columns: 1fr; }
            .feature.wide { grid-column: span 1; grid-template-columns: 1fr; gap: 22px; }
            .meet-cards { grid-template-columns: 1fr; }
            .final-cta { padding: 44px 26px; }
            .hero-cta { width: 100%; flex-direction: column; align-items: stretch; gap: 12px; }
            .hero-cta .btn { width: 100%; }
        }

        /* Small phones — keep the headline + Sign-up above the fold and comfy taps */
        @media (max-width: 560px) {
            .nav-inner { height: 60px; padding: 0 16px; }
            .brand-name { font-size: 19px; }
            .wrap { padding: 0 18px; }
            .hero { padding: 30px 0 22px; }
            .hero-grid { gap: 26px; }
            .hero-figure { order: 0; }                 /* text + CTA first, preview after */
            .hero-badge { font-size: 12px; padding: 5px 12px; margin-bottom: 16px; }
            .hero h1 { font-size: clamp(28px, 8.5vw, 34px); line-height: 1.12; }
            .hero-lede { font-size: 16px; margin-bottom: 22px; }
            .btn-lg { font-size: 16px; padding: 15px 22px; }
            .btn { min-height: 48px; }                  /* accessible tap target */
            .hero-reassure { font-size: 13px; }
            .hero-panel { padding: 20px 18px 18px; border-radius: 22px; }
            section.band { padding: 46px 0; }
            .final-cta { padding: 34px 20px; }
            .final-cta h2 { font-size: clamp(24px, 7.5vw, 30px); }
            .trust-row { grid-template-columns: 1fr 1fr; gap: 20px 14px; }
            .footer-inner { flex-direction: column; text-align: center; gap: 12px; }
            body { padding-bottom: 76px; }              /* room for the sticky CTA */
        }

        /* Sticky mobile sign-up bar (guests only) */
        .mobile-cta { display: none; }
        @media (max-width: 760px) {
            .mobile-cta {
                display: flex; gap: 12px; align-items: center;
                position: fixed; left: 0; right: 0; bottom: 0; z-index: 60;
                padding: 10px 16px calc(10px + env(safe-area-inset-bottom));
                background: rgba(251,248,242,.94); backdrop-filter: blur(10px);
                border-top: 1px solid var(--line); box-shadow: 0 -6px 20px rgba(18,34,46,.08);
            }
            .mobile-cta .btn { flex: 1 1 auto; min-height: 48px; }
            .mobile-cta .mc-signin { flex: 0 0 auto; font-size: 14px; font-weight: 800; color: var(--teal); text-decoration: none; padding: 0 4px; }
        }

        @media (min-width: 761px) { #nav-open, .nav-toggle, .nav-drawer { display: none; } }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation: none !important; transition: none !important; }
            [data-reveal] { opacity: 1 !important; transform: none !important; }
            .hero-panel .bar i { width: var(--w); }
        }
    </style>
</head>
<body>

<!-- NAV -->
<header class="nav">
    <input type="checkbox" id="nav-open" hidden>
    <div class="nav-inner">
        <a class="brand" href="/">
            <span class="brand-mark">⛵</span>
            <span class="brand-name">SmoothSeas</span>
        </a>

        @auth
            <nav class="nav-menu" aria-label="Primary">
                <a class="nav-link" href="{{ route('about') }}">About</a>
                <a class="nav-link" href="{{ route('faq') }}">FAQ</a>
                <a class="nav-link" href="{{ route('contact') }}">Contact</a>
            </nav>
            <div class="nav-actions desktop">
                <span class="nav-user">Hi, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }} 👋</span>
                <a class="btn btn-primary" href="{{ $homeUrl }}">My Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" class="nav-logout">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Log out</button>
                </form>
            </div>
        @else
            <nav class="nav-menu" aria-label="Primary">
                <a class="nav-link" href="#why-it-works">Why us</a>
                <a class="nav-link" href="#meet-smooth">Meet Smooth</a>
                <a class="nav-link" href="#for-parents">For parents</a>
                <a class="nav-link" href="#pricing">Pricing</a>
                <a class="nav-link" href="{{ route('blog.index') }}">Resources</a>
                <a class="nav-link" href="{{ route('faq') }}">FAQ</a>
            </nav>
            <div class="nav-actions desktop">
                <a class="link-quiet" href="{{ route('login') }}">Sign In</a>
                <a class="btn btn-primary" href="{{ route('register') }}">Sign up free</a>
            </div>
        @endauth

        <label for="nav-open" class="nav-toggle" aria-label="Open menu" role="button">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="7" x2="21" y2="7"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="17" x2="21" y2="17"/></svg>
        </label>
    </div>

    <!-- mobile drawer -->
    <div class="nav-drawer">
        @auth
            <a class="nav-link" href="{{ route('about') }}">About</a>
            <a class="nav-link" href="{{ route('faq') }}">FAQ</a>
            <a class="nav-link" href="{{ route('contact') }}">Contact</a>
            <div class="drawer-actions">
                <a class="btn btn-primary" href="{{ $homeUrl }}">My Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width:100%">Log out</button>
                </form>
            </div>
        @else
            <a class="nav-link" href="#why-it-works">Why us</a>
            <a class="nav-link" href="#meet-smooth">Meet Smooth</a>
            <a class="nav-link" href="#for-parents">For parents</a>
            <a class="nav-link" href="#pricing">Pricing</a>
            <a class="nav-link" href="{{ route('blog.index') }}">Resources</a>
            <a class="nav-link" href="{{ route('faq') }}">FAQ</a>
            <a class="nav-link" href="{{ route('contact') }}">Contact</a>
            <div class="drawer-actions">
                <a class="btn btn-primary" href="{{ route('register') }}">Sign up free</a>
                <a class="btn btn-secondary" href="{{ route('login') }}">Sign In</a>
                <a class="nav-link" href="{{ route('book.call') }}" style="text-align:center;">or book a free call</a>
            </div>
        @endauth
    </div>
</header>

<!-- HERO -->
<section class="hero">
    <div class="wrap">
        <div class="hero-lead" data-reveal>
            <span class="hero-badge">🇹🇹 SEA 2027 · built for Caribbean families</span>
            <h1>They'll think it's a <span class="accent">game</span>. It's <span class="accent">AI-assisted</span> learning for the whole SEA.</h1>
            <p class="hero-lede">
                SmoothSeas turns Math, ELA and Writing into a voyage your child <strong>begs to sail</strong> —
                while an AI plans every day around them, <strong>re-teaches</strong> whatever they miss,
                and shows you exactly where they stand. They stay hooked, they truly learn, and you'll
                <strong>never have to guess</strong>.
            </p>
            <div class="hero-cta">
                @auth
                    <a class="btn btn-primary btn-lg" href="{{ $homeUrl }}">Go to your dashboard →</a>
                @else
                    <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Sign up free →</a>
                    <a class="btn btn-secondary btn-lg" href="{{ route('book.call') }}">Book a free call</a>
                @endauth
            </div>
            <div class="hero-captain">
                <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle" width="44" height="44">
                <span><strong>Meet Smooth</strong> — your child's AI captain, patient on every miss.</span>
            </div>
            @guest
                <p class="hero-reassure">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--teal)" stroke-width="2.4"><path d="M20 6L9 17l-5-5"/></svg>
                    No credit card · 14-day money-back promise · cancel anytime
                </p>
            @endguest
        </div>

        @include('partials.sim-demo')
    </div>
</section>

<!-- TRUST BAR -->
<div class="trust">
    <div class="wrap">
        <div class="trust-row">
            <div><span class="trust-num">3</span><div class="trust-label">SEA subjects, one voyage</div></div>
            <div><span class="trust-num">20m–2h</span><div class="trust-label">daily · unlimited practice</div></div>
            <div><span class="trust-num">1 / week</span><div class="trust-label">honest progress report</div></div>
            <div><span class="trust-num">14 days</span><div class="trust-label">money-back guarantee</div></div>
        </div>
    </div>
</div>

<!-- WHY IT WORKS — the two engines -->
<section class="band" id="why-it-works">
    <div class="wrap">
        <div class="section-head" data-reveal>
            <span class="eyebrow">Why it works</span>
            <h2>Most apps make you choose. We fused both.</h2>
            <p>Learning apps are usually one or the other — a game they abandon in a week, or a tutor they dread opening. SmoothSeas runs on two engines that need each other.</p>
        </div>
        <div class="engine-grid">
            <div class="engine-card game" data-reveal>
                <div class="engine-icon">🗺️</div>
                <span class="engine-tag">🎮 Gamification</span>
                <h3>The game they want to play</h3>
                <p>Lessons live on a gamified <strong>voyage map</strong> — glowing islands to conquer, <strong>streaks</strong> to protect, a Captain's Locker of rewards, and a celebration on every win. The fun is the fuel: it's why they show up, every single day.</p>
                <ul class="engine-list">
                    <li>Voyage map</li><li>Streaks</li><li>Captain's Locker</li><li>Mastery stars</li>
                </ul>
            </div>

            <div class="engine-fuse" data-reveal aria-hidden="true">
                <span class="fuse-label">drives</span>
                <span class="fuse-arrow">→</span>
                <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="">
                <span class="fuse-arrow">←</span>
                <span class="fuse-label">powers</span>
            </div>

            <div class="engine-card ai" data-reveal style="--rd:.08s">
                <div class="engine-icon">🧠</div>
                <span class="engine-tag">🤖 AI-assisted learning</span>
                <h3>The AI that makes it teach</h3>
                <p>Under the fun, an AI charts the whole SEA to your child, re-plans it every day to their pace, and <strong>re-teaches</strong> any rule they miss — patiently, until it clicks. It tracks real mastery, never just points, and hands you the honest picture.</p>
                <ul class="engine-list">
                    <li>Adaptive daily plan</li><li>AI re-teach</li><li>True mastery</li><li>Honest tracking</li>
                </ul>
            </div>
        </div>
        <p class="engine-fuse-line" data-reveal>The game drives the effort. <b>The AI makes the effort count.</b></p>

        <div class="tandem">
            <div class="tandem-head" data-reveal>
                <h3>How the two layers work in tandem</h3>
                <p>Every lesson is one turn of the same loop — the game pulls them in, the AI makes sure they learn.</p>
            </div>
            <div class="tandem-flow">
                <div class="tandem-step" data-layer="game" data-reveal>
                    <span class="tandem-badge">🎮 Game</span>
                    <div class="tn">01</div>
                    <h4>They set sail</h4>
                    <p>A glowing island on the voyage map pulls them in. They start because it's fun — not because they're told to.</p>
                </div>
                <div class="tandem-step" data-layer="ai" data-reveal style="--rd:.06s">
                    <span class="tandem-badge">🤖 AI-assisted</span>
                    <div class="tn">02</div>
                    <h4>The AI reads every answer</h4>
                    <p>As they play, it adapts the plan in real time to exactly where they are — easing off or circling back.</p>
                </div>
                <div class="tandem-step" data-layer="ai" data-reveal style="--rd:.12s">
                    <span class="tandem-badge">🤖 AI-assisted</span>
                    <div class="tn">03</div>
                    <h4>It re-teaches misses</h4>
                    <p>Miss a rule and Smooth takes it again, patiently, until it clicks — while the game keeps their streak safe.</p>
                </div>
                <div class="tandem-step" data-layer="game" data-reveal style="--rd:.18s">
                    <span class="tandem-badge">🎮 Game</span>
                    <div class="tn">04</div>
                    <h4>They conquer the island</h4>
                    <p>A celebration, a mastery star, the next island unlocks. Effort rewarded — learning locked in.</p>
                </div>
            </div>
            <p class="tandem-loop" data-reveal>↻ …and the loop begins again, one island at a time.</p>
        </div>
    </div>
</section>

<!-- MEET SMOOTH -->
<section class="band" id="meet-smooth">
    <div class="wrap">
        <div class="meet-grid">
            <div class="meet-figure" data-reveal>
                <img src="{{ asset('images/voyage/companion/smooth-cheer.webp') }}" alt="Smooth the turtle, cheering">
            </div>
            <div data-reveal style="--rd:.1s">
                <span class="eyebrow">Meet the AI captain</span>
                <h2 style="font-size:clamp(24px,3.8vw,34px); margin:12px 0 18px;">Your child's study buddy is a turtle named Smooth.</h2>
                <div class="meet-quote">
                    <strong>“Ahoy! I'm Smooth.”</strong> I'm the friendly face of the AI. I sail with your child through every lesson —
                    miss a rule and we take it again together, word by word, until it clicks. I never make anyone feel small.
                </div>
                <div class="meet-cards">
                    <div class="meet-card">
                        <span class="m-icon">🧭</span>
                        <h4>He shows them the way</h4>
                        <p>A friendly how-to the first time they open any screen — then never nags.</p>
                    </div>
                    <div class="meet-card">
                        <span class="m-icon">💛</span>
                        <h4>Explains, never scolds</h4>
                        <p>Miss a rule twice? He re-teaches that exact rule until it sticks.</p>
                    </div>
                    <div class="meet-card">
                        <span class="m-icon">🎉</span>
                        <h4>He celebrates every win</h4>
                        <p>Streaks, mastery stars and end-of-lesson victories, big and small.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOR PARENTS -->
<section class="band" id="for-parents" style="background:var(--paper-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
    <div class="wrap">
        <div class="section-head" data-reveal>
            <span class="eyebrow">For parents</span>
            <h2>The worries you carry — handled.</h2>
            <p>You don't need another app to police. You need to stop guessing. Here's what SmoothSeas takes off your plate.</p>
        </div>

        <div class="features">
            <!-- wide: visibility -->
            <div class="feature wide" data-reveal>
                <div>
                    <div class="feature-icon">👀</div>
                    <h3>You'll always know where they stand.</h3>
                    <p>“How was school today?” — “Fine.” Every week a clear picture waits in your Parent Portal: what they conquered, what they're working on, and every gentle re-teach, with the rule named. No rosy spin, no surprises at term's end.</p>
                    <ul class="feature-list">
                        <li>Weekly progress, strand by strand</li>
                        <li>Every re-teach visible to you</li>
                        <li>Honest pace — never inflated</li>
                    </ul>
                </div>
                <div class="report-mini">
                    <div class="rm-head">📎 This week at a glance</div>
                    <div class="bar-row"><span>Math</span><div class="bar"><i style="--w:74%"></i></div><b>74%</b></div>
                    <div class="bar-row"><span>Reading</span><div class="bar"><i style="--w:82%"></i></div><b>82%</b></div>
                    <div class="bar-row"><span>Writing</span><div class="bar"><i style="--w:68%"></i></div><b>68%</b></div>
                </div>
            </div>

            <div class="feature" data-reveal>
                <div class="feature-icon">🗺️</div>
                <h3>The curriculum plans itself — around your child.</h3>
                <p>A friendly diagnostic charts the whole SEA voyage, then re-plans every day around their pace. Breezed through? They advance. Struggled? It circles back. And when life happens, pause and resume with one tap.</p>
            </div>
            <div class="feature" data-reveal style="--rd:.06s">
                <div class="feature-icon">🏝️</div>
                <h3>They'll ask to log in.</h3>
                <p>Lessons live on a gamified voyage map — glowing islands to conquer, streaks to keep, celebrations on every win. They want to sail; you want them to.</p>
            </div>
            <div class="feature" data-reveal style="--rd:.12s">
                <div class="feature-icon">⚓</div>
                <h3>Everything in one harbour.</h3>
                <p>Lessons, tutorials and practice — all in one place. No worksheet hunting, no six apps, no lost logins.</p>
            </div>
            <div class="feature" data-reveal>
                <div class="feature-icon">🧭</div>
                <h3>Every component of the SEA.</h3>
                <p>Mathematics, English Language Arts and Writing — taught as one connected journey, not disconnected drills.</p>
            </div>
            <div class="feature" data-reveal style="--rd:.06s">
                <div class="feature-icon">🏆</div>
                <h3>Effort pays off at home.</h3>
                <p>You set the treasure. Streaks and mastery stars become the currency for the rewards you choose — the beach trip, the new book, the extra bedtime story.</p>
            </div>
            <div class="feature" data-reveal style="--rd:.12s">
                <div class="feature-icon">☀️</div>
                <h3>A rhythm that fits.</h3>
                <p>A daily study plan bends to your family: as little as 20 minutes or as much as two full hours — their choice — anchored by a morning vocabulary ritual and daily reading assignments. Unlimited practice, always.</p>
            </div>
            <div class="feature" data-reveal>
                <div class="feature-icon">🏫</div>
                <h3>We work with their school.</h3>
                <p>Graded school papers join the journal, and what their teacher sees weighs into the daily plan — one honest picture of your child. <em>Coming in the MVP.</em></p>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="band" id="how-it-works">
    <div class="wrap">
        <div class="section-head" data-reveal>
            <span class="eyebrow">How it works</span>
            <h2>From sign-up to SEA day, in three steps.</h2>
            <p>You set the compass once. Smooth handles the sailing.</p>
        </div>
        <div class="steps">
            <div class="step" data-reveal>
                <div class="step-num">1</div>
                <h3>Set the compass</h3>
                <p>Create your parent account, add your child, choose their exam year. Two minutes — no credit card, no consultation.</p>
            </div>
            <div class="step" data-reveal style="--rd:.08s">
                <div class="step-num">2</div>
                <h3>Smooth charts the voyage</h3>
                <p>A friendly diagnostic finds where they truly are — not where the syllabus assumes — and plans the whole curriculum from there.</p>
            </div>
            <div class="step" data-reveal style="--rd:.16s">
                <div class="step-num">3</div>
                <h3>Daily sails, weekly reports</h3>
                <p>From a 20-minute sail to a two-hour deep dive, plus unlimited practice. You watch the horizon from the Parent Portal with a fresh report each week.</p>
            </div>
        </div>
    </div>
</section>

<!-- SEE IT IN ACTION (child gameplay reel) -->
<style>
    .reel-figure {
        position: relative;
        max-width: 900px;
        margin: 28px auto 0;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--line);
        box-shadow: 0 24px 60px rgba(10, 30, 60, .28);
        background: #0d1b34;
        aspect-ratio: 16 / 10;
    }
    .reel-figure video,
    .reel-figure img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .reel-figure .reel-static { display: none; }
    .reel-sound {
        position: absolute;
        left: 14px;
        bottom: 14px;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 8px 13px;
        border: 0;
        border-radius: 999px;
        background: rgba(8, 20, 40, .78);
        color: #fff;
        font: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, .3);
    }
    .reel-sound:hover { background: rgba(8, 20, 40, .92); }
    .reel-sound:focus-visible { outline: 2px solid #f5b544; outline-offset: 2px; }
    /* Hero jumbotron demo: Child video / Parent portal tabs */
    .hero-demo { width: 100%; max-width: 500px; }
    .hero-tabs {
        display: inline-flex; gap: 5px; margin-bottom: 14px;
        background: var(--paper); border: 1px solid var(--line);
        border-radius: 999px; padding: 5px;
    }
    .hero-tab {
        border: 0; background: transparent; font: inherit; cursor: pointer;
        font-weight: 800; font-size: 13px; color: var(--ink-soft);
        padding: 8px 15px; border-radius: 999px; transition: background .18s, color .18s;
    }
    .hero-tab.is-active { background: var(--teal); color: #fff; box-shadow: 0 4px 12px rgba(20, 120, 140, .3); }
    .hero-tab:focus-visible { outline: 2px solid var(--teal); outline-offset: 2px; }
    .hero-pane[hidden] { display: none; }
    .hero-demo .reel-figure { margin: 0; max-width: none; border-radius: 20px; }
    .hero-parent-soon {
        display: inline-flex; align-items: center; gap: 6px; margin-top: 14px;
        font-size: 12px; font-weight: 800; color: #8a5a00;
        background: var(--amber-tint); border: 1px solid #f2d69a;
        border-radius: 999px; padding: 6px 13px;
    }
    .reel-caption {
        text-align: center;
        margin: 14px auto 0;
        max-width: 640px;
        color: var(--ink-2, #5b6b82);
        font-size: 14px;
    }
    /* Full-bleed demo reel: break the "See it in action" video out of the wrap
       so it spans the whole screen width, edge to edge. */
    #see-it .reel-figure {
        max-width: none;
        width: 100vw;
        margin-left: calc(50% - 50vw);
        margin-right: calc(50% - 50vw);
        margin-top: 34px;
        border-radius: 0;
        border-left: 0;
        border-right: 0;
    }
    @media (prefers-reduced-motion: reduce) {
        .reel-figure video,
        .reel-sound { display: none; }
        .reel-figure .reel-static { display: block; }
    }
</style>
<section class="band" id="see-it" style="background:var(--paper-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
    <div class="wrap">
        <div class="section-head" data-reveal>
            <span class="eyebrow">See it in action</span>
            <h2>A real sail through the Voyage.</h2>
            <p>Watch both engines at once: the game your child plays — island map, Captain's Locker, streaks — and the AI teaching underneath, right down to the moment Smooth re-teaches a missed rule. No slideshow, just the app.</p>
        </div>
        <div class="hero-demo" data-reveal style="margin:0 auto; max-width:900px;">
            <div class="hero-tabs" role="tablist" aria-label="Choose a demo">
                <button type="button" class="hero-tab is-active" id="tab-child" role="tab"
                        aria-selected="true" aria-controls="pane-child">▶ For your child</button>
                <button type="button" class="hero-tab" id="tab-parent" role="tab"
                        aria-selected="false" aria-controls="pane-parent">For you (parent)</button>
            </div>
            <div class="hero-pane is-active" id="pane-child" role="tabpanel" aria-labelledby="tab-child">
                <figure class="reel-figure">
                    <video class="reel-video" autoplay muted loop playsinline preload="metadata"
                           poster="{{ asset('reels/child-reel-poster.png') }}"
                           aria-label="A sped-up, narrated walkthrough of a child's voyage: the daily warm-up and writing, the island map, rewards locker, and the three ways to master a lesson.">
                        <source src="{{ asset('reels/child-reel.mp4') }}" type="video/mp4">
                        <source src="{{ asset('reels/child-reel.webm') }}" type="video/webm">
                    </video>
                    <button type="button" class="reel-sound" aria-label="Turn sound on">🔇 <span>Sound off</span></button>
                    <img class="reel-static" src="{{ asset('reels/child-reel-poster.png') }}"
                         alt="A child's Voyage map with illustrated islands, the Captain's Orders panel, and Smooth the turtle welcoming them back.">
                </figure>
            </div>
            <div class="hero-pane" id="pane-parent" role="tabpanel" aria-labelledby="tab-parent" hidden>
                <figure class="reel-figure">
                    <video class="reel-video" muted loop playsinline preload="none"
                           poster="{{ asset('reels/parent-reel-poster.png') }}"
                           aria-label="A narrated walkthrough of the guardian portal: the four honest answers, the pace calendar, the SEA placement estimator, parent controls, and the school journal.">
                        <source src="{{ asset('reels/parent-reel.mp4') }}" type="video/mp4">
                        <source src="{{ asset('reels/parent-reel.webm') }}" type="video/webm">
                    </video>
                    <button type="button" class="reel-sound" aria-label="Turn sound on">🔇 <span>Sound off</span></button>
                    <img class="reel-static" src="{{ asset('reels/parent-reel-poster.png') }}"
                         alt="The guardian dashboard — the honest layer — showing a student's readiness, pace and projected SEA placement.">
                </figure>
            </div>
        </div>
        <p class="reel-caption">Real recordings of the app, sped up and narrated — the child's voyage and the parent portal.</p>
    </div>
</section>
<script>
    (function () {
        // Sound toggles — each button controls the video in its own figure.
        document.querySelectorAll('.reel-sound').forEach(function (btn) {
            var fig = btn.closest('.reel-figure');
            var v = fig && fig.querySelector('video');
            if (!v) { return; }
            var label = btn.querySelector('span');
            btn.addEventListener('click', function () {
                v.muted = !v.muted;
                if (!v.muted) { v.play().catch(function () {}); }
                btn.firstChild.textContent = (v.muted ? '🔇 ' : '🔊 ');
                label.textContent = v.muted ? 'Sound off' : 'Sound on';
                btn.setAttribute('aria-label', v.muted ? 'Turn sound on' : 'Turn sound off');
            });
        });

        // Hero jumbotron tabs — swap Child video / Parent portal.
        var tabs = Array.prototype.slice.call(document.querySelectorAll('.hero-tab'));
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (t) {
                    var on = t === tab;
                    t.classList.toggle('is-active', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                document.querySelectorAll('.hero-pane').forEach(function (pane) {
                    var on = pane.id === tab.getAttribute('aria-controls');
                    pane.hidden = !on;
                    pane.classList.toggle('is-active', on);
                    var vid = pane.querySelector('video');
                    if (vid) { on ? vid.play().catch(function () {}) : vid.pause(); }
                });
            });
        });
    })();
</script>

<!-- PRICING -->
<section class="band" id="pricing" style="background:var(--paper-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
    <div class="wrap">
        <div class="section-head" data-reveal>
            <span class="eyebrow">One simple fare</span>
            <h2>$200 a month. Everything included.</h2>
            <p>Every component of the SEA, the adaptive daily plans, the weekly reports and Smooth himself — one price, backed by two promises in writing.</p>
        </div>
        <div class="pricing-grid">
            <div class="price-card" data-reveal>
                <span class="price-flag">All-inclusive</span>
                <div class="price">$200<span> / month</span></div>
                <p class="price-note">per family · cancel anytime</p>
                <ul class="price-feats">
                    <li>All three SEA components — Math, ELA &amp; Writing</li>
                    <li>Adaptive daily plans: 20 minutes to 2 hours</li>
                    <li>Unlimited practice time</li>
                    <li>Weekly parent reports + the Parent Portal</li>
                    <li>Smooth the turtle, at the helm every day</li>
                </ul>
                @auth
                    <a class="btn btn-primary btn-lg" href="{{ $homeUrl }}">Go to your dashboard →</a>
                @else
                    <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Sign up free →</a>
                    <p class="price-note" style="margin-top:14px; text-align:center;">Prefer to talk first? <a class="link-quiet" href="{{ route('book.call') }}">Book a free call</a></p>
                @endauth
            </div>
            <div class="guarantees">
                <div class="guarantee" data-reveal style="--rd:.08s">
                    <span class="g-icon">🛟</span>
                    <h3>14-day money-back guarantee</h3>
                    <p>Your child logs in and uses the platform. Unsatisfied for <em>any</em> reason? We refund every cent — <strong>no questions asked</strong>.</p>
                </div>
                <div class="guarantee" data-reveal style="--rd:.16s">
                    <span class="g-icon">📈</span>
                    <h3>Measurable improvement in 14 days</h3>
                    <p>Use SmoothSeas for two weeks and we guarantee you'll see <strong>measurable improvement</strong> in their Math, ELA, Writing and Vocabulary — in 14 days or less.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FINAL CTA -->
<section class="band">
    <div class="wrap">
        <div class="final-cta" data-reveal>
            @auth
                <h2>Welcome back aboard! 🌟</h2>
                <p>Pick up right where you left off — the tide is waiting.</p>
                <a class="btn btn-primary btn-lg" href="{{ $homeUrl }}">Go to your dashboard →</a>
            @else
                <h2>Give your child a smoother SEA.</h2>
                <p>Create your free account and start today — no credit card, covered by the 14-day money-back promise. You risk nothing.</p>
                <a class="btn btn-primary btn-lg" href="{{ route('register') }}">Sign up free →</a>
                <div style="margin-top:18px;"><a class="link-quiet" href="{{ route('book.call') }}">or book a free 15-minute call</a></div>
            @endauth
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="site">
    <div class="wrap footer-inner">
        <div>© {{ date('Y') }} SmoothSeas · Built with ❤️ in Trinidad &amp; Tobago</div>
        <div class="footer-links">
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('blog.index') }}">Resources</a>
            <a href="{{ route('faq') }}">FAQ</a>
            <a href="{{ route('contact') }}">Contact</a>
            <a href="{{ route('book.call') }}">Book a call</a>
            @auth
                <a href="{{ $homeUrl }}">My Dashboard</a>
            @else
                <a href="{{ route('login') }}">Sign In</a>
            @endauth
        </div>
    </div>
</footer>

@guest
    <div class="mobile-cta">
        <a class="btn btn-primary" href="{{ route('register') }}">Sign up free →</a>
        <a class="mc-signin" href="{{ route('login') }}">Sign in</a>
    </div>
@endguest

<script>
    (function () {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
            });
        }, { threshold: 0.15 });
        document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });

        // animate hero report bars once on load
        var panel = document.getElementById('heroPanel');
        if (panel) { requestAnimationFrame(function () { panel.classList.add('in'); }); }

        // close mobile drawer after a link tap
        var toggle = document.getElementById('nav-open');
        document.querySelectorAll('.nav-drawer a').forEach(function (a) {
            a.addEventListener('click', function () { if (toggle) { toggle.checked = false; } });
        });
    })();
</script>
@include('partials.chat-widget')
</body>
</html>
