<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ForMyNieces ✨</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka+One&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Nunito', sans-serif;
            background: #fdf4ff;
            margin: 0;
            min-height: 100vh;
        }

        .fmn-group {
            background: white; border: 1.5px solid #f3e8ff;
            border-radius: 14px; margin-bottom: 10px; overflow: hidden;
        }
        .fmn-group-header {
            width: 100%; background: none; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; font-family: 'Nunito', sans-serif; text-align: left;
        }
        .fmn-group-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            align-items: start;
            margin-bottom: 12px;
        }
        .fmn-group-grid .fmn-group { margin-bottom: 0; }
        @media (max-width: 640px) {
            .fmn-group-grid { grid-template-columns: 1fr; }
        }
        .fmn-group-titles { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
        .fmn-group-name { font-size: 0.95rem; font-weight: 800; color: #1f2937; }
        .fmn-group-summary { font-size: 0.72rem; color: #9ca3af; font-weight: 700; }
        .fmn-group-chevron { color: #a78bfa; font-size: 1rem; transition: transform 0.2s; }
        .fmn-group-chevron.open { transform: rotate(180deg); }
        .fmn-group-body { padding: 0 18px 10px; }
        .fmn-leaf {
            display: flex; flex-direction: column; align-items: flex-start; gap: 8px;
            padding: 10px 0; border-top: 1px solid #f9f5ff;
        }
        .fmn-leaf-actions {
            display: flex; align-items: center; gap: 10px;
            width: 100%;
        }
        .fmn-practice-link {
            font-size: 0.74rem; font-weight: 800; color: #db2777;
            text-decoration: none; padding: 3px 10px; border-radius: 999px;
            background: #fce7f3; border: 1.5px solid #fbcfe8; white-space: nowrap;
            transition: background 0.15s, transform 0.1s;
        }
        .fmn-practice-link:hover { background: #fbcfe8; transform: translateY(-1px); }
        .fmn-leaf-icon { font-size: 1rem; flex-shrink: 0; }
        .fmn-leaf-name { font-size: 0.85rem; color: #374151; font-weight: 600; width: 100%; }.sdot-needswork { background: #f59e0b; }
        .fmn-hearts { display: inline-flex; gap: 1px; flex-shrink: 0; letter-spacing: -1px; }
        .fmn-heart { font-size: 0.8rem; }
        .fmn-legend {
            display: flex; flex-wrap: wrap; gap: 14px;
            padding: 10px 14px; margin-bottom: 14px;
            background: #fdf4ff; border: 1.5px solid #f3e8ff; border-radius: 12px;
            font-size: 0.72rem; font-weight: 700; color: #7c3aed;
        }
        .fmn-legend-item { display: inline-flex; align-items: center; gap: 6px; }
        /* ── NAVBAR ── */
        .fmn-nav {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1.5px solid #f3e8ff;
            padding: 0 1rem;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .fmn-nav-brand {
            font-family: 'Fredoka One', cursive;
            font-size: 1.4rem;
            color: #9333ea;
            text-decoration: none;
        }
        .fmn-nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .fmn-nav-greeting {
            font-size: 0.85rem;
            font-weight: 700;
            color: #a78bfa;
        }
        @media (max-width: 480px) {
            .fmn-nav-greeting { display: none; }
        }

        /* ── PAGE WRAPPER ── */
        .fmn-page {
            max-width: 760px;
            margin: 0 auto;
            padding: 1.25rem 1rem 3rem;
        }

        /* ── BUTTONS ── */
        .fmn-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 20px;
            border-radius: 999px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            transition: transform 0.15s, box-shadow 0.15s;
            text-decoration: none;
        }
        .fmn-btn-primary {
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white;
        }
        .fmn-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(168,85,247,0.35);
        }
        .fmn-btn-sm {
            padding: 7px 16px;
            font-size: 0.8rem;
        }
        .fmn-btn-ghost {
            background: white;
            color: #9333ea;
            border: 1.5px solid #e9d5ff;
        }
        .fmn-btn-ghost:hover {
            background: #fdf4ff;
        }

        /* ── HERO CARD ── */
        .fmn-hero {
            background: linear-gradient(135deg, #9333ea 0%, #db2777 100%);
            border-radius: 20px;
            padding: 1.5rem;
            color: white;
            margin-bottom: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .fmn-hero::after {
            content: '✦';
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 5rem;
            opacity: 0.1;
            pointer-events: none;
        }
        .fmn-hero-title {
            font-family: 'Fredoka One', cursive;
            font-size: 1.6rem;
            margin: 0 0 0.25rem;
        }
        .fmn-hero-sub {
            font-size: 0.88rem;
            opacity: 0.88;
            margin: 0 0 1.1rem;
        }
        .fmn-progress-track {
            background: rgba(255,255,255,0.25);
            border-radius: 999px;
            height: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .fmn-progress-fill {
            background: white;
            height: 100%;
            border-radius: 999px;
            transition: width 1.2s ease;
        }
        .fmn-progress-label {
            font-size: 0.8rem;
            opacity: 0.9;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
        }

        /* ── STAT GRID ── */
        .fmn-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 1.25rem;
        }
        @media (max-width: 480px) {
            .fmn-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        .fmn-stat {
            background: white;
            border: 1.5px solid #f3e8ff;
            border-radius: 16px;
            padding: 0.9rem 0.5rem;
            text-align: center;
        }
        .fmn-stat-num {
            font-family: 'Fredoka One', cursive;
            font-size: 1.9rem;
            color: #9333ea;
            line-height: 1;
        }
        .fmn-stat-lbl {
            font-size: 0.68rem;
            font-weight: 800;
            color: #c4b5fd;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-top: 4px;
        }

        /* ── SECTION TITLE ── */
        .fmn-section-title {
            font-family: 'Fredoka One', cursive;
            font-size: 1.15rem;
            color: #7c3aed;
            display: flex;
            align-items: center;
            gap: 7px;
            margin: 0 0 0.85rem;
        }

        /* ── TARGET CARD ── */
        .fmn-card {
            background: white;
            border: 1.5px solid #f3e8ff;
            border-radius: 18px;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .fmn-card-inner-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* ── BADGES ── */
        .fmn-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 999px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }
        .badge-math         { background: #d1fae5; color: #065f46; }
        .badge-editing      { background: #fce7f3; color: #9d174d; }
        .badge-comprehension{ background: #ede9fe; color: #4c1d95; }

        .fmn-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .pill-progress { background: #fff7ed; color: #c2410c; border: 1.5px solid #fed7aa; }
        .pill-done     { background: #f0fdf4; color: #166534; border: 1.5px solid #bbf7d0; }

        /* ── STREAK CHIP ── */
        .fmn-streak-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 999px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 0.82rem;
            background: #fff7ed;
            color: #c2410c;
            border: 1.5px solid #fed7aa;
        }
        .fmn-streak-return {
            display: inline-block;
            margin-top: 8px;
            padding: 5px 14px;
            border-radius: 999px;
            font-family: 'Nunito', sans-serif;
            font-weight: 800;
            font-size: 0.8rem;
            color: #166534;
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
        }

        /* ── TAB BAR ── */
        .fmn-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 1.1rem;
            overflow-x: auto;
            padding-bottom: 4px;
            scrollbar-width: none;
        }
        .fmn-tabs::-webkit-scrollbar { display: none; }
        .fmn-tab {
            flex-shrink: 0;
            padding: 7px 16px;
            border-radius: 999px;
            font-family: 'Nunito', sans-serif;
            font-size: 0.82rem;
            font-weight: 800;
            cursor: pointer;
            border: 1.5px solid #e9d5ff;
            background: white;
            color: #7c3aed;
            transition: all 0.18s;
        }
        .fmn-tab[data-active="true"] {
            background: linear-gradient(135deg, #9333ea, #db2777);
            color: white;
            border-color: transparent;
        }
        .fmn-tab-math[data-active="true"]    { background: linear-gradient(135deg, #059669, #34d399); border-color: transparent; }
        .fmn-tab-edit[data-active="true"]    { background: linear-gradient(135deg, #db2777, #f472b6); border-color: transparent; }
        .fmn-tab-comp[data-active="true"]    { background: linear-gradient(135deg, #7c3aed, #a78bfa); border-color: transparent; }

        /* ── ROADMAP ── */
        .fmn-roadmap { position: relative; }

        .fmn-roadmap-line {
            position: absolute;
            left: 19px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #e9d5ff, #fce7f3);
            z-index: 0;
        }

        .fmn-roadmap-item {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 1;
            margin-bottom: 10px;
        }

        .fmn-node-dot {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #e9d5ff;
        }
        .dot-math         { background: #d1fae5; }
        .dot-editing      { background: #fce7f3; }
        .dot-comprehension{ background: #ede9fe; }
        .dot-mastered     { box-shadow: 0 0 0 2px #6ee7b7; }
        .dot-diagnostic   { box-shadow: 0 0 0 2px #c4b5fd; }
        .dot-notstarted   { opacity: 0.6; }

        .fmn-node-content {
            background: white;
            border: 1.5px solid #f3e8ff;
            border-radius: 14px;
            padding: 10px 14px;
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }
        .node-mastered   { border-color: #a7f3d0; }
        .node-diagnostic { border-color: #ddd6fe; }
        .node-notstarted { opacity: 0.7; }

        .fmn-node-topic {
            font-size: 0.85rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0 0 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }
        .fmn-node-meta {
            font-size: 0.72rem;
            color: #9ca3af;
            margin: 0;
        }
        .fmn-node-score {
            font-size: 0.75rem;
            font-weight: 800;
            color: #7c3aed;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .fmn-status-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .sdot-mastered   { background: #10b981; }
        .sdot-diagnostic { background: #8b5cf6; }
        .sdot-notstarted { background: #d1d5db; }

        /* ── PARENT CARDS ── */
        .fmn-avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Fredoka One', cursive;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .fmn-alert {
            background: #fdf4ff;
            border: 1.5px solid #e9d5ff;
            border-radius: 14px;
            padding: 1rem 1.25rem;
            color: #7c3aed;
            font-weight: 700;
            font-size: 0.88rem;
        }
    </style>
</head>
<body>

    {{-- NAVBAR --}}
    <nav class="fmn-nav">
        <span class="fmn-nav-brand">✨ ForMyNieces</span>
        <div class="fmn-nav-right">
            <span class="fmn-nav-greeting">Hi, {{ $user->name }} 👋</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="fmn-btn fmn-btn-ghost fmn-btn-sm">Log out</button>
            </form>
        </div>
    </nav>

    <div class="fmn-page">

    {{-- ══════════════ STUDENT DASHBOARD ══════════════ --}}
    @if($user->isStudent())

        {{-- HERO --}}
        <div class="fmn-hero">
            <p class="fmn-hero-title">Welcome back, {{ explode(' ', $user->name)[0] }}! 🌸</p>
            <p class="fmn-hero-sub">You're doing amazing — your SEA goal is within reach!</p>
            <div class="fmn-progress-track">
                <div class="fmn-progress-fill" style="width:{{ $completionPercent }}%;"></div>
            </div>
            <div class="fmn-progress-label">
                <span>Syllabus Completion</span>
                <span>{{ $completionPercent }}% mastered ⭐</span>
            </div>
        </div>

        {{-- PRACTICE STREAK --}}
        <div style="margin-bottom:1.25rem;">
            <span class="fmn-streak-chip">🔥 {{ $dayStreak }} day practice streak</span>
            <span class="fmn-streak-chip" style="margin-left:8px;">🔑 {{ $loginStreak }} day login streak</span>
            <span class="fmn-streak-chip" style="margin-left:8px;">🏆 {{ $masteryStreak }} day mastery streak</span>
            @if($streakRestarted)
                <span class="fmn-streak-return">🌱 Back at it — let's build a fresh streak today!</span>
            @endif
        </div>

        {{-- STATS (three real buckets) --}}
        <div class="fmn-stats" style="grid-template-columns: repeat(3, minmax(0,1fr));">
            <div class="fmn-stat">
                <div class="fmn-stat-num">{{ $masteredCount }}</div>
                <div class="fmn-stat-lbl">Mastered</div>
            </div>
            <div class="fmn-stat">
                <div class="fmn-stat-num">{{ $likelyCount }}</div>
                <div class="fmn-stat-lbl">Likely Known</div>
            </div>
            <div class="fmn-stat">
                <div class="fmn-stat-num">{{ $needsCount }}</div>
                <div class="fmn-stat-lbl">Needs Work</div>
            </div>
        </div>

        {{-- EXAM AGENT --}}
        <div style="margin-bottom:1.25rem;">
            <a href="{{ route('exam-agent') }}" class="fmn-btn fmn-btn-primary">
                🤖 View Exam Agent Report
            </a>
        </div>

        {{-- WEEKLY TARGET --}}
        <p class="fmn-section-title">🎯 This Week's Target</p>
        @if($weeklyTarget)
            <div class="fmn-card">
                <div class="fmn-card-inner-row">
                    <div style="min-width:0; flex:1;">
                        @php
                            $subj = $weeklyTarget->module->subject;
                            $bc = match($subj) { 'Math'=>'badge-math','ELA'=>'badge-comprehension',default=>'badge-comprehension' };
                        @endphp
                        <span class="fmn-badge {{ $bc }}">{{ $subj }}</span>
                        <p style="font-weight:800; font-size:0.95rem; color:#1f2937; margin:0 0 4px;">
                            {{ $weeklyTarget->module->topic }}
                        </p>
                        <p style="font-size:0.78rem; color:#9ca3af; margin:0;">
                            {{ $weeklyTarget->module->sea_section }} · Week of {{ $weeklyTarget->week_start_date->format('M d, Y') }}
                        </p>
                    </div>
                    <div style="flex-shrink:0;">
                        @if($weeklyTarget->is_completed)
                            <span class="fmn-pill pill-done">✅ Done!</span>
                        @else
                            <span class="fmn-pill pill-progress">⏳ In Progress</span>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="fmn-alert" style="margin-bottom:1.25rem;">
                🌟 No target set for this week yet — check back soon!
            </div>
        @endif

        {{-- ROADMAP — collapsible Subject → prefix groups --}}
        <p class="fmn-section-title">🗺️ Your Learning Journey</p>

        @if(!empty($roadmap))
            <div x-data="{ tab: 'all' }">

                {{-- TABS --}}
                <div class="fmn-tabs">
                    <button class="fmn-tab" :data-active="tab === 'all'" @click="tab = 'all'">✨ All</button>
                    <button class="fmn-tab fmn-tab-math" :data-active="tab === 'math'" @click="tab = 'math'">🔢 Math</button>
                    <button class="fmn-tab fmn-tab-comp" :data-active="tab === 'ela'" @click="tab = 'ela'">📖 ELA</button>
                </div>

                {{-- Legend --}}
                <div class="fmn-legend">
                    <span class="fmn-legend-item"><span class="fmn-hearts">❤️❤️❤️</span> Mastered</span>
                    <span class="fmn-legend-item"><span class="fmn-hearts">❤️❤️🤍</span> Likely known</span>
                    <span class="fmn-legend-item"><span class="fmn-hearts">❤️🤍🤍</span> Needs work</span>
                </div>

                @foreach($roadmap as $subject => $groups)
                    @php $subjKey = $subject === 'Math' ? 'math' : 'ela'; @endphp
                    <div class="fmn-group-grid" x-show="tab === 'all' || tab === '{{ $subjKey }}'">
                        @foreach($groups as $prefix => $group)
                            @php
                                $t = $group['tally'];
                                $summary = [];
                                if ($t['mastered'] > 0)          $summary[] = $t['mastered'].' mastered';
                                if ($t['inferred_mastered'] > 0) $summary[] = $t['inferred_mastered'].' likely';
                                if ($t['needs_work'] > 0)        $summary[] = $t['needs_work'].' needs work';
                                $summaryText = implode(' · ', $summary);
                            @endphp
                            <div class="fmn-group" x-data="{ open: false }">
                                <button type="button" class="fmn-group-header" @click="open = !open">
                                    <div class="fmn-group-titles">
                                        <span class="fmn-group-name">{{ $prefix }}</span>
                                        <span class="fmn-group-summary">{{ $summaryText }}</span>
                                    </div>
                                    <span class="fmn-group-chevron" :class="{ 'open': open }">▾</span>
                                </button>
                                <div class="fmn-group-body" x-show="open" x-collapse>
                                     @foreach($group['items'] as $item)
                                        @php
                                            $filled = match($item['status']) {
                                                'mastered' => 3,
                                                'inferred_mastered' => 2,
                                                'needs_work' => 1,
                                                default => 0,
                                            };
                                            $label = match($item['status']) {
                                                'mastered' => 'Mastered',
                                                'inferred_mastered' => 'Likely known',
                                                'needs_work' => 'Needs work',
                                                default => 'Not started',
                                            };
                                        @endphp
                                            <div class="fmn-leaf">
                                                <span class="fmn-leaf-name">{{ $item['leaf'] }}</span>
                                                <div class="fmn-leaf-actions">
                                                    @if ($item['status'] === 'needs_work')
                                                        <a href="{{ route('practice.lesson', $item['id']) }}"
                                                        class="fmn-practice-link">Take Lesson →</a>
                                                    @endif
                                                    <span class="fmn-hearts" title="{{ $label }}" aria-label="{{ $label }}">
                                                        @for ($h = 1; $h <= 3; $h++)
                                                            <span class="fmn-heart">{{ $h <= $filled ? '❤️' : '🤍' }}</span>
                                                        @endfor
                                                    </span>
                                                </div>
                                            </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

            </div>
        @else
            <div class="fmn-alert">
                📚 Your roadmap is being prepared — check back soon!
            </div>
        @endif

    {{-- ══════════════ PARENT DASHBOARD ══════════════ --}}
    @elseif($user->isParent())

        <div class="fmn-hero">
            <p class="fmn-hero-title">Parent Portal 👩‍👧</p>
            <p class="fmn-hero-sub">Live overview of your child's SEA preparation progress.</p>
        </div>

        @if(isset($studentSummaries) && $studentSummaries->count() > 0)
            @foreach($studentSummaries as $summary)
                <div class="fmn-card">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:1rem;">
                        <div class="fmn-avatar">
                            {{ strtoupper(substr($summary['student']->name, 0, 2)) }}
                        </div>
                        <div>
                            <p style="font-weight:800; font-size:1rem; color:#1f2937; margin:0;">
                                {{ $summary['student']->name }}
                            </p>
                            <p style="font-size:0.78rem; color:#9ca3af; margin:0;">
                                {{ $summary['student']->email }}
                            </p>
                        </div>
                    </div>

                    <div class="fmn-stats" style="margin-bottom:1rem;">
                        <div class="fmn-stat">
                            <div class="fmn-stat-num">{{ $summary['masteredCount'] }}</div>
                            <div class="fmn-stat-lbl">Mastered</div>
                        </div>
                        <div class="fmn-stat">
                            <div class="fmn-stat-num">{{ $summary['totalCount'] }}</div>
                            <div class="fmn-stat-lbl">Total</div>
                        </div>
                    </div>

                    <div style="margin-bottom:1rem;">
                        <div style="display:flex; justify-content:space-between; font-size:0.8rem; font-weight:700; color:#7c3aed; margin-bottom:5px;">
                            <span>Completion</span>
                            <span>{{ $summary['completionPercent'] }}%</span>
                        </div>
                        <div style="background:#f3e8ff; border-radius:999px; height:10px; overflow:hidden;">
                            <div style="width:{{ $summary['completionPercent'] }}%; background:linear-gradient(90deg,#9333ea,#db2777); height:100%; border-radius:999px;"></div>
                        </div>
                    </div>

                    <p style="font-size:0.72rem; font-weight:800; color:#c4b5fd; text-transform:uppercase; letter-spacing:0.06em; margin:0 0 8px;">
                        This Week's Target
                    </p>
                    @if($summary['currentTarget'])
                        @php
                            $subj = $summary['currentTarget']->module->subject;
                            $bc = match($subj) { 'Math'=>'badge-math','English Editing'=>'badge-editing',default=>'badge-comprehension' };
                        @endphp
                        <div style="background:#fdf4ff; border-radius:12px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                            <div>
                                <span class="fmn-badge {{ $bc }}">{{ $subj }}</span>
                                <p style="font-weight:700; font-size:0.85rem; color:#1f2937; margin:4px 0 0;">
                                    {{ $summary['currentTarget']->module->topic }}
                                </p>
                            </div>
                            @if($summary['currentTarget']->is_completed)
                                <span class="fmn-pill pill-done">✅ Done</span>
                            @else
                                <span class="fmn-pill pill-progress">⏳ Pending</span>
                            @endif
                        </div>
                    @else
                        <p style="font-size:0.85rem; color:#9ca3af;">No target assigned this week.</p>
                    @endif
                </div>
            @endforeach
        @else
            <div class="fmn-alert">
                👧 No students linked to your account yet.
            </div>
        @endif

    @endif

    </div>{{-- end fmn-page --}}

</body>
</html>