<div>
    <style>
        .gd-header { margin-bottom: 1.5rem; }
        .gd-title {
            font-family: 'Fredoka One', cursive;
            font-size: 1.5rem; color: #7c3aed; margin: 0 0 0.25rem;
        }
        .gd-subtitle { font-size: 0.9rem; color: #9ca3af; margin: 0; font-weight: 700; }
        .gd-card {
            background: white; border: 1.5px solid #f3e8ff;
            border-radius: 18px; padding: 1.1rem 1.25rem; margin-bottom: 1rem;
        }
        .gd-card.gd-warn { border-color: #fed7aa; background: #fffbf5; }
        .gd-eyebrow {
            font-size: 0.68rem; font-weight: 800; color: #c4b5fd;
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
        .gd-paper-weight { font-size: 0.72rem; font-weight: 700; color: #c4b5fd; }
        .gd-paper-stat { font-size: 0.78rem; color: #6b7280; font-weight: 600; }
        .gd-track {
            background: #f3e8ff; border-radius: 999px; height: 8px;
            overflow: hidden; margin-top: 5px;
        }
        .gd-fill { background: #a78bfa; height: 100%; border-radius: 999px; }
        .gd-unassessed { font-size: 0.78rem; color: #9ca3af; font-weight: 600; font-style: italic; }
    </style>

    <div class="gd-header">
        <h1 class="gd-title">Weekly guardian summary</h1>
        <p class="gd-subtitle">The four questions, answered honestly.</p>
    </div>

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

        @if ($paceStatus === 'warning')
            <p class="gd-warn-line">{{ $weeksBehind }} week(s) behind the pacing calendar</p>
        @endif

        @foreach ($pace as $paper => $row)
            <div class="gd-paper">
                <div class="gd-paper-head">
                    <span class="gd-paper-name">{{ $paper }} <span class="gd-paper-weight">{{ $row['weight'] }}%</span></span>
                    @if ($row['assessed'])
                        <span class="gd-paper-stat">{{ $row['completed'] }}/{{ $row['expected'] }} on track · {{ $row['behind_count'] }} behind</span>
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

    {{-- Q4 — writing feedback pointer --}}
    <div class="gd-card">
        <p class="gd-eyebrow">Writing feedback</p>
        @if ($writingFeedback)
            <p class="gd-answer">Latest feedback is available to review.</p>
        @else
            <p class="gd-answer">No writing feedback yet.</p>
        @endif
    </div>
</div>
