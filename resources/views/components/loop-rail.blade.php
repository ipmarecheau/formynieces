@props(['stage' => 'check', 'reteach' => false])

{{--
  LL-08 — the learning-loop rail. A fixed board-track down the left of every module
  screen so the child always sees the four named stages of the loop, where she is,
  and the way back on a miss. Same layout for every module. Child-layer only — no
  pace, percentage, or grade. Stages: Check → Lesson → Practice → Mastered.

  Props:
    stage    'check' | 'lesson' | 'practice' | 'mastered' — her current stage.
    reteach  true while she is re-learning after a practice miss (shows the loop-back).
--}}
@php
    $steps = [
        ['k' => 'check', 'lbl' => 'Check'],
        ['k' => 'lesson', 'lbl' => 'Lesson'],
        ['k' => 'practice', 'lbl' => 'Practice'],
        ['k' => 'mastered', 'lbl' => 'Mastered'],
    ];
    $cur = array_search($stage, array_column($steps, 'k'), true);
    $cur = $cur === false ? 0 : $cur;
    $tops = [14, 40, 64, 88];
    $tokenTop = $reteach ? 52 : $tops[$cur];
@endphp

<aside class="lr" data-stage="{{ $stage }}" aria-label="Learning loop — you are at: {{ $steps[$cur]['lbl'] }}">
    <style>
        .lr {
            position: fixed; left: 16px; top: 50%; transform: translateY(-50%);
            width: 20vw; min-width: 120px; max-width: 260px;
            height: 46vh; min-height: 340px; z-index: 30; padding: 14px 0; pointer-events: none;
            background: linear-gradient(180deg, rgba(8,24,40,.82), rgba(6,18,30,.82));
            border: 1px solid rgba(120,180,220,.18); border-radius: 18px;
            box-shadow: 0 14px 36px rgba(0,0,0,.42);
            backdrop-filter: blur(3px); font-family: 'Nunito', sans-serif;
        }
        /* Reserve a left gutter on the module screens so their content never sits
           under the rail (LL-08). Kept here so the rail owns its own spacing. */
        @media (min-width: 641px) {
            .me-wrap, .lw-wrap, .tw-wrap, .pw-wrap { padding-left: calc(20vw + 52px) !important; }
            .rw-wrap { margin-left: calc(20vw + 52px) !important; margin-right: auto !important; }
        }
        .lr-track {
            position: absolute; left: 50%; top: 13%; bottom: 9%; width: 3px; transform: translateX(-50%);
            background: repeating-linear-gradient(180deg, #3a6a86 0 6px, transparent 6px 12px);
        }
        .lr-sq {
            position: absolute; left: 50%; transform: translate(-50%,-50%);
            width: 84px; min-height: 46px; padding: 7px 4px; border-radius: 13px;
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            background: rgba(255,255,255,.05); border: 1.5px solid #3a6a86; color: #6f93ad;
            transition: border-color .35s, background .35s, box-shadow .35s; z-index: 2;
        }
        .lr-sq.is-done { background: rgba(87,214,160,.16); border-color: #57d6a0; color: #57d6a0; }
        .lr-sq.is-now { background: rgba(246,183,30,.16); border-color: #f6b71e; color: #f6b71e; box-shadow: 0 0 0 5px rgba(246,183,30,.16); }
        .lr-num { font-family: 'Fredoka', 'Nunito', sans-serif; font-weight: 600; font-size: 1.05rem; line-height: 1; }
        .lr-lbl { font-size: .72rem; font-weight: 800; letter-spacing: .02em; }
        .lr-token {
            position: absolute; left: 50%; transform: translate(-50%,-50%);
            font-size: 1.55rem; z-index: 3; transition: top .5s cubic-bezier(.5,1.3,.4,1);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,.5));
        }
        .lr-chute {
            position: absolute; right: 5px; transform: translateY(-50%);
            font-size: .6rem; font-weight: 800; color: #c084fc; text-align: center; width: 40px; line-height: 1.1;
            opacity: 0; transition: opacity .3s; z-index: 3;
        }
        .lr-chute.is-on { opacity: 1; }
        @media (max-width: 640px) {
            .lr { width: 22vw; min-width: 64px; max-width: 96px; height: 42vh; left: 6px; }
            .lr-sq { width: 56px; }
            .lr-lbl { font-size: .58rem; }
        }
        @media (prefers-reduced-motion: reduce) { .lr-token { transition: none; } }
    </style>

    <div class="lr-track"></div>
    @foreach ($steps as $i => $s)
        <div class="lr-sq {{ $i < $cur ? 'is-done' : '' }} {{ $i === $cur && ! $reteach ? 'is-now' : '' }}" style="top: {{ $tops[$i] }}%">
            <span class="lr-num">{{ $i < $cur ? '✓' : ($s['k'] === 'mastered' ? '⭐' : $i + 1) }}</span>
            <span class="lr-lbl">{{ $s['lbl'] }}</span>
        </div>
    @endforeach

    <div class="lr-chute {{ $reteach ? 'is-on' : '' }}" style="top: 52%">↩ re-learn</div>
    <div class="lr-token" style="top: {{ $tokenTop }}%">{{ $stage === 'mastered' ? '🏁' : '🐢' }}</div>
</aside>
