<div wire:init="loadAiSummary">
    <style>
        /* ── Guardian Bridge — light editorial dashboard ── */
        .g-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:22px; }
        .g-h1 { font-size:30px; color:var(--ink); margin:0 0 3px; }
        .g-sub { font-size:14px; color:var(--ink-faint); font-weight:700; margin:0; }
        .g-updated { display:inline-flex; align-items:center; gap:6px; font-size:12px; font-weight:800; color:var(--teal-deep); background:var(--teal-tint); border-radius:999px; padding:4px 11px; margin:8px 0 0; }
        .g-dot-live { width:7px; height:7px; border-radius:50%; background:var(--teal); flex-shrink:0; }
        .g-switch { display:flex; align-items:center; gap:8px; }
        .g-switch label { font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-faint); }
        .g-switch select { font-family:'Nunito',sans-serif; font-size:14px; font-weight:800; color:var(--ink); border:1px solid var(--line); border-radius:11px; padding:8px 12px; background:var(--paper-2); box-shadow:var(--shadow-sm); }

        .card { background:var(--paper-2); border:1px solid var(--line); border-radius:var(--radius); padding:18px 20px; box-shadow:var(--shadow-sm); }
        .eyebrow { font-size:11px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--teal); margin:0 0 12px; }
        .h-sec { font-size:17px; color:var(--ink); margin:0 0 4px; }
        .p { font-size:14.5px; color:var(--ink-soft); margin:0; line-height:1.6; }
        .p.soft { color:var(--ink-faint); }
        .p.ink { color:var(--ink); }

        /* KPI band */
        .kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px; }
        @media (max-width:820px){ .kpis{ grid-template-columns:repeat(2,1fr); } }
        .kpi { background:var(--paper-2); border:1px solid var(--line); border-radius:var(--radius); padding:15px 16px; box-shadow:var(--shadow-sm); }
        .kpi-l { font-size:10.5px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--ink-faint); margin:0; }
        .kpi-v { font-family:'Fredoka',sans-serif; font-weight:700; font-size:26px; color:var(--ink); margin:8px 0 0; line-height:1; }
        .kpi-u { font-size:14px; font-weight:800; color:var(--ink-faint); }
        .kpi-n { font-size:12px; font-weight:700; color:var(--ink-faint); margin:6px 0 0; }
        .kpi.accent { background:linear-gradient(135deg,var(--teal),var(--teal-deep)); border-color:transparent; }
        .kpi.accent .kpi-l,.kpi.accent .kpi-n { color:rgba(255,255,255,.75); }
        .kpi.accent .kpi-v,.kpi.accent .kpi-u { color:#fff; }

        .badge { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:999px; font-size:12.5px; font-weight:800; }
        .badge-good { background:var(--good-tint); color:var(--good); }
        .badge-warn { background:var(--warn-tint); color:var(--warn); }

        /* Lead verdict */
        .lead { border-radius:var(--radius); padding:16px 20px; margin-bottom:14px; border:1px solid; }
        .lead-good { background:var(--good-tint); border-color:#bfe6d1; }
        .lead-watch { background:var(--amber-tint); border-color:#f2d69a; }
        .lead-warn { background:var(--warn-tint); border-color:#efc59b; }
        .lead-neutral { background:var(--paper-2); border-color:var(--line); box-shadow:var(--shadow-sm); }
        .lead-h { font-size:19px; color:var(--ink); margin:2px 0 3px; }
        .lead-p { font-size:14.5px; color:var(--ink-soft); margin:0; line-height:1.55; }

        /* Grids */
        .col2 { display:grid; grid-template-columns:1.1fr 1fr; gap:14px; margin-bottom:14px; }
        .col3 { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:14px; }
        @media (max-width:820px){ .col2,.col3{ grid-template-columns:1fr; } }

        /* Pace bars (from the landing bar system) */
        .bar-row { display:grid; grid-template-columns:118px 1fr 128px; gap:12px; align-items:center; margin-bottom:14px; }
        .bar-row:last-child { margin-bottom:0; }
        .bar-name { font-size:13.5px; font-weight:800; color:var(--ink); }
        .bar-name small { display:block; font-size:11px; font-weight:700; color:var(--ink-faint); }
        .bar { position:relative; height:11px; border-radius:999px; background:var(--paper); overflow:hidden; border:1px solid var(--line); }
        .bar i { display:block; height:100%; border-radius:999px; background:linear-gradient(90deg,var(--teal),var(--amber)); }
        .bar-val { font-size:12px; font-weight:700; color:var(--ink-soft); text-align:right; }
        .bar-na { font-size:12px; font-style:italic; color:var(--ink-faint); }

        /* Trajectory gauge */
        .traj { position:relative; height:16px; border-radius:999px; background:var(--paper); border:1px solid var(--line); overflow:visible; margin:6px 0 10px; }
        .traj-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,var(--teal),var(--teal-deep)); }
        .traj-mark { position:absolute; top:-5px; width:3px; height:26px; background:var(--amber); border-radius:2px; }
        .traj-mark::after { content:'▼'; position:absolute; top:-11px; left:-4px; font-size:10px; color:var(--amber); }
        .traj-legend { display:flex; gap:16px; flex-wrap:wrap; }
        .traj-legend span { font-size:12px; font-weight:700; color:var(--ink-faint); display:inline-flex; align-items:center; gap:6px; }
        .sw { width:14px; height:4px; border-radius:2px; display:inline-block; }

        .warn-line { font-size:13.5px; font-weight:800; color:var(--warn); margin:0 0 10px; }
        .step { font-size:14px; color:var(--ink); margin:0 0 6px; line-height:1.5; } .step:last-child{ margin:0; }

        .excel { display:flex; gap:9px; margin-bottom:9px; } .excel:last-child{ margin:0; }
        .excel .star { color:var(--amber); font-size:14px; line-height:1.4; }
        .excel .t { font-size:13.5px; color:var(--ink-soft); margin:0; line-height:1.45; }
        .excel .t b { color:var(--ink); }

        .chips { display:flex; flex-wrap:wrap; gap:8px; }
        .chip { background:var(--paper); border:1px solid var(--line); border-radius:12px; padding:8px 12px; min-width:74px; }
        .chip-l { font-size:10px; font-weight:800; letter-spacing:.05em; text-transform:uppercase; color:var(--ink-faint); margin:0; }
        .chip-v { font-family:'Fredoka',sans-serif; font-weight:700; font-size:18px; color:var(--ink); margin:3px 0 0; }
        .chip-u { font-size:11px; font-weight:700; color:var(--ink-faint); }

        .btns { display:flex; flex-wrap:wrap; gap:8px; }
        .btn-gold { font-family:'Nunito',sans-serif; font-size:13.5px; font-weight:800; cursor:pointer; color:#5a3d00; background:var(--amber); border:none; border-radius:11px; padding:8px 15px; }
        .btn-gold:hover { filter:brightness(1.05); }
        .btn-out { font-family:'Nunito',sans-serif; font-size:13.5px; font-weight:800; cursor:pointer; color:var(--teal-deep); background:var(--paper-2); border:1px solid var(--line); border-radius:11px; padding:8px 15px; }
        .btn-out:hover { border-color:var(--teal); color:var(--teal); }
        .ai { white-space:pre-line; min-height:1.2rem; }
        summary { cursor:pointer; }

        /* Weekly plan */
        .plan-grid { display:grid; grid-template-columns:1.3fr 1fr; gap:18px; }
        @media (max-width:640px){ .plan-grid{ grid-template-columns:1fr; } }
        .plan-item { display:flex; align-items:center; gap:9px; padding:7px 0; border-bottom:1px solid var(--line); }
        .plan-item:last-child { border-bottom:0; }
        .plan-check { width:17px; height:17px; border-radius:6px; flex-shrink:0; display:grid; place-items:center; font-size:11px; font-weight:800; }
        .plan-check.done { background:var(--good-tint); color:var(--good); }
        .plan-check.todo { background:var(--paper); border:1px solid var(--line); color:transparent; }
        .plan-topic { font-size:13.5px; font-weight:700; color:var(--ink); }
        .plan-topic.done { color:var(--ink-faint); text-decoration:line-through; }
        .plan-tag { margin-left:auto; font-size:10px; font-weight:800; letter-spacing:.04em; text-transform:uppercase; color:var(--ink-faint); }
        .assign { border:1px solid var(--line); border-radius:12px; padding:11px 13px; margin-bottom:9px; background:var(--paper); }
        .assign-k { font-size:10px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; color:var(--teal); margin:0 0 3px; }
        .assign-t { font-size:13.5px; font-weight:700; color:var(--ink); margin:0; }

        /* Pace calendar */
        .cal-toolbar { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:12px; }
        .cal-now { font-family:'Nunito',sans-serif; font-size:13px; font-weight:800; cursor:pointer; color:#fff; background:var(--teal); border:none; border-radius:999px; padding:7px 16px; }
        .cal-now:hover { background:var(--teal-deep); }
        .cal-legend { display:flex; gap:12px; flex-wrap:wrap; }
        .cal-legend span { font-size:11.5px; font-weight:700; color:var(--ink-faint); display:inline-flex; align-items:center; gap:5px; }
        .dot { width:10px; height:10px; border-radius:3px; display:inline-block; }
        .dot.mastered { background:var(--teal); } .dot.working { background:var(--amber); } .dot.review { background:#7cc3cc; } .dot.upcoming { background:var(--line); }
        .cal-month { border:1px solid var(--line); border-radius:13px; margin-bottom:9px; overflow:hidden; }
        .cal-month.current { border-color:var(--teal); }
        .cal-mhead { width:100%; display:flex; align-items:center; justify-content:space-between; gap:10px; background:var(--paper-2); border:0; cursor:pointer; padding:13px 15px; font-family:'Nunito',sans-serif; text-align:left; }
        .cal-mname { font-family:'Fredoka',sans-serif; font-weight:600; font-size:15.5px; color:var(--ink); display:flex; align-items:center; gap:9px; }
        .cal-now-chip { font-size:9.5px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:#fff; background:var(--teal); border-radius:999px; padding:2px 8px; }
        .cal-mtally { font-size:12.5px; font-weight:800; color:var(--ink-soft); font-variant-numeric:tabular-nums; }
        .cal-mbar { height:5px; background:var(--paper); border-radius:999px; overflow:hidden; margin:0 15px 12px; }
        .cal-mbar i { display:block; height:100%; background:var(--teal); border-radius:999px; }
        .cal-weeks { padding:0 15px 13px; display:flex; flex-direction:column; gap:9px; }
        .cal-week { border:1px solid var(--line); border-radius:10px; padding:10px 12px; background:var(--paper-2); }
        .cal-week.current { border-color:var(--teal); box-shadow:0 0 0 3px var(--teal-tint); }
        .cal-whead { display:flex; align-items:baseline; justify-content:space-between; gap:8px; margin-bottom:7px; }
        .cal-wname { font-size:12.5px; font-weight:800; color:var(--ink); }
        .cal-wtally { font-size:11px; font-weight:800; color:var(--ink-faint); font-variant-numeric:tabular-nums; }
        .cal-topics { display:flex; flex-wrap:wrap; gap:6px; }
        .cal-topic { display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:700; color:var(--ink-soft); background:var(--paper); border:1px solid var(--line); border-radius:7px; padding:4px 8px; }
        .cal-toggle { font-size:12px; }

        /* Overview peek nav into sections */
        .peeks { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:14px; }
        @media (max-width:820px){ .peeks{ grid-template-columns:repeat(2,1fr); } }
        .peek { text-align:left; cursor:pointer; background:var(--paper-2); border:1px solid var(--line); border-radius:14px; padding:14px 15px; box-shadow:var(--shadow-sm); transition:border-color .15s, transform .1s; font-family:'Nunito',sans-serif; }
        .peek:hover { border-color:var(--teal); transform:translateY(-1px); }
        .peek-k { display:block; font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; color:var(--teal); margin:0 0 6px; }
        .peek-v { display:block; font-family:'Fredoka',sans-serif; font-weight:600; font-size:17px; color:var(--ink); }
        .peek-s { display:block; font-size:12.5px; font-weight:700; color:var(--ink-faint); margin-top:2px; }
    </style>

    @php
        $sectionTitles = [
            'overview' => 'Overview', 'this-week' => "This week's plan", 'pace' => 'Pace',
            'estimator' => 'Estimator', 'rewards' => 'Rewards & controls',
        ];
        $topicsDone = collect($weeklyPlan['topics'] ?? [])->where('done', true)->count();
        $topicsTotal = count($weeklyPlan['topics'] ?? []);
        $perkTotal = collect($perks ?? [])->sum('count');
    @endphp

    {{-- Header (always) --}}
    <div class="g-head">
        <div>
            <h1 class="g-h1">{{ $studentName ?? 'Guardian Bridge' }}</h1>
            <p class="g-sub">{{ $sectionTitles[$section] ?? 'Overview' }} · {{ $weekLabel }} · the four questions, answered honestly.</p>
            @if ($student)
                <p class="g-updated">
                    <span class="g-dot-live"></span>
                    @if ($paceUpdatedAt)
                        Progress updated {{ $paceUpdatedAt->format('j M Y') }} · recalculated weekly
                    @else
                        Progress recalculates weekly · first update pending
                    @endif
                </p>
            @endif
        </div>
        @if ($students->count() > 1)
            <div class="g-switch">
                <label for="g-child">Child</label>
                <select id="g-child" wire:model.live="studentId">
                    @foreach ($students as $child)
                        <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    {{-- GD-10: pending reconciliation (always surfaced — it blocks her map) --}}
    @if ($reconciliationPending)
        <div class="lead lead-warn">
            <p class="eyebrow" style="color:var(--warn);">A quick check before we finish</p>
            <h3 class="lead-h">{{ $studentName }}'s diagnostic sees things differently</h3>
            <p class="lead-p" style="margin-bottom:12px;">
                You told us {{ $studentName }} struggles with <strong>{{ implode(', ', $clearedStrands) }}</strong>,
                but the diagnostic found she's already got a good handle on {{ count($clearedStrands) === 1 ? 'it' : 'them' }}.
                Her map won't start until you choose. This decision cannot be undone.
            </p>
            <div class="btns">
                <button type="button" wire:click="proceedWithDiagnostic" class="btn-gold">Use the diagnostic result</button>
                <button type="button" wire:click="keepWeakAreas" class="btn-out">Keep my weak areas</button>
            </div>
        </div>
    @endif

    {{-- KPI band [overview] --}}
    <div class="kpis" @style(['display:none' => $section !== 'overview'])>
        <div class="kpi">
            <p class="kpi-l">Readiness</p>
            <p class="kpi-v" style="font-size:16px; line-height:1.25; margin-top:8px;">{{ $readiness['headline'] }}</p>
        </div>
        <div class="kpi">
            <p class="kpi-l">SEA exam in</p>
            @if ($daysToExam !== null)
                <p class="kpi-v">{{ $daysToExam }}<span class="kpi-u"> days</span></p>
                <p class="kpi-n">{{ $examDate }}</p>
            @else
                <p class="kpi-v" style="font-size:18px;">—</p>
            @endif
        </div>
        <div class="kpi">
            <p class="kpi-l">This week's target</p>
            <p class="kpi-v" style="font-size:16px; margin-top:10px;">
                @if ($targetCompleted)<span class="badge badge-good">Completed</span>@else<span class="badge badge-warn">In progress</span>@endif
            </p>
        </div>
        <div class="kpi accent">
            <p class="kpi-l">Overall mastery</p>
            <p class="kpi-v">{{ $trajectory['actual_pct'] }}<span class="kpi-u">%</span></p>
            <p class="kpi-n">{{ $trajectory['completed'] }} of {{ $trajectory['total'] }} modules</p>
        </div>
    </div>

    {{-- GD-11: plain-language readiness verdict [overview] --}}
    <div class="lead lead-{{ $readiness['tone'] }}" @style(['display:none' => $section !== 'overview'])>
        <p class="eyebrow">Where {{ $studentName ?? 'she' }} stands</p>
        <h3 class="lead-h">{{ $readiness['headline'] }}</h3>
        <p class="lead-p">{{ $readiness['detail'] }}</p>
    </div>

    {{-- This week's plan — topics, writing and reading [this-week] --}}
    @if ($student)
        <div class="card" @style(['margin-bottom:14px;', 'display:none' => $section !== 'this-week'])>
            <p class="eyebrow">This week's plan</p>
            <h3 class="h-sec">What {{ $studentName }} is working on this week</h3>
            <div class="plan-grid" style="margin-top:14px;">
                <div>
                    <p class="chip-l" style="margin-bottom:6px;">Topics to cover</p>
                    @forelse ($weeklyPlan['topics'] as $t)
                        <div class="plan-item">
                            <span class="plan-check {{ $t['done'] ? 'done' : 'todo' }}">{{ $t['done'] ? '✓' : '' }}</span>
                            <span class="plan-topic {{ $t['done'] ? 'done' : '' }}">{{ $t['topic'] ?? 'Topic' }}</span>
                            <span class="plan-tag">{{ $t['subject'] === 'Math' ? 'Maths' : $t['subject'] }}</span>
                        </div>
                    @empty
                        <p class="p soft">No topics set for this week yet — they appear each Sunday.</p>
                    @endforelse
                </div>
                <div>
                    <div class="assign">
                        <p class="assign-k">✍️ Writing assignment</p>
                        @if ($weeklyPlan['writing'])
                            <p class="assign-t">{{ $weeklyPlan['writing']->title }}</p>
                            <p class="p soft" style="margin-top:4px; font-size:12.5px;">{{ ucfirst($weeklyPlan['writing']->type ?? 'composition') }}</p>
                        @else
                            <p class="p soft" style="font-size:12.5px;">No shared writing prompt set for this week.</p>
                        @endif
                    </div>
                    <div class="assign" style="margin-bottom:0;">
                        <p class="assign-k">📖 Reading this week</p>
                        @forelse ($weeklyPlan['reading'] as $r)
                            <p class="assign-t" style="display:flex; align-items:center; gap:7px;">
                                <span class="plan-check {{ $r['done'] ? 'done' : 'todo' }}" style="width:15px; height:15px;">{{ $r['done'] ? '✓' : '' }}</span>
                                {{ $r['title'] }}
                            </p>
                        @empty
                            <p class="p soft" style="font-size:12.5px;">Reading passages are assigned each day — none logged yet this week.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Overview → jump into detail [overview] --}}
    @if ($student)
        <div class="peeks" @style(['display:none' => $section !== 'overview'])>
            <button class="peek" wire:click="$set('section', 'this-week')">
                <span class="peek-k">🗓️ This week</span>
                <span class="peek-v">{{ $topicsDone }}/{{ $topicsTotal }} topics</span>
                <span class="peek-s">Plan, reading &amp; writing</span>
            </button>
            <button class="peek" wire:click="$set('section', 'pace')">
                <span class="peek-k">🧭 Pace</span>
                <span class="peek-v">{{ $trajectory['actual_pct'] }}% mastered</span>
                <span class="peek-s">Year · month · week</span>
            </button>
            <button class="peek" wire:click="$set('section', 'estimator')">
                <span class="peek-k">🎯 Estimator</span>
                <span class="peek-v">{{ $estimate && $estimate['composite'] !== null ? $estimate['composite'].'%' : '—' }}</span>
                <span class="peek-s">Projected placement</span>
            </button>
            <button class="peek" wire:click="$set('section', 'rewards')">
                <span class="peek-k">🎁 Rewards</span>
                <span class="peek-v">{{ $perkTotal }} perks</span>
                <span class="peek-s">Perks &amp; controls</span>
            </button>
        </div>
    @endif

    {{-- GD-04: catch-up triage [overview] --}}
    @if ($triage)
        <div class="lead lead-warn" @style(['display:none' => $section !== 'overview'])>
            <p class="eyebrow" style="color:var(--warn);">Catch-up plan</p>
            <p class="warn-line">{{ $triage['priority'] }}</p>
            @foreach ($triage['subjects'] as $subject)
                <p class="step"><strong>{{ $subject['name'] }}:</strong> {{ $subject['weekly_step'] }}</p>
            @endforeach
        </div>
    @endif

    {{-- GD-03: on-track affirmation [overview] --}}
    @if ($onTrack)
        <div class="lead lead-good" @style(['display:none' => $section !== 'overview'])>
            <p class="eyebrow" style="color:var(--good);">This week</p>
            <h3 class="lead-h" style="color:var(--good);">Target met and on pace</h3>
            <p class="lead-p">Nothing to carry into next week</p>
        </div>
    @endif

    {{-- Pace card [pace] --}}
    <div @style(['margin-bottom:14px;', 'display:none' => $section !== 'pace'])>
        <div class="card">
            <p class="eyebrow">Pace</p>
            <h3 class="h-sec">Modules mastered against plan</h3>
            <p class="p soft" style="margin:2px 0 16px;">Each bar counts mastered modules against this point in the year — not a score.</p>
            @if ($paceStatus === 'warning')
                <p class="warn-line">{{ $weeksBehind }} {{ $weeksBehind === 1 ? 'week' : 'weeks' }} behind — see the catch-up plan.</p>
            @endif
            @foreach ($pace as $paper => $row)
                <div class="bar-row">
                    <span class="bar-name">{{ $paper }}<small>{{ $row['weight'] }}% of the exam</small></span>
                    @if ($row['assessed'] && $row['expected'] > 0)
                        <span class="bar"><i style="width:{{ (int) round(($row['completed'] / max($row['expected'],1)) * 100) }}%;"></i></span>
                        <span class="bar-val">
                            {{ $row['completed'] }} of {{ $row['expected'] }} modules mastered
                            @if ($row['behind_count'] > 0)
                                <br>{{ $row['behind_count'] }} to catch up
                            @endif
                        </span>
                    @else
                        <span class="bar"></span>
                        <span class="bar-na">Not yet assessed</span>
                    @endif
                </div>
            @endforeach

            {{-- Trajectory gauge — where she is vs where the plan expects her --}}
            <p class="eyebrow" style="margin:18px 0 8px;">Trajectory</p>
            <div class="traj">
                <div class="traj-fill" style="width:{{ min($trajectory['actual_pct'],100) }}%;"></div>
                <div class="traj-mark" style="left:calc({{ min($trajectory['required_pct'],100) }}% - 1.5px);"></div>
            </div>
            <div class="traj-legend">
                <span><span class="sw" style="background:var(--teal);"></span>Mastered now — {{ $trajectory['actual_pct'] }}%</span>
                <span><span class="sw" style="background:var(--amber);"></span>Required pace to exam — {{ $trajectory['required_pct'] }}%</span>
            </div>
            <p class="p soft" style="margin-top:10px;">
                @if ($trajectory['on_or_ahead'])
                    The teal fill has reached the on-pace mark — she's where the plan expects her, or ahead.
                @else
                    The teal fill sits left of the amber mark — a little behind the plan; the catch-up steps close the gap.
                @endif
            </p>
        </div>
    </div>

    {{-- Exam-agent read [overview] --}}
    <div class="card" @style(['margin-bottom:14px;', 'display:none' => $section !== 'overview'])>
            <p class="eyebrow">Exam agent</p>
            <h3 class="h-sec">Strengths &amp; what to work on</h3>
            <div class="ai p ink" style="margin-top:10px;" wire:loading.remove wire:target="loadAiSummary">
                @if ($aiSummaryLoaded){{ $aiSummary ?: 'A briefing appears once there is enough activity to summarise.' }}@endif
            </div>
            <p class="p soft" wire:loading wire:target="loadAiSummary" style="margin-top:10px;">Reading {{ $studentName ?? 'the' }}'s week…</p>

            <p class="eyebrow" style="margin:18px 0 8px;">Recommendation</p>
            <p class="p ink">{{ $recommendation }}</p>
    </div>

    {{-- Pace calendar — collapsible year → month → week drill-down [pace] --}}
    @if (! empty($paceCalendar))
        @php $currentMonthKey = collect($paceCalendar)->firstWhere('is_current', true)['key'] ?? ($paceCalendar[0]['key'] ?? ''); @endphp
        <div class="card" x-data="{ open: @js($currentMonthKey) }" @style(['margin-bottom:14px;', 'display:none' => $section !== 'pace'])>
            <div class="cal-toolbar">
                <div>
                    <p class="eyebrow" style="margin-bottom:2px;">Pace calendar</p>
                    <h3 class="h-sec">The whole year, month by month</h3>
                </div>
                <button type="button" class="cal-now"
                        @click="open = @js($currentMonthKey); $nextTick(() => $refs.currentWeek && $refs.currentWeek.scrollIntoView({ behavior: 'smooth', block: 'center' }))">
                    Jump to this week
                </button>
            </div>
            <div class="cal-legend" style="margin-bottom:14px;">
                <span><span class="dot mastered"></span>Mastered</span>
                <span><span class="dot working"></span>Working on</span>
                <span><span class="dot review"></span>In review</span>
                <span><span class="dot upcoming"></span>Upcoming</span>
            </div>

            @foreach ($paceCalendar as $month)
                <div class="cal-month {{ $month['is_current'] ? 'current' : '' }}">
                    <button type="button" class="cal-mhead" @click="open = (open === @js($month['key'])) ? '' : @js($month['key'])">
                        <span class="cal-mname">
                            <span class="cal-toggle" x-text="open === @js($month['key']) ? '▾' : '▸'">▸</span>
                            {{ $month['label'] }}
                            @if ($month['is_current'])<span class="cal-now-chip">This month</span>@endif
                        </span>
                        <span class="cal-mtally">{{ $month['mastered'] }}/{{ $month['total'] }} mastered</span>
                    </button>
                    <div class="cal-mbar"><i style="width:{{ $month['total'] > 0 ? (int) round(($month['mastered'] / $month['total']) * 100) : 0 }}%;"></i></div>
                    <div class="cal-weeks" x-show="open === @js($month['key'])" x-collapse.duration.250ms>
                        @foreach ($month['weeks'] as $week)
                            <div class="cal-week {{ $week['is_current'] ? 'current' : '' }}" @if ($week['is_current']) x-ref="currentWeek" @endif>
                                <div class="cal-whead">
                                    <span class="cal-wname">Week {{ $week['week_no'] }} · {{ $week['date'] }} @if ($week['is_current'])<span class="cal-now-chip">This week</span>@endif</span>
                                    <span class="cal-wtally">{{ $week['mastered'] }}/{{ $week['total'] }}</span>
                                </div>
                                <div class="cal-topics">
                                    @foreach ($week['topics'] as $topic)
                                        <span class="cal-topic"><span class="dot {{ $topic['status'] }}"></span>{{ $topic['topic'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Estimator — projected SEA placement from her own history [estimator] --}}
    @if ($estimate)
        <div class="card" @style(['margin-bottom:14px;', 'display:none' => $section !== 'estimator'])>
            <p class="eyebrow">Estimator</p>
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:18px; flex-wrap:wrap;">
                <div style="flex:1 1 260px;">
                    <h3 class="h-sec">Projected SEA placement</h3>
                    <p class="p soft" style="margin:2px 0 12px;">{{ $estimate['covered_note'] }}</p>
                    @if ($estimate['has_data'])
                        <p class="p ink" style="font-size:15.5px;"><strong>{{ $estimate['placement']['tier'] }}</strong></p>
                        <p class="p soft" style="margin-top:3px;">{{ $estimate['placement']['note'] }}</p>
                        <p class="p soft" style="margin-top:8px; font-size:12.5px;">Projection confidence: <strong style="text-transform:capitalize;">{{ $estimate['confidence'] }}</strong> — indicative tiers from public SEA cut-off ranges, not a guarantee.</p>
                    @else
                        <p class="p soft">Not enough assessment activity yet to project placement. It appears once she has practised a few topics.</p>
                    @endif
                </div>
                @if ($estimate['composite'] !== null)
                    <div class="kpi accent" style="min-width:140px; text-align:center;">
                        <p class="kpi-l">Projected composite</p>
                        <p class="kpi-v" style="font-size:32px;">{{ $estimate['composite'] }}<span class="kpi-u">%</span></p>
                        <p class="kpi-n">weighted 50/30/20</p>
                    </div>
                @endif
            </div>

            @if ($estimate['has_data'])
                <div style="margin-top:16px;">
                    <p class="chip-l" style="margin-bottom:8px;">Average score per subject — covered material</p>
                    @foreach ($estimate['subjects'] as $es)
                        <div class="bar-row">
                            <span class="bar-name">{{ $es['label'] }}</span>
                            @if ($es['accuracy'] !== null)
                                <span class="bar"><i style="width:{{ $es['accuracy'] }}%;"></i></span>
                                <span class="bar-val">
                                    {{ $es['accuracy'] }}% average
                                    @if (! $es['confident'] && $es['subject'] !== 'Writing')<br><em>few attempts yet</em>@endif
                                </span>
                            @else
                                <span class="bar"></span>
                                <span class="bar-na">No attempts yet</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- Excelling + writing feedback [overview] --}}
    <div class="col2" @style(['display:none' => $section !== 'overview'])>
        <div class="card {{ empty($excelling) ? '' : '' }}">
            <p class="eyebrow">Where {{ $studentName ?? 'she' }} is excelling</p>
            @forelse ($excelling as $item)
                <div class="excel"><span class="star">★</span><p class="t"><b>{{ $item['facet'] }}</b> — {{ $item['detail'] }}</p></div>
            @empty
                <p class="p soft">Strengths surface here as she masters modules ahead of pace and scores well on writing.</p>
            @endforelse
        </div>

        <div class="card">
            <p class="eyebrow">Writing feedback</p>
            @if ($writingFeedback)
                <p class="p ink">Latest essay {{ $writingFeedback['scored_at'] }} — averaging {{ $writingFeedback['average'] }}/10.</p>
                @if ($writingFeedback['did_well'])<p class="p" style="margin-top:6px;"><strong>Did well:</strong> {{ $writingFeedback['did_well'] }}</p>@endif
                @if ($writingFeedback['try_next'])<p class="p soft" style="margin-top:3px;"><strong>Try next:</strong> {{ $writingFeedback['try_next'] }}</p>@endif
            @else
                <p class="p soft">No writing feedback yet.</p>
            @endif
        </div>

    </div>

    {{-- ===== Rewards & controls [rewards] ===== --}}
    <div @style(['display:none' => $section !== 'rewards'])>
        @if ($student)
            <div class="card" style="margin-bottom:14px;">
                <p class="eyebrow">Consistency &amp; perks</p>
                @if (! empty($streaks))
                    <p class="chip-l" style="margin-bottom:7px;">Day counts</p>
                    <div class="chips" style="margin-bottom:14px;">
                        @foreach ($streaks as $s)
                            <div class="chip"><p class="chip-l">{{ $s['label'] }}</p><p class="chip-v">{{ $s['count'] }}<span class="chip-u"> d</span></p></div>
                        @endforeach
                    </div>
                @endif
                <p class="chip-l" style="margin-bottom:7px;">Perks in the Locker</p>
                <div class="chips">
                    @foreach ($perks as $p)
                        <div class="chip"><p class="chip-l">{{ $p['label'] }}</p><p class="chip-v">{{ $p['count'] }}</p></div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Controls --}}
        @if ($student)
        <div class="card" style="margin-bottom:14px;">
            <p class="eyebrow">Grant a reward</p>
            <p class="p soft" style="margin-bottom:12px;">Controls &amp; rewards — never shown to {{ $studentName }}. A granted reward appears in her Captain's Locker.</p>
            <div class="btns" style="margin-bottom:10px;">
                @foreach ($rewardTypes as $type => $label)
                    <button type="button" class="btn-gold" wire:click="grantReward('{{ $type }}')">Grant {{ $label }}</button>
                @endforeach
            </div>
            <div class="btns">
                <button type="button" class="btn-out" wire:click="pauseJourney">Pause journey</button>
                <button type="button" class="btn-out" wire:click="resumeJourney">Resume journey</button>
                <button type="button" class="btn-out" wire:click="requestRetake">Request diagnostic retake</button>
            </div>
            <p class="p" wire:loading wire:target="grantReward" style="margin-top:8px; color:var(--good); font-weight:800;">Granting…</p>

            @if ($pauseHistory->isNotEmpty())
                <details style="margin-top:14px;">
                    <summary class="chip-l">Pause history ({{ $pauseHistory->count() }})</summary>
                    <ul style="margin:8px 0 0; padding-left:18px;">
                        @foreach ($pauseHistory as $span)
                            <li class="p soft">{{ $span->paused_at?->format('j M Y') }} — {{ $span->resumed_at ? $span->resumed_at->format('j M Y') : 'still paused' }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>
    @endif

    {{-- From school (SJ-04) --}}
    @if ($schoolThisWeek->isNotEmpty())
        <div class="card">
            <p class="eyebrow">From school 🏫 (journal)</p>
            <ul style="margin:0; padding-left:18px;">
                @foreach ($schoolThisWeek as $entry)
                    <li class="p ink">{{ $entry->assessment_date?->format('j M') }} — {{ $entry->strand ?? 'strand pending' }}, {{ $entry->assessment_type ?? 'assessment' }}: <strong>{{ $entry->score ?? 'score pending' }}</strong></li>
                @endforeach
            </ul>
            <p class="p soft" style="margin-top:8px;">Classroom evidence — kept beside, never merged into, our own picture.
                <a href="{{ route('guardian.journal', $student) }}" style="color:var(--teal); font-weight:700;">Open the journal →</a></p>
        </div>
    @endif
    </div>{{-- /rewards section --}}
</div>
