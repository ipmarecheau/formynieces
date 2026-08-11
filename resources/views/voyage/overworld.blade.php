{{-- resources/views/voyage/overworld.blade.php --}}
{{-- The Voyage overworld (tier 1): the 13 painted islands of the sea map, each a
     stop on the dashed trail. Curriculum is chunked across the islands in order;
     markers and the trail live in the map image's own coordinate space, so they
     scale together at any size. Mastery-gated, always kind — islands show a
     conquered COUNT, never a pace percentage. [AM] --}}
@php
    $mapW = 2752;
    $mapH = 1536;

    // Island centres in the map's pixel space (for the SVG trail).
    $points = array_map(fn ($i) => [
        'x' => round($i['x'] / 100 * $mapW, 1),
        'y' => round($i['y'] / 100 * $mapH, 1),
    ], $islands);

    // The bright trail runs from the start up to the current island; the rest
    // stays faded and dashed ahead of the boat.
    $currentIndex = collect($islands)->search(fn ($i) => $i['current']);
    $currentIndex = $currentIndex === false ? 0 : $currentIndex;

    $travelled = collect($points)->take($currentIndex + 1)
        ->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ');
    $ahead = collect($points)->slice($currentIndex)
        ->map(fn ($p) => "{$p['x']},{$p['y']}")->implode(' ');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Voyage — SmoothSeas</title>
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
        .vy-nav-right { display: flex; align-items: center; gap: 10px; }
        .vy-switch, .vy-logout {
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.82rem;
            padding: 8px 16px; border-radius: 999px; cursor: pointer; text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.35); color: #e6f2fb;
            background: rgba(255,255,255,0.08);
        }
        .vy-switch:hover, .vy-logout:hover { background: rgba(255,255,255,0.18); }
        .vy-logout { border: none; }
        .vy-streak {
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.82rem;
            padding: 8px 16px; border-radius: 999px; color: #fff7ed;
            background: linear-gradient(135deg, rgba(249,115,22,0.85), rgba(246,183,30,0.85));
            box-shadow: 0 4px 12px rgba(246,183,30,0.35);
        }
        /* SH-02: this week's islands shimmer with a gold ring + banner. */
        .vy-island.is-thisweek .vy-badge { box-shadow: 0 0 0 4px rgba(253,224,71,0.9), 0 0 22px rgba(253,224,71,0.75); }
        .vy-thisweek {
            position: absolute; top: -14px; left: 50%; transform: translateX(-50%);
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.66rem; white-space: nowrap;
            background: #fde047; color: #78350f; padding: 2px 10px; border-radius: 999px;
            box-shadow: 0 2px 8px rgba(120,53,15,0.35);
        }

        .vy-wrap {
            max-width: 1500px; margin: 0 auto; padding: 14px 16px 18px;
            min-height: calc(100vh - 58px);   /* fill below the sticky nav */
            display: flex; flex-direction: column;
        }
        .vy-title {
            font-family: 'Fredoka One', cursive; font-size: 1.9rem; text-align: center;
            margin-bottom: 6px; text-shadow: 0 2px 12px rgba(0,0,0,0.35);
        }
        .vy-sub { text-align: center; color: rgba(243,232,255,0.85); font-size: 0.95rem; margin-bottom: 22px; }

        /* VC-01..03: the companion panel — a warm greeting over honest data. */
        .vy-companion {
            display: flex; align-items: center; gap: 14px;
            max-width: 720px; margin: 0 auto 22px;
            padding: 16px 20px; border-radius: 20px;
            background: rgba(12, 20, 50, 0.55);
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(147, 197, 253, 0.28);
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        .vy-companion-avatar {
            flex: 0 0 auto;
            height: clamp(78px, 17vw, 116px); width: auto;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.45));
        }
        .vy-companion-body { min-width: 0; }
        .vy-companion-greeting {
            font-family: 'Fredoka One', cursive;
            font-size: clamp(1.1rem, 3vw, 1.5rem); color: #f8fafc;
            text-shadow: 0 2px 10px rgba(0,0,0,0.35);
        }
        .vy-companion-line { font-size: 0.92rem; font-weight: 700; margin-top: 4px; line-height: 1.35; }
        .vy-companion-streak { color: #fde68a; }
        .vy-companion-plan { color: #bfe6ff; }

        /* The map: a fixed-aspect stage. The image, the SVG trail, and every
           island marker share this box, so they scale as one. */
        .vy-map {
            position: relative;
            width: 100%;
            aspect-ratio: 2752 / 1536;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(0,0,0,0.45);
            background:
                url('/images/voyage/overworld.webp') center / cover no-repeat,
                linear-gradient(180deg, #1b2a6b, #1a7fb0);
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

        .vy-island {
            position: absolute; transform: translate(-50%, -50%);
            display: flex; flex-direction: column; align-items: center; gap: 4px;
            text-decoration: none; color: inherit; cursor: pointer;
            transition: transform 0.16s;
        }
        .vy-island:hover { transform: translate(-50%, -50%) scale(1.08); }
        .vy-island.is-locked { cursor: not-allowed; }

        .vy-badge {
            width: 6.5cqw; height: 6.5cqw;
            display: grid; place-items: center;
            font-size: 3.6cqw; line-height: 1;
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

        /* AM-08: a compact number chip under each island badge, keyed to the legend. */
        .vy-num {
            font-family: 'Fredoka One', cursive;
            font-size: 2.6cqw; line-height: 1; color: #f8fafc;
            padding: 0.4cqw 1.4cqw; border-radius: 999px;
            background: rgba(9, 14, 34, 0.82);
            box-shadow: 0 2px 8px rgba(0,0,0,0.5);
        }
        .is-locked .vy-num { opacity: 0.6; }
        .vy-count-done { background: rgba(52, 211, 153, 0.35); color: #bbf7d0; }
        .is-locked .vy-label, .is-locked .vy-count { opacity: 0.6; }

        .vy-boat {
            position: absolute; transform: translate(-50%, -140%);
            font-size: 5cqw;
            filter: drop-shadow(0 3px 6px rgba(0,0,0,0.5));
            pointer-events: none;
        }
        /* SH-02 this-week banner scales with the map too. */
        .vy-thisweek { font-size: 2cqw; }

        /* AM-10: map on one side, messaging + legend on the other. Fills the
           viewport height; only the legend list scrolls (the greeting stays put).
           Stacks on narrow screens (map on top, panel below). */
        .vy-stage { display: flex; flex-direction: column; gap: 16px; flex: 1 1 auto; min-height: 0; }
        .vy-map-col { height: 56vh; }
        .vy-panel { display: flex; flex-direction: column; gap: 14px; min-height: 0; }
        .vy-panel .vy-companion { max-width: none; margin: 0; flex: 0 0 auto; }
        .vy-panel .vy-legend { flex: 1 1 auto; min-height: 0; display: flex; flex-direction: column; }
        .vy-legend-head { flex: 0 0 auto; }
        .vy-legend-list { overflow-y: auto; min-height: 0; max-height: 45vh; }
        @media (min-width: 900px) {
            /* Definite height (not min-height) so the flex-grow + height:100% chain
               below resolves and the map window fills the column. */
            .vy-wrap { height: calc(100vh - 58px); }
            .vy-stage { flex-direction: row; align-items: stretch; }
            .vy-map-col { flex: 1 1 52%; min-width: 0; height: 100%; }
            .vy-panel { flex: 1 1 48%; min-width: 0; height: 100%; }
            .vy-legend-list { max-height: none; }
        }

        .vy-legend {
            background: rgba(12, 20, 50, 0.55);
            backdrop-filter: blur(8px);
            border: 1.5px solid rgba(147, 197, 253, 0.28);
            border-radius: 18px; padding: 14px 16px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.3);
        }
        .vy-legend-head { font-family: 'Fredoka One', cursive; font-size: 0.9rem; color: #cfe6fb; margin-bottom: 10px; }
        .vy-legend-list { list-style: none; display: flex; flex-direction: column; gap: 7px; }
        .vy-legend-row {
            display: grid; grid-template-columns: 26px 1fr auto auto; align-items: center; gap: 9px;
            padding: 6px 8px; border-radius: 12px; background: rgba(255,255,255,0.04);
        }
        .vy-legend-num {
            font-family: 'Fredoka One', cursive; font-size: 0.82rem; text-align: center; color: #e6f2fb;
            background: rgba(9,14,34,0.7); border-radius: 999px; padding: 2px 0;
        }
        .vy-legend-name { font-size: 0.82rem; font-weight: 700; color: #e6f2fb; line-height: 1.2; }
        .vy-legend-count { font-size: 0.75rem; font-weight: 800; color: #bfe6ff; white-space: nowrap; }
        .vy-legend-status {
            font-size: 0.66rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em;
            padding: 2px 8px; border-radius: 999px; white-space: nowrap;
            background: rgba(148,163,184,0.25); color: #cbd5e1;
        }
        .vy-legend-row.is-mastered .vy-legend-status { background: rgba(52,211,153,0.22); color: #6ee7b7; }
        .vy-legend-row.is-current .vy-legend-status { background: rgba(253,230,138,0.24); color: #fde68a; }
        .vy-legend-row.is-thisweek { outline: 2px solid rgba(253,224,71,0.8); }
        .vy-legend-row.is-thisweek .vy-legend-status { background: #fde047; color: #78350f; }
        .vy-legend-row.is-locked { opacity: 0.6; }
    </style>
</head>
<body>
    <nav class="vy-nav">
        <span class="vy-brand">⛵ Your Voyage</span>
        <div class="vy-nav-right">
            {{-- SH-04: her streak rides along the top of her home. --}}
            @if(($streaks['practice'] ?? 0) > 0)
                <span class="vy-streak" title="Practice day streak">🔥 {{ $streaks['practice'] }} day streak</span>
            @elseif(($streaks['login'] ?? 0) > 0)
                <span class="vy-streak" title="Login day streak">🔥 {{ $streaks['login'] }} day streak</span>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="vy-logout">Log out</button>
            </form>
        </div>
    </nav>

    <div class="vy-wrap">
        <livewire:smooth-guide guide="voyage" wire:key="guide-voyage" />

        <div class="vy-stage">
            <div class="vy-map-col">
                <x-map-viewport :fx="$islands[$currentIndex]['x'] ?? 50" :fy="$islands[$currentIndex]['y'] ?? 50">
                    <div class="vy-map">
                        <svg class="vy-trail" viewBox="0 0 {{ $mapW }} {{ $mapH }}" preserveAspectRatio="none" aria-hidden="true">
                            <polyline class="vy-trail-ahead" points="{{ $ahead }}" />
                            <polyline class="vy-trail-done" points="{{ $travelled }}" />
                        </svg>

                        @foreach($islands as $island)
                            {{-- AM-08 pattern: a numbered marker on the map; the name + status
                                 live in the legend beside it, so labels never overlap. --}}
                            <a href="{{ $island['state'] === 'locked' ? '#' : route('student.voyage.island', $island['slug']) }}"
                               class="vy-island is-{{ $island['state'] }} {{ $island['current'] ? 'is-current' : '' }} {{ ($island['this_week'] ?? false) ? 'is-thisweek' : '' }}"
                               style="left: {{ $island['x'] }}%; top: {{ $island['y'] }}%;"
                               data-island-slug="{{ $island['slug'] }}"
                               title="{{ $loop->iteration }}. {{ $island['name'] }}">
                                <span class="vy-badge">{{ $island['state'] === 'locked' ? '🔒' : $island['icon'] }}</span>
                                <span class="vy-num">{{ $loop->iteration }}</span>
                                @if($island['this_week'] ?? false)
                                    <span class="vy-thisweek">This week</span>
                                @endif
                            </a>
                        @endforeach

                        @if(isset($points[$currentIndex]))
                            <span class="vy-boat" style="left: {{ $islands[$currentIndex]['x'] }}%; top: {{ $islands[$currentIndex]['y'] }}%;">⛵</span>
                        @endif
                    </div>
                </x-map-viewport>
            </div>

            <aside class="vy-panel">
                {{-- VC-01..03: the companion — a warm voice over honest data. --}}
                <div class="vy-companion">
                    <img class="vy-companion-avatar" src="{{ $companion['avatarUrl'] }}"
                         alt="Smooth the turtle, your guide">
                    <div class="vy-companion-body">
                        <p class="vy-companion-greeting">{{ $companion['greeting'] }}</p>
                        @if($companion['streak'])
                            <p class="vy-companion-line vy-companion-streak">{{ $companion['streak'] }}</p>
                        @endif
                        @if($companion['plan'])
                            <p class="vy-companion-line vy-companion-plan">🗺️ {{ $companion['plan'] }}</p>
                        @endif
                    </div>
                </div>

                {{-- AM-10: the legend — every island by name + status, beside the map. --}}
                <div class="vy-legend" aria-label="Islands on your Voyage">
                    <p class="vy-legend-head">Islands</p>
                    <ol class="vy-legend-list">
                        @foreach($islands as $island)
                            <li class="vy-legend-row is-{{ $island['state'] }} {{ $island['current'] ? 'is-current' : '' }} {{ ($island['this_week'] ?? false) ? 'is-thisweek' : '' }}">
                                <span class="vy-legend-num">{{ $loop->iteration }}</span>
                                <span class="vy-legend-name">{{ $island['state'] === 'locked' ? '🔒' : $island['icon'] }} {{ $island['name'] }}</span>
                                <span class="vy-legend-count">{{ $island['conquered'] }} / {{ $island['total'] }}</span>
                                <span class="vy-legend-status">
                                    @if($island['this_week'] ?? false) This week
                                    @elseif($island['current']) Sailing here
                                    @elseif($island['state'] === 'mastered') Mastered
                                    @elseif($island['state'] === 'locked') Locked
                                    @else Ready @endif
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
