<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Exam Agent ✨ — ForMyNieces</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka+One&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-annotation/3.0.1/chartjs-plugin-annotation.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { font-family: 'Nunito', sans-serif; background: #fdf4ff; margin: 0; min-height: 100vh; }

        /* NAV */
        .fmn-nav { background: rgba(255,255,255,0.92); backdrop-filter: blur(10px); border-bottom: 1.5px solid #f3e8ff; padding: 0 1rem; height: 58px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .fmn-nav-brand { font-family: 'Fredoka One', cursive; font-size: 1.4rem; color: #9333ea; text-decoration: none; }
        .fmn-nav-right { display: flex; align-items: center; gap: 10px; }
        @media (max-width: 480px) { .fmn-nav-greeting { display: none; } }
        .fmn-nav-greeting { font-size: 0.85rem; font-weight: 700; color: #a78bfa; }

        /* PAGE */
        .fmn-page { max-width: 860px; margin: 0 auto; padding: 1.25rem 1rem 3rem; }

        /* BUTTONS */
        .fmn-btn { display: inline-flex; align-items: center; gap: 6px; padding: 9px 20px; border-radius: 999px; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.85rem; cursor: pointer; border: none; transition: transform 0.15s, box-shadow 0.15s; text-decoration: none; }
        .fmn-btn-primary { background: linear-gradient(135deg, #a855f7, #ec4899); color: white; }
        .fmn-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(168,85,247,0.35); }
        .fmn-btn-ghost { background: white; color: #9333ea; border: 1.5px solid #e9d5ff; }
        .fmn-btn-ghost:hover { background: #fdf4ff; }
        .fmn-btn-sm { padding: 7px 16px; font-size: 0.8rem; }
        .fmn-btn-success { background: linear-gradient(135deg, #059669, #34d399); color: white; }

        /* HERO */
        .fmn-hero { border-radius: 20px; padding: 1.5rem; color: white; margin-bottom: 1.25rem; position: relative; overflow: hidden; }
        .fmn-hero::after { content: '🤖'; position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); font-size: 4rem; opacity: 0.18; pointer-events: none; }
        .hero-on-track { background: linear-gradient(135deg, #059669, #34d399); }
        .hero-slight { background: linear-gradient(135deg, #d97706, #fbbf24); }
        .hero-at-risk { background: linear-gradient(135deg, #dc2626, #f87171); }
        .hero-revision { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .fmn-hero-title { font-family: 'Fredoka One', cursive; font-size: 1.6rem; margin: 0 0 0.25rem; }
        .fmn-hero-sub { font-size: 0.88rem; opacity: 0.92; margin: 0 0 1rem; line-height: 1.5; }
        .fmn-chips { display: flex; gap: 10px; flex-wrap: wrap; }
        .fmn-chip { background: rgba(255,255,255,0.22); border: 1.5px solid rgba(255,255,255,0.35); border-radius: 999px; padding: 5px 14px; font-size: 0.8rem; font-weight: 800; color: white; }

        /* SECTION TITLE */
        .fmn-section-title { font-family: 'Fredoka One', cursive; font-size: 1.15rem; color: #7c3aed; display: flex; align-items: center; gap: 7px; margin: 1.5rem 0 0.85rem; }

        /* CARDS */
        .fmn-card { background: white; border: 1.5px solid #f3e8ff; border-radius: 18px; padding: 1.1rem 1.25rem; margin-bottom: 1rem; }

        /* CHART SECTION */
        .fmn-chart-card { background: white; border: 1.5px solid #f3e8ff; border-radius: 18px; padding: 1.25rem; margin-bottom: 1rem; }
        .fmn-chart-tabs { display: flex; gap: 8px; margin-bottom: 1rem; flex-wrap: wrap; }
        .fmn-chart-tab { padding: 6px 16px; border-radius: 999px; font-size: 0.8rem; font-weight: 800; cursor: pointer; border: 1.5px solid #e9d5ff; background: white; color: #7c3aed; transition: all 0.18s; }
        .fmn-chart-tab.active { background: linear-gradient(135deg, #9333ea, #db2777); color: white; border-color: transparent; }
        .fmn-chart-tab.active-math { background: linear-gradient(135deg, #059669, #34d399); color: white; border-color: transparent; }
        .fmn-chart-tab.active-editing { background: linear-gradient(135deg, #db2777, #f472b6); color: white; border-color: transparent; }
        .fmn-chart-tab.active-comp { background: linear-gradient(135deg, #7c3aed, #a78bfa); color: white; border-color: transparent; }
        .fmn-chart-wrap { position: relative; height: 260px; }

        /* THERMOMETERS */
        .fmn-thermo-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-bottom: 1rem; }
        @media (max-width: 480px) { .fmn-thermo-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .fmn-thermo-card { background: white; border: 1.5px solid #f3e8ff; border-radius: 16px; padding: 1rem 0.75rem; text-align: center; }
        .fmn-thermo-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; margin-bottom: 10px; }
        .fmn-thermo-wrap { display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .fmn-thermo-svg { width: 36px; }
        .fmn-thermo-pct { font-family: 'Fredoka One', cursive; font-size: 1.3rem; }
        .fmn-thermo-status { font-size: 0.72rem; font-weight: 800; margin-top: 2px; }
        .thermo-hot .fmn-thermo-pct { color: #059669; }
        .thermo-hot .fmn-thermo-status { color: #059669; }
        .thermo-warm .fmn-thermo-pct { color: #d97706; }
        .thermo-warm .fmn-thermo-status { color: #d97706; }
        .thermo-cold .fmn-thermo-pct { color: #dc2626; }
        .thermo-cold .fmn-thermo-status { color: #dc2626; }

        /* AI SUMMARY TABS */
        .fmn-summary-tabs { display: flex; gap: 8px; margin-bottom: 1rem; }
        .fmn-summary-tab { padding: 7px 18px; border-radius: 999px; font-size: 0.82rem; font-weight: 800; cursor: pointer; border: 1.5px solid #e9d5ff; background: white; color: #7c3aed; transition: all 0.18s; }
        .fmn-summary-tab.active { background: linear-gradient(135deg, #9333ea, #db2777); color: white; border-color: transparent; }
        .fmn-summary-content { display: none; }
        .fmn-summary-content.active { display: block; }
        .fmn-summary-text { font-size: 0.9rem; line-height: 1.75; color: #374151; }
        .fmn-summary-loading { display: flex; align-items: center; gap: 10px; color: #a78bfa; font-weight: 700; font-size: 0.88rem; padding: 1rem 0; }
        .fmn-spinner { width: 20px; height: 20px; border: 2.5px solid #e9d5ff; border-top-color: #9333ea; border-radius: 50%; animation: spin 0.8s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* TIMETABLE */
        .fmn-timetable { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px; margin-bottom: 1rem; }
        @media (max-width: 600px) { .fmn-timetable { grid-template-columns: 1fr; } }
        .fmn-tt-day { background: white; border: 1.5px solid #f3e8ff; border-radius: 14px; padding: 0.85rem 0.75rem; }
        .fmn-tt-day-name { font-family: 'Fredoka One', cursive; font-size: 0.9rem; color: #9333ea; margin-bottom: 8px; }
        .fmn-tt-item { font-size: 0.72rem; font-weight: 700; padding: 4px 8px; border-radius: 8px; margin-bottom: 5px; line-height: 1.3; }
        .tt-math { background: #d1fae5; color: #065f46; }
        .tt-editing { background: #fce7f3; color: #9d174d; }
        .tt-comp { background: #ede9fe; color: #4c1d95; }
        .tt-revision { background: #fef3c7; color: #92400e; }

        /* TOPIC GRID */
        .fmn-topic-filters { display: flex; gap: 8px; margin-bottom: 1rem; flex-wrap: wrap; }
        .fmn-filter-btn { padding: 6px 14px; border-radius: 999px; font-size: 0.78rem; font-weight: 800; cursor: pointer; border: 1.5px solid #e9d5ff; background: white; color: #7c3aed; transition: all 0.18s; }
        .fmn-filter-btn.active { background: linear-gradient(135deg, #9333ea, #db2777); color: white; border-color: transparent; }

        .fmn-topic-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        @media (min-width: 640px) { .fmn-topic-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (min-width: 860px) { .fmn-topic-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .fmn-topic-tile { background: white; border: 1.5px solid #f3e8ff; border-radius: 12px; padding: 0.75rem; cursor: pointer; transition: all 0.15s; position: relative; }
        .fmn-topic-tile:hover { border-color: #c4b5fd; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(147,51,234,0.12); }
        .fmn-topic-tile.mastered { border-color: #a7f3d0; }
        .fmn-topic-tile.diagnostic { border-color: #ddd6fe; }
        .fmn-topic-tile.not-started { opacity: 0.7; }
        .fmn-tile-subject { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .tile-math { color: #059669; }
        .tile-editing { color: #db2777; }
        .tile-comp { color: #7c3aed; }
        .fmn-tile-topic { font-size: 0.75rem; font-weight: 700; color: #1f2937; line-height: 1.3; margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .fmn-tile-score { font-size: 0.7rem; font-weight: 800; }
        .score-good { color: #059669; }
        .score-mid { color: #d97706; }
        .score-low { color: #dc2626; }
        .score-none { color: #9ca3af; }
        .fmn-tile-week { position: absolute; top: 6px; right: 8px; font-size: 0.6rem; font-weight: 800; color: #c4b5fd; }

        /* MODAL OVERLAY */
        .fmn-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 200; align-items: flex-end; justify-content: center; }
        .fmn-modal-overlay.open { display: flex; }
        @media (min-width: 640px) { .fmn-modal-overlay { align-items: center; } }
        .fmn-modal { background: white; border-radius: 24px 24px 0 0; width: 100%; max-width: 560px; max-height: 88vh; overflow-y: auto; padding: 1.5rem; }
        @media (min-width: 640px) { .fmn-modal { border-radius: 24px; } }
        .fmn-modal-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 1rem; }
        .fmn-modal-title { font-family: 'Fredoka One', cursive; font-size: 1.1rem; color: #1f2937; margin: 0; line-height: 1.3; }
        .fmn-modal-close { background: #f3e8ff; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; font-size: 1rem; color: #7c3aed; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .fmn-modal-section { margin-bottom: 1rem; }
        .fmn-modal-label { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: #c4b5fd; margin-bottom: 6px; }
        .fmn-modal-text { font-size: 0.85rem; color: #374151; line-height: 1.65; }
        .fmn-score-row { display: flex; gap: 10px; flex-wrap: wrap; }
        .fmn-score-chip { background: #fdf4ff; border: 1.5px solid #e9d5ff; border-radius: 12px; padding: 8px 14px; text-align: center; }
        .fmn-score-chip-num { font-family: 'Fredoka One', cursive; font-size: 1.4rem; color: #9333ea; }
        .fmn-score-chip-lbl { font-size: 0.65rem; font-weight: 800; color: #c4b5fd; text-transform: uppercase; }
        .fmn-resource-list { list-style: none; padding: 0; margin: 0; }
        .fmn-resource-list li { padding: 7px 0; border-bottom: 1px solid #f3e8ff; font-size: 0.83rem; }
        .fmn-resource-list li:last-child { border-bottom: none; }
        .fmn-resource-list a { color: #7c3aed; font-weight: 700; text-decoration: none; }
        .fmn-resource-list a:hover { text-decoration: underline; }
        .fmn-resource-no-url { color: #9ca3af; }
    </style>
</head>
<body>

{{-- NAVBAR --}}
<nav class="fmn-nav">
    <span class="fmn-nav-brand">✨ ForMyNieces</span>
    <div class="fmn-nav-right">
        <span class="fmn-nav-greeting">Hi, {{ $user->name }} 👋</span>
        <a href="{{ route('dashboard') }}" class="fmn-btn fmn-btn-ghost fmn-btn-sm">← Dashboard</a>
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
    $heroClass  = match(true) {
        $inRevision               => 'hero-revision',
        $status === 'on_track'    => 'hero-on-track',
        $status === 'slight_risk' => 'hero-slight',
        default                   => 'hero-at-risk',
    };
    $heroIcon  = match(true) {
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

    // Build chart data for all subjects
    $allModules    = \App\Models\SyllabusModule::orderBy('pacing_week')->orderBy('sequence_order')->get();
    $progressMap   = \App\Models\StudentProgress::where('student_id', $user->id)->get()->keyBy('module_id');
    $currentWeek   = $examAgent['current_week'];
    $totalWeeks    = 30;

    // Build cumulative data per week for each subject + all
    $subjects = ['all', 'Math', 'English Editing', 'English Comprehension'];
    $chartData = [];

    foreach ($subjects as $subj) {
        $subModules = $subj === 'all'
            ? $allModules
            : $allModules->where('subject', $subj);

        $total = $subModules->count();
        $required = [];
        $actual   = [];
        $completedSoFar = 0;

        for ($w = 1; $w <= $totalWeeks; $w++) {
            // Required: modules that should be done by this week
            $requiredCount = $subModules->where('pacing_week', '<=', $w)->count();
            $required[] = $requiredCount;

            // Actual: modules completed (mastered or diagnostic_passed) with pacing_week <= w
            $actualCount = $subModules->filter(function($m) use ($progressMap, $w) {
                $p = $progressMap->get($m->id);
                return $p && in_array($p->status, ['mastered', 'diagnostic_passed']) && $m->pacing_week <= $w;
            })->count();
            $actual[] = $actualCount;
        }

        // Projected trajectory: linear from current actual to end
        $currentActual = $actual[$currentWeek - 1] ?? 0;
        $weeksLeft = $totalWeeks - $currentWeek;
        $modulesLeft = $total - $currentActual;
        $projected = [];
        for ($w = 1; $w <= $totalWeeks; $w++) {
            if ($w <= $currentWeek) {
                $projected[] = null;
            } else {
                $rate = $weeksLeft > 0 ? $modulesLeft / $weeksLeft : 0;
                $projected[] = round($currentActual + ($rate * ($w - $currentWeek)));
            }
        }

        $chartData[$subj] = [
            'required'  => $required,
            'actual'    => $actual,
            'projected' => $projected,
            'total'     => $total,
        ];
    }

    // Thermometer data
    $thermoData = [];
    foreach (['Math', 'English Editing', 'English Comprehension'] as $subj) {
        $analysis = $examAgent['subject_analysis'][$subj];
        $pct = $analysis['expected'] > 0
            ? round(($analysis['completed'] / $analysis['expected']) * 100)
            : ($currentWeek === 0 ? 100 : 0);
        $thermoData[$subj] = [
            'pct'    => $pct,
            'status' => $pct >= 85 ? 'hot' : ($pct >= 60 ? 'warm' : 'cold'),
            'label'  => $pct >= 85 ? '🔥 Hot' : ($pct >= 60 ? '⚡ Warm' : '🧊 Cold'),
        ];
    }

    // Build next week timetable
    $nextWeek = $currentWeek > $totalWeeks ? $totalWeeks : min($currentWeek + 1, $totalWeeks);
    $nextWeekModules = $allModules->where('pacing_week', $nextWeek);
    $behindModules = collect();
    foreach ($examAgent['subject_analysis'] as $subj => $data) {
        foreach ($data['behind_modules'] as $m) {
            $behindModules->push(['module' => $m, 'subject' => $subj]);
        }
    }

    // Assign topics to Mon-Fri
    $timetable = ['Monday' => [], 'Tuesday' => [], 'Wednesday' => [], 'Thursday' => [], 'Friday' => []];

    // Next week scheduled modules
    $nextWeekModules = $allModules->where('pacing_week', $nextWeek);
    $mathNext    = $nextWeekModules->where('subject', 'Math')->values();
    $editNext    = $nextWeekModules->where('subject', 'English Editing')->values();
    $compNext    = $nextWeekModules->where('subject', 'English Comprehension')->values();

    // Build next week timetable
    $nextWeek   = min($currentWeek + 1, $totalWeeks);
    $status     = $examAgent['overall_status'];
    $isAtRisk   = $status === 'at_risk';
    $isSlight   = $status === 'slight_risk';
    $dailyMins  = ($isAtRisk || $isSlight) ? 120 : 90;
    $studyDays  = ($isAtRisk || $isSlight)
        ? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
        : ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    $mathMins = (int) round($dailyMins * 0.50);
    $editMins = (int) round($dailyMins * 0.30);
    $compMins = (int) round($dailyMins * 0.20);

    $behindModules = collect();
    foreach ($examAgent['subject_analysis'] as $subj => $data) {
        foreach ($data['behind_modules'] as $m) {
            $behindModules->push(['module' => $m, 'subject' => $subj]);
        }
    }

    $nextWeekModules = $allModules->where('pacing_week', $nextWeek);
    $mathNext = $nextWeekModules->where('subject', 'Math')->values();
    $editNext = $nextWeekModules->where('subject', 'English Editing')->values();
    $compNext = $nextWeekModules->where('subject', 'English Comprehension')->values();

    $daySchedule = [
        'Monday'    => [
            'primary'   => ['subject' => 'Math',                  'module' => $mathNext->get(0), 'mins' => $mathMins],
            'secondary' => ['subject' => 'English Comprehension', 'module' => $compNext->get(0), 'mins' => $compMins],
        ],
        'Tuesday'   => [
            'primary'   => ['subject' => 'English Editing',       'module' => $editNext->get(0), 'mins' => $editMins],
            'secondary' => ['subject' => 'Math',                  'module' => $mathNext->get(1), 'mins' => $mathMins],
        ],
        'Wednesday' => [
            'primary'   => ['subject' => 'Math',                  'module' => $mathNext->get(2), 'mins' => $mathMins],
            'secondary' => ['subject' => 'English Editing',       'module' => $editNext->get(1), 'mins' => $editMins],
        ],
        'Thursday'  => [
            'primary'   => ['subject' => 'English Comprehension', 'module' => $compNext->get(1), 'mins' => $compMins],
            'secondary' => ['subject' => 'Math',                  'module' => $mathNext->get(3), 'mins' => $mathMins],
        ],
        'Friday'    => [
            'primary'   => ['subject' => 'Math',                  'module' => $mathNext->get(4), 'mins' => $mathMins],
            'secondary' => ['subject' => 'English Editing',       'module' => $editNext->get(2), 'mins' => $editMins],
        ],
        'Saturday'  => [
            'primary'   => ['subject' => 'Math',                  'module' => $behindModules->get(0)['module'] ?? null, 'mins' => $mathMins],
            'secondary' => ['subject' => 'English Editing',       'module' => $behindModules->get(1)['module'] ?? null, 'mins' => $editMins],
        ],
    ];

    // Build progress map for topic grid
    $allProgress = \App\Models\StudentProgress::where('student_id', $user->id)->get()->keyBy('module_id');
    $topicJson = $allModules->map(fn($m) => [
        'id'          => $m->id,
        'topic'       => $m->topic,
        'subject'     => $m->subject,
        'sea_section' => $m->sea_section,
        'pacing_week' => $m->pacing_week,
        'description' => $m->description,
        'resources'   => $m->resources ?? [],
    ])->keyBy('id');

    $progressJson = $allProgress->map(fn($p) => [
        'status'         => $p->status,
        'score'          => $p->score,
        'previous_score' => $p->previous_score,
    ]);
@endphp

{{-- ═══════════════════════════════════════ --}}
{{-- HERO --}}
{{-- ═══════════════════════════════════════ --}}
<div class="fmn-hero {{ $heroClass }}">
    <p class="fmn-hero-title">{{ $heroIcon }} {{ $heroTitle }}</p>
    <p class="fmn-hero-sub">
        Your personalised SEA pacing report — based on the official Ministry of Education
        syllabus and Standard 5 curriculum guide.
    </p>
    <div class="fmn-chips">
        <span class="fmn-chip">📅 Exam: {{ $examAgent['exam_date'] }}</span>
        <span class="fmn-chip">⏳ {{ $examAgent['weeks_to_exam'] }} weeks to go</span>
        <span class="fmn-chip">📚 Teaching Week {{ $examAgent['current_week'] }} of 30</span>
        <span class="fmn-chip">
            @if($examAgent['total_behind'] === 0) ✅ All caught up
            @else 🚨 {{ $examAgent['total_behind'] }} topic(s) behind
            @endif
        </span>
    </div>
</div>

{{-- ═══════════════════════════════════════ --}}
{{-- THERMOMETERS --}}
{{-- ═══════════════════════════════════════ --}}
<p class="fmn-section-title">🌡️ Subject Temperature</p>
<div class="fmn-thermo-grid">
    @foreach(['Math' => '🔢', 'English Editing' => '✏️', 'English Comprehension' => '📖'] as $subj => $icon)
        @php
            $t = $thermoData[$subj];
            $fillH = round($t['pct'] * 0.6);
        @endphp
        <div class="fmn-thermo-card thermo-{{ $t['status'] }}">
            <div class="fmn-thermo-label">{{ $icon }} {{ $subj === 'English Comprehension' ? 'Comprehension' : ($subj === 'English Editing' ? 'ELA Editing' : $subj) }}</div>
            <div class="fmn-thermo-wrap">
                <svg class="fmn-thermo-svg" viewBox="0 0 36 100" xmlns="http://www.w3.org/2000/svg">
                    <!-- Tube background -->
                    <rect x="13" y="5" width="10" height="72" rx="5" fill="#f3e8ff"/>
                    <!-- Fill -->
                    @php $fillY = 5 + (72 - $fillH); $fillColor = $t['status'] === 'hot' ? '#10b981' : ($t['status'] === 'warm' ? '#f59e0b' : '#ef4444'); @endphp
                    <rect x="13" y="{{ $fillY }}" width="10" height="{{ $fillH }}" rx="3" fill="{{ $fillColor }}"/>
                    <!-- Bulb -->
                    <circle cx="18" cy="85" r="11" fill="{{ $fillColor }}"/>
                    <circle cx="18" cy="85" r="7" fill="white" opacity="0.3"/>
                </svg>
                <div class="fmn-thermo-pct">{{ $t['pct'] }}%</div>
                <div class="fmn-thermo-status">{{ $t['label'] }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- ═══════════════════════════════════════ --}}
{{-- PROGRESS CHART --}}
{{-- ═══════════════════════════════════════ --}}
<p class="fmn-section-title">📈 Progress Chart</p>
<div class="fmn-chart-card">
    <div class="fmn-chart-tabs">
        <button class="fmn-chart-tab active" onclick="switchChart('all', this)">✨ All</button>
        <button class="fmn-chart-tab" onclick="switchChart('math', this)">🔢 Math</button>
        <button class="fmn-chart-tab" onclick="switchChart('editing', this)">✏️ ELA Editing</button>
        <button class="fmn-chart-tab" onclick="switchChart('comp', this)">📖 Comprehension</button>
    </div>
    <div class="fmn-chart-wrap" style="position:relative; height:320px;">
        <canvas id="progressChart"></canvas>
    </div>
    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px; font-size:0.73rem; font-weight:700; color:#374151;">
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:22px;height:3px;background:#9333ea;border-radius:2px;display:inline-block;"></span> Required Pace</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:22px;height:3px;background:#10b981;border-radius:2px;display:inline-block;"></span> Actual Progress</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:22px;height:3px;border-top:2.5px dashed #6b7280;display:inline-block;"></span> Current Trajectory</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:22px;height:3px;border-top:2.5px dashed #10b981;display:inline-block;"></span> Corrected Pace</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:14px;height:10px;background:rgba(16,185,129,0.15);border-radius:2px;display:inline-block;border:1px solid rgba(16,185,129,0.4);"></span> On Track Zone</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:14px;height:10px;background:rgba(245,158,11,0.15);border-radius:2px;display:inline-block;border:1px solid rgba(245,158,11,0.4);"></span> Worry Zone (≤5 behind)</span>
        <span style="display:flex;align-items:center;gap:5px;"><span style="width:14px;height:10px;background:rgba(239,68,68,0.13);border-radius:2px;display:inline-block;border:1px solid rgba(239,68,68,0.35);"></span> Support Required (5+ behind)</span>
    </div>
</div>

{{-- ═══════════════════════════════════════ --}}
{{-- AI SUMMARY --}}
{{-- ═══════════════════════════════════════ --}}
<p class="fmn-section-title">🤖 AI Performance Summary</p>
<div class="fmn-card">
    <div class="fmn-summary-tabs">
        <button class="fmn-summary-tab active" onclick="switchSummary('student', this)">👧 For the Student</button>
        <button class="fmn-summary-tab" onclick="switchSummary('parent', this)">👩 For the Parent</button>
    </div>

    <div id="summary-student" class="fmn-summary-content active">
        <div class="fmn-summary-loading" id="loading-student">
            <div class="fmn-spinner"></div>
            Generating your personalised summary...
        </div>
        <div class="fmn-summary-text" id="text-student" style="display:none;"></div>
    </div>

    <div id="summary-parent" class="fmn-summary-content">
        <div class="fmn-summary-loading" id="loading-parent" style="display:none;">
            <div class="fmn-spinner"></div>
            Generating parent report...
        </div>
        <div class="fmn-summary-text" id="text-parent" style="display:none;"></div>
    </div>
</div>

{{-- ═══════════════════════════════════════ --}}
{{-- NEXT WEEK TIMETABLE --}}
{{-- ═══════════════════════════════════════ --}}
<p class="fmn-section-title">📅 Next Week Study Plan</p>

{{-- Summary bar --}}
<div style="background:white; border:1.5px solid #f3e8ff; border-radius:14px; padding:0.85rem 1.25rem; margin-bottom:1rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div style="font-size:0.85rem; font-weight:700; color:#1f2937;">
        📆 {{ count($studyDays) }} study days · ⏱️ {{ $dailyMins }} mins/day · 🧮 {{ $dailyMins * count($studyDays) }} mins total this week
    </div>
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <span style="background:#d1fae5; color:#065f46; padding:3px 10px; border-radius:999px; font-size:0.72rem; font-weight:800;">Math: {{ $mathMins }}m/day</span>
        <span style="background:#fce7f3; color:#9d174d; padding:3px 10px; border-radius:999px; font-size:0.72rem; font-weight:800;">ELA: {{ $editMins }}m/day</span>
        <span style="background:#ede9fe; color:#4c1d95; padding:3px 10px; border-radius:999px; font-size:0.72rem; font-weight:800;">Comp: {{ $compMins }}m/day</span>
    </div>
</div>

{{-- Timetable: rows = subjects, columns = days --}}
<div style="background:white; border:1.5px solid #f3e8ff; border-radius:18px; overflow:hidden; margin-bottom:1rem;">
    <table style="width:100%; border-collapse:collapse; font-size:0.78rem;">
        {{-- Header row --}}
        <thead>
            <tr style="background:#fdf4ff;">
                <th style="padding:10px 12px; text-align:left; font-weight:800; color:#7c3aed; border-bottom:1.5px solid #f3e8ff; width:100px;">Subject</th>
                @foreach($studyDays as $day)
                    <th style="padding:10px 8px; text-align:center; font-weight:800; color:#7c3aed; border-bottom:1.5px solid #f3e8ff; @if($day === 'Saturday') background:#fffbeb; color:#d97706; @endif">
                        {{ substr($day, 0, 3) }}
                        @if($day === 'Saturday')<br><span style="font-size:0.6rem; font-weight:700;">catch-up</span>@endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            {{-- MATH ROW --}}
            <tr>
            <td style="padding:10px 12px; font-weight:800; color:#065f46; background:#f0fdf4; border-bottom:1.5px solid #f3e8ff; vertical-align:top;">
                🔢 Math
            </td>
                @foreach($studyDays as $day)
                    @php
                        $d = $daySchedule[$day];
                        $module = null;
                        if ($d['primary']['subject'] === 'Math') $module = $d['primary']['module'];
                        elseif ($d['secondary']['subject'] === 'Math') $module = $d['secondary']['module'];
                    @endphp
                    <td style="padding:8px; border-bottom:1.5px solid #f3e8ff; border-left:1px solid #f3e8ff; vertical-align:top; @if($day === 'Saturday') background:#fffbeb; @endif">
                        @if($module)
                        <div style="background:#d1fae5; color:#065f46; padding:5px 7px; border-radius:8px; font-size:0.72rem; font-weight:700; line-height:1.3;">
                            {{ Str::limit($module->topic, 40) }}
                            <div style="font-size:0.65rem; margin-top:3px; opacity:0.8;">⏱ {{ $mathMins }} mins</div>
                        </div>
                        @else
                            <div style="color:#9ca3af; font-size:0.72rem; text-align:center; padding:4px;">—</div>
                        @endif
                    </td>
                @endforeach
            </tr>

            {{-- ELA EDITING ROW --}}
            <tr>
            <td style="padding:10px 12px; font-weight:800; color:#9d174d; background:#fdf2f8; border-bottom:1.5px solid #f3e8ff; vertical-align:top;">
                ✏️ ELA Editing
            </td>
                @foreach($studyDays as $day)
                    @php
                        $d = $daySchedule[$day];
                        $module = null;
                        if ($d['primary']['subject'] === 'English Editing') $module = $d['primary']['module'];
                        elseif ($d['secondary']['subject'] === 'English Editing') $module = $d['secondary']['module'];
                    @endphp
                    <td style="padding:8px; border-bottom:1.5px solid #f3e8ff; border-left:1px solid #f3e8ff; vertical-align:top; @if($day === 'Saturday') background:#fffbeb; @endif">
                        @if($module)
                        <div style="background:#fce7f3; color:#9d174d; padding:5px 7px; border-radius:8px; font-size:0.72rem; font-weight:700; line-height:1.3;">
                            {{ Str::limit($module->topic, 40) }}
                            <div style="font-size:0.65rem; margin-top:3px; opacity:0.8;">⏱ {{ $editMins }} mins</div>
                        </div>
                        @else
                            <div style="color:#9ca3af; font-size:0.72rem; text-align:center; padding:4px;">—</div>
                        @endif
                    </td>
                @endforeach
            </tr>

            {{-- COMPREHENSION ROW --}}
            <tr>
            <td style="padding:10px 12px; font-weight:800; color:#9d174d; background:#fdf2f8; border-bottom:1.5px solid #f3e8ff; vertical-align:top;">
                ✏️ ELA Editing
            </td>
                @foreach($studyDays as $day)
                    @php
                        $d = $daySchedule[$day];
                        $module = null;
                        if ($d['primary']['subject'] === 'English Comprehension') $module = $d['primary']['module'];
                        elseif ($d['secondary']['subject'] === 'English Comprehension') $module = $d['secondary']['module'];
                    @endphp
                    <td style="padding:8px; border-left:1px solid #f3e8ff; vertical-align:top; @if($day === 'Saturday') background:#fffbeb; @endif">
                        @if($module)
                        <div style="background:#ede9fe; color:#4c1d95; padding:5px 7px; border-radius:8px; font-size:0.72rem; font-weight:700; line-height:1.3;">
                            {{ Str::limit($module->topic, 40) }}
                            <div style="font-size:0.65rem; margin-top:3px; opacity:0.8;">⏱ {{ $compMins }} mins</div>
                        </div>
                        @else
                            <div style="color:#9ca3af; font-size:0.72rem; text-align:center; padding:4px;">—</div>
                        @endif
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
</div>




{{-- ═══════════════════════════════════════ --}}
{{-- TOPIC GRID --}}
{{-- ═══════════════════════════════════════ --}}
<p class="fmn-section-title">📋 All Topics</p>
<div class="fmn-topic-filters">
    <button class="fmn-filter-btn active" onclick="filterTopics('all', this)">All ({{ $allModules->count() }})</button>
    <button class="fmn-filter-btn" onclick="filterTopics('math', this)">🔢 Math</button>
    <button class="fmn-filter-btn" onclick="filterTopics('editing', this)">✏️ ELA Editing</button>
    <button class="fmn-filter-btn" onclick="filterTopics('comp', this)">📖 Comprehension</button>
    <button class="fmn-filter-btn" onclick="filterTopics('mastered', this)">✅ Mastered</button>
    <button class="fmn-filter-btn" onclick="filterTopics('behind', this)">🚨 Behind</button>
</div>

<div class="fmn-topic-grid" id="topicGrid">
    @foreach($allModules as $module)
        @php
            $prog      = $allProgress->get($module->id);
            $status    = $prog?->status ?? 'not_started';
            $score     = $prog?->score;
            $prevScore = $prog?->previous_score;
            $tileClass = match($status) {
                'mastered'          => 'mastered',
                'diagnostic_passed' => 'diagnostic',
                default             => 'not-started',
            };
            $subjKey = match($module->subject) {
                'Math'                  => 'math',
                'English Editing'       => 'editing',
                default                 => 'comp',
            };
            $subjClass = match($module->subject) {
                'Math'                  => 'tile-math',
                'English Editing'       => 'tile-editing',
                default                 => 'tile-comp',
            };
            $scoreClass = match(true) {
                $score === null   => 'score-none',
                $score >= 80      => 'score-good',
                $score >= 60      => 'score-mid',
                default           => 'score-low',
            };
            $isBehind = in_array($module->id, collect($examAgent['subject_analysis'])
                ->flatMap(fn($s) => collect($s['behind_modules'])->pluck('id'))
                ->toArray());
        @endphp
        <div class="fmn-topic-tile {{ $tileClass }}"
             data-subject="{{ $subjKey }}"
             data-status="{{ $status }}"
             data-behind="{{ $isBehind ? 'true' : 'false' }}"
             onclick="openTopic({{ $module->id }})">
            <div class="fmn-tile-week">Wk {{ $module->pacing_week }}</div>
            <div class="fmn-tile-subject {{ $subjClass }}">
                {{ $module->subject === 'English Comprehension' ? 'Comprehension' : ($module->subject === 'English Editing' ? 'ELA Editing' : $module->subject) }}
            </div>
            <div class="fmn-tile-topic">{{ $module->topic }}</div>
            <div class="fmn-tile-score {{ $scoreClass }}">
                @if($score !== null)
                    {{ $score }}%
                    @if($prevScore !== null)
                        @if($score > $prevScore) ↑ @elseif($score < $prevScore) ↓ @else → @endif
                    @endif
                @else
                    Not attempted
                @endif
            </div>
        </div>
    @endforeach
</div>

</div>{{-- end fmn-page --}}

{{-- ═══════════════════════════════════════ --}}
{{-- TOPIC MODAL --}}
{{-- ═══════════════════════════════════════ --}}
<div class="fmn-modal-overlay" id="topicModal" onclick="closeModal(event)">
    <div class="fmn-modal">
        <div class="fmn-modal-header">
            <p class="fmn-modal-title" id="modal-title">Topic</p>
            <button class="fmn-modal-close" onclick="closeTopicModal()">✕</button>
        </div>

        <div class="fmn-modal-section">
            <div class="fmn-modal-label">What This Tests</div>
            <p class="fmn-modal-text" id="modal-description">—</p>
        </div>

        <div class="fmn-modal-section">
            <div class="fmn-modal-label">Diagnostic Results</div>
            <div class="fmn-score-row" id="modal-scores">
                <div class="fmn-score-chip">
                    <div class="fmn-score-chip-num" id="modal-score-latest">—</div>
                    <div class="fmn-score-chip-lbl">Latest</div>
                </div>
                <div class="fmn-score-chip">
                    <div class="fmn-score-chip-num" id="modal-score-prev">—</div>
                    <div class="fmn-score-chip-lbl">Previous</div>
                </div>
                <div class="fmn-score-chip">
                    <div class="fmn-score-chip-num" id="modal-status-chip">—</div>
                    <div class="fmn-score-chip-lbl">Status</div>
                </div>
            </div>
        </div>

        <div class="fmn-modal-section">
            <div class="fmn-modal-label">Suggested Resources</div>
            <ul class="fmn-resource-list" id="modal-resources"></ul>
        </div>

        <div style="margin-top:1rem;">
            <button class="fmn-btn fmn-btn-success" style="width:100%; justify-content:center;" onclick="closeTopicModal()">
                🚀 Take Diagnostic Now
            </button>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════ --}}
{{-- JAVASCRIPT --}}
{{-- ═══════════════════════════════════════ --}}
<script>
// ── CHART ────────────────────────────────────────────────────────

const chartData = @json($chartData);
const currentWeek = {{ $currentWeek }};

const TOTAL_WEEKS    = 36;
const TEACHING_WEEKS = 30;
const EXAM_WEEK      = 36;
const weeks          = Array.from({length: TOTAL_WEEKS}, (_, i) => 'W' + (i + 1));

let progressChart = null;

function getSubjectTotals(key) {
    const map = { all: 90, math: 51, editing: 21, comp: 18 };
    return map[key] || 90;
}

function buildRequiredPace(total) {
    return Array.from({length: TOTAL_WEEKS}, (_, i) => {
        const w = i + 1;
        return w <= TEACHING_WEEKS ? Math.round((total / TEACHING_WEEKS) * w) : total;
    });
}

function buildZoneBands(required) {
    const upper    = required.map(v => Math.min(v + 3, 95));
    const worryLow = required.map(v => Math.max(v - 2, 0));
    const suppLow  = required.map(v => Math.max(v - 5, 0));
    return { upper, worryLow, suppLow };
}

function getActualData(key) {
    const subjKey = key === 'math' ? 'Math' : key === 'editing' ? 'English Editing' : key === 'comp' ? 'English Comprehension' : 'all';
    const d = chartData[subjKey];
    // Find last week with actual progress
    let lastActiveWeek = 0;
    for (let i = 0; i < d.actual.length; i++) {
        if (d.actual[i] > 0) lastActiveWeek = i + 1;
    }
    const stopAt = Math.min(currentWeek, lastActiveWeek || currentWeek);
    return Array.from({length: TOTAL_WEEKS}, (_, i) => {
        if (i < stopAt) return d.actual[i] ?? null;
        return null;
    });
}

function getCurrentTrajectory(key) {
    const subjKey = key === 'math' ? 'Math' : key === 'editing' ? 'English Editing' : key === 'comp' ? 'English Comprehension' : 'all';
    const d = chartData[subjKey];
    const total = getSubjectTotals(key);

    // Find last week with actual progress
    let lastActiveWeek = 0;
    for (let i = 0; i < d.actual.length; i++) {
        if (d.actual[i] > 0) lastActiveWeek = i + 1;
    }
    const stopAt = Math.min(currentWeek, lastActiveWeek || 1);
    const currentActual = d.actual[stopAt - 1] ?? 0;
    const weeklyRate    = stopAt > 0 ? currentActual / stopAt : 0;

    return Array.from({length: TOTAL_WEEKS}, (_, i) => {
        const w = i + 1;
        if (w < stopAt) return null;
        if (w === stopAt) return currentActual;
        return Math.min(Math.round(currentActual + weeklyRate * (w - stopAt)), total);
    });
}

function getCorrectedPace(key) {
    const subjKey = key === 'math' ? 'Math' : key === 'editing' ? 'English Editing' : key === 'comp' ? 'English Comprehension' : 'all';
    const d = chartData[subjKey];
    const total = getSubjectTotals(key);
    const currentActual  = d.actual[currentWeek - 1] ?? 0;
    const weeksRemaining = TEACHING_WEEKS - currentWeek;
    const topicsLeft     = total - currentActual;
    const correctedRate  = weeksRemaining > 0 ? topicsLeft / weeksRemaining : 0;
    return Array.from({length: TOTAL_WEEKS}, (_, i) => {
        const w = i + 1;
        if (w < currentWeek) return null;
        if (w === currentWeek) return currentActual;
        if (w <= TEACHING_WEEKS) return Math.min(Math.round(currentActual + correctedRate * (w - currentWeek)), total);
        return total;
    });
}

function buildDatasets(key) {
    const total    = getSubjectTotals(key);
    const required = buildRequiredPace(total);
    const zones    = buildZoneBands(required);
    const actual   = getActualData(key);
    const traj     = getCurrentTrajectory(key);
    const corrected= getCorrectedPace(key);
    return [
        { label: '_zone_green_top', data: zones.upper,    borderWidth: 0, pointRadius: 0, backgroundColor: 'rgba(16,185,129,0.13)', fill: '+1', tension: 0 },
        { label: '_zone_green_bot', data: required,       borderWidth: 0, pointRadius: 0, backgroundColor: 'rgba(16,185,129,0.13)', fill: false, tension: 0 },
        { label: '_zone_amber_top', data: required,       borderWidth: 0, pointRadius: 0, backgroundColor: 'rgba(245,158,11,0.13)', fill: '+1', tension: 0 },
        { label: '_zone_amber_bot', data: zones.worryLow, borderWidth: 0, pointRadius: 0, backgroundColor: 'rgba(245,158,11,0.13)', fill: false, tension: 0 },
        { label: '_zone_red_top',   data: zones.worryLow, borderWidth: 0, pointRadius: 0, backgroundColor: 'rgba(239,68,68,0.12)',  fill: '+1', tension: 0 },
        { label: '_zone_red_bot',   data: zones.suppLow,  borderWidth: 0, pointRadius: 0, backgroundColor: 'rgba(239,68,68,0.12)',  fill: false, tension: 0 },
        { label: 'Required Pace',      data: required,  borderColor: '#9333ea', borderWidth: 2.5, pointRadius: 0, tension: 0.2, fill: false, backgroundColor: 'transparent' },
        { label: 'Actual Progress',    data: actual,    borderColor: '#10b981', borderWidth: 3,   pointRadius: (ctx) => ctx.dataIndex === currentWeek - 1 ? 6 : 2, pointBackgroundColor: '#10b981', tension: 0.3, fill: false, backgroundColor: 'transparent' },
        { label: 'Current Trajectory', data: traj,      borderColor: '#6b7280', borderWidth: 2,   borderDash: [6,4], pointRadius: 0, tension: 0.2, fill: false, backgroundColor: 'transparent' },
        { label: 'Corrected Pace',     data: corrected, borderColor: '#10b981', borderWidth: 2,   borderDash: [5,3], pointRadius: 0, tension: 0.2, fill: false, backgroundColor: 'transparent' },
    ];
}

function initChart() {
    const ctx = document.getElementById('progressChart').getContext('2d');

    const currentWeekPlugin = {
        id: 'currentWeekLine',
        afterDraw(chart) {
            const { ctx, chartArea, scales } = chart;
            if (!chartArea) return;
            const x = scales.x.getPixelForValue(currentWeek - 1);
            ctx.save();
            ctx.strokeStyle = 'rgba(236,72,153,0.7)';
            ctx.lineWidth = 2;
            ctx.setLineDash([5, 4]);
            ctx.beginPath();
            ctx.moveTo(x, chartArea.top);
            ctx.lineTo(x, chartArea.bottom);
            ctx.stroke();
            ctx.fillStyle = '#db2777';
            ctx.font = 'bold 11px Nunito, sans-serif';
            ctx.fillText('Now', x + 4, chartArea.top + 14);
            ctx.restore();
        }
    };

    const examDatePlugin = {
        id: 'examDateLine',
        afterDraw(chart) {
            const { ctx, chartArea, scales } = chart;
            if (!chartArea) return;
            const x = scales.x.getPixelForValue(EXAM_WEEK - 1);
            ctx.save();
            ctx.strokeStyle = 'rgba(220,38,38,0.8)';
            ctx.lineWidth = 2.5;
            ctx.setLineDash([]);
            ctx.beginPath();
            ctx.moveTo(x, chartArea.top);
            ctx.lineTo(x, chartArea.bottom);
            ctx.stroke();
            ctx.fillStyle = '#dc2626';
            ctx.font = 'bold 11px Nunito, sans-serif';
            ctx.fillText('Exam', x - 30, chartArea.top + 14);
            ctx.restore();
        }
    };

    const revisionPlugin = {
        id: 'revisionZone',
        beforeDraw(chart) {
            const { ctx, chartArea, scales } = chart;
            if (!chartArea) return;
            const x30 = scales.x.getPixelForValue(TEACHING_WEEKS - 1);
            const x36 = scales.x.getPixelForValue(EXAM_WEEK - 1);
            ctx.save();
            ctx.fillStyle = 'rgba(147,51,234,0.05)';
            ctx.fillRect(x30, chartArea.top, x36 - x30, chartArea.bottom - chartArea.top);
            ctx.fillStyle = 'rgba(147,51,234,0.5)';
            ctx.font = 'bold 10px Nunito, sans-serif';
            ctx.fillText('REVISION', x30 + 4, chartArea.top + 14);
            ctx.restore();
        }
    };

    progressChart = new Chart(ctx, {
        type: 'line',
        data: { labels: weeks, datasets: buildDatasets('all') },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    filter: (item) => !item.dataset.label.startsWith('_zone'),
                    callbacks: { title: (items) => 'Week ' + (items[0].dataIndex + 1) }
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(147,51,234,0.06)' },
                    ticks: { font: { family: 'Nunito', size: 11 }, color: '#9ca3af', maxTicksLimit: 12,
                        callback: (val, idx) => (idx + 1) % 5 === 0 || idx === 0 ? 'W'+(idx+1) : '' }
                },
                y: {
                    grid: { color: 'rgba(147,51,234,0.06)' },
                    ticks: { font: { family: 'Nunito', size: 11 }, color: '#9ca3af' },
                    beginAtZero: true, max: 95,
                    title: { display: true, text: 'Topics Completed', font: { family: 'Nunito', size: 11, weight: 'bold' }, color: '#9ca3af' }
                }
            }
        },
        plugins: [revisionPlugin, currentWeekPlugin, examDatePlugin],
    });
}

function switchChart(key, btn) {
    document.querySelectorAll('.fmn-chart-tab').forEach(b => { b.className = 'fmn-chart-tab'; });
    const classMap = { all: 'active', math: 'active-math', editing: 'active-editing', comp: 'active-comp' };
    btn.classList.add(classMap[key]);
    progressChart.data.datasets = buildDatasets(key);
    progressChart.update();
}

// ── AI SUMMARY ───────────────────────────────────────────────────
const agentData = @json($examAgent);
let summaryLoaded = { student: false, parent: false };
let currentSummaryTab = 'student';

async function loadSummary(type) {
    if (summaryLoaded[type]) return;

    const loadingEl = document.getElementById('loading-' + type);
    const textEl    = document.getElementById('text-' + type);

    loadingEl.style.display = 'flex';
    textEl.style.display    = 'none';

    const prompt = type === 'student'
        ? buildStudentPrompt()
        : buildParentPrompt();

    try {
        const response = await fetch('https://api.anthropic.com/v1/messages', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                model: 'claude-sonnet-4-20250514',
                max_tokens: 1000,
                messages: [{ role: 'user', content: prompt }]
            })
        });

        const data = await response.json();
        const text = data.content?.find(b => b.type === 'text')?.text || 'Unable to generate summary.';

        textEl.innerHTML = text.replace(/\n\n/g, '</p><p>').replace(/\n/g, '<br>');
        textEl.innerHTML = '<p>' + textEl.innerHTML + '</p>';
        loadingEl.style.display = 'none';
        textEl.style.display    = 'block';
        summaryLoaded[type]     = true;

    } catch (e) {
        loadingEl.style.display = 'none';
        textEl.innerHTML = '<p style="color:#ef4444;">Could not load summary. Please check your connection.</p>';
        textEl.style.display = 'block';
    }
}

function buildStudentPrompt() {
    const behind = agentData.total_behind;
    const week   = agentData.current_week;
    const exam   = agentData.exam_date;
    const subjs  = agentData.subject_analysis;

    let behindText = '';
    for (const [subj, data] of Object.entries(subjs)) {
        if (data.behind_count > 0) {
            behindText += `${subj}: ${data.behind_count} topics behind. `;
        }
    }

    return `You are a warm, encouraging SEA exam tutor writing directly to a 10-year-old girl in Trinidad and Tobago.

Write a short, friendly performance summary (3 paragraphs) about her SEA exam preparation this week.

Facts:
- Current teaching week: ${week} of 30
- Weeks until exam: ${agentData.weeks_to_exam}
- Exam date: ${exam}
- Topics behind: ${behind > 0 ? behindText : 'None — she is on track!'}
- Overall status: ${agentData.overall_status}

Guidelines:
- Write in simple, warm language a 10-year-old can understand
- Be encouraging and positive even if she is behind
- Give 1-2 specific actionable tips for next week
- Use friendly emojis sparingly
- Do NOT use markdown headers or bullet points — write in natural paragraphs
- Keep it under 150 words`;
}

function buildParentPrompt() {
    const behind = agentData.total_behind;
    const week   = agentData.current_week;
    const subjs  = agentData.subject_analysis;

    let subjDetails = '';
    for (const [subj, data] of Object.entries(subjs)) {
        subjDetails += `${subj}: expected ${data.expected} topics, completed ${data.completed}, behind ${data.behind_count}, status: ${data.status}. `;
    }

    return `You are an analytical educational advisor writing a formal performance report for a parent of a Standard 5 student preparing for the SEA exam in Trinidad and Tobago.

Write a structured analytical summary (3-4 paragraphs) covering:
1. Current pacing status vs expected curriculum progress
2. Subject-by-subject performance gaps with specific topics at risk
3. Risk assessment for exam readiness
4. Recommended parental interventions for next week

Facts:
- Current teaching week: ${week} of 30
- Weeks until exam: ${agentData.weeks_to_exam}
- Total topics behind: ${behind}
- Subject breakdown: ${subjDetails}
- Overall status: ${agentData.overall_status}

Guidelines:
- Use formal, analytical language appropriate for a parent
- Be specific about which topics and subjects need attention
- Reference the SEA weighting (Math 100%, ELA 60%, Writing 40%)
- Do NOT use markdown headers or bullet points — write in formal paragraphs
- Keep it under 220 words`;
}

function switchSummary(type, btn) {
    document.querySelectorAll('.fmn-summary-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.fmn-summary-content').forEach(c => c.classList.remove('active'));
    document.getElementById('summary-' + type).classList.add('active');
    currentSummaryTab = type;
    if (!summaryLoaded[type]) loadSummary(type);
}

// ── TOPIC GRID FILTER ────────────────────────────────────────────
function filterTopics(filter, btn) {
    document.querySelectorAll('.fmn-filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('.fmn-topic-tile').forEach(tile => {
        const subj   = tile.dataset.subject;
        const status = tile.dataset.status;
        const behind = tile.dataset.behind === 'true';

        let show = false;
        if (filter === 'all')      show = true;
        else if (filter === 'math')     show = subj === 'math';
        else if (filter === 'editing')  show = subj === 'editing';
        else if (filter === 'comp')     show = subj === 'comp';
        else if (filter === 'mastered') show = status === 'mastered';
        else if (filter === 'behind')   show = behind;

        tile.style.display = show ? '' : 'none';
    });
}

// ── TOPIC MODAL ──────────────────────────────────────────────────
const topicData = @json($topicJson);
const progressData = @json($progressJson);

function openTopic(id) {
    const m = topicData[id];
    const p = progressData[id];

    document.getElementById('modal-title').textContent = m.topic;
    document.getElementById('modal-description').textContent = m.description || 'Description coming soon.';

    // Scores
    const latest = p?.score !== null && p?.score !== undefined ? p.score + '%' : 'Not taken';
    const prev   = p?.previous_score !== null && p?.previous_score !== undefined ? p.previous_score + '%' : '—';
    const status = p ? p.status.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()) : 'Not Started';

    document.getElementById('modal-score-latest').textContent = latest;
    document.getElementById('modal-score-prev').textContent   = prev;
    document.getElementById('modal-status-chip').textContent  = status;

    // Resources
    const resList = document.getElementById('modal-resources');
    resList.innerHTML = '';
    if (m.resources && m.resources.length > 0) {
        m.resources.forEach(r => {
            const li = document.createElement('li');
            if (r.url) {
                li.innerHTML = `<a href="${r.url}" target="_blank" rel="noopener">📎 ${r.title}</a>`;
            } else {
                li.innerHTML = `<span class="fmn-resource-no-url">📚 ${r.title}</span>`;
            }
            resList.appendChild(li);
        });
    } else {
        resList.innerHTML = '<li>No resources added yet.</li>';
    }

    document.getElementById('topicModal').classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeTopicModal() {
    document.getElementById('topicModal').classList.remove('open');
    document.body.style.overflow = '';
}

function closeModal(e) {
    if (e.target === document.getElementById('topicModal')) closeTopicModal();
}

// ── INIT ──────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    initChart();
    loadSummary('student');
});
</script>

</body>
</html>