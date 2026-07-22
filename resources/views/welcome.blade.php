@auth
    @php($homeUrl = auth()->user()->isStudent() ? route('student.map') : route('dashboard'))
@endauth
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ForMyNieces — SEA Exam Prep for T&T Girls</title>
    <meta name="description" content="A magical study companion for SEA exam preparation. Built for primary school girls in Trinidad and Tobago.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple: #9333ea;
            --purple-light: #c084fc;
            --pink: #db2777;
            --pink-light: #f472b6;
            --bg: #0f0720;
            --card: #1a0d30;
            --card2: #140a28;
            --border: rgba(147,51,234,0.3);
            --text: #f3e8ff;
            --muted: #c4b5fd;
            --dim: rgba(196,181,253,0.6);
            --teal: #0d9488;
            --green: #16a34a;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg);
            font-family: 'Nunito', sans-serif;
            color: var(--text);
            overflow-x: hidden;
        }

        /* ── STARS ── */
        #stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .star {
            position: absolute; background: #fff; border-radius: 50%;
            animation: twinkle var(--d) ease-in-out infinite var(--dl);
        }
        @keyframes twinkle {
            0%,100% { opacity: .12; transform: scale(1); }
            50%      { opacity: .85; transform: scale(1.5); }
        }

        /* ── ORBS ── */
        .orb {
            position: fixed; border-radius: 50%;
            filter: blur(100px); pointer-events: none; z-index: 0;
        }
        .orb-1 { width: 500px; height: 500px; background: rgba(147,51,234,.18); top: -150px; left: -150px; }
        .orb-2 { width: 400px; height: 400px; background: rgba(219,39,119,.14); bottom: -100px; right: -100px; }
        .orb-3 { width: 300px; height: 300px; background: rgba(13,148,136,.1);  top: 40%;    left: 60%; }

        /* ── LAYOUT ── */
        .page { position: relative; z-index: 1; }
        .container { max-width: 800px; margin: 0 auto; padding: 0 24px; }

        /* ── NAV ── */
        nav {
            position: sticky; top: 0; z-index: 100;
            backdrop-filter: blur(16px);
            background: rgba(15,7,32,.7);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
        }
        .nav-inner {
            max-width: 800px; margin: 0 auto;
            display: flex; align-items: center; justify-content: space-between;
            height: 60px;
        }
        .nav-brand {
            display: flex; align-items: center; gap: 10px;
            font-family: 'Fredoka One', cursive; font-size: 20px;
            background: linear-gradient(135deg, var(--purple-light), var(--pink-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text; text-decoration: none;
        }
        .nav-brand-icon {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
        }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .nav-user { color: var(--text); font-size: 14px; font-weight: 700; margin-right: 4px; }
        .nav-logout { display: inline; margin: 0; }
        .btn-nav-ghost {
            padding: 8px 16px; border-radius: 999px;
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--muted); font-family: 'Nunito', sans-serif;
            font-size: 14px; font-weight: 700;
            cursor: pointer; text-decoration: none;
            transition: background .2s, color .2s;
        }
        .btn-nav-ghost:hover { background: rgba(147,51,234,.15); color: var(--text); }
        .btn-nav-primary {
            padding: 8px 18px; border-radius: 999px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border: none;
            color: white; font-family: 'Fredoka One', cursive;
            font-size: 15px; cursor: pointer;
            text-decoration: none;
            transition: opacity .2s;
        }
        .btn-nav-primary:hover { opacity: .88; }

        /* ── HERO ── */
        .hero {
            padding: 80px 0 64px;
            text-align: center;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 7px;
            background: rgba(147,51,234,.18);
            border: 1.5px solid rgba(147,51,234,.4);
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 13px; font-weight: 700;
            color: var(--purple-light);
            margin-bottom: 28px;
            letter-spacing: .03em;
        }
        .hero h1 {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(36px, 8vw, 62px);
            line-height: 1.1;
            background: linear-gradient(135deg, #e9d5ff 0%, var(--pink-light) 60%, #fde68a 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 22px;
        }
        .hero-sub {
            font-size: 18px; line-height: 1.7;
            color: var(--muted); max-width: 520px;
            margin: 0 auto 36px;
        }
        .hero-cta {
            display: flex; align-items: center; justify-content: center;
            gap: 12px; flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-block;
            padding: 14px 32px; border-radius: 999px;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            color: white; font-family: 'Fredoka One', cursive;
            font-size: 17px; text-decoration: none;
            border: none; cursor: pointer;
            transition: opacity .2s, transform .1s;
            box-shadow: 0 0 28px rgba(147,51,234,.4);
        }
        .btn-primary:hover  { opacity: .9; }
        .btn-primary:active { transform: scale(.98); }
        .btn-ghost {
            display: inline-block;
            padding: 14px 28px; border-radius: 999px;
            background: transparent;
            border: 1.5px solid rgba(147,51,234,.5);
            color: var(--muted); font-family: 'Nunito', sans-serif;
            font-size: 16px; font-weight: 700; text-decoration: none;
            transition: background .2s, color .2s;
        }
        .btn-ghost:hover { background: rgba(147,51,234,.15); color: var(--text); }

        /* hero visual */
        .hero-visual {
            margin-top: 52px;
            display: flex; justify-content: center; gap: 14px;
            flex-wrap: wrap;
        }
        .subject-pill {
            padding: 10px 20px; border-radius: 999px;
            font-weight: 700; font-size: 14px;
            display: flex; align-items: center; gap: 8px;
            animation: floatPill var(--fp, 4s) ease-in-out infinite var(--fpd, 0s);
        }
        @keyframes floatPill {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-8px); }
        }
        .pill-math        { background: rgba(13,148,136,.2); border: 1.5px solid rgba(13,148,136,.5); color: #5eead4; --fp:4.2s; }
        .pill-editing     { background: rgba(219,39,119,.18); border: 1.5px solid rgba(219,39,119,.45); color: #f472b6; --fp:3.8s; --fpd:-.6s; }
        .pill-comprehension { background: rgba(147,51,234,.2); border: 1.5px solid rgba(147,51,234,.5); color: #c084fc; --fp:4.5s; --fpd:-1.2s; }

        .exam-countdown {
            margin: 28px auto 0;
            display: inline-flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,.04);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 14px 22px;
            font-size: 15px; color: var(--muted);
        }
        .countdown-num {
            font-family: 'Fredoka One', cursive;
            font-size: 22px; color: var(--purple-light);
        }

        /* ── SECTION SHARED ── */
        section { padding: 64px 0; }
        .section-label {
            text-align: center;
            font-size: 12px; font-weight: 800;
            letter-spacing: .12em; text-transform: uppercase;
            color: var(--purple-light); margin-bottom: 12px;
        }
        .section-title {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(24px, 5vw, 36px);
            text-align: center;
            background: linear-gradient(135deg, var(--text) 30%, var(--muted));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 14px;
        }
        .section-sub {
            text-align: center; color: var(--dim);
            font-size: 16px; line-height: 1.7;
            max-width: 500px; margin: 0 auto 44px;
        }

        /* ── FEATURES ── */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .feature-card {
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 20px;
            padding: 26px 22px;
            transition: border-color .25s, transform .25s;
        }
        .feature-card:hover {
            border-color: rgba(147,51,234,.6);
            transform: translateY(-4px);
        }
        .feature-icon {
            font-size: 28px; margin-bottom: 14px; display: block;
        }
        .feature-card h3 {
            font-family: 'Fredoka One', cursive;
            font-size: 18px; margin-bottom: 8px; color: var(--text);
        }
        .feature-card p {
            font-size: 14px; line-height: 1.65; color: var(--dim);
        }

        /* ── SUBJECTS ── */
        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .subject-card {
            border-radius: 20px; padding: 28px 24px;
            border: 1.5px solid;
        }
        .subject-card.math {
            background: rgba(13,148,136,.1);
            border-color: rgba(13,148,136,.35);
        }
        .subject-card.editing {
            background: rgba(219,39,119,.1);
            border-color: rgba(219,39,119,.35);
        }
        .subject-card.comprehension {
            background: rgba(147,51,234,.1);
            border-color: rgba(147,51,234,.35);
        }
        .subject-card .s-icon { font-size: 32px; margin-bottom: 12px; display: block; }
        .subject-card h3 {
            font-family: 'Fredoka One', cursive; font-size: 19px;
            margin-bottom: 6px;
        }
        .subject-card.math         h3 { color: #5eead4; }
        .subject-card.editing       h3 { color: #f472b6; }
        .subject-card.comprehension h3 { color: #c084fc; }
        .subject-card p { font-size: 13px; line-height: 1.6; color: var(--dim); margin-bottom: 14px; }
        .subject-tag {
            display: inline-block;
            padding: 4px 12px; border-radius: 999px;
            font-size: 12px; font-weight: 700;
        }
        .math .subject-tag        { background: rgba(13,148,136,.25); color: #5eead4; }
        .editing .subject-tag     { background: rgba(219,39,119,.25); color: #f472b6; }
        .comprehension .subject-tag { background: rgba(147,51,234,.25); color: #c084fc; }

        /* ── HOW IT WORKS ── */
        .steps { display: flex; flex-direction: column; gap: 20px; }
        .step {
            display: flex; align-items: flex-start; gap: 20px;
            background: var(--card); border: 1.5px solid var(--border);
            border-radius: 18px; padding: 22px 24px;
            transition: border-color .25s;
        }
        .step:hover { border-color: rgba(147,51,234,.55); }
        .step-num {
            width: 40px; height: 40px; flex-shrink: 0;
            background: linear-gradient(135deg, var(--purple), var(--pink));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Fredoka One', cursive; font-size: 18px; color: white;
        }
        .step-body h3 {
            font-family: 'Fredoka One', cursive; font-size: 17px;
            margin-bottom: 5px; color: var(--text);
        }
        .step-body p { font-size: 14px; line-height: 1.6; color: var(--dim); }

        /* ── STATS ── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            text-align: center;
        }
        .stat-card {
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 18px; padding: 28px 16px;
        }
        .stat-num {
            font-family: 'Fredoka One', cursive;
            font-size: 38px;
            background: linear-gradient(135deg, var(--purple-light), var(--pink-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
            display: block; margin-bottom: 6px;
        }
        .stat-label { font-size: 13px; font-weight: 700; color: var(--dim); }

        /* ── CTA BANNER ── */
        .cta-banner {
            background: linear-gradient(135deg, rgba(147,51,234,.25), rgba(219,39,119,.2));
            border: 1.5px solid rgba(147,51,234,.4);
            border-radius: 24px; padding: 52px 36px;
            text-align: center;
        }
        .cta-banner h2 {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(24px, 5vw, 34px);
            margin-bottom: 14px;
            background: linear-gradient(135deg, #e9d5ff, var(--pink-light));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .cta-banner p { color: var(--muted); font-size: 16px; margin-bottom: 30px; line-height: 1.6; }

        /* ── FOOTER ── */
        footer {
            border-top: 1px solid var(--border);
            padding: 32px 24px;
            text-align: center;
            font-size: 13px; color: var(--dim);
        }
        footer a { color: var(--muted); text-decoration: none; }
        footer a:hover { color: var(--purple-light); }

        /* ── DIVIDER ── */
        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border), transparent);
            margin: 0;
        }

        /* ── MOBILE ── */
        @media (max-width: 520px) {
            .nav-links .btn-nav-ghost { display: none; }
            .hero { padding: 52px 0 44px; }
        }
    </style>
</head>
<body>
<div id="stars"></div>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div class="orb orb-3"></div>

<div class="page">

    <!-- NAV -->
    <nav>
        <div class="nav-inner">
            <a class="nav-brand" href="/">
                <span class="nav-brand-icon">✨</span>
                ForMyNieces
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
                    <a class="btn-nav-ghost" href="{{ route('login') }}">Sign In</a>
                    <a class="btn-nav-primary" href="{{ route('register') }}">Get Started</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero">
        <div class="container">
            <div class="hero-badge">🇹🇹 Built for Trinidad & Tobago</div>

            <h1>Your SEA Exam<br>Study Companion</h1>

            <p class="hero-sub">
                A personalised, AI-powered platform helping primary school girls
                in T&T prepare for the SEA with confidence — one module at a time.
            </p>

            <div class="hero-cta">
                @auth
                    <a class="btn-primary" href="{{ $homeUrl }}">Go to your dashboard →</a>
                @else
                    <a class="btn-primary" href="{{ route('register') }}">Start Learning Free ✨</a>
                    <a class="btn-ghost" href="{{ route('login') }}">Sign In</a>
                @endauth
            </div>

            <div class="hero-visual">
                <div class="subject-pill pill-math">🔢 Mathematics</div>
                <div class="subject-pill pill-editing">✏️ ELA Editing</div>
                <div class="subject-pill pill-comprehension">📖 Comprehension</div>
            </div>

            <div class="exam-countdown">
                📅 SEA 2026 &nbsp;·&nbsp;
                <span class="countdown-num" id="weeks-left">—</span>
                weeks away — let's get ready
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- STATS -->
    <section>
        <div class="container">
            <div class="stats-row">
                <div class="stat-card">
                    <span class="stat-num">90</span>
                    <span class="stat-label">Syllabus Modules</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num">30</span>
                    <span class="stat-label">Study Weeks</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num">3</span>
                    <span class="stat-label">Core Subjects</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num">AI</span>
                    <span class="stat-label">Powered Coach</span>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- SUBJECTS -->
    <section>
        <div class="container">
            <p class="section-label">What We Cover</p>
            <h2 class="section-title">The Full SEA Curriculum</h2>
            <p class="section-sub">Aligned to the MOE SEA 2025–2028 framework across all three tested subjects.</p>

            <div class="subjects-grid">
                <div class="subject-card math">
                    <span class="s-icon">🔢</span>
                    <h3>Mathematics</h3>
                    <p>Number theory, fractions, geometry, measurement, statistics, and problem-solving across 51 modules.</p>
                    <span class="subject-tag">51 modules · Weeks 1–17</span>
                </div>
                <div class="subject-card editing">
                    <span class="s-icon">✏️</span>
                    <h3>ELA Editing</h3>
                    <p>Grammar, punctuation, sentence structure, and language editing skills across 21 focused modules.</p>
                    <span class="subject-tag">21 modules · Weeks 1–21</span>
                </div>
                <div class="subject-card comprehension">
                    <span class="s-icon">📖</span>
                    <h3>Comprehension</h3>
                    <p>Reading strategies, inference, vocabulary in context, and text analysis across 18 modules.</p>
                    <span class="subject-tag">18 modules · Weeks 1–18</span>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- FEATURES -->
    <section>
        <div class="container">
            <p class="section-label">Platform Features</p>
            <h2 class="section-title">Everything She Needs to Succeed</h2>
            <p class="section-sub">Smart tools that adapt to where she is, not just where the syllabus says she should be.</p>

            <div class="features-grid">
                <div class="feature-card">
                    <span class="feature-icon">🤖</span>
                    <h3>AI Exam Agent</h3>
                    <p>Real-time analysis of pacing, risk, and weekly recommendations powered by Claude AI.</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">📊</span>
                    <h3>Progress Tracking</h3>
                    <p>Visual thermometers and pace charts per subject — always know if you're on track.</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">🗓️</span>
                    <h3>Weekly Timetable</h3>
                    <p>Auto-generated study schedule that adjusts when you fall behind. No guesswork.</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">🎯</span>
                    <h3>Diagnostic Tests</h3>
                    <p>Module-level diagnostics that reveal exactly which topics need attention.</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">👩‍👧</span>
                    <h3>Parent Dashboard</h3>
                    <p>Parents get a bird's-eye view of progress, targets, and AI summaries for each child.</p>
                </div>
                <div class="feature-card">
                    <span class="feature-icon">🏆</span>
                    <h3>Mastery System</h3>
                    <p>Three clear stages — Not Started, Diagnostic Passed, Mastered — with visual feedback.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="divider"></div>

    <!-- HOW IT WORKS -->
    <section>
        <div class="container">
            <p class="section-label">How It Works</p>
            <h2 class="section-title">Simple. Structured. Smart.</h2>
            <p class="section-sub">From sign-up to SEA day in four steps.</p>

            <div class="steps">
                <div class="step">
                    <div class="step-num">1</div>
                    <div class="step-body">
                        <h3>Create an Account</h3>
                        <p>Students and parents register in seconds. Link your child's account for full family visibility.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">2</div>
                    <div class="step-body">
                        <h3>Meet Your Exam Agent</h3>
                        <p>The AI analyses the current week, your progress, and how much time remains before the SEA — then tells you exactly where to focus.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">3</div>
                    <div class="step-body">
                        <h3>Follow Your Weekly Plan</h3>
                        <p>A personalised timetable maps out each subject per day — 90 minutes on track, 120 when catching up.</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-num">4</div>
                    <div class="step-body">
                        <h3>Take Diagnostics &amp; Advance</h3>
                        <p>Complete module diagnostics to mark topics as passed or mastered, and watch your progress thermometers fill up.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA BANNER -->
    <section>
        <div class="container">
            <div class="cta-banner">
                @auth
                    <h2>Welcome back! 🌟</h2>
                    <p>Pick up right where you left off — your dashboard is ready.</p>
                    <a class="btn-primary" href="{{ $homeUrl }}">Go to your dashboard →</a>
                @else
                    <h2>Ready to Start Preparing? 🌟</h2>
                    <p>
                        Join ForMyNieces today and give your daughter the structured,
                        AI-guided preparation she deserves for SEA 2026.
                    </p>
                    <a class="btn-primary" href="{{ route('register') }}">Create a Free Account</a>
                @endauth
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer>
        <p>
            © {{ date('Y') }} ForMyNieces &nbsp;·&nbsp;
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
    // Stars
    const sc = document.getElementById('stars');
    for (let i = 0; i < 130; i++) {
        const s = document.createElement('div');
        s.className = 'star';
        const sz = Math.random() * 2.2 + .8;
        s.style.cssText = `width:${sz}px;height:${sz}px;top:${Math.random()*100}%;left:${Math.random()*100}%;--d:${(Math.random()*4+2).toFixed(1)}s;--dl:-${(Math.random()*6).toFixed(1)}s`;
        sc.appendChild(s);
    }

    // Countdown to SEA
    const exam = new Date('2026-05-21');
    const today = new Date();
    const weeks = Math.max(0, Math.ceil((exam - today) / (7 * 24 * 60 * 60 * 1000)));
    document.getElementById('weeks-left').textContent = weeks;
</script>
</body>
</html>