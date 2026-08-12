<div>
<style>
    .me-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 32px 20px 48px; }
    .me-topic { font-family: 'Fredoka One', cursive; font-size: 26px; color: #e6f2fb; text-align: center; margin-bottom: 26px; }
    .me-card { background: #0c2440; border: 1.5px solid rgba(34,211,238,0.35); border-radius: 24px; padding: 36px 30px; width: 100%; max-width: 600px; animation: meFade 0.4s ease both; }
    @keyframes meFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .me-head { font-family: 'Fredoka One', cursive; font-size: 20px; color: #67e8f9; margin: 0 0 16px; }
    .me-lead { font-size: 17px; line-height: 1.7; color: rgba(243,232,255,0.92); margin-bottom: 20px; }
    .me-steps { list-style: none; padding: 0; margin: 0 0 30px; display: flex; flex-direction: column; gap: 12px; }
    .me-step { display: flex; gap: 12px; align-items: flex-start; font-size: 16px; line-height: 1.5; color: rgba(243,232,255,0.92); }
    .me-step b { color: #f0abfc; }
    .me-dot { flex: 0 0 auto; width: 26px; height: 26px; border-radius: 999px; background: rgba(34,211,238,0.18); border: 1.5px solid rgba(34,211,238,0.5); color: #67e8f9; font-weight: 700; font-size: 14px; display: flex; align-items: center; justify-content: center; }
    .me-start { display: block; margin: 0 auto; background: linear-gradient(135deg, #0e7490, #f6b71e); border: none; border-radius: 999px; padding: 16px 38px; color: white; font-family: 'Fredoka One', cursive; font-size: 17px; cursor: pointer; text-align: center; max-width: 300px; text-decoration: none; }
    .me-count { font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 700; color: rgba(196,181,253,0.7); }
    .me-options { display: flex; flex-direction: column; gap: 12px; margin-top: 8px; }
    .me-option { text-align: left; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(34,211,238,0.3); border-radius: 14px; padding: 14px 18px; color: #e6f2fb; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.15s, border-color 0.15s; }
    .me-option:hover { background: rgba(34,211,238,0.12); border-color: rgba(34,211,238,0.6); }
    @media (prefers-reduced-motion: reduce) { .me-card { animation: none; } }
</style>

<div class="me-wrap">
    <p class="me-topic">{{ $topic }}</p>

    <div class="me-card">
        @if ($phase === 'explainer')
            <p class="me-head">How this level works</p>
            <p class="me-lead">
                Every level is the same little adventure. Here's the plan, so you always
                know what's coming:
            </p>
            <ul class="me-steps">
                <li class="me-step"><span class="me-dot">1</span><span>First, a <b>quick check</b> — three questions, one easy, one medium, one tricky. Ace all three and you've <b>already mastered it</b> — no lesson needed!</span></li>
                <li class="me-step"><span class="me-dot">2</span><span>If not, that's totally fine. You pick how to learn it: a <b>lesson</b>, some <b>worked examples</b>, or jump into <b>practice</b>.</span></li>
                <li class="me-step"><span class="me-dot">3</span><span>In practice you climb from easy to tricky. Every question gives you a <b>second try</b>, and nothing is ever "wrong" — just <b>not yet</b>.</span></li>
                <li class="me-step"><span class="me-dot">4</span><span>Get three tricky ones right in a row and the level is <b>yours</b>. 🎉</span></li>
            </ul>
            <button type="button" class="me-start" wire:click="beginCheck">Start the quick check →</button>

        @elseif ($phase === 'check')
            @php $current = $checkQuestions[$checkIndex] ?? null; @endphp
            <p class="me-head">Quick check <span class="me-count">{{ $checkIndex + 1 }} of {{ count($checkQuestions) }}</span></p>
            @if ($current)
                <p class="me-lead">{{ $current['prompt'] }}</p>
                <div class="me-options">
                    @foreach ($current['options'] as $i => $option)
                        <button type="button" class="me-option" wire:click="answerCheck({{ $i }})" wire:key="opt-{{ $checkIndex }}-{{ $i }}">{{ $option }}</button>
                    @endforeach
                </div>
            @else
                <p class="me-lead">No questions are ready for this level yet — jump into the lesson to get started.</p>
            @endif

        @else
            @if ($mastered)
                <p class="me-head">You tested out! 🎉</p>
                <p class="me-lead">You nailed the easy, medium and tricky one first try — this level is <b>mastered</b>. No lesson needed.</p>
                <a href="{{ route('student.voyage') }}" class="me-start">Back to my voyage →</a>
            @else
                <p class="me-head">Good try — let's learn it together</p>
                <p class="me-lead">Not quite a test-out this time, and that's completely fine. Pick how you'd like to learn it. <em>(Choosing comes next — LL-21.)</em></p>
            @endif
        @endif
    </div>
</div>
</div>
