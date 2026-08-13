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
    .me-prompt { font-size: 18px; line-height: 1.55; color: rgba(243,232,255,0.95); margin-bottom: 20px; }
    .me-prompt p { margin: 0 0 10px; }
    .me-prompt img { max-width: 100%; height: auto; display: block; margin: 12px auto; border-radius: 12px; background: #fff; padding: 6px; }
    .me-options { display: flex; flex-direction: column; gap: 12px; margin-top: 8px; }
    .me-option { text-align: left; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(34,211,238,0.3); border-radius: 14px; padding: 14px 18px; color: #e6f2fb; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.15s, border-color 0.15s; }
    .me-option:hover { background: rgba(34,211,238,0.12); border-color: rgba(34,211,238,0.6); }
    .me-smooth { display: block; width: 96px; height: 96px; object-fit: contain; margin: 0 auto 10px; filter: drop-shadow(0 8px 18px rgba(0,0,0,0.4)); animation: meBob 2.4s ease-in-out infinite; }
    @keyframes meBob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    .me-choices { display: flex; flex-direction: column; gap: 12px; margin-top: 8px; }
    .me-choice { display: flex; align-items: center; gap: 14px; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(34,211,238,0.3); border-radius: 16px; padding: 16px 18px; text-decoration: none; transition: background 0.15s, border-color 0.15s, transform 0.1s; }
    .me-choice:hover { background: rgba(34,211,238,0.12); border-color: rgba(34,211,238,0.6); transform: translateY(-2px); }
    .me-choice-emoji { font-size: 28px; flex: 0 0 auto; }
    .me-choice-text { display: flex; flex-direction: column; }
    .me-choice-text b { font-family: 'Fredoka One', cursive; font-size: 17px; color: #e6f2fb; }
    .me-choice-text small { font-size: 13px; color: rgba(196,181,253,0.75); margin-top: 2px; }
    button.me-choice { width: 100%; text-align: left; font: inherit; cursor: pointer; }
    .me-choice.is-locked { background: rgba(255,255,255,0.03); border-color: rgba(148,163,184,0.25); border-style: dashed; opacity: 0.6; }
    .me-choice.is-locked:hover { background: rgba(255,255,255,0.05); border-color: rgba(148,163,184,0.4); transform: none; }
    .me-lock-note { background: rgba(251,191,36,0.12); border: 1.5px solid rgba(251,191,36,0.45); color: #fde68a; border-radius: 14px; padding: 12px 16px; margin: 8px 0 4px; font-size: 15px; }
    .me-lock-popup { background: rgba(15,23,42,0.96); border: 1.5px solid rgba(34,211,238,0.5); border-radius: 16px; padding: 18px; margin-top: 6px; text-align: center; }
    .me-lock-popup p { color: #e6f2fb; font-size: 16px; margin: 0 0 12px; }
    .me-lock-ok { background: linear-gradient(135deg, #22d3ee, #a855f7); color: #fff; border: none; border-radius: 12px; padding: 10px 22px; font-family: 'Fredoka One', cursive; font-size: 15px; cursor: pointer; }
    [x-cloak] { display: none !important; }
    @media (prefers-reduced-motion: reduce) { .me-smooth { animation: none; } }
    @media (prefers-reduced-motion: reduce) { .me-card { animation: none; } }
</style>

@php
    $backHref = $islandSlug ? route('student.voyage.island', $islandSlug) : route('student.voyage');
@endphp
<div class="me-wrap">
    <p class="me-topic">{{ $topic }}</p>

    <div class="me-card">
        @if ($phase === 'maintained')
            <img class="me-smooth" src="{{ asset('images/voyage/companion/smooth-cheer.webp') }}" alt="Smooth the turtle">
            <p class="me-head">You've mastered this! ⭐</p>
            <p class="me-lead">You've got <b>{{ $topic }}</b> locked in. Come back in <b>{{ $daysToDue }}</b> {{ $daysToDue === 1 ? 'day' : 'days' }} to keep it sharp — no need to do anything until then.</p>
            <a href="{{ $backHref }}" class="me-start">Back to the island →</a>

        @elseif ($phase === 'maintenance_due')
            <img class="me-smooth" src="{{ asset('images/voyage/companion/smooth-cheer.webp') }}" alt="Smooth the turtle">
            <p class="me-head">Time to keep it sharp! ✨</p>
            <p class="me-lead">You mastered <b>{{ $topic }}</b> a while back. Answer three tricky ones first try to prove you've still got it — and keep your star.</p>
            <button type="button" class="me-start" wire:click="beginCheck">Start the re-check →</button>

        @elseif ($phase === 'explainer')
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
            <p class="me-head">{{ $isMaintenance ? 'Re-check' : 'Quick check' }} <span class="me-count">{{ $checkIndex + 1 }} of {{ count($checkQuestions) }}</span></p>
            @if ($current)
                <div class="me-prompt">{!! $current['prompt'] !!}</div>
                <div class="me-options">
                    @foreach ($current['options'] as $i => $option)
                        <button type="button" class="me-option" wire:click="answerCheck({{ $i }})" wire:key="opt-{{ $checkIndex }}-{{ $i }}">{{ $option }}</button>
                    @endforeach
                </div>
            @else
                <p class="me-lead">No questions are ready for this level yet — jump into the lesson to get started.</p>
            @endif

        @else
            @if (! $mastered)
                <img class="me-smooth" src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle">
                <p class="me-head">That's okay — let's learn it together!</p>
                @if ($workedExamplesLocked || $practiceLocked)
                    <p class="me-lead">Let's learn <b>{{ $topic }}</b> step by step: start with the <b>lesson</b>, then the <b>worked examples</b>, then <b>practice</b> — each one opens up the next!</p>
                @else
                    <p class="me-lead">Every explorer needs a map sometimes. Pick how you'd like to learn <b>{{ $topic }}</b> — there's no wrong way in:</p>
                @endif

                @if ($lockMessage)
                    <div class="me-lock-note">{{ $lockMessage }}</div>
                @endif

                <div class="me-choices" x-data="{ lockNote: null }">
                    <a href="{{ route('practice.lesson', $moduleId) }}" class="me-choice">
                        <span class="me-choice-emoji">📖</span>
                        <span class="me-choice-text"><b>Lesson</b><small>Learn it step by step</small></span>
                    </a>

                    @if ($workedExamplesLocked)
                        <button type="button" class="me-choice is-locked" @click="lockNote = @js($workedExamplesLockMessage)">
                            <span class="me-choice-emoji">🔒</span>
                            <span class="me-choice-text"><b>Worked examples</b><small>Finish the lesson first</small></span>
                        </button>
                    @else
                        <a href="{{ route('practice.tutorial', $moduleId) }}" class="me-choice">
                            <span class="me-choice-emoji">🧭</span>
                            <span class="me-choice-text"><b>Worked examples</b><small>Watch it done, then try</small></span>
                        </a>
                    @endif

                    @if ($practiceLocked)
                        <button type="button" class="me-choice is-locked" @click="lockNote = @js($practiceLockMessage)">
                            <span class="me-choice-emoji">🔒</span>
                            <span class="me-choice-text"><b>Practice</b><small>Finish the worked examples first</small></span>
                        </button>
                    @else
                        <a href="{{ route('practice.walk', $moduleId) }}" class="me-choice">
                            <span class="me-choice-emoji">⚡</span>
                            <span class="me-choice-text"><b>Practice</b><small>Jump straight in</small></span>
                        </a>
                    @endif

                    <div class="me-lock-popup" x-show="lockNote" x-cloak @click.outside="lockNote = null" style="display:none;">
                        <p x-text="lockNote"></p>
                        <button type="button" class="me-lock-ok" @click="lockNote = null">Got it! 🐢</button>
                    </div>
                </div>
            @endif
        @endif
    </div>

    @if ($phase === 'outcome' && $mastered)
        <x-celebration
            :title="$isMaintenance ? 'Still sharp! ⭐' : 'You tested out! 🎉'"
            :sub="$isMaintenance
                ? 'Three tricky ones, first try — you\'ve still got '.$topic.'. Your star is safe for another two weeks!'
                : 'You aced the easy, medium and tricky one first try — '.$topic.' is mastered. No lesson needed!'">
            <a href="{{ $backHref }}">Back to the island →</a>
        </x-celebration>
    @endif
</div>
</div>
