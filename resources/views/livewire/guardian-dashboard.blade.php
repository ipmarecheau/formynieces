<div>
    <style>
        .gd-header { margin-bottom: 1.5rem; }
        .gd-title {
            font-family: 'Fredoka One', cursive;
            font-size: 1.5rem; color: #0e7490; margin: 0 0 0.25rem;
        }
        .gd-subtitle { font-size: 0.9rem; color: #9ca3af; margin: 0; font-weight: 700; }
        .gd-childswitch { margin-top: 0.7rem; display: flex; align-items: center; gap: 0.5rem; }
        .gd-childswitch label {
            font-size: 0.68rem; font-weight: 800; color: #93b2cc;
            text-transform: uppercase; letter-spacing: 0.07em;
        }
        .gd-childswitch select {
            font-family: 'Nunito', sans-serif; font-size: 0.85rem; font-weight: 700; color: #0e7490;
            border: 1.5px solid #e6f2fb; border-radius: 10px; padding: 0.35rem 0.6rem; background: white;
        }
        .gd-card {
            background: white; border: 1.5px solid #e6f2fb;
            border-radius: 18px; padding: 1.1rem 1.25rem; margin-bottom: 1rem;
        }
        .gd-card.gd-warn { border-color: #fed7aa; background: #fffbf5; }
        .gd-eyebrow {
            font-size: 0.68rem; font-weight: 800; color: #93b2cc;
            text-transform: uppercase; letter-spacing: 0.07em; margin: 0 0 0.6rem;
        }
        .gd-answer { font-size: 0.95rem; color: #1f2937; margin: 0; line-height: 1.5; }
        .gd-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 13px; border-radius: 999px;
            font-size: 0.78rem; font-weight: 800;
        }
        .gd-pill-done { background: #f0fdf4; color: #166534; border: 1.5px solid #bbf7d0; }
        .gd-pill-pending { background: #fff7ed; color: #c2410c; border: 1.5px solid #fed7aa; }
        .gd-warn-line {
            font-size: 0.82rem; font-weight: 800; color: #c2410c;
            margin: 0 0 0.75rem;
        }
        .gd-paper { margin-bottom: 0.7rem; }
        .gd-paper:last-child { margin-bottom: 0; }
        .gd-paper-head {
            display: flex; align-items: baseline; justify-content: space-between;
            margin-bottom: 4px;
        }
        .gd-paper-name { font-size: 0.85rem; font-weight: 800; color: #374151; }
        .gd-paper-weight { font-size: 0.72rem; font-weight: 700; color: #93b2cc; }
        .gd-paper-stat { font-size: 0.78rem; color: #6b7280; font-weight: 600; }
        .gd-track {
            background: #e6f2fb; border-radius: 999px; height: 8px;
            overflow: hidden; margin-top: 5px;
        }
        .gd-fill { background: #93b2cc; height: 100%; border-radius: 999px; }
        .gd-unassessed { font-size: 0.78rem; color: #9ca3af; font-weight: 600; font-style: italic; }
        .gd-affirm {
            background: #f0fdf4; border: 1.5px solid #bbf7d0;
            border-radius: 18px; padding: 1.1rem 1.25rem; margin-bottom: 1rem;
        }
        .gd-affirm-lead { font-size: 1rem; font-weight: 800; color: #166534; margin: 0 0 0.3rem; }
        .gd-affirm-sub { font-size: 0.85rem; color: #15803d; margin: 0; font-weight: 600; }
        .gd-triage-step { font-size: 0.9rem; color: #1f2937; margin: 0 0 0.4rem; line-height: 1.5; }
        .gd-triage-step:last-child { margin-bottom: 0; }
        .gd-rewards { display: flex; flex-wrap: wrap; gap: 0.5rem; }
        .gd-reward-btn {
            font-family: 'Nunito', sans-serif; font-size: 0.82rem; font-weight: 800; cursor: pointer;
            color: #0e7490; background: white; border: 1.5px solid #cbe4f0; border-radius: 10px; padding: 0.45rem 0.9rem;
        }
        .gd-reward-btn:hover { background: #eff6ff; }
    </style>

    <div class="gd-header">
        <h1 class="gd-title">{{ $studentName ?? 'Weekly guardian summary' }}</h1>
        <p class="gd-subtitle">{{ $weekLabel }} · the four questions, answered honestly.</p>

        @if ($students->count() > 1)
            <div class="gd-childswitch">
                <label for="gd-child">Child</label>
                <select id="gd-child" wire:model.live="studentId">
                    @foreach ($students as $child)
                        <option value="{{ $child->id }}">{{ $child->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    @if ($reconciliationPending)
        {{-- RR-04: the diagnostic cleared a strand the guardian flagged. She must
             choose before {{ $studentName }}'s onboarding completes. --}}
        <div class="gd-card gd-warn">
            <p class="gd-eyebrow">A quick check before we finish</p>
            <p class="gd-warn-line">{{ $studentName }}'s diagnostic sees things differently</p>
            <p class="gd-answer" style="margin-bottom:0.4rem;">
                You told us {{ $studentName }} struggles with
                <strong>{{ implode(', ', $clearedStrands) }}</strong>, but the diagnostic
                found she's already got a good handle on
                {{ count($clearedStrands) === 1 ? 'it' : 'them' }}.
            </p>
            <p class="gd-answer" style="margin-bottom:0.9rem;">
                Her map won't start until you choose.
            </p>
            <div style="display:flex; gap:0.6rem; flex-wrap:wrap;">
                <button type="button" wire:click="proceedWithDiagnostic"
                        style="flex:1 1 auto; background:#0e7490; color:white; border:none; border-radius:12px; padding:0.7rem 1rem; font-weight:800; cursor:pointer;">
                    Use the diagnostic result
                </button>
                <button type="button" wire:click="keepWeakAreas"
                        style="flex:1 1 auto; background:white; color:#0e7490; border:1.5px solid #ddd6fe; border-radius:12px; padding:0.7rem 1rem; font-weight:800; cursor:pointer;">
                    Keep my weak areas
                </button>
            </div>
        </div>
    @endif

    @if ($triage)
        {{-- Calm triage for a significantly-behind student: feasible weekly steps, not a deficit total. --}}
        <div class="gd-card gd-warn">
            <p class="gd-eyebrow">Catch-up plan</p>
            <p class="gd-warn-line">{{ $triage['priority'] }}</p>
            @foreach ($triage['subjects'] as $subject)
                <p class="gd-triage-step">
                    <strong>{{ $subject['name'] }}:</strong> {{ $subject['weekly_step'] }}
                </p>
            @endforeach
        </div>
    @endif

    @if ($onTrack)
        <div class="gd-affirm">
            <p class="gd-eyebrow">This week</p>
            <p class="gd-affirm-lead">Target met and on pace</p>
            <p class="gd-affirm-sub">Nothing to carry into next week</p>
        </div>
    @endif

    {{-- Q1 — target completion --}}
    <div class="gd-card">
        <p class="gd-eyebrow">This week's target</p>
        @if ($targetCompleted)
            <span class="gd-pill gd-pill-done">Completed</span>
            <p class="gd-answer" style="margin-top:0.5rem;">Every module set for this week is done.</p>
        @else
            <span class="gd-pill gd-pill-pending">In progress</span>
            <p class="gd-answer" style="margin-top:0.5rem;">This week's target isn't complete yet.</p>
        @endif
    </div>

    {{-- Q2 — pace, weighted 50/30/20 --}}
    <div class="gd-card {{ $paceStatus === 'warning' ? 'gd-warn' : '' }}">
        <p class="gd-eyebrow">Pace</p>

        <p class="gd-answer" style="margin-bottom:0.7rem;">
            These count modules she has mastered against the pace for this point in the year — not a score.
        </p>

        @if ($paceStatus === 'warning')
            <p class="gd-warn-line">
                {{ $weeksBehind }} {{ $weeksBehind === 1 ? 'week' : 'weeks' }} behind — the catch-up plan above breaks it into feasible weekly steps.
            </p>
        @endif

        @foreach ($pace as $paper => $row)
            <div class="gd-paper">
                <div class="gd-paper-head">
                    <span class="gd-paper-name">{{ $paper }} <span class="gd-paper-weight">{{ $row['weight'] }}% of the exam</span></span>
                    @if ($row['assessed'])
                        <span class="gd-paper-stat">{{ $row['completed'] }} of {{ $row['expected'] }} modules mastered@if ($row['behind_count'] > 0) · {{ $row['behind_count'] }} to catch up@endif</span>
                    @else
                        <span class="gd-unassessed">Not yet assessed</span>
                    @endif
                </div>
                @if ($row['assessed'] && $row['expected'] > 0)
                    <div class="gd-track">
                        <div class="gd-fill" style="width: {{ (int) round(($row['completed'] / max($row['expected'], 1)) * 100) }}%;"></div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Q3 — recommendation --}}
    <div class="gd-card">
        <p class="gd-eyebrow">Recommendation</p>
        <p class="gd-answer">{{ $recommendation }}</p>
    </div>

    {{-- SE-15 — grant a streak reward to the student's Captain's Locker (the one honest→child crossing) --}}
    @if ($student)
        <div class="gd-card">
            <p class="gd-eyebrow">Grant a reward</p>
            <p class="gd-answer" style="margin-bottom:0.7rem;">
                Protect an upcoming day off — a granted reward appears in {{ $studentName }}'s Captain's Locker.
            </p>
            <div class="gd-rewards">
                @foreach ($rewardTypes as $type => $label)
                    <button type="button" class="gd-reward-btn" wire:click="grantReward('{{ $type }}')">{{ $label }}</button>
                @endforeach
            </div>
            <p class="gd-answer" wire:loading wire:target="grantReward" style="margin-top:0.5rem; color:#16a34a;">Granting…</p>
        </div>
    @endif

    {{-- Q4 — writing feedback pointer --}}
    <div class="gd-card">
        <p class="gd-eyebrow">Writing feedback</p>
        @if ($writingFeedback)
            <p class="gd-answer">Latest feedback is available to review.</p>
        @else
            <p class="gd-answer">No writing feedback yet.</p>
        @endif
    </div>

    {{-- From school this week (SJ-04) — labelled so the parent knows which source is which --}}
    @if ($schoolThisWeek->isNotEmpty())
        <div class="gd-card">
            <p class="gd-eyebrow">From school 🏫 (journal)</p>
            <ul style="margin: 0; padding-left: 1.1rem;">
                @foreach ($schoolThisWeek as $entry)
                    <li class="gd-answer">
                        {{ $entry->assessment_date?->format('j M') }} — {{ $entry->strand ?? 'strand pending' }},
                        {{ $entry->assessment_type ?? 'assessment' }}: <strong>{{ $entry->score ?? 'score pending' }}</strong>
                    </li>
                @endforeach
            </ul>
            <p class="gd-answer" style="opacity: .75; margin-top: .4rem;">
                Classroom evidence from the school journal — kept beside (never merged into) our own picture.
                <a href="{{ route('guardian.journal', $student) }}" style="text-decoration: underline;">Open the journal →</a>
            </p>
        </div>
    @endif
</div>
