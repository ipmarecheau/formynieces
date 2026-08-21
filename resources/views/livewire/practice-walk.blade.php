<div>
<livewire:smooth-guide guide="practice" wire:key="guide-practice" />
<x-loop-rail :stage="$isMastered ? 'mastered' : 'practice'" />
<style>
    .pw-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 32px 20px 48px; }
    .pw-topic { font-family: 'Fredoka One', cursive; font-size: 20px; color: #e6f2fb; text-align: center; margin-bottom: 8px; }
    .pw-rung { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(196,181,253,0.7); margin-bottom: 14px; }
    .pw-ladder { display: flex; gap: 10px; margin-bottom: 14px; }
    .pw-rung-pip { width: 46px; height: 8px; border-radius: 999px; background: rgba(34,211,238,0.25); transition: background 0.4s ease; }
    .pw-rung-pip.done { background: linear-gradient(90deg,#67e8f9,#fcd34d); }
    .pw-rung-pip.active { background: rgba(192,132,252,0.55); }
    .pw-streak { display: flex; gap: 7px; margin-bottom: 26px; }
    .pw-dot { width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.18); }
    .pw-dot.filled { background: #fcd34d; box-shadow: 0 0 10px rgba(244,114,182,0.6); }
    .pw-card { background: #0c2440; border: 1.5px solid rgba(34,211,238,0.35); border-radius: 24px; padding: 36px 30px; width: 100%; max-width: 600px; animation: pwFade 0.4s ease both; }
    @keyframes pwFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .pw-prompt { font-family: 'Fredoka One', cursive; font-size: 23px; line-height: 1.45; margin-bottom: 26px; text-align: center; color: #e6f2fb; }
    .pw-prompt p { margin: 0 0 10px; }
    /* Imported questions carry figures + worked-solution HTML. */
    .pw-prompt img, .pw-explanation img { max-width: 100%; height: auto; display: block; margin: 12px auto; border-radius: 12px; background: #fff; padding: 6px; }
    .pw-explanation ol, .pw-explanation ul { text-align: left; display: inline-block; margin: 8px auto; padding-left: 22px; }
    .pw-explanation p { margin: 0 0 8px; }
    .pw-options { display: flex; flex-direction: column; gap: 14px; }
    .pw-option { background: rgba(255,255,255,0.05); border: 2px solid rgba(34,211,238,0.3); border-radius: 16px; padding: 18px 22px; font-size: 17px; font-weight: 600; color: #e6f2fb; cursor: pointer; text-align: left; width: 100%; font-family: 'Nunito', sans-serif; transition: border-color 0.15s, background 0.15s, transform 0.1s, box-shadow 0.15s; }
    .pw-option:hover { border-color: rgba(192,132,252,0.8); background: rgba(34,211,238,0.14); }
    .pw-option:active { transform: scale(0.985); box-shadow: 0 0 20px rgba(244,114,182,0.4); border-color: #fcd34d; }
    .pw-option:focus-visible { outline: 3px solid #67e8f9; outline-offset: 2px; }
    .pw-feedback-head { font-family: 'Fredoka One', cursive; font-size: 22px; text-align: center; margin-bottom: 16px; }
    .pw-feedback-head.good { color: #67e8f9; }
    .pw-feedback-head.notyet { color: #f0abfc; }
    .pw-explanation { font-size: 16px; line-height: 1.65; color: rgba(243,232,255,0.92); text-align: center; margin-bottom: 26px; }
    .pw-misconception { margin-bottom: 26px; }
    .pw-misconception-label { font-size: 16px; color: #f0abfc; text-align: center; margin: 0 0 10px; }
    .pw-misconception .pw-explanation { margin-bottom: 0; }
    .pw-next { display: block; margin: 0 auto; background: linear-gradient(135deg, #0e7490, #f6b71e); border: none; border-radius: 999px; padding: 14px 34px; color: white; font-family: 'Fredoka One', cursive; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; }
    .pw-empty { font-family: 'Fredoka One', cursive; font-size: 20px; color: #67e8f9; text-align: center; }
    .pw-master-head { font-family: 'Fredoka One', cursive; font-size: 28px; color: #fcd34d; text-align: center; margin-bottom: 10px; }
    .pw-master-sub { font-size: 16px; line-height: 1.6; color: rgba(243,232,255,0.9); text-align: center; margin-bottom: 26px; }
    .pw-tries { font-size: 13px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: rgba(196,181,253,0.7); text-align: center; margin: -12px 0 20px; }
    .pw-retry-hint { font-size: 14px; line-height: 1.55; color: rgba(253,230,138,0.92); text-align: center; margin: 8px auto 20px; max-width: 40ch; }
    .pw-splash { text-align: center; }
    .pw-splash-img { width: 116px; height: 116px; object-fit: contain; margin: 0 auto 16px; display: block; animation: pwBob 2.2s ease-in-out infinite; }
    @keyframes pwBob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-9px); } }
    .pw-splash-head { font-family: 'Fredoka One', cursive; font-size: 25px; color: #fcd34d; margin-bottom: 14px; line-height: 1.25; }
    .pw-splash-body { font-size: 16px; line-height: 1.7; color: rgba(243,232,255,0.92); margin: 0 auto 26px; max-width: 42ch; }
    @media (prefers-reduced-motion: reduce) { .pw-card { transition: none; animation: none; } .pw-splash-img { animation: none; } }
</style>

<div class="pw-wrap">
    <p class="pw-topic">{{ $topic }}</p>
    <p class="pw-rung">{{ $isMastered ? 'Mastered!' : 'Level ' . $rungOrdinal . ' of 3' }}</p>

    <div class="pw-ladder" aria-label="{{ $isMastered ? 'Mastered' : 'Level ' . $rungOrdinal . ' of 3' }}">
        @for ($r = 1; $r <= 3; $r++)
            <div class="pw-rung-pip {{ ($isMastered || $r < $rungOrdinal) ? 'done' : ($r === $rungOrdinal ? 'active' : '') }}"></div>
        @endfor
    </div>

    @if (! $isMastered)
        <div class="pw-streak" aria-label="{{ $currentStreak }} in a row">
            @for ($d = 0; $d < 3; $d++)
                <div class="pw-dot {{ $d < $currentStreak ? 'filled' : '' }}"></div>
            @endfor
        </div>
    @endif

    @if ($reteachSplash)
        <div class="pw-card pw-splash">
            <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle" class="pw-splash-img">
            <p class="pw-splash-head">Let's take a little detour together!</p>
            <p class="pw-splash-body">A couple of these were tricky — no worries at all. We'll revisit the lesson together, with Smooth right beside you, so it really clicks. This isn't a test. 🐢</p>
            <button type="button" class="pw-next" wire:click="enterReteach">Let's revisit it →</button>
        </div>

    @elseif ($celebration)
        <x-celebration :title="$celebration['title']" :sub="$celebration['sub']">
            @if (in_array($celebration['type'], ['mastery', 'weekcomplete'], true))
                <a href="{{ route('student.voyage') }}">Back to my voyage →</a>
            @else
                <button type="button" wire:click="continueAfterCelebration">Keep climbing! →</button>
            @endif
        </x-celebration>

    @elseif ($isMastered)
        <div class="pw-card">
            <p class="pw-master-head">You mastered this! 🎉</p>
            <p class="pw-master-sub">You climbed all three levels of {{ $topic }}. Brilliant work, explorer!</p>
            <a href="{{ route('student.voyage') }}" class="pw-next">Back to my voyage →</a>
        </div>

    @elseif ($question === null)
        <div class="pw-card"><p class="pw-empty">More practice for this one is coming soon! 🌱</p></div>

    @elseif ($feedback !== null)
        <div class="pw-card">
            @if ($feedback['correct'])
                <p class="pw-feedback-head good">Nice work! ⭐</p>
            @else
                <p class="pw-feedback-head notyet">Not yet — here's the idea 🌱</p>
            @endif
            @if (!empty($feedback['misconception']))
                {{-- LL-09: a correction targeted to the mistake she actually made, framed as not-yet. --}}
                <div class="pw-misconception">
                    <p class="pw-misconception-label">{{ $feedback['misconception'] }}</p>
                    <div class="pw-explanation">{!! $feedback['worked_example'] !!}</div>
                </div>
            @else
                <div class="pw-explanation">{!! $feedback['explanation'] !!}</div>
            @endif
            <button type="button" class="pw-next" wire:click="next">Next →</button>
        </div>

    @else
        <div class="pw-card" wire:key="q-{{ $question['id'] }}">
            <div class="pw-prompt">{!! $question['prompt'] !!}</div>
            @if ($awaitingRetry)
                <p class="pw-feedback-head notyet">Not yet — one more go! 🌱</p>
                <p class="pw-retry-hint">Try 2 of 2 · if this one stays tricky, Smooth will pop in to help you relearn it 🐢</p>
            @else
                <p class="pw-tries">Try 1 of 2</p>
            @endif
            <div class="pw-options">
                @foreach ($question['options'] as $index => $optionText)
                    <button type="button" class="pw-option" wire:click="choose({{ $index }})" wire:loading.attr="disabled"
                        @if ($tourMode) data-tour-option data-correct="{{ (int) ($index === $question['correct_index']) }}" @endif>{{ $optionText }}</button>
                @endforeach
            </div>
        </div>
    @endif
</div>

<livewire:loop-coach leg="practice" wire:key="loop-coach-practice" />
</div>