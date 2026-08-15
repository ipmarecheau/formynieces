@php
    $homeUrl = auth()->user() && auth()->user()->isStudent() ? route('student.voyage') : route('dashboard');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SmoothSeas — SEA exam prep for Caribbean children, sailed with a turtle named Smooth.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>@yield('title', 'SmoothSeas')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

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

        .page { position: relative; z-index: 1; }
        .container { max-width: 880px; margin: 0 auto; padding: 0 24px; }

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

        section { padding: 64px 0; }
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
            max-width: 560px; margin: 0 auto 48px;
        }

        .divider { height: 1px; background: linear-gradient(90deg, transparent, var(--border), transparent); }

        [data-reveal] { opacity: 0; transform: translateY(26px); transition: opacity .7s ease var(--rd, 0s), transform .7s ease var(--rd, 0s); }
        [data-reveal].in { opacity: 1; transform: none; }

        footer { border-top: 1px solid var(--border); padding: 34px 24px; text-align: center; font-size: 13px; color: var(--dim); }
        footer a { color: var(--muted); text-decoration: none; }
        footer a:hover { color: var(--aqua); }

        @media (max-width: 620px) {
            .nav-anchor { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation: none !important; transition: none !important; }
            [data-reveal] { opacity: 1 !important; transform: none !important; }
        }
    </style>
    @yield('styles')
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>
<div id="stars" aria-hidden="true"></div>

<div class="page">

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
                    <a class="nav-anchor" href="{{ route('about') }}">About</a>
                    <a class="nav-anchor" href="{{ route('faq') }}">FAQ</a>
                    <a class="nav-anchor" href="{{ route('contact') }}">Contact</a>
                    <a class="btn-nav-ghost" href="{{ route('login') }}">Sign In</a>
                    <a class="btn-nav-primary" href="{{ route('book.call') }}">Book a call</a>
                @endauth
            </div>
        </div>
    </nav>

    @yield('content')

    <footer>
        <p>
            © {{ date('Y') }} SmoothSeas &nbsp;·&nbsp;
            Built with ❤️ in Trinidad &amp; Tobago &nbsp;·&nbsp;
            <a href="{{ route('about') }}">About</a> ·
            <a href="{{ route('faq') }}">FAQ</a> ·
            <a href="{{ route('contact') }}">Contact</a> ·
            <a href="{{ route('book.call') }}">Book a call</a>
            @guest · <a href="{{ route('login') }}">Sign In</a>@endguest
        </p>
    </footer>

</div>

<script>
    (function () {
        var stars = document.getElementById('stars');
        for (var i = 0; i < 70; i++) {
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

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) {
                    e.target.classList.add('in');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.15 });
        document.querySelectorAll('[data-reveal]').forEach(function (el) { io.observe(el); });
    })();
</script>
@yield('scripts')
@include('partials.chat-widget')
</body>
</html>
