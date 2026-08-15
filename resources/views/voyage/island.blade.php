{{-- resources/views/voyage/island.blade.php --}}
{{-- The Voyage tier 2: an island's own mini-voyage. Its ~7 levels are stops on
     a walkable interior path (a town, a building, a cavern…), gated in sequence
     and always kind. Bespoke art per island; a themed gradient stands in until
     an island's artwork lands. Reuses the overworld's aspect-ratio stage so the
     background, SVG trail, and pinned level-stops scale as one. [AM] --}}
@php
    $mapW = 2752;
    $mapH = 1536;

    // Pair each level with its stop coordinate (same index order).
    $levels = $island['levels'];
    $points = array_map(fn ($s) => [
        'x' => round($s['x'] / 100 * $mapW, 1),
        'y' => round($s['y'] / 100 * $mapH, 1),
    ], $stops);

    $travelled = collect($points)->take($currentStop + 1)
        ->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ');
    $ahead = collect($points)->slice($currentStop)
        ->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ');

    // AM-08: one shared row per stop drives both the numbered map marker and the
    // named legend beside it, so status stays in lockstep. Long topic names live
    // in the legend now, not as floating labels that overlap on a busy path.
    $stopRows = [];
    foreach ($levels as $index => $level) {
        $state = ($level['review'] ?? false)
            ? 'review'
            : ($level['mastered'] ? 'mastered' : ($index === $currentStop ? 'current' : 'locked'));
        $stopCoord = $stops[$index] ?? ['x' => 50, 'y' => 50];
        $stopRows[] = [
            'number' => $index + 1,
            'topic' => $level['topic'],
            'state' => $state,
            'badge' => match ($state) { 'mastered' => '⭐', 'review' => '⭐', 'current' => '▶', default => '🔒' },
            'status' => match ($state) { 'mastered' => 'Conquered', 'review' => 'Needs review', 'current' => 'Current', default => 'Locked' },
            'thisWeek' => in_array($level['id'], $thisWeekModuleIds ?? [], true),
            'href' => $state === 'locked' ? '#' : route('practice.enter', $level['id']),
            'x' => $stopCoord['x'],
            'y' => $stopCoord['y'],
        ];
    }
    $hasReview = collect($stopRows)->contains(fn ($r) => $r['state'] === 'review');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $island['name'] }} — Your Voyage</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            color: #e6f2fb;
            background: linear-gradient(180deg, #1b2a6b 0%, #223a8c 30%, #1f5fa8 70%, #1a7fb0 100%);
        }

        .vy-nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 20px;
            background: rgba(12, 20, 50, 0.55);
            backdrop-filter: blur(8px);
            position: sticky; top: 0; z-index: 10;
        }
        .vy-brand { font-family: 'Fredoka One', cursive; font-size: 1.3rem; color: #e6f2fb; }
        .vy-back {
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.82rem;
            padding: 8px 16px; border-radius: 999px; text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.35); color: #e6f2fb;
            background: rgba(255,255,255,0.08);
        }
        .vy-back:hover { background: rgba(255,255,255,0.18); }

        .vy-wrap {
            max-width: min(94vw, 2200px); margin: 0 auto; padding: 14px 16px 18px;
            min-height: calc(100vh - 58px);
            display: flex; flex-direction: column;
        }
        .vy-title {
            font-family: 'Fredoka One', cursive; font-size: 1.9rem; text-align: center;
            margin-bottom: 6px; text-shadow: 0 2px 12px rgba(0,0,0,0.35);
        }
        .vy-sub { text-align: center; color: rgba(243,232,255,0.85); font-size: 0.95rem; margin-bottom: 22px; }

        .vy-map {
            position: relative;
            width: 100%;
            aspect-ratio: 2752 / 1536;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(0,0,0,0.45);
            @if($background)
                background: url('{{ $background }}') center / cover no-repeat;
            @else
                background:
                    radial-gradient(120% 100% at 50% 0%, rgba(253,230,138,0.18), transparent 60%),
                    linear-gradient(180deg, #24306b 0%, #2b4a86 55%, #1f6f9c 100%);
            @endif
        }

        .vy-trail { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }
        .vy-trail-ahead {
            fill: none; stroke: rgba(255,255,255,0.5);
            stroke-width: 7; stroke-linecap: round; stroke-dasharray: 3 26;
        }
        .vy-trail-done {
            fill: none; stroke: #fde68a;
            stroke-width: 10; stroke-linecap: round; stroke-linejoin: round;
            filter: drop-shadow(0 0 6px rgba(253,230,138,0.7));
        }

        .vy-stop {
            position: absolute; transform: translate(-50%, -50%);
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            text-decoration: none; color: inherit; cursor: pointer;
            transition: transform 0.16s;
        }
        .vy-stop:hover { transform: translate(-50%, -50%) scale(1.08); }
        .vy-stop.is-locked { cursor: not-allowed; }

        .vy-badge {
            width: 9cqw; height: 9cqw;
            display: grid; place-items: center;
            font-size: 5cqw; line-height: 1;
            border-radius: 50%;
            background: rgba(20, 30, 66, 0.72);
            border: 2.5px solid rgba(147, 197, 253, 0.6);
            box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }
        .is-mastered .vy-badge { border-color: #34d399; box-shadow: 0 0 16px rgba(52,211,153,0.7); }
        .is-current .vy-badge { border-color: #fde68a; box-shadow: 0 0 20px rgba(253,230,138,0.9); animation: vy-pulse 1.8s ease-in-out infinite; }
        .is-locked .vy-badge { filter: grayscale(0.7) brightness(0.7); border-color: rgba(255,255,255,0.25); }
        /* LL-25: a mastered level due for review glows and pulses with a red outline. */
        .is-review .vy-badge { border-color: #f87171; animation: vy-review-pulse 1.4s ease-in-out infinite; }
        .is-review .vy-label { color: #fecaca; }

        @keyframes vy-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        @keyframes vy-review-pulse {
            0%, 100% { box-shadow: 0 0 0 3px rgba(248,113,113,0.45), 0 0 16px rgba(248,113,113,0.7); }
            50% { box-shadow: 0 0 0 6px rgba(248,113,113,0.8), 0 0 26px rgba(248,113,113,1); }
        }
        @media (prefers-reduced-motion: reduce) { .is-review .vy-badge { animation: none; box-shadow: 0 0 0 4px rgba(248,113,113,0.7); } }

        .vy-label {
            font-family: 'Fredoka One', cursive;
            font-size: 2.6cqw;
            color: #f8fafc;
            text-shadow: 0 1px 2px rgba(0,0,0,0.9);
            max-width: 24cqw;
            text-align: center; line-height: 1.15;
            padding: 0.4cqw 1.4cqw; border-radius: 12px;
            background: rgba(9, 14, 34, 0.72);
            box-shadow: 0 2px 8px rgba(0,0,0,0.45);
        }
        .is-locked .vy-label { opacity: 0.6; }

        /* The Writer's Log — one writing stop on every island. Amber and calm;
           links to this week's writing prompt (WR-01/SH-05). */
        .vy-stop.is-writing { cursor: pointer; }
        .is-writing .vy-badge {
            border-color: rgba(251, 191, 36, 0.75);
            background: rgba(120, 66, 12, 0.72);
            box-shadow: 0 0 14px rgba(251, 191, 36, 0.45);
        }
        .is-writing .vy-label { color: #fde68a; }
        /* SH-02: this week's levels shimmer with a gold ring + banner. */
        .vy-stop.is-thisweek .vy-badge { box-shadow: 0 0 0 4px rgba(253,224,71,0.9), 0 0 22px rgba(253,224,71,0.75); }
        .vy-stop.is-thisweek .vy-thisweek {
            position: absolute; top: -13px; left: 50%; transform: translateX(-50%);
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 2cqw; white-space: nowrap;
            background: #fde047; color: #78350f; padding: 0.4cqw 1.4cqw; border-radius: 999px;
            box-shadow: 0 2px 8px rgba(120,53,15,0.35);
        }
        .vy-soon {
            font-family: 'Nunito', sans-serif; font-weight: 800;
            font-size: clamp(0.42rem, 0.85vw, 0.6rem);
            letter-spacing: 0.06em; text-transform: uppercase;
            color: rgba(253, 230, 138, 0.75);
        }

        .vy-boat {
            position: absolute; transform: translate(-50%, -140%);
            font-size: 5.5cqw;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,0.5));
            pointer-events: none;
        }

        /* AM-08: the map holds a compact number per stop; the legend beside it
           carries the full names + status, so long labels never overlap again. */
        .vy-stage { display: flex; flex-direction: column; gap: 16px; flex: 1 1 auto; min-height: 0; }
        /* Mobile (stacked): the container hugs the map's own aspect ratio — no wasted padding. */
        .vy-map-col { aspect-ratio: 2752 / 1536; }
        .vy-map-col .mv-viewport { min-height: 0; }
        .vy-legend { display: flex; flex-direction: column; min-height: 0; }
        .vy-legend-head { flex: 0 0 auto; }
        .vy-legend-list { overflow-y: auto; min-height: 0; max-height: 45vh; }
        @media (min-width: 900px) {
            /* Definite height (not min-height) so the flex-grow + height:100% chain
               below resolves and the map window fills the column. */
            .vy-wrap { height: calc(100vh - 58px); justify-content: center; }
            /* The map keeps its own aspect ratio (no letterbox); the row hugs it and centres
               vertically, so big screens make the map bigger instead of padding it. */
            .vy-stage { flex: 0 1 auto; flex-direction: row; align-items: stretch; }
            .vy-map-col { flex: 1 1 70%; min-width: 0; aspect-ratio: 2752 / 1536; }
            .vy-legend { flex: 1 1 30%; min-width: 0; }
            .vy-legend-list { max-height: none; }
        }

        .vy-num {
            font-family: 'Fredoka One', cursive;
            font-size: 3cqw; line-height: 1;
            color: #f8fafc; min-width: 3cqw; text-align: center;
            padding: 0.3cqw 1cqw; border-radius: 999px;
            background: rgba(9, 14, 34, 0.82);
            box-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }
        .is-locked .vy-num { opacity: 0.65; }

        .vy-legend {
            background: rgba(12, 20, 50, 0.55);
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(147, 197, 253, 0.28);
            border-radius: 18px; padding: 14px 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        .vy-legend-head {
            font-family: 'Fredoka One', cursive; font-size: 0.9rem;
            color: #cfe6fb; margin-bottom: 10px;
        }
        .vy-legend-list { list-style: none; display: flex; flex-direction: column; gap: 7px; }
        .vy-legend-row {
            display: grid; grid-template-columns: 24px 1fr auto; align-items: center; gap: 9px;
            padding: 6px 8px; border-radius: 12px;
            background: rgba(255,255,255,0.04);
        }
        .vy-legend-num {
            font-family: 'Fredoka One', cursive; font-size: 0.8rem;
            text-align: center; color: #e6f2fb;
        }
        .vy-legend-name { font-size: 0.82rem; font-weight: 700; color: #e6f2fb; line-height: 1.2; }
        .vy-legend-status {
            font-size: 0.66rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em;
            padding: 2px 8px; border-radius: 999px; white-space: nowrap;
            background: rgba(148,163,184,0.25); color: #cbd5e1;
        }
        .vy-legend-row.is-mastered .vy-legend-status { background: rgba(52,211,153,0.22); color: #6ee7b7; }
        .vy-legend-row.is-current .vy-legend-status { background: rgba(253,230,138,0.24); color: #fde68a; }
        .vy-legend-row.is-review { outline: 2px solid rgba(248,113,113,0.85); background: rgba(248,113,113,0.12); }
        .vy-legend-row.is-review .vy-legend-status { background: rgba(248,113,113,0.28); color: #fecaca; }
        .vy-legend-row.is-writing .vy-legend-status { background: rgba(251,191,36,0.2); color: #fcd34d; }
        .vy-legend-row.is-thisweek { outline: 2px solid rgba(253,224,71,0.8); }
        .vy-legend-row.is-thisweek .vy-legend-status { background: #fde047; color: #78350f; }
    </style>
</head>
<body>
    <nav class="vy-nav">
        <span class="vy-brand">{{ $island['icon'] }} {{ $island['name'] }}</span>
        <a href="{{ route('student.voyage') }}" class="vy-back">← Back to the sea</a>
    </nav>

    <div class="vy-wrap">
        <livewire:smooth-guide guide="island" wire:key="guide-island" />
        @if ($hasReview)
            <livewire:smooth-guide guide="review" :alert="true" wire:key="guide-review" />
        @endif
        <h1 class="vy-title">{{ $island['name'] }}</h1>
        <p class="vy-sub">{{ $island['conquered'] }} of {{ $island['total'] }} levels conquered — clear them in order to master this island.</p>

        <div class="vy-stage">
        <div class="vy-map-col">
        <x-map-viewport :fx="$stops[$currentStop]['x'] ?? 50" :fy="$stops[$currentStop]['y'] ?? 50">
        <div class="vy-map">
            <svg class="vy-trail" viewBox="0 0 {{ $mapW }} {{ $mapH }}" preserveAspectRatio="none" aria-hidden="true">
                <polyline class="vy-trail-ahead" points="{{ $ahead }}" />
                <polyline class="vy-trail-done" points="{{ $travelled }}" />
            </svg>

            @foreach($stopRows as $row)
                <a href="{{ $row['href'] }}"
                   class="vy-stop is-{{ $row['state'] }} {{ $row['thisWeek'] ? 'is-thisweek' : '' }}"
                   style="left: {{ $row['x'] }}%; top: {{ $row['y'] }}%;"
                   title="{{ $row['number'] }}. {{ $row['topic'] }}{{ $row['thisWeek'] ? ' — this week' : '' }}">
                    <span class="vy-badge">{{ $row['badge'] }}</span>
                    <span class="vy-num">{{ $row['number'] }}</span>
                    @if($row['thisWeek'])<span class="vy-thisweek">This week</span>@endif
                </a>
            @endforeach

            @if(isset($writingStop))
                <a href="{{ route('student.writing') }}"
                   class="vy-stop is-writing"
                   style="left: {{ $writingStop['x'] }}%; top: {{ $writingStop['y'] }}%;"
                   title="Writer's Log — this week's writing prompt">
                    <span class="vy-badge">✍️</span>
                    <span class="vy-label">Writer's Log</span>
                </a>
            @endif

            @if(isset($stops[$currentStop]))
                <span class="vy-boat" style="left: {{ $stops[$currentStop]['x'] }}%; top: {{ $stops[$currentStop]['y'] }}%;">⛵</span>
            @endif
        </div>
        </x-map-viewport>
        </div>

        <aside class="vy-legend" aria-label="Stops on this island">
            <p class="vy-legend-head">Stops on this island</p>
            <ol class="vy-legend-list">
                @foreach($stopRows as $row)
                    <li class="vy-legend-row is-{{ $row['state'] }} {{ $row['thisWeek'] ? 'is-thisweek' : '' }}">
                        <span class="vy-legend-num">{{ $row['number'] }}</span>
                        <span class="vy-legend-name">{{ $row['topic'] }}</span>
                        <span class="vy-legend-status">{{ $row['thisWeek'] ? 'This week' : $row['status'] }}</span>
                    </li>
                @endforeach
                @if(isset($writingStop))
                    <li class="vy-legend-row is-writing">
                        <span class="vy-legend-num">✍️</span>
                        <span class="vy-legend-name">Writer's Log</span>
                        <span class="vy-legend-status">Writing</span>
                    </li>
                @endif
            </ol>
        </aside>
        </div>
    </div>
</body>
</html>
