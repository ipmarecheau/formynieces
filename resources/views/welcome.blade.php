@auth
    @php($homeUrl = auth()->user()->isStudent() ? route('student.voyage') : route('dashboard'))
@endauth
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmoothSeas — SEA English prep, sailed with a turtle named Smooth</title>
    <meta name="description" content="The ELA companion for Caribbean primary-school children: a daily plan that adapts to them, weekly reports for parents, and Smooth the turtle at the helm.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* SmoothSeas ocean palette — teal/cyan (sea) and gold (treasure). */
        :root {
            --teal-deep: #0e7490;
            --aqua: #67e8f9;
            --gold: #f6b71e;
            --gold-light: #fcd34d;
            --bg: linear-gradient(180deg, #06182e 0%, #0b2a4a 38%, #0e4d6e 72%, #0e7490 100%);
            --card: #0c2440;
            --card2: #081c33;
            --border: rgba(103,232,249,0.28);
            --text: #e6f2fb;
            --muted: #93b2cc;
            --dim: rgba(147,178,204,0.72);
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg);
            background-attachment: fixed;
            font-family: 'Nunito', sans-serif;
            color: var(--text);
            overflow-x: hidden;
        }

        /* ── BACKDROP ── */
        #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .star {
            position: absolute; background: #fff; border-radius: 50%;
            animation: twinkle var(--d, 4s) ease-in-out infinite var(--dl, 0s);
        }
        @keyframes twinkle {
            0%,100% { opacity: .12; transform: scale(1); }
            50%     { opacity: .85; transform: scale(1.5); }
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(100px); pointer-events: none; z-index: 0; }
        .orb-1 { width: 500px; height: 500px; background: rgba(34,211,238,.18); top: -150px; left: -150px; }
        .orb-2 { width: 400px; height: 400px; background: rgba(246,183,30,.14); bottom: -100px; right: -100px; }
        .orb-3 { width: 300px; height: 300px; background: rgba(13,148,136,.1); top: 40%; left: 60%; }

        .page { position: relative; z-index: 1; }
        .container { max-width: 1040px; margin: 0 auto; padding: 0 24px; }
        .container-narrow { max-width: 800px; }

        /* ── NAV ── */
        nav {
            position: sticky; top: 0; z-index: 100;
            backdrop-filter: blur(16px);
            background: rgba(20,30,66,.72);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
        }
        .nav-inner {
            max-width: 1040px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            height: 62px; gap: 16px;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 10px;
            font-family: 'Fredoka One', cursive; font-size: 20px;
            background: linear-gradient(135deg, var(--aqua), var(--gold-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; text-decoration: none; flex-shrink: 0;
        }
        .nav-brand-icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--teal-deep), var(--gold));
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
        }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-anchor {
            color: var(--muted); font-size: 14px; font-weight: 700;
            text-decoration: none; padding: 8px 12px; border-radius: 999px;
            transition: color .2s, background .2s;
        }
        .nav-anchor:hover { color: var(--text); background: rgba(34,211,238,.12); }
        .nav-user { color: var(--text); font-size: 14px; font-weight: 700; margin-right: 4px; }
        .nav-logout { display: inline; margin: 0; }
        .btn-nav-ghost {
            padding: 8px 16px; border-radius: 999px;
            background: transparent; border: 1.5px solid var(--border);
            color: var(--muted); font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 700; cursor: pointer; text-decoration: none;
            transition: background .2s, color .2s;
        }
        .btn-nav-ghost:hover { background: rgba(34,211,238,.15); color: var(--text); }
        .btn-nav-primary {
            padding: 8px 18px; border-radius: 999px;
            background: linear-gradient(135deg, var(--teal-deep), var(--gold));
            border: none; color: white; font-family: 'Fredoka One', cursive;
            font-size: 15px; cursor: pointer; text-decoration: none;
            transition: opacity .2s;
        }
        .btn-nav-primary:hover { opacity: .88; }

        /* ── BUTTONS ── */
        .btn-primary {
            display: inline-block; padding: 15px 34px; border-radius: 999px;
            background: linear-gradient(135deg, var(--teal-deep), var(--gold));
            color: white; font-family: 'Fredoka One', cursive; font-size: 17px;
            text-decoration: none; border: none; cursor: pointer;
            transition: opacity .2s, transform .1s;
            box-shadow: 0 0 32px rgba(34,211,238,.4);
        }
        .btn-primary:hover  { opacity: .92; }
        .btn-primary:active { transform: scale(.98); }
        .btn-ghost {
            display: inline-block; padding: 15px 30px; border-radius: 999px;
            background: transparent; border: 1.5px solid rgba(34,211,238,.5);
            color: var(--muted); font-family: 'Nunito', sans-serif;
            font-size: 16px; font-weight: 700; text-decoration: none;
            transition: background .2s, color .2s;
        }
        .btn-ghost:hover { background: rgba(34,211,238,.15); color: var(--text); }

        /* ── SECTION SHARED ── */
        section { padding: 72px 0; }
        .section-label {
            text-align: center; font-size: 12px; font-weight: 800;
            letter-spacing: .14em; text-transform: uppercase;
            color: var(--aqua); margin-bottom: 12px;
        }
        .section-title {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(26px, 5vw, 38px); text-align: center;
            background: linear-gradient(135deg, var(--text) 30%, var(--muted));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 14px;
        }
        .section-sub {
            text-align: center; color: var(--dim);
            font-size: 16.5px; line-height: 1.7;
            max-width: 560px; margin: 0 auto 52px;
        }
        .divider { height: 1px; background: linear-gradient(90deg, transparent, var(--border), transparent); margin: 0; }

        /* ── SCROLL REVEAL ── */
        [data-reveal] { opacity: 0; transform: translateY(26px); transition: opacity .7s ease var(--rd, 0s), transform .7s ease var(--rd, 0s); }
        [data-reveal].in { opacity: 1; transform: none; }

        /* ── HERO ── */
        .hero { padding: 84px 0 96px; }
        .hero-grid {
            display: grid; grid-template-columns: 1.08fr .92fr;
            gap: 48px; align-items: center;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(34,211,238,.18);
            border: 1.5px solid rgba(34,211,238,.4);
            border-radius: 999px; padding: 6px 16px;
            font-size: 13px; font-weight: 700; color: var(--aqua);
            margin-bottom: 26px; letter-spacing: .03em;
        }
        .hero h1 {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(34px, 5.2vw, 54px); line-height: 1.14;
            background: linear-gradient(135deg, #ecfeff 0%, var(--aqua) 45%, #fcd34d 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 20px;
        }
        .hero-sub { font-size: 18px; line-height: 1.75; color: var(--muted); max-width: 540px; margin-bottom: 34px; }
        .hero-sub strong { color: var(--text); }
        .hero-cta { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .hero-chips { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 34px; }
        .chip {
            padding: 8px 16px; border-radius: 999px; font-weight: 700; font-size: 13.5px;
            background: rgba(12,36,64,.7); border: 1.5px solid var(--border); color: var(--muted);
            animation: floatPill var(--fp, 4s) ease-in-out infinite var(--fpd, 0s);
        }
        @keyframes floatPill { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }

        /* Smooth's stage */
        .hero-stage { position: relative; display: flex; justify-content: center; padding: 20px 0; }
        .stage-glow {
            position: absolute; top: 50%; left: 50%; width: 380px; height: 380px;
            transform: translate(-50%, -50%); border-radius: 50%;
            background: radial-gradient(circle, rgba(34,211,238,.28) 0%, rgba(34,211,238,0) 68%);
            animation: glowPulse 5s ease-in-out infinite;
        }
        @keyframes glowPulse { 0%,100% { opacity: .7; transform: translate(-50%,-50%) scale(1); } 50% { opacity: 1; transform: translate(-50%,-50%) scale(1.08); } }
        .stage-ring {
            position: absolute; top: 50%; left: 50%; width: 300px; height: 300px;
            transform: translate(-50%, -50%); border-radius: 50%;
            border: 1.5px dashed rgba(103,232,249,.4);
            animation: slowSpin 40s linear infinite;
        }
        @keyframes slowSpin { from { transform: translate(-50%,-50%) rotate(0deg); } to { transform: translate(-50%,-50%) rotate(360deg); } }
        .stage-smooth {
            position: relative; z-index: 2; width: 280px; max-width: 64vw; height: auto;
            filter: drop-shadow(0 18px 34px rgba(0,0,0,.45));
            animation: smoothBob 4.4s ease-in-out infinite;
        }
        @keyframes smoothBob { 0%,100% { transform: translateY(0) rotate(-1.2deg); } 50% { transform: translateY(-14px) rotate(1.2deg); } }
        .stage-bubble {
            position: absolute; z-index: 3; top: 6px; right: 2%; max-width: 230px;
            background: #f0fbff; color: #0b2a4a; font-weight: 700; font-size: 14.5px; line-height: 1.45;
            border-radius: 18px; border-bottom-left-radius: 4px;
            padding: 12px 16px; box-shadow: 0 10px 26px rgba(0,0,0,.35);
            transition: opacity .4s ease, transform .4s ease;
        }
        .stage-bubble::after {
            content: ''; position: absolute; left: 14px; bottom: -9px;
            border: 10px solid transparent; border-top-color: #f0fbff; border-bottom: 0;
        }
        .stage-bubble.swap { opacity: 0; transform: translateY(8px); }
        .stage-spark { position: absolute; color: var(--gold-light); z-index: 1; animation: twinkle 3s ease-in-out infinite; }
        .stage-spark.s1 { top: 18%; left: 8%; font-size: 18px; }
        .stage-spark.s2 { bottom: 12%; right: 6%; font-size: 24px; animation-delay: -1.2s; }
        .stage-spark.s3 { top: 44%; left: -2%; font-size: 14px; animation-delay: -2.1s; }

        /* ── HERO JUMBOTRON (auto-rotating messages) ── */
        .jumbotron { position: relative; outline: none; }
        .jumbo-track { display: grid; }
        .jumbo-slide {
            grid-area: 1 / 1;
            opacity: 0; transform: translateY(16px);
            transition: opacity .55s ease, transform .55s ease;
            pointer-events: none; visibility: hidden;
        }
        .jumbo-slide.is-active { opacity: 1; transform: none; pointer-events: auto; visibility: visible; }
        .jumbo-kicker {
            display: block; font-size: 12.5px; font-weight: 800; letter-spacing: .12em;
            text-transform: uppercase; color: var(--gold); margin-bottom: 12px;
        }
        .jumbo-title {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(28px, 4.4vw, 46px); line-height: 1.16;
            background: linear-gradient(135deg, #ecfeff 0%, var(--aqua) 45%, #fcd34d 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; margin-bottom: 16px;
        }
        .jumbo-sub { font-size: 17px; line-height: 1.7; color: var(--muted); max-width: 540px; margin-bottom: 30px; }
        .jumbo-sub strong { color: var(--text); }
        .jumbo-dots { display: flex; gap: 9px; margin: 26px 0 4px; }
        .jumbo-dot {
            width: 12px; height: 12px; border-radius: 50%; padding: 0; cursor: pointer;
            background: rgba(103,232,249,.25); border: 1.5px solid rgba(103,232,249,.45);
            position: relative; transition: background .2s, border-color .2s;
        }
        .jumbo-dot:hover { background: rgba(103,232,249,.45); }
        .jumbo-dot.is-active { border-color: var(--gold); }
        .jumbo-dot::after {
            content: ''; position: absolute; inset: 2px; border-radius: 50%;
            background: var(--gold); transform: scale(0);
        }
        .jumbo-dot.is-active::after { animation: dotFill var(--jumbo-dur, 5.4s) linear forwards; }
        .jumbotron.is-paused .jumbo-dot.is-active::after { animation-play-state: paused; }
        @keyframes dotFill { from { transform: scale(0); } to { transform: scale(1); } }
        .jumbo-hint { font-size: 11.5px; color: var(--dim); font-weight: 700; letter-spacing: .04em; }

        /* ── ANIMATED WAVE DIVIDER ── */
        .wave-wrap { position: relative; height: 70px; overflow: hidden; margin-top: -70px; }
        .wave-wrap svg { position: absolute; bottom: 0; left: 0; width: 200%; height: 100%; animation: waveDrift 14s linear infinite; }
        @keyframes waveDrift { from { transform: translateX(0); } to { transform: translateX(-50%); } }

        /* ── MEET SMOOTH ── */
        .meet-grid { display: grid; grid-template-columns: .9fr 1.1fr; gap: 48px; align-items: center; }
        .meet-figure { position: relative; display: flex; justify-content: center; }
        .meet-figure img {
            width: 300px; max-width: 70vw; height: auto;
            filter: drop-shadow(0 14px 28px rgba(0,0,0,.4));
            animation: smoothBob 5.2s ease-in-out infinite;
        }
        .meet-quote {
            background: var(--card); border: 1.5px solid var(--border);
            border-radius: 20px; padding: 20px 24px; margin-bottom: 24px;
            font-size: 16.5px; line-height: 1.7; color: var(--text);
            position: relative;
        }
        .meet-quote::before { content: '“'; position: absolute; top: -18px; left: 14px; font-family: 'Fredoka One', cursive; font-size: 44px; color: var(--aqua); }
        .meet-quote strong { color: var(--aqua); }
        .meet-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .meet-card {
            background: var(--card); border: 1.5px solid var(--border);
            border-radius: 16px; padding: 18px 14px; text-align: center;
            transition: border-color .25s, transform .25s;
        }
        .meet-card:hover { border-color: rgba(34,211,238,.6); transform: translateY(-4px); }
        .meet-card .m-icon { font-size: 26px; display: block; margin-bottom: 10px; }
        .meet-card h4 { font-family: 'Fredoka One', cursive; font-size: 14.5px; color: var(--aqua); margin-bottom: 6px; }
        .meet-card p { font-size: 12.5px; line-height: 1.55; color: var(--dim); }

        /* ── PILLARS ── */
        .spotlight { display: grid; grid-template-columns: 1fr 1fr; gap: 44px; align-items: center; margin-bottom: 64px; }
        .spotlight.flip .spot-visual { order: -1; }
        .spot-copy .kicker {
            display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .12em;
            text-transform: uppercase; color: var(--gold); margin-bottom: 10px;
        }
        .spot-copy h3 { font-family: 'Fredoka One', cursive; font-size: clamp(22px, 3.4vw, 30px); line-height: 1.2; margin-bottom: 14px; }
        .spot-copy p { font-size: 16px; line-height: 1.75; color: var(--muted); margin-bottom: 16px; }
        .spot-copy .worry {
            font-style: italic; color: var(--dim); font-size: 14.5px;
            border-left: 3px solid rgba(246,183,30,.5); padding-left: 14px; margin-bottom: 18px;
        }
        .spot-points { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .spot-points li { display: flex; gap: 10px; align-items: baseline; font-size: 14.5px; font-weight: 700; color: var(--text); }
        .spot-points li::before { content: '✓'; color: var(--gold); font-weight: 800; }

        /* mock cards (product visuals) */
        .spot-visual { position: relative; }
        .mock-card {
            background: var(--card2); border: 1.5px solid var(--border);
            border-radius: 20px; padding: 20px 22px;
            box-shadow: 0 24px 60px rgba(0,0,0,.38);
            position: relative;
        }
        .mock-card + .mock-float {
            position: absolute; bottom: -18px; right: -8px;
            background: var(--card); border: 1.5px solid rgba(246,183,30,.5);
            border-radius: 14px; padding: 10px 16px; font-size: 13px; font-weight: 800;
            color: var(--gold-light); box-shadow: 0 10px 24px rgba(0,0,0,.35);
            animation: floatPill 4.6s ease-in-out infinite;
        }
        .mock-head {
            font-family: 'Fredoka One', cursive; font-size: 14.5px; color: var(--aqua);
            padding-bottom: 12px; margin-bottom: 14px; border-bottom: 1.5px dashed rgba(103,232,249,.25);
        }
        .mock-row { display: grid; grid-template-columns: 118px 1fr 44px; gap: 10px; align-items: center; margin-bottom: 12px; font-size: 13.5px; font-weight: 700; color: var(--muted); }
        .bar { height: 10px; border-radius: 999px; background: rgba(103,232,249,.12); overflow: hidden; }
        .bar i { display: block; height: 100%; width: 0; border-radius: 999px; background: linear-gradient(90deg, #22d3ee, #f6b71e); transition: width 1.3s ease .35s; }
        .mock-card.in .bar i { width: var(--w); }
        .mock-row b { font-family: 'Fredoka One', cursive; color: var(--aqua); text-align: right; font-size: 13px; }
        .mock-chip {
            margin-top: 14px; display: flex; gap: 8px; align-items: baseline;
            background: rgba(13,148,136,.16); border: 1.5px solid rgba(13,148,136,.45);
            border-radius: 12px; padding: 10px 14px; font-size: 12.5px; line-height: 1.5; color: #5eead4; font-weight: 700;
        }
        .rechart { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .rechart li {
            display: grid; grid-template-columns: 42px 1fr auto; gap: 10px; align-items: baseline;
            background: rgba(103,232,249,.06); border: 1.5px solid rgba(103,232,249,.15);
            border-radius: 12px; padding: 10px 14px; font-size: 13.5px;
        }
        .rechart .day { font-family: 'Fredoka One', cursive; color: var(--aqua); font-size: 13px; }
        .rechart .what { color: var(--text); font-weight: 700; }
        .rechart .what small { display: block; font-weight: 600; color: var(--dim); font-size: 12px; }
        .rechart .mv { font-size: 11.5px; font-weight: 800; border-radius: 999px; padding: 3px 10px; white-space: nowrap; }
        .mv.moved { background: rgba(246,183,30,.18); color: var(--gold-light); }
        .mv.kept   { background: rgba(13,148,136,.2); color: #5eead4; }
        .mv.added  { background: rgba(34,211,238,.18); color: var(--aqua); }
        .mock-note { margin-top: 12px; font-size: 12px; color: var(--dim); text-align: center; }

        /* pillar grid */
        .pillars-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
        .pillar {
            background: var(--card); border: 1.5px solid var(--border);
            border-radius: 20px; padding: 26px 22px;
            transition: border-color .25s, transform .25s;
            position: relative; overflow: hidden;
        }
        .pillar:hover { border-color: rgba(34,211,238,.6); transform: translateY(-5px); }
        .pillar-icon {
            width: 52px; height: 52px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; margin-bottom: 16px;
            background: linear-gradient(135deg, rgba(34,211,238,.18), rgba(246,183,30,.14));
            border: 1.5px solid rgba(103,232,249,.3);
        }
        .pillar h3 { font-family: 'Fredoka One', cursive; font-size: 17.5px; margin-bottom: 9px; color: var(--text); line-height: 1.3; }
        .pillar p { font-size: 14px; line-height: 1.65; color: var(--dim); }
        .pillar .accent { margin-top: 16px; min-height: 26px; }
        .pill-tag {
            display: inline-block; padding: 4px 12px; border-radius: 999px;
            font-size: 11.5px; font-weight: 800; letter-spacing: .04em;
            background: rgba(246,183,30,.16); color: var(--gold-light);
            border: 1.5px solid rgba(246,183,30,.45);
            animation: shimmer 3s ease-in-out infinite;
        }
        @keyframes shimmer { 0%,100% { box-shadow: 0 0 0 rgba(246,183,30,0); } 50% { box-shadow: 0 0 18px rgba(246,183,30,.45); } }
        .island-dots { display: flex; align-items: center; gap: 0; }
        .island-dots span {
            width: 14px; height: 14px; border-radius: 50%;
            background: rgba(103,232,249,.25); border: 1.5px solid rgba(103,232,249,.45);
            position: relative; flex-shrink: 0;
        }
        .island-dots span:not(:last-child) { margin-right: 22px; }
        .island-dots span:not(:last-child)::after {
            content: ''; position: absolute; top: 50%; left: 100%; width: 22px; height: 1.5px;
            background: rgba(103,232,249,.35);
        }
        .island-dots span.lit { background: var(--gold); border-color: var(--gold-light); animation: islandPulse 2.4s ease-in-out infinite; }
        .island-dots span.lit:nth-child(2) { animation-delay: .3s; }
        .island-dots span.lit:nth-child(3) { animation-delay: .6s; }
        @keyframes islandPulse { 0%,100% { transform: scale(1); box-shadow: 0 0 0 rgba(246,183,30,0); } 50% { transform: scale(1.25); box-shadow: 0 0 14px rgba(246,183,30,.7); } }
        .strand-chips { display: flex; flex-wrap: wrap; gap: 7px; }
        .strand-chips span {
            font-size: 11.5px; font-weight: 800; border-radius: 999px; padding: 4px 11px;
            background: rgba(34,211,238,.12); color: var(--aqua); border: 1.5px solid rgba(34,211,238,.35);
        }
        .sun-rise { font-size: 22px; display: inline-block; animation: sunPulse 3.4s ease-in-out infinite; }
        @keyframes sunPulse { 0%,100% { transform: scale(1) rotate(-4deg); } 50% { transform: scale(1.15) rotate(6deg); } }
        .star-spark { font-size: 20px; display: inline-block; margin-right: 6px; }
        .star-spark:nth-child(2) { animation-delay: -.5s; } .star-spark:nth-child(3) { animation-delay: -1s; }
        .paper-stamp {
            display: inline-block; font-family: 'Fredoka One', cursive; font-size: 13px;
            color: #5eead4; border: 2px dashed rgba(94,234,212,.55); border-radius: 10px;
            padding: 4px 12px; transform: rotate(-4deg);
        }

        /* ── QUICK FACTS ── */
        .facts-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; text-align: center; }
        .fact-card { background: var(--card); border: 1.5px solid var(--border); border-radius: 18px; padding: 28px 16px; }
        .fact-num {
            font-family: 'Fredoka One', cursive; font-size: 36px; display: block; margin-bottom: 6px;
            background: linear-gradient(135deg, var(--aqua), var(--gold-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .fact-label { font-size: 13px; font-weight: 700; color: var(--dim); }

        /* ── HOW IT WORKS ── */
        .steps { display: flex; flex-direction: column; gap: 20px; }
        .step {
            display: flex; align-items: flex-start; gap: 20px;
            background: var(--card); border: 1.5px solid var(--border);
            border-radius: 18px; padding: 24px 26px; transition: border-color .25s;
        }
        .step:hover { border-color: rgba(34,211,238,.55); }
        .step-num {
            width: 42px; height: 42px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--teal-deep), var(--gold));
            border-radius: 13px; display: flex; align-items: center; justify-content: center;
            font-family: 'Fredoka One', cursive; font-size: 19px; color: white;
        }
        .step-body h3 { font-family: 'Fredoka One', cursive; font-size: 17.5px; margin-bottom: 5px; color: var(--text); }
        .step-body p { font-size: 14.5px; line-height: 1.65; color: var(--dim); }

        /* ── CTA BANNER ── */
        .cta-banner {
            background: linear-gradient(135deg, rgba(34,211,238,.25), rgba(246,183,30,.2));
            border: 1.5px solid rgba(34,211,238,.4);
            border-radius: 24px; padding: 56px 36px; text-align: center;
            position: relative; overflow: hidden;
        }
        .cta-banner::before { content: '⚓'; position: absolute; left: 1.2rem; top: .6rem; font-size: 2.6rem; opacity: .12; }
        .cta-banner::after { content: '🐢'; position: absolute; right: 1.2rem; bottom: .4rem; font-size: 2.8rem; opacity: .16; }
        .cta-banner h2 {
            font-family: 'Fredoka One', cursive; font-size: clamp(24px, 5vw, 36px); margin-bottom: 14px;
            background: linear-gradient(135deg, #ecfeff, var(--aqua));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .cta-banner p { color: var(--muted); font-size: 16.5px; margin-bottom: 30px; line-height: 1.65; max-width: 520px; margin-left: auto; margin-right: auto; }

        /* ── FOOTER ── */
        footer { border-top: 1px solid var(--border); padding: 34px 24px; text-align: center; font-size: 13px; color: var(--dim); }
        footer a { color: var(--muted); text-decoration: none; }
        footer a:hover { color: var(--aqua); }

        /* ── RESPONSIVE ── */
        @media (max-width: 940px) {
            .hero-grid, .meet-grid, .spotlight { grid-template-columns: 1fr; gap: 36px; }
            .spotlight.flip .spot-visual { order: 0; }
            .pillars-grid { grid-template-columns: repeat(2, 1fr); }
            .facts-row { grid-template-columns: repeat(2, 1fr); }
            .hero { padding: 56px 0 72px; }
            .stage-smooth { width: 230px; }
        }
        @media (max-width: 620px) {
            .nav-anchor { display: none; }
            .pillars-grid { grid-template-columns: 1fr; }
            .meet-cards { grid-template-columns: 1fr; }
            .mock-row { grid-template-columns: 96px 1fr 40px; }
        }

        /* ── REDUCED MOTION ── */
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation: none !important; transition: none !important; }
            [data-reveal] { opacity: 1 !important; transform: none !important; }
            .mock-card .bar i { width: var(--w); }
            .stage-bubble { position: static; margin-top: 18px; max-width: 300px; }
            .jumbo-slide { transition: none !important; }
            .jumbo-dot.is-active::after { transform: scale(1); }
        }
    </style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>
<div id="stars" aria-hidden="true"></div>

<div class="page">

    <!-- NAV -->
    <nav>
        <div class="nav-inner">
            <a class="nav-brand" href="/">
                <span class="nav-brand-icon">⛵</span>
                SmoothSeas
            </a>
            <div class="nav-links">
                @auth
                    <span class="nav-user">Hi, {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }} 👋</span>
                    <a class="btn-nav-ghost" href="{{ $homeUrl }}">My Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="nav-logout">
                        @csrf
                        <button type="submit" class="btn-nav-primary">Log out</button>
                    </form>
                @else
                    <a class="nav-anchor" href="#meet-smooth">Meet Smooth</a>
                    <a class="nav-anchor" href="#for-parents">For Parents</a>
                    <a class="nav-anchor" href="#how-it-works">How it works</a>
                    <a class="btn-nav-ghost" href="{{ route('login') }}">Sign In</a>
                    <a class="btn-nav-primary" href="{{ route('register') }}">Get Started</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-grid">
                <div data-reveal>
                    <div class="hero-badge">🇹🇹 Now charting: SEA 2027 — for Caribbean families</div>

                    <div class="jumbotron" id="jumbotron" tabindex="0" role="region" aria-roledescription="carousel" aria-label="What SmoothSeas does for your family">
                        <div class="jumbo-track">
                            <div class="jumbo-slide is-active" role="group" aria-roledescription="slide" aria-label="1 of 5">
                                <span class="jumbo-kicker">For parents who want the truth, weekly</span>
                                <h1 class="jumbo-title">You'll never have to guess how your child is doing in English again.</h1>
                                <p class="jumbo-sub">
                                    SmoothSeas plans the whole ELA journey, adjusts it <strong>every single day</strong>,
                                    and shows you — <strong>every week</strong> — exactly where they stand.
                                </p>
                            </div>
                            <div class="jumbo-slide" role="group" aria-roledescription="slide" aria-label="2 of 5" aria-hidden="true">
                                <span class="jumbo-kicker">Control &amp; adaptability</span>
                                <h2 class="jumbo-title">The curriculum plans itself — around your child.</h2>
                                <p class="jumbo-sub">
                                    Breezed through? They advance. Struggled? It circles back gently.
                                    And when life happens, <strong>pause and resume</strong> with one tap.
                                </p>
                            </div>
                            <div class="jumbo-slide" role="group" aria-roledescription="slide" aria-label="3 of 5" aria-hidden="true">
                                <span class="jumbo-kicker">Enjoyment</span>
                                <h2 class="jumbo-title">They'll ask to log in. Really.</h2>
                                <p class="jumbo-sub">
                                    A gamified <strong>voyage map</strong> of glowing islands, streaks and celebrations —
                                    with a turtle named <strong>Smooth</strong> at the helm.
                                </p>
                            </div>
                            <div class="jumbo-slide" role="group" aria-roledescription="slide" aria-label="4 of 5" aria-hidden="true">
                                <span class="jumbo-kicker">Convenience &amp; coverage</span>
                                <h2 class="jumbo-title">Everything they need, in one harbour.</h2>
                                <p class="jumbo-sub">
                                    <strong>Lessons, tutorials and practice</strong> — covering every ELA strand of the
                                    SEA: grammar, vocabulary, reading comprehension, writing.
                                </p>
                            </div>
                            <div class="jumbo-slide" role="group" aria-roledescription="slide" aria-label="5 of 5" aria-hidden="true">
                                <span class="jumbo-kicker">Effectiveness</span>
                                <h2 class="jumbo-title">Twenty focused minutes a day.</h2>
                                <p class="jumbo-sub">
                                    A short <strong>daily study plan</strong> anchored by a <strong>morning vocabulary
                                    ritual</strong> and daily <strong>reading assignments</strong> — small sails that compound.
                                </p>
                            </div>
                        </div>
                        <div class="jumbo-dots" role="tablist" aria-label="Choose a message">
                            <button type="button" class="jumbo-dot is-active" data-i="0" aria-label="Message 1: visibility"></button>
                            <button type="button" class="jumbo-dot" data-i="1" aria-label="Message 2: adaptability"></button>
                            <button type="button" class="jumbo-dot" data-i="2" aria-label="Message 3: enjoyment"></button>
                            <button type="button" class="jumbo-dot" data-i="3" aria-label="Message 4: convenience"></button>
                            <button type="button" class="jumbo-dot" data-i="4" aria-label="Message 5: effectiveness"></button>
                        </div>
                    </div>

                    <div class="hero-cta">
                        @auth
                            <a class="btn-primary" href="{{ $homeUrl }}">Go to your dashboard →</a>
                        @else
                            <a class="btn-primary" href="{{ route('register') }}">Start the voyage ⛵</a>
                            <a class="btn-ghost" href="{{ route('login') }}">Sign In</a>
                        @endauth
                    </div>

                    <div class="hero-chips">
                        <span class="chip" style="--fp:4.2s">📖 Reading</span>
                        <span class="chip" style="--fp:3.8s; --fpd:-.6s">✏️ Grammar</span>
                        <span class="chip" style="--fp:4.5s; --fpd:-1.2s">🗣️ Vocabulary</span>
                        <span class="chip" style="--fp:4s; --fpd:-1.8s">✍️ Writing</span>
                    </div>
                </div>

                <div class="hero-stage" data-reveal style="--rd:.15s">
                    <div class="stage-glow"></div>
                    <div class="stage-ring"></div>
                    <span class="stage-spark s1">✦</span>
                    <span class="stage-spark s2">✦</span>
                    <span class="stage-spark s3">✦</span>
                    <img class="stage-smooth" src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle, waving hello">
                    <div class="stage-bubble" id="smoothBubble" aria-live="polite">Ahoy! Ready for today's sail?</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ANIMATED WAVE -->
    <div class="wave-wrap" aria-hidden="true">
        <svg viewBox="0 0 2880 70" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C120,65 240,65 360,40 C480,15 600,15 720,40 C840,65 960,65 1080,40 C1200,15 1320,15 1440,40 C1560,65 1680,65 1800,40 C1920,15 2040,15 2160,40 C2280,65 2400,65 2520,40 C2640,15 2760,15 2880,40 L2880,70 L0,70 Z" fill="rgba(34,211,238,.09)"/>
            <path d="M0,52 C160,70 320,70 480,52 C640,34 800,34 960,52 C1120,70 1280,70 1440,52 C1600,34 1760,34 1920,52 C2080,70 2240,70 2400,52 C2560,34 2720,34 2880,52 L2880,70 L0,70 Z" fill="rgba(103,232,249,.07)"/>
        </svg>
    </div>

    <!-- MEET SMOOTH -->
    <section id="meet-smooth">
        <div class="container">
            <p class="section-label" data-reveal>Meet the captain</p>
            <h2 class="section-title" data-reveal style="--rd:.08s">Your child's study buddy is a turtle named Smooth.</h2>
            <p class="section-sub" data-reveal style="--rd:.16s">
                He's not a mascot bolted onto a test bank — he's the companion on every screen,
                in every lesson, all the way to the SEA.
            </p>

            <div class="meet-grid">
                <div class="meet-figure" data-reveal>
                    <img src="{{ asset('images/voyage/companion/smooth-cheer.webp') }}" alt="Smooth the turtle, cheering">
                </div>
                <div data-reveal style="--rd:.15s">
                    <div class="meet-quote">
                        <strong>Ahoy! I'm Smooth.</strong> I sail with your child through every lesson. When they miss
                        a rule, we take it again — together, word by word, until it clicks. And I never,
                        ever make anyone feel small.
                    </div>
                    <div class="meet-cards">
                        <div class="meet-card">
                            <span class="m-icon">🧭</span>
                            <h4>He shows them the way</h4>
                            <p>A friendly how-to appears the first time they open any screen — then never nags again.</p>
                        </div>
                        <div class="meet-card">
                            <span class="m-icon">💛</span>
                            <h4>He explains, never scolds</h4>
                            <p>Miss a rule twice? Smooth re-teaches that exact rule until it sticks — no red pen, no sighs.</p>
                        </div>
                        <div class="meet-card">
                            <span class="m-icon">🎉</span>
                            <h4>He celebrates every win</h4>
                            <p>Streaks, mastery stars, end-of-lesson victories — the big ones and the small ones.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- FOR PARENTS -->
    <section id="for-parents">
        <div class="container">
            <p class="section-label" data-reveal>For parents</p>
            <h2 class="section-title" data-reveal style="--rd:.08s">The worries you carry — handled.</h2>
            <p class="section-sub" data-reveal style="--rd:.16s">
                You don't need another app to police. You need to stop guessing.
                Here is what SmoothSeas takes off your plate.
            </p>

            <!-- Visibility -->
            <div class="spotlight">
                <div class="spot-visual" data-reveal>
                    <div class="mock-card">
                        <div class="mock-head">📎 Your weekly report — Week 6</div>
                        <div class="mock-row"><span>📖 Reading</span><div class="bar"><i style="--w:82%"></i></div><b>82%</b></div>
                        <div class="mock-row"><span>✏️ Grammar</span><div class="bar"><i style="--w:64%"></i></div><b>64%</b></div>
                        <div class="mock-row"><span>🗣️ Vocabulary</span><div class="bar"><i style="--w:91%"></i></div><b>91%</b></div>
                        <div class="mock-chip">🔁 Re-teach completed: Plurals (consonant + y → ies) — tried gently, then mastered</div>
                    </div>
                    <div class="mock-float">✅ Voyage on pace</div>
                </div>
                <div class="spot-copy" data-reveal style="--rd:.12s">
                    <span class="kicker">Visibility</span>
                    <h3>You'll always know where she stands.</h3>
                    <p class="worry">"How was school today?" — "Fine." …That's all I ever get.</p>
                    <p>
                        Every week, a clear picture waits in your Parent Portal: what they conquered, what they're
                        still working on, and where the voyage is headed. And when a rule needs extra work, the
                        plan quietly reroutes through a gentle re-teach — <strong>and you see it</strong>, so nothing
                        surprises you at term's end.
                    </p>
                    <ul class="spot-points">
                        <li>Weekly progress picture, strand by strand</li>
                        <li>Every re-teach visible to you, with the rule named</li>
                        <li>Honest pace — never a rosy spin</li>
                    </ul>
                </div>
            </div>

            <!-- Control & adaptability -->
            <div class="spotlight flip">
                <div class="spot-visual" data-reveal>
                    <div class="mock-card">
                        <div class="mock-head">🗺️ The voyage — recharted overnight</div>
                        <ul class="rechart">
                            <li><span class="day">Mon</span><span class="what">Plurals practice <small>needs one more win</small></span><span class="mv moved">→ Tue</span></li>
                            <li><span class="day">Tue</span><span class="what">Vocabulary sail</span><span class="mv kept">kept</span></li>
                            <li><span class="day">Wed</span><span class="what">Reading: tides &amp; currents</span><span class="mv kept">kept</span></li>
                            <li><span class="day">Thu</span><span class="what">Re-teach: plurals <small>their one wobbly rule</small></span><span class="mv added">+ added</span></li>
                        </ul>
                        <div class="mock-note">Paused for the school fair? It recharts around that too.</div>
                    </div>
                </div>
                <div class="spot-copy" data-reveal style="--rd:.12s">
                    <span class="kicker">Control &amp; adaptability</span>
                    <h3>The curriculum plans itself — around her.</h3>
                    <p class="worry">I bought workbooks. We did two pages. Then life happened.</p>
                    <p>
                        You don't build the timetable — the platform does. It charts the whole ELA voyage from a
                        friendly diagnostic, then re-plans <strong>every single day</strong> around their pace.
                        Breezed through? They advance. Struggled? It circles back. And when life happens,
                        you can pause and resume with one tap — no guilt, no catching-up cliff.
                    </p>
                    <ul class="spot-points">
                        <li>A full curriculum, planned for her</li>
                        <li>Re-charted daily around pace and misses</li>
                        <li>Pause &amp; resume when family life demands it</li>
                    </ul>
                </div>
            </div>

            <!-- The other six pillars -->
            <div class="pillars-grid">
                <div class="pillar" data-reveal>
                    <div class="pillar-icon">🏝️</div>
                    <h3>Enjoyment — they'll ask to sail.</h3>
                    <p>
                        The lessons live on a gamified voyage map — glowing islands to conquer, streaks to keep,
                        celebrations on every win. They'll want to log in; you'll want them to.
                    </p>
                    <div class="accent"><div class="island-dots"><span class="lit"></span><span class="lit"></span><span class="lit"></span><span></span></div></div>
                </div>

                <div class="pillar" data-reveal style="--rd:.08s">
                    <div class="pillar-icon">⚓</div>
                    <h3>Lessons, tutorials and practice — one harbour.</h3>
                    <p>
                        Everything she needs is in one place: interactive lessons, guided tutorials and adaptive
                        practice. No worksheet hunting, no six different apps, no lost logins.
                    </p>
                    <div class="accent strand-chips"><span>📖 Lessons</span><span>🧭 Tutorials</span><span>🎯 Practice</span></div>
                </div>

                <div class="pillar" data-reveal style="--rd:.16s">
                    <div class="pillar-icon">🧭</div>
                    <h3>Coverage — all of ELA, one voyage.</h3>
                    <p>
                        Grammar, Vocabulary, Reading comprehension and Writing — the strands they'll meet on the
                        SEA, taught as one connected journey instead of disconnected drills.
                    </p>
                    <div class="accent strand-chips"><span>✏️ Grammar</span><span>🗣️ Vocabulary</span><span>📖 Reading</span><span>✍️ Writing</span></div>
                </div>

                <div class="pillar" data-reveal>
                    <div class="pillar-icon">☀️</div>
                    <h3>Effectiveness — a daily rhythm that compounds.</h3>
                    <p>
                        A short daily study plan they can actually finish — anchored by a morning vocabulary ritual
                        and daily reading assignments. Twenty focused minutes beat two exhausting hours.
                    </p>
                    <div class="accent"><span class="sun-rise">☀️</span></div>
                </div>

                <div class="pillar" data-reveal style="--rd:.08s">
                    <div class="pillar-icon">🏆</div>
                    <h3>Reinforcement — her effort pays off at home.</h3>
                    <p>
                        You set the treasure. Streaks and mastery stars become the currency for the rewards
                        you choose — the beach trip, the new book, the extra story at bedtime.
                    </p>
                    <div class="accent"><span class="star-spark">⭐</span><span class="star-spark">⭐</span><span class="star-spark">🎁</span></div>
                </div>

                <div class="pillar" data-reveal style="--rd:.16s">
                    <div class="pillar-icon">🏫</div>
                    <h3>Consolidation — we work with her school.</h3>
                    <p>
                        Graded school papers join the journal, and what their teacher sees in the classroom
                        weighs into the daily plan. One picture of your child — not two.
                    </p>
                    <div class="accent">
                        <span class="paper-stamp">Graded · B+</span>
                        <span class="pill-tag" style="margin-left:8px">Coming in the MVP</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- QUICK FACTS -->
    <section>
        <div class="container">
            <div class="facts-row">
                <div class="fact-card" data-reveal>
                    <span class="fact-num">4</span>
                    <span class="fact-label">ELA strands, one voyage</span>
                </div>
                <div class="fact-card" data-reveal style="--rd:.08s">
                    <span class="fact-num">~20 min</span>
                    <span class="fact-label">their daily sail</span>
                </div>
                <div class="fact-card" data-reveal style="--rd:.16s">
                    <span class="fact-num">1 / wk</span>
                    <span class="fact-label">your progress report</span>
                </div>
                <div class="fact-card" data-reveal style="--rd:.24s">
                    <span class="fact-num">∞</span>
                    <span class="fact-label">patience, from Smooth</span>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- HOW IT WORKS -->
    <section id="how-it-works">
        <div class="container container-narrow">
            <p class="section-label" data-reveal>How it works</p>
            <h2 class="section-title" data-reveal style="--rd:.08s">From sign-up to SEA day, in three steps.</h2>
            <p class="section-sub" data-reveal style="--rd:.16s">You set the compass once. Smooth handles the sailing.</p>

            <div class="steps">
                <div class="step" data-reveal>
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <h3>Set the compass</h3>
                        <p>Create your parent account, add your child, and choose their exam year. Two minutes — no credit card, no consultation.</p>
                    </div>
                </div>
                <div class="step" data-reveal style="--rd:.1s">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <h3>Smooth charts the voyage</h3>
                        <p>A friendly diagnostic finds where they truly are — not where the syllabus assumes they are — and the whole ELA curriculum is planned from there.</p>
                    </div>
                </div>
                <div class="step" data-reveal style="--rd:.2s">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <h3>Daily sails, weekly reports</h3>
                        <p>About twenty focused minutes a day — a lesson, a tutorial, some practice. You watch the horizon from the Parent Portal, with a fresh report every week.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section>
        <div class="container">
            <div class="cta-banner" data-reveal>
                @auth
                    <h2>Welcome back aboard! 🌟</h2>
                    <p>Pick up right where you left off — the tide is waiting.</p>
                    <a class="btn-primary" href="{{ $homeUrl }}">Go to your dashboard →</a>
                @else
                    <h2>Give your child a smoother SEA.</h2>
                    <p>
                        The voyage to SEA 2027 starts with one calm tap — and a turtle who never
                        lets them feel lost.
                    </p>
                    <a class="btn-primary" href="{{ route('register') }}">Start the voyage ⛵</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>
            © {{ date("Y") }} SmoothSeas &nbsp;·&nbsp;
            Built with ❤️ in Trinidad &amp; Tobago &nbsp;·&nbsp;
            @auth
                <a href="{{ $homeUrl }}">My Dashboard</a>
            @else
                <a href="{{ route('login') }}">Sign In</a>
            @endauth
        </p>
    </footer>

</div>

<script>
    (function () {
        var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Star field
        var stars = document.getElementById('stars');
        for (var i = 0; i < 90; i++) {
            var s = document.createElement('span');
            s.className = 'star';
            var size = Math.random() * 2.2 + 0.6;
            s.style.cssText =
                'left:' + (Math.random() * 100) + '%;' +
                'top:' + (Math.random() * 100) + '%;' +
                'width:' + size + 'px;height:' + size + 'px;' +
                '--d:' + (Math.random() * 5 + 2.5) + 's;' +
                '--dl:-' + (Math.random() * 5) + 's;';
            stars.appendChild(s);
        }

        if (reduced) return;

        // Scroll reveal
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 });
        document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });

        // Hero jumbotron — auto-rotating core messages (LP-11)
        var jumbo = document.getElementById('jumbotron');
        if (jumbo) {
            var slides = Array.prototype.slice.call(jumbo.querySelectorAll('.jumbo-slide'));
            var dots = Array.prototype.slice.call(jumbo.querySelectorAll('.jumbo-dot'));
            var ji = 0, jtimer = null, JDUR = 5400;

            function jGo(n) {
                ji = ((n % slides.length) + slides.length) % slides.length;
                slides.forEach(function (s, i) {
                    s.classList.toggle('is-active', i === ji);
                    s.setAttribute('aria-hidden', i === ji ? 'false' : 'true');
                });
                dots.forEach(function (d, i) {
                    d.classList.toggle('is-active', i === ji);
                    d.setAttribute('aria-selected', i === ji ? 'true' : 'false');
                });
            }
            function jRestart() {
                if (jtimer) { clearInterval(jtimer); jtimer = null; }
                if (!reduced) { jtimer = setInterval(function () { jGo(ji + 1); }, JDUR); }
            }
            dots.forEach(function (d) {
                d.addEventListener('click', function () { jGo(+d.getAttribute('data-i')); jRestart(); });
            });
            jumbo.addEventListener('mouseenter', function () { jumbo.classList.add('is-paused'); if (jtimer) { clearInterval(jtimer); jtimer = null; } });
            jumbo.addEventListener('mouseleave', function () { jumbo.classList.remove('is-paused'); jRestart(); });
            jumbo.addEventListener('focusin', function () { jumbo.classList.add('is-paused'); if (jtimer) { clearInterval(jtimer); jtimer = null; } });
            jumbo.addEventListener('focusout', function () { jumbo.classList.remove('is-paused'); jRestart(); });
            jumbo.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowRight') { jGo(ji + 1); jRestart(); }
                if (e.key === 'ArrowLeft')  { jGo(ji - 1); jRestart(); }
            });
            // Swipe on touch screens
            var jx = null;
            jumbo.addEventListener('pointerdown', function (e) { jx = e.clientX; });
            jumbo.addEventListener('pointerup', function (e) {
                if (jx === null) { return; }
                var dx = e.clientX - jx;
                if (Math.abs(dx) > 40) { jGo(ji + (dx < 0 ? 1 : -1)); jRestart(); }
                jx = null;
            });
            // Don't burn cycles in a hidden tab
            document.addEventListener('visibilitychange', function () {
                if (document.hidden) { if (jtimer) { clearInterval(jtimer); jtimer = null; } }
                else { jRestart(); }
            });
            jGo(0);
            jRestart();
        }

        // Smooth's rotating greetings
        var bubble = document.getElementById('smoothBubble');
        var messages = [
            'Ahoy! Ready for today\u2019s sail?',
            'Missed one? We\u2019ll take that rule again \u2014 together.',
            'Two more words and that\u2019s a five-day streak!',
            'You\u2019re getting faster at this. I checked.',
            'Land ho! That\u2019s another island conquered.'
        ];
        var mi = 0;
        setInterval(function () {
            if (!bubble) return;
            bubble.classList.add('swap');
            setTimeout(function () {
                mi = (mi + 1) % messages.length;
                bubble.textContent = messages[mi];
                bubble.classList.remove('swap');
            }, 400);
        }, 3800);
    })();
</script>
</body>
</html>
