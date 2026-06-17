<div>
<style>
    .pw-wrap {
        min-height: 100vh;
        display: flex; flex-direction: column; align-items: center;
        padding: 32px 20px 48px;
    }
    .pw-topic {
        font-family: 'Fredoka One', cursive; font-size: 20px;
        color: #f3e8ff; text-align: center; margin-bottom: 8px;
    }
    .pw-rung {
        font-size: 12px; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: rgba(196,181,253,0.7);
        margin-bottom: 26px;
    }
    .pw-card {
        background: #1a0d30; border: 1.5px solid rgba(147,51,234,0.35);
        border-radius: 24px; padding: 36px 30px;
        width: 100%; max-width: 600px;
        animation: pwFade 0.4s ease both;
    }
    @keyframes pwFade {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .pw-prompt {
        font-family: 'Fredoka One', cursive; font-size: 23px;
        line-height: 1.45; margin-bottom: 26px; text-align: center;
        color: #f3e8ff;
    }
    .pw-options { display: flex; flex-direction: column; gap: 14px; }
    .pw-option {
        background: rgba(255,255,255,0.05);
        border: 2px solid rgba(147,51,234,0.3);
        border-radius: 16px; padding: 18px 22px;
        font-size: 17px; font-weight: 600; color: #f3e8ff;
        cursor: pointer; text-align: left; width: 100%;
        font-family: 'Nunito', sans-serif;
        transition: border-color 0.15s, background 0.15s, transform 0.1s, box-shadow 0.15s;
    }
    .pw-option:hover { border-color: rgba(192,132,252,0.8); background: rgba(147,51,234,0.14); }
    .pw-option:active { transform: scale(0.985); box-shadow: 0 0 20px rgba(244,114,182,0.4); border-color: #f472b6; }
    .pw-option:focus-visible { outline: 3px solid #c084fc; outline-offset: 2px; }
    .pw-empty {
        font-family: 'Fredoka One', cursive; font-size: 20px;
        color: #c084fc; text-align: center;
    }
    @media (prefers-reduced-motion: reduce) {
        .pw-card { transition: none; animation: none; }
    }
</style>

<div class="pw-wrap">
    <p class="pw-topic">{{ $topic }}</p>
    <p class="pw-rung">Level {{ $currentRung }} of 3</p>

    @if ($question === null)
        <div class="pw-card">
            <p class="pw-empty">More practice for this one is coming soon! 🌱</p>
        </div>
    @else
        <div class="pw-card" wire:key="q-{{ $question['id'] }}">
            <p class="pw-prompt">{{ $question['prompt'] }}</p>
            <div class="pw-options">
                @foreach ($question['options'] as $index => $optionText)
                    <button type="button" class="pw-option"
                        wire:click="choose({{ $index }})"
                        wire:loading.attr="disabled">{{ $optionText }}</button>
                @endforeach
            </div>
        </div>
    @endif
</div>
</div>