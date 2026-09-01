<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Guardian Bridge · SmoothSeas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── SmoothSeas — light editorial system (shared with the landing page) ── */
        :root {
            --ink:#12222e; --ink-soft:#40566a; --ink-faint:#6b8199;
            --paper:#fbf8f2; --paper-2:#ffffff; --line:#e7ddcd;
            --teal:#0d7d8c; --teal-deep:#0a5c68; --teal-tint:#e6f3f4;
            --amber:#f2a900; --amber-tint:#fdf1d6;
            --good:#1a8f5f; --good-tint:#e4f4ec;
            --warn:#c2681a; --warn-tint:#fbecdb;
            --shadow-sm:0 1px 2px rgba(18,34,46,.06),0 4px 12px rgba(18,34,46,.05);
            --shadow-md:0 10px 30px rgba(18,34,46,.09);
            --radius:18px;
        }
        *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--paper); font-family:'Nunito',system-ui,sans-serif; color:var(--ink); min-height:100vh; -webkit-font-smoothing:antialiased; }
        h1,h2,h3,h4 { font-family:'Fredoka','Nunito',sans-serif; line-height:1.15; letter-spacing:-.01em; font-weight:600; }
        a { color:inherit; }

        .gb-shell { display:flex; min-height:100vh; }

        /* Sidebar */
        .gb-side {
            width:236px; flex-shrink:0; position:sticky; top:0; align-self:flex-start; height:100vh;
            background:var(--paper-2); border-right:1px solid var(--line);
            padding:22px 16px; display:flex; flex-direction:column; gap:26px;
        }
        .gb-brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .gb-brand-mark { width:38px; height:38px; border-radius:11px; flex-shrink:0; background:linear-gradient(135deg,var(--teal),var(--teal-deep)); display:flex; align-items:center; justify-content:center; font-size:19px; box-shadow:var(--shadow-sm); }
        .gb-brand-name { font-family:'Fredoka',sans-serif; font-weight:700; font-size:19px; color:var(--ink); }
        .gb-brand-tag { display:block; font-size:10px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:var(--teal); margin-top:1px; }
        .gb-nav { display:flex; flex-direction:column; gap:2px; }
        .gb-nav-eyebrow { font-size:10.5px; font-weight:800; letter-spacing:.11em; text-transform:uppercase; color:var(--ink-faint); margin:0 0 8px 12px; }
        .gb-nav-link { display:flex; align-items:center; gap:11px; text-decoration:none; font-size:14.5px; font-weight:700; color:var(--ink-soft); padding:9px 12px; border-radius:11px; transition:background .15s,color .15s; }
        .gb-nav-link:hover { color:var(--teal); background:var(--teal-tint); }
        .gb-nav-link.is-active { color:#fff; background:var(--teal); }
        .gb-nav-link .ic { width:19px; text-align:center; font-size:15px; }
        .gb-nav-link.is-soon { opacity:.5; }
        .gb-nav-link .soon { margin-left:auto; font-size:9px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:var(--ink-faint); }
        .gb-side-foot { margin-top:auto; display:flex; flex-direction:column; gap:10px; }
        .gb-side-user { font-size:13px; font-weight:800; color:var(--ink); padding:0 12px; }
        .gb-logout { background:var(--paper-2); color:var(--teal-deep); border:1px solid var(--line); border-radius:11px; padding:9px 0; width:100%; font-family:'Nunito',sans-serif; font-weight:800; font-size:14px; cursor:pointer; box-shadow:var(--shadow-sm); transition:border-color .2s,color .2s; }
        .gb-logout:hover { border-color:var(--teal); color:var(--teal); }

        .gb-main { flex:1; min-width:0; padding:26px 30px 64px; max-width:1120px; }

        /* Mobile */
        .gb-topbar,.gb-mobnav { display:none; }
        @media (max-width:860px) {
            .gb-shell { flex-direction:column; }
            .gb-side { display:none; }
            .gb-main { padding:14px 16px 56px; max-width:100%; }
            .gb-topbar { display:flex; align-items:center; justify-content:space-between; gap:12px; position:sticky; top:0; z-index:100; height:58px; padding:0 16px; margin:-14px -16px 0; background:rgba(251,248,242,.9); backdrop-filter:blur(10px); border-bottom:1px solid var(--line); }
            .gb-mobnav { display:flex; gap:7px; overflow-x:auto; padding:12px 0 2px; }
            .gb-mobnav a { white-space:nowrap; font-size:13.5px; font-weight:800; text-decoration:none; color:var(--ink-soft); padding:7px 14px; border-radius:999px; border:1px solid var(--line); background:var(--paper-2); }
            .gb-mobnav a.is-active { color:#fff; background:var(--teal); border-color:var(--teal); }
        }
    </style>
</head>
<body>
    <div class="gb-shell">
        <aside class="gb-side">
            <a href="{{ route('guardian.dashboard') }}" class="gb-brand">
                <span class="gb-brand-mark">⚓</span>
                <span><span class="gb-brand-name">SmoothSeas</span><span class="gb-brand-tag">Guardian Bridge</span></span>
            </a>
            @php
                $sec = request()->routeIs('guardian.dashboard') ? request()->query('section', 'overview') : null;
                $dash = fn (string $s) => route('guardian.dashboard').'?section='.$s;
            @endphp
            <nav class="gb-nav">
                <p class="gb-nav-eyebrow">The honest layer</p>
                <a href="{{ $dash('overview') }}" wire:navigate class="gb-nav-link {{ $sec === 'overview' ? 'is-active' : '' }}"><span class="ic">🧭</span> Overview</a>
                <a href="{{ $dash('this-week') }}" wire:navigate class="gb-nav-link {{ $sec === 'this-week' ? 'is-active' : '' }}"><span class="ic">🗓️</span> This week</a>
                <a href="{{ $dash('pace') }}" wire:navigate class="gb-nav-link {{ $sec === 'pace' ? 'is-active' : '' }}"><span class="ic">🧭</span> Pace</a>
                <a href="{{ route('guardian.progress') }}" wire:navigate class="gb-nav-link {{ request()->routeIs('guardian.progress') ? 'is-active' : '' }}"><span class="ic">📈</span> Progress</a>
                <a href="{{ $dash('estimator') }}" wire:navigate class="gb-nav-link {{ $sec === 'estimator' ? 'is-active' : '' }}"><span class="ic">🎯</span> Estimator</a>
                <a href="{{ $dash('rewards') }}" wire:navigate class="gb-nav-link {{ $sec === 'rewards' ? 'is-active' : '' }}"><span class="ic">🎁</span> Rewards &amp; controls</a>
                <a href="{{ route('guardian.family') }}" wire:navigate class="gb-nav-link {{ request()->routeIs('guardian.family') ? 'is-active' : '' }}"><span class="ic">👪</span> Family</a>
                <a href="{{ route('guardian.children') }}" class="gb-nav-link {{ request()->routeIs('guardian.children') ? 'is-active' : '' }}"><span class="ic">🔑</span> Children's logins</a>
                <a href="{{ route('guardian.account') }}" wire:navigate class="gb-nav-link {{ request()->routeIs('guardian.account') ? 'is-active' : '' }}"><span class="ic">⚙️</span> Account</a>
            </nav>
            <div class="gb-side-foot">
                <span class="gb-side-user">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="gb-logout">Log out</button></form>
            </div>
        </aside>

        <div class="gb-main">
            <div class="gb-topbar">
                <a href="{{ route('guardian.dashboard') }}" class="gb-brand"><span class="gb-brand-mark">⚓</span><span class="gb-brand-name">SmoothSeas</span></a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="gb-logout" style="width:auto; padding:7px 16px; border-radius:999px;">Log out</button></form>
            </div>
            <nav class="gb-mobnav">
                <a href="{{ $dash('overview') }}" wire:navigate class="{{ $sec === 'overview' ? 'is-active' : '' }}">Overview</a>
                <a href="{{ $dash('this-week') }}" wire:navigate class="{{ $sec === 'this-week' ? 'is-active' : '' }}">This week</a>
                <a href="{{ $dash('pace') }}" wire:navigate class="{{ $sec === 'pace' ? 'is-active' : '' }}">Pace</a>
                <a href="{{ route('guardian.progress') }}" wire:navigate class="{{ request()->routeIs('guardian.progress') ? 'is-active' : '' }}">Progress</a>
                <a href="{{ $dash('estimator') }}" wire:navigate class="{{ $sec === 'estimator' ? 'is-active' : '' }}">Estimator</a>
                <a href="{{ $dash('rewards') }}" wire:navigate class="{{ $sec === 'rewards' ? 'is-active' : '' }}">Rewards</a>
                <a href="{{ route('guardian.family') }}" wire:navigate class="{{ request()->routeIs('guardian.family') ? 'is-active' : '' }}">Family</a>
                <a href="{{ route('guardian.children') }}" class="{{ request()->routeIs('guardian.children') ? 'is-active' : '' }}">Logins</a>
                <a href="{{ route('guardian.account') }}" wire:navigate class="{{ request()->routeIs('guardian.account') ? 'is-active' : '' }}">Account</a>
            </nav>

            {{ $slot }}
        </div>
    </div>
</body>
</html>
