<div>
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
    .pw-next { display: block; margin: 0 auto; background: linear-gradient(135deg, #0e7490, #f6b71e); border: none; border-radius: 999px; padding: 14px 34px; color: white; font-family: 'Fredoka One', cursive; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; }
    .pw-empty { font-family: 'Fredoka One', cursive; font-size: 20px; color: #67e8f9; text-align: center; }
    .pw-master-head { font-family: 'Fredoka One', cursive; font-size: 28px; color: #fcd34d; text-align: center; margin-bottom: 10px; }
    .pw-master-sub { font-size: 16px; line-height: 1.6; color: rgba(243,232,255,0.9); text-align: center; margin-bottom: 26px; }
    @media (prefers-reduced-motion: reduce) { .pw-card { transition: none; animation: none; } }
</style>

<div class="pw-wrap">
    <p class="pw-topic">{{ $topic }}</p>
    <p class="pw-rung">{{ $isMastered ? 'Mastered!' : 'Level ' . $currentRung . ' of 3' }}</p>

    <div class="pw-ladder" aria-label="{{ $isMastered ? 'Mastered' : 'Level ' . $currentRung . ' of 3' }}">
        @for ($r = 1; $r <= 3; $r++)
            <div class="pw-rung-pip {{ ($isMastered || $r < $currentRung) ? 'done' : ($r === $currentRung ? 'active' : '') }}"></div>
        @endfor
    </div>

    @if (! $isMastered)
        <div class="pw-streak" aria-label="{{ $currentStreak }} in a row">
            @for ($d = 0; $d < 3; $d++)
                <div class="pw-dot {{ $d < $currentStreak ? 'filled' : '' }}"></div>
            @endfor
        </div>
    @endif

    @if ($isMastered)
        <div class="pw-card">
            <p class="pw-master-head">You mastered this! 🎉</p>
            <p class="pw-master-sub">You climbed all three levels of {{ $topic }}. Brilliant work, explorer!</p>
            <a href="{{ route('student.map') }}" class="pw-next">Back to my map →</a>
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
            <div class="pw-explanation">{!! $feedback['explanation'] !!}</div>
            <button type="button" class="pw-next" wire:click="next">Next →</button>
        </div>

    @else
        <div class="pw-card" wire:key="q-{{ $question['id'] }}">
            <div class="pw-prompt">{!! $question['prompt'] !!}</div>
            <div class="pw-options">
                @foreach ($question['options'] as $index => $optionText)
                    <button type="button" class="pw-option" wire:click="choose({{ $index }})" wire:loading.attr="disabled">{{ $optionText }}</button>
                @endforeach
            </div>
        </div>
    @endif
</div>
</div>