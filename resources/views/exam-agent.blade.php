<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Exam Agent ✨ — ForMyNieces</title>
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

        /* ── PAGE ── */
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
        .fmn-btn-ghost {
            background: white;
            color: #9333ea;
            border: 1.5px solid #e9d5ff;
        }
        .fmn-btn-ghost:hover { background: #fdf4ff; }
        .fmn-btn-sm { padding: 7px 16px; font-size: 0.8rem; }

        /* ── HERO ── */
        .fmn-hero {
            border-radius: 20px;
            padding: 1.5rem;
            color: white;
            margin-bottom: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .fmn-hero::after {
            content: '🤖';
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            font-size: 4rem;
            opacity: 0.2;
            pointer-events: none;
        }
        .hero-on-track  { background: linear-gradient(135deg, #059669, #34d399); }
        .hero-slight    { background: linear-gradient(135deg, #d97706, #fbbf24); }
        .hero-at-risk   { background: linear-gradient(135deg, #dc2626, #f87171); }
        .hero-revision  { background: linear-gradient(135deg, #7c3aed, #a78bfa); }

        .fmn-hero-title {
            font-family: 'Fredoka One', cursive;
            font-size: 1.6rem;
            margin: 0 0 0.25rem;
        }
        .fmn-hero-sub {
            font-size: 0.88rem;
            opacity: 0.92;
            margin: 0 0 1rem;
            line-height: 1.5;
        }

        /* ── COUNTDOWN CHIPS ── */
        .fmn-chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .fmn-chip {
            background: rgba(255,255,255,0.22);
            border: 1.5px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            padding: 5px 14px;
            font-size: 0.8rem;
            font-weight: 800;
            color: white;
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

        /* ── CARDS ── */
        .fmn-card {
            background: white;
            border: 1.5px solid #f3e8ff;
            border-radius: 18px;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1rem;
        }

        /* ── SUBJECT STATUS CARDS ── */
        .fmn-subject-card {
            background: white;
            border-radius: 18px;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1rem;
            border: 1.5px solid #f3e8ff;
        }
        .subject-on-track  { border-left: 4px solid #10b981; }
        .subject-slight    { border-left: 4px solid #f59e0b; }
        .subject-at-risk   { border-left: 4px solid #ef4444; }
        .subject-not-started { border-left: 4px solid #d1d5db; }

        .fmn-subject-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 0.85rem;
        }

        .fmn-subject-name {
            font-family: 'Fredoka One', cursive;
            font-size: 1.05rem;
            margin: 0;
        }
        .name-math         { color: #059669; }
        .name-editing      { color: #db2777; }
        .name-comprehension{ color: #7c3aed; }

        .fmn-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 800;
        }
        .badge-on-track   { background: #d1fae5; color: #065f46; }
        .badge-slight     { background: #fef3c7; color: #92400e; }
        .badge-at-risk    { background: #fee2e2; color: #991b1b; }
        .badge-not-started{ background: #f3f4f6; color: #6b7280; }

        /* ── PROGRESS BAR ── */
        .fmn-prog-track {
            background: #f3e8ff;
            border-radius: 999px;
            height: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .fmn-prog-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 1.2s ease;
        }
        .fill-math         { background: linear-gradient(90deg, #059669, #34d399); }
        .fill-editing      { background: linear-gradient(90deg, #db2777, #f472b6); }
        .fill-comprehension{ background: linear-gradient(90deg, #7c3aed, #a78bfa); }
        .fill-on-track     { background: linear-gradient(90deg, #059669, #34d399); }
        .fill-slight       { background: linear-gradient(90deg, #d97706, #fbbf24); }
        .fill-at-risk      { background: linear-gradient(90deg, #dc2626, #f87171); }

        .fmn-prog-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: #9ca3af;
            font-weight: 700;
            margin-bottom: 10px;
        }

        /* ── BEHIND MODULES LIST ── */
        .fmn-behind-list {
            border-top: 1.5px solid #f3e8ff;
            padding-top: 0.85rem;
            margin-top: 0.5rem;
        }
        .fmn-behind-title {
            font-size: 0.72rem;
            font-weight: 800;
            color: #ef4444;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin: 0 0 8px;
        }
        .fmn-behind-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            background: #fff5f5;
            border: 1.5px solid #fecaca;
            border-radius: 10px;
            margin-bottom: 6px;
        }
        .fmn-behind-week {
            background: #fee2e2;
            color: #991b1b;
            font-size: 0.68rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .fmn-behind-topic {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        /* ── AHEAD MODULES LIST ── */
        .fmn-ahead-list {
            border-top: 1.5px solid #f3e8ff;
            padding-top: 0.85rem;
            margin-top: 0.5rem;
        }
        .fmn-ahead-title {
            font-size: 0.72rem;
            font-weight: 800;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin: 0 0 8px;
        }
        .fmn-ahead-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            background: #f0fdf4;
            border: 1.5px solid #bbf7d0;
            border-radius: 10px;
            margin-bottom: 6px;
        }
        .fmn-ahead-week {
            background: #d1fae5;
            color: #065f46;
            font-size: 0.68rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .fmn-ahead-topic {
            font-size: 0.82rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        /* ── RECOMMENDATION BOX ── */
        .fmn-recommendation {
            border-radius: 16px;
            padding: 1.1rem 1.25rem;
            margin-bottom: 1.25rem;
            border: 1.5px solid;
        }
        .rec-on-track  { background: #f0fdf4; border-color: #bbf7d0; }
        .rec-slight    { background: #fffbeb; border-color: #fde68a; }
        .rec-at-risk   { background: #fff5f5; border-color: #fecaca; }
        .rec-revision  { background: #fdf4ff; border-color: #e9d5ff; }

        .fmn-rec-label {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin: 0 0 6px;
        }
        .label-on-track  { color: #059669; }
        .label-slight    { color: #d97706; }
        .label-at-risk   { color: #dc2626; }
        .label-revision  { color: #7c3aed; }

        .fmn-rec-text {
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.6;
            margin: 0;
        }
        .text-on-track  { color: #065f46; }
        .text-slight    { color: #92400e; }
        .text-at-risk   { color: #991b1b; }
        .text-revision  { color: #4c1d95; }

        /* ── WEEK INDICATOR ── */
        .fmn-week-card {
            background: white;
            border: 1.5px solid #f3e8ff;
            border-radius: 18px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .fmn-week-circle {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #a855f7, #ec4899);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .fmn-week-num {
            font-family: 'Fredoka One', cursive;
            font-size: 1.3rem;
            line-height: 1;
        }
        .fmn-week-lbl {
            font-size: 0.55rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 0.85;
        }
        .fmn-week-info { flex: 1; min-width: 0; }
        .fmn-week-title {
            font-weight: 800;
            font-size: 0.95rem;
            color: #1f2937;
            margin: 0 0 3px;
        }
        .fmn-week-sub {
            font-size: 0.78rem;
            color: #9ca3af;
            margin: 0;
        }

        /* ── STATS GRID ── */
        .fmn-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
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
        .num-green  { color: #059669; }
        .num-amber  { color: #d97706; }
        .num-red    { color: #dc2626; }
        .num-purple { color: #9333ea; }
    </style>
</head>
<body>

    {{-- NAVBAR --}}
    <nav class="fmn-nav">
        <span class="fmn-nav-brand">✨ ForMyNieces</span>
        <div class="fmn-nav-right">
            <span class="fmn-nav-greeting">Hi, {{ $user->name }} 👋</span>
            <a href="{{ route('dashboard') }}" class="fmn-btn fmn-btn-ghost fmn-btn-sm">
                ← Dashboard
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="fmn-btn fmn-btn-ghost fmn-btn-sm">Log out</button>
            </form>
        </div>
    </nav>

    <div class="fmn-page">

        @php
            $status     = $examAgent['overall_status'];
            $inRevision = $examAgent['in_revision'];

            $heroClass = match(true) {
                $inRevision          => 'hero-revision',
                $status === 'on_track'    => 'hero-on-track',
                $status === 'slight_risk' => 'hero-slight',
                default                   => 'hero-at-risk',
            };

            $heroIcon = match(true) {
                $inRevision               => '🎓',
                $status === 'on_track'    => '🌟',
                $status === 'slight_risk' => '⚡',
                default                   => '🚨',
            };

            $heroTitle = match(true) {
                $inRevision               => 'Revision Mode — Exam Soon!',
                $status === 'on_track'    => 'You\'re On Track! Keep Going!',
                $status === 'slight_risk' => 'Slightly Behind — Let\'s Catch Up!',
                default                   => 'At Risk — Action Needed!',
            };

            $recClass   = $inRevision ? 'rec-revision' :
                match($status) { 'on_track'=>'rec-on-track','slight_risk'=>'rec-slight',default=>'rec-at-risk' };
            $labelClass = $inRevision ? 'label-revision' :
                match($status) { 'on_track'=>'label-on-track','slight_risk'=>'label-slight',default=>'label-at-risk' };
            $textClass  = $inRevision ? 'text-revision' :
                match($status) { 'on_track'=>'text-on-track','slight_risk'=>'text-slight',default=>'text-at-risk' };
        @endphp

        {{-- HERO --}}
        <div class="fmn-hero {{ $heroClass }}">
            <p class="fmn-hero-title">{{ $heroIcon }} {{ $heroTitle }}</p>
            <p class="fmn-hero-sub">
                Your personalised SEA pacing report — based on the official
                Ministry of Education syllabus and Standard 5 curriculum guide.
            </p>
            <div class="fmn-chips">
                <span class="fmn-chip">📅 Exam: {{ $examAgent['exam_date'] }}</span>
                <span class="fmn-chip">⏳ {{ $examAgent['weeks_to_exam'] }} weeks to go</span>
                <span class="fmn-chip">📚 Teaching Week {{ $examAgent['current_week'] }} of 30</span>
            </div>
        </div>

        {{-- WEEK INDICATOR --}}
        <div class="fmn-week-card">
            <div class="fmn-week-circle">
                <span class="fmn-week-num">{{ $examAgent['current_week'] }}</span>
                <span class="fmn-week-lbl">Week</span>
            </div>
            <div class="fmn-week-info">
                <p class="fmn-week-title">
                    @if($inRevision)
                        You are in the 6-week revision buffer period
                    @elseif($examAgent['current_week'] === 0)
                        School year has not started yet
                    @else
                        Currently in Teaching Week {{ $examAgent['current_week'] }} of 30
                    @endif
                </p>
                <p class="fmn-week-sub">
                    All 90 syllabus modules should be completed by Week 30 (Apr 9, 2026),
                    leaving 6 weeks for revision before the SEA exam.
                </p>
            </div>
        </div>

        {{-- OVERALL STATS --}}
        @php
            $totalExpected  = collect($examAgent['subject_analysis'])->sum('expected');
            $totalCompleted = collect($examAgent['subject_analysis'])->sum('completed');
            $totalBehind    = $examAgent['total_behind'];
        @endphp
        <div class="fmn-stats">
            <div class="fmn-stat">
                <div class="fmn-stat-num num-purple">{{ $totalExpected }}</div>
                <div class="fmn-stat-lbl">Expected by Now</div>
            </div>
            <div class="fmn-stat">
                <div class="fmn-stat-num num-green">{{ $totalCompleted }}</div>
                <div class="fmn-stat-lbl">Completed</div>
            </div>
            <div class="fmn-stat">
                <div class="fmn-stat-num {{ $totalBehind > 0 ? 'num-red' : 'num-green' }}">
                    {{ $totalBehind }}
                </div>
                <div class="fmn-stat-lbl">Behind</div>
            </div>
        </div>

        {{-- RECOMMENDATION --}}
        <p class="fmn-section-title">💡 Agent Recommendation</p>
        <div class="fmn-recommendation {{ $recClass }} mb-5">
            <p class="fmn-rec-label {{ $labelClass }}">
                {{ $inRevision ? 'Revision Advice' :
                    match($status) { 'on_track'=>'Great News','slight_risk'=>'Action Advised',default=>'Urgent Action Required' } }}
            </p>
            <p class="fmn-rec-text {{ $textClass }}">
                {{ $examAgent['recommendation'] }}
            </p>
        </div>

        {{-- SUBJECT BREAKDOWN --}}
        <p class="fmn-section-title">📊 Subject Breakdown</p>

        @foreach($examAgent['subject_analysis'] as $subject => $data)
            @php
                $subjectStatus = $data['status'];
                $cardClass  = match($subjectStatus) {
                    'on_track'    => 'subject-on-track',
                    'slight_risk' => 'subject-slight',
                    'at_risk'     => 'subject-at-risk',
                    default       => 'subject-not-started',
                };
                $badgeClass = match($subjectStatus) {
                    'on_track'    => 'badge-on-track',
                    'slight_risk' => 'badge-slight',
                    'at_risk'     => 'badge-at-risk',
                    default       => 'badge-not-started',
                };
                $badgeText = match($subjectStatus) {
                    'on_track'    => '✅ On Track',
                    'slight_risk' => '⚡ Slight Risk',
                    'at_risk'     => '🚨 At Risk',
                    default       => '⭕ Not Started',
                };
                $nameClass  = match($subject) {
                    'Math'                  => 'name-math',
                    'English Editing'       => 'name-editing',
                    default                 => 'name-comprehension',
                };
                $fillClass  = match($subject) {
                    'Math'                  => 'fill-math',
                    'English Editing'       => 'fill-editing',
                    default                 => 'fill-comprehension',
                };
                $pct = $data['expected'] > 0
                    ? round(($data['completed'] / $data['expected']) * 100)
                    : 100;
            @endphp

            <div class="fmn-subject-card {{ $cardClass }}">

                <div class="fmn-subject-header">
                    <p class="fmn-subject-name {{ $nameClass }}">
                        @if($subject === 'Math') 🔢
                        @elseif($subject === 'English Editing') ✏️
                        @else 📖
                        @endif
                        {{ $subject }}
                    </p>
                    <span class="fmn-status-badge {{ $badgeClass }}">{{ $badgeText }}</span>
                </div>

                {{-- Progress bar --}}
                <div class="fmn-prog-labels">
                    <span>{{ $data['completed'] }}/{{ $data['expected'] }} expected topics done</span>
                    <span>{{ $pct }}%</span>
                </div>
                <div class="fmn-prog-track">
                    <div class="fmn-prog-fill {{ $fillClass }}" style="width:{{ $pct }}%;"></div>
                </div>

                {{-- Stats row --}}
                <div style="display:flex; gap:16px; flex-wrap:wrap; margin-top:10px; font-size:0.78rem; font-weight:700; color:#9ca3af;">
                    <span>Total modules: <strong style="color:#1f2937;">{{ $data['total'] }}</strong></span>
                    <span>Completed: <strong style="color:#059669;">{{ $data['completed'] }}</strong></span>
                    @if($data['behind_count'] > 0)
                        <span>Behind: <strong style="color:#dc2626;">{{ $data['behind_count'] }}</strong></span>
                        <span>Weeks lost: <strong style="color:#dc2626;">~{{ $data['weeks_lost'] }}</strong></span>
                    @endif
                    @if(count($data['ahead_modules']) > 0)
                        <span>Ahead: <strong style="color:#059669;">{{ count($data['ahead_modules']) }}</strong></span>
                    @endif
                </div>

                {{-- Behind modules --}}
                @if(count($data['behind_modules']) > 0)
                    <div class="fmn-behind-list">
                        <p class="fmn-behind-title">
                            🚨 {{ $data['behind_count'] }} topic(s) behind schedule
                        </p>
                        @foreach($data['behind_modules'] as $module)
                            <div class="fmn-behind-item">
                                <span class="fmn-behind-week">Wk {{ $module->pacing_week }}</span>
                                <p class="fmn-behind-topic">{{ $module->topic }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Ahead modules --}}
                @if(count($data['ahead_modules']) > 0)
                    <div class="fmn-ahead-list">
                        <p class="fmn-ahead-title">
                            ⭐ {{ count($data['ahead_modules']) }} topic(s) completed ahead of schedule
                        </p>
                        @foreach($data['ahead_modules'] as $module)
                            <div class="fmn-ahead-item">
                                <span class="fmn-ahead-week">Wk {{ $module->pacing_week }}</span>
                                <p class="fmn-ahead-topic">{{ $module->topic }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

            </div>
        @endforeach

        {{-- BACK BUTTON --}}
        <div style="text-align:center; margin-top:1.5rem;">
            <a href="{{ route('dashboard') }}" class="fmn-btn fmn-btn-primary">
                ← Back to Dashboard
            </a>
        </div>

    </div>{{-- end fmn-page --}}

</body>
</html>