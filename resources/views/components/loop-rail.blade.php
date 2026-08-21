@props(['stage' => 'check', 'reteach' => false, 'viaCheck' => false])

{{--
  LL-08 — the learning-loop route map. A fixed board down the left of every module
  screen showing the loop as three routes:
    • the main route      Check → Lesson → Practice → Mastered  (1→2→3→4)
    • the direct route     Check → Mastered  (1→4, when she aces the check)
    • the re-learn loopback  Practice ↺ Lesson  (between 3 and 2, on a practice miss)
  The nodes and edges she has actually travelled illuminate; the rest stay faint.
  Same layout for every module. Child-layer only — no pace, percentage, or grade.

  Props:
    stage     'check' | 'lesson' | 'practice' | 'mastered' — her current stage.
    reteach   true while she is re-learning after a practice miss (lights the loopback).
    viaCheck  true when she reached Mastered straight from the check (lights the direct route).
--}}
@php
    $order = ['check', 'lesson', 'practice', 'mastered'];
    $labels = ['Check', 'Lesson', 'Practice', 'Mastered'];
    $cur = array_search($stage, $order, true);
    $cur = $cur === false ? 0 : $cur;
    $mastered = $stage === 'mastered';
    $shortcut = $mastered && $viaCheck;

    // Node state per index: 'done' | 'now' | 'dim'.
    $node = [];
    foreach ($order as $i => $k) {
        if ($shortcut) {
            $node[$i] = $i === 0 ? 'done' : ($i === 3 ? 'now' : 'dim');
        } elseif (! $reteach && $i === $cur) {
            $node[$i] = 'now';
        } elseif ($i < $cur) {
            $node[$i] = 'done';
        } else {
            $node[$i] = $reteach && $i === $cur ? 'now' : 'dim';
        }
    }

    // Which edges have been travelled (and so illuminate).
    $e12 = $cur >= 1 && ! $shortcut;
    $e23 = $cur >= 2 && ! $shortcut;
    $e34 = $mastered && ! $viaCheck;
    $eDirect = $shortcut;
    $eLoop = $reteach;

    // Node y positions (share the SVG viewBox's 0–100 space and the HTML tops).
    $ys = [12, 42, 66, 90];

    // Turtle: ride beside the current node, on the loopback while re-learning,
    // beside Mastered on the direct route — never on top of a node's number.
    if ($reteach) {
        $tokenTop = 54;
        $tokenLeft = 'calc(50% - 46px)';
    } elseif ($shortcut) {
        $tokenTop = $ys[3];
        $tokenLeft = 'calc(50% + 46px)';
    } else {
        $tokenTop = $ys[$cur];
        $tokenLeft = 'calc(50% + 46px)';
    }
@endphp

<aside class="lr" data-stage="{{ $stage }}" @if ($reteach) data-reteach="1" @endif aria-label="Learning loop — you are at: {{ $labels[$cur] }}">
    <style>
        .lr {
            position: fixed; left: 16px; top: 50%; transform: translateY(-50%);
            width: 20vw; min-width: 128px; max-width: 264px;
            height: 46vh; min-height: 344px; z-index: 30; padding: 12px 0; pointer-events: none;
            background: linear-gradient(180deg, rgba(8,24,40,.85), rgba(6,18,30,.85));
            border: 1px solid rgba(120,180,220,.18); border-radius: 18px;
            box-shadow: 0 14px 36px rgba(0,0,0,.42);
            backdrop-filter: blur(3px); font-family: 'Nunito', sans-serif;
        }
        /* Reserve a left gutter on the module screens so their content never sits
           under the rail (LL-08). Kept here so the rail owns its own spacing. */
        @media (min-width: 641px) {
            .me-wrap, .lw-wrap, .tw-wrap, .pw-wrap { padding-left: calc(20vw + 56px) !important; }
            .rw-wrap { margin-left: calc(20vw + 56px) !important; margin-right: auto !important; }
        }
        .lr-map { position: absolute; inset: 0; width: 100%; height: 100%; }
        .lr-edge { fill: none; stroke: #33546c; stroke-width: 2.4; stroke-dasharray: 4 5; vector-effect: non-scaling-stroke; }
        .lr-edge.lit { stroke-dasharray: none; stroke-width: 3.4; }
        .lr-edge.lit.main { stroke: #57d6a0; filter: drop-shadow(0 0 3px rgba(87,214,160,.6)); }
        .lr-edge.lit.direct { stroke: #f6b71e; filter: drop-shadow(0 0 3px rgba(246,183,30,.6)); }
        .lr-edge.lit.loop { stroke: #c084fc; filter: drop-shadow(0 0 3px rgba(192,132,252,.6)); }
        .lr-sq {
            position: absolute; left: 50%; transform: translate(-50%,-50%);
            width: 82px; min-height: 44px; padding: 6px 4px; border-radius: 13px;
            display: flex; flex-direction: column; align-items: center; gap: 2px;
            background: rgba(10,26,42,.9); border: 1.5px solid #3a6a86; color: #6f93ad;
            transition: border-color .35s, background .35s, box-shadow .35s, color .35s; z-index: 2;
        }
        .lr-sq.is-done { background: rgba(87,214,160,.16); border-color: #57d6a0; color: #57d6a0; }
        .lr-sq.is-now { background: rgba(246,183,30,.16); border-color: #f6b71e; color: #f6b71e; box-shadow: 0 0 0 5px rgba(246,183,30,.16); }
        .lr-num { font-family: 'Fredoka', 'Nunito', sans-serif; font-weight: 600; font-size: 1.05rem; line-height: 1; }
        .lr-lbl { font-size: .72rem; font-weight: 800; letter-spacing: .02em; }
        .lr-token {
            position: absolute; transform: translate(-50%,-50%);
            font-size: 1.5rem; z-index: 4; transition: top .5s cubic-bezier(.5,1.3,.4,1), left .5s;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,.5));
        }
        @media (max-width: 640px) {
            .lr { width: 24vw; min-width: 68px; max-width: 104px; height: 42vh; left: 6px; }
            .lr-sq { width: 54px; }
            .lr-lbl { font-size: .56rem; }
            .lr-token { font-size: 1.2rem; }
            .me-wrap, .lw-wrap, .tw-wrap, .pw-wrap, .rw-wrap { padding-left: 0 !important; margin-left: auto !important; }
        }
        @media (prefers-reduced-motion: reduce) { .lr-token { transition: none; } }
    </style>

    {{-- Routes (drawn behind the nodes). Base edges are faint; travelled edges light up. --}}
    <svg class="lr-map" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
        {{-- direct route 1 → 4 (bows right) --}}
        <path class="lr-edge" d="M50 12 C 84 34, 84 68, 50 90" />
        {{-- re-learn loopback 3 → 2 (bows left) --}}
        <path class="lr-edge" d="M50 66 C 18 60, 18 48, 50 42" />
        {{-- main route segments --}}
        <path class="lr-edge" d="M50 12 L50 42" />
        <path class="lr-edge" d="M50 42 L50 66" />
        <path class="lr-edge" d="M50 66 L50 90" />

        @if ($eDirect)<path class="lr-edge lit direct" d="M50 12 C 84 34, 84 68, 50 90" />@endif
        @if ($eLoop)<path class="lr-edge lit loop" d="M50 66 C 18 60, 18 48, 50 42" />@endif
        @if ($e12)<path class="lr-edge lit main" d="M50 12 L50 42" />@endif
        @if ($e23)<path class="lr-edge lit main" d="M50 42 L50 66" />@endif
        @if ($e34)<path class="lr-edge lit main" d="M50 66 L50 90" />@endif
    </svg>

    @foreach ($order as $i => $k)
        <div class="lr-sq {{ $node[$i] === 'done' ? 'is-done' : '' }} {{ $node[$i] === 'now' ? 'is-now' : '' }}" style="top: {{ $ys[$i] }}%">
            <span class="lr-num">{{ $node[$i] === 'done' ? '✓' : ($k === 'mastered' ? '⭐' : $i + 1) }}</span>
            <span class="lr-lbl">{{ $labels[$i] }}</span>
        </div>
    @endforeach

    <div class="lr-token" style="top: {{ $tokenTop }}%; left: {{ $tokenLeft }}">{{ $mastered ? '🏁' : '🐢' }}</div>
</aside>
