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

        .vy-wrap { max-width: 1200px; margin: 0 auto; padding: 24px 16px 48px; }
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
            width: clamp(38px, 5.2vw, 62px); height: clamp(38px, 5.2vw, 62px);
            display: grid; place-items: center;
            font-size: clamp(1rem, 2.4vw, 1.7rem); line-height: 1;
            border-radius: 50%;
            background: rgba(20, 30, 66, 0.72);
            border: 2.5px solid rgba(147, 197, 253, 0.6);
            box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }
        .is-mastered .vy-badge { border-color: #34d399; box-shadow: 0 0 16px rgba(52,211,153,0.7); }
        .is-current .vy-badge { border-color: #fde68a; box-shadow: 0 0 20px rgba(253,230,138,0.9); animation: vy-pulse 1.8s ease-in-out infinite; }
        .is-locked .vy-badge { filter: grayscale(0.7) brightness(0.7); border-color: rgba(255,255,255,0.25); }

        @keyframes vy-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .vy-label {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(0.5rem, 1.05vw, 0.78rem);
            text-shadow: 0 1px 5px rgba(0,0,0,0.85);
            max-width: clamp(90px, 14vw, 180px);
            text-align: center; line-height: 1.15;
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
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.62rem; white-space: nowrap;
            background: #fde047; color: #78350f; padding: 2px 9px; border-radius: 999px;
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
            font-size: clamp(1rem, 2.4vw, 1.8rem);
            filter: drop-shadow(0 3px 6px rgba(0,0,0,0.5));
            pointer-events: none;
        }
    </style>
</head>
<body>
    <nav class="vy-nav">
        <span class="vy-brand">{{ $island['icon'] }} {{ $island['name'] }}</span>
        <a href="{{ route('student.voyage') }}" class="vy-back">← Back to the sea</a>
    </nav>

    <div class="vy-wrap">
        <h1 class="vy-title">{{ $island['name'] }}</h1>
        <p class="vy-sub">{{ $island['conquered'] }} of {{ $island['total'] }} levels conquered — clear them in order to master this island.</p>

        <div class="vy-map">
            <svg class="vy-trail" viewBox="0 0 {{ $mapW }} {{ $mapH }}" preserveAspectRatio="none" aria-hidden="true">
                <polyline class="vy-trail-ahead" points="{{ $ahead }}" />
                <polyline class="vy-trail-done" points="{{ $travelled }}" />
            </svg>

            @foreach($levels as $index => $level)
                @php
                    if ($level['mastered']) {
                        $state = 'mastered';
                    } elseif ($index === $currentStop) {
                        $state = 'current';
                    } else {
                        $state = 'locked';
                    }
                    $stop = $stops[$index] ?? ['x' => 50, 'y' => 50];
                    $badge = match ($state) {
                        'mastered' => '⭐',
                        'current' => '▶',
                        default => '🔒',
                    };
                @endphp
                @php $isThisWeek = in_array($level['id'], $thisWeekModuleIds ?? [], true); @endphp
                <a href="{{ $state === 'locked' ? '#' : route('practice.lesson', $level['id']) }}"
                   class="vy-stop is-{{ $state }} {{ $isThisWeek ? 'is-thisweek' : '' }}"
                   style="left: {{ $stop['x'] }}%; top: {{ $stop['y'] }}%;"
                   title="{{ $level['topic'] }}{{ $isThisWeek ? ' — this week' : '' }}">
                    <span class="vy-badge">{{ $badge }}</span>
                    <span class="vy-label">{{ $level['topic'] }}</span>
                    @if($isThisWeek)<span class="vy-thisweek">This week</span>@endif
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
    </div>
</body>
</html>
