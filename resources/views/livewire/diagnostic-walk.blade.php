<div>
<style>
    .dw-wrap {
        min-height: 100vh;
        display: flex; flex-direction: column; align-items: center;
        padding: 32px 20px 48px;
    }

    /* ── ISLAND BANNER ── */
    .dw-island {
        display: inline-flex; flex-direction: column; align-items: center;
        gap: 2px; margin-bottom: 22px;
    }
    .dw-island-name {
        font-family: 'Fredoka One', cursive; font-size: 20px;
        display: inline-flex; align-items: center; gap: 8px;
        color: #f3e8ff;
    }
    .dw-island-strand {
        font-size: 12px; font-weight: 700; letter-spacing: 0.08em;
        text-transform: uppercase; color: rgba(196,181,253,0.7);
    }

    /* ── VOYAGE TRAIL ── */
    .dw-trail {
        position: relative; width: 100%; max-width: 460px;
        height: 40px; margin-bottom: 32px;
    }
    .dw-trail-line {
        position: absolute; top: 50%; left: 0; right: 0; height: 3px;
        transform: translateY(-50%);
        background: repeating-linear-gradient(
            to right,
            rgba(147,51,234,0.4) 0, rgba(147,51,234,0.4) 6px,
            transparent 6px, transparent 12px
        );
    }
    .dw-trail-fill {
        position: absolute; top: 50%; left: 0; height: 3px;
        transform: translateY(-50%);
        background: linear-gradient(90deg, #c084fc, #f472b6);
        border-radius: 999px;
        transition: width 0.5s ease;
    }
    .dw-boat {
        position: absolute; top: 50%; font-size: 24px;
        transform: translate(-50%, -55%);
        transition: left 0.5s ease;
        filter: drop-shadow(0 0 8px rgba(244,114,182,0.6));
    }

    /* ── QUESTION CARD ── */
    .dw-card {
        background: #1a0d30; border: 1.5px solid rgba(147,51,234,0.35);
        border-radius: 24px; padding: 36px 30px;
        width: 100%; max-width: 600px;
        animation: dwFade 0.4s ease both;
    }
    @keyframes dwFade {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .dw-prompt {
        font-family: 'Fredoka One', cursive; font-size: 23px;
        line-height: 1.45; margin-bottom: 26px; text-align: center;
        color: #f3e8ff;
    }
    .dw-options { display: flex; flex-direction: column; gap: 14px; }
    .dw-option {
        background: rgba(255,255,255,0.05);
        border: 2px solid rgba(147,51,234,0.3);
        border-radius: 16px; padding: 18px 22px;
        font-size: 17px; font-weight: 600; color: #f3e8ff;
        cursor: pointer; text-align: left; width: 100%;
        font-family: 'Nunito', sans-serif;
        transition: border-color 0.15s, background 0.15s, transform 0.1s, box-shadow 0.15s;
    }
    .dw-option:hover {
        border-color: rgba(192,132,252,0.8);
        background: rgba(147,51,234,0.14);
    }
    .dw-option:active {
        transform: scale(0.985);
        box-shadow: 0 0 20px rgba(244,114,182,0.4);
        border-color: #f472b6;
    }
    .dw-option:focus-visible {
        outline: 3px solid #c084fc; outline-offset: 2px;
    }

    /* ── INTERSTITIAL + COMPLETION ── */
    .dw-cheer {
        font-family: 'Fredoka One', cursive; font-size: 22px;
        text-align: center; color: #f3e8ff; margin-bottom: 24px;
    }
    .dw-done {
        font-family: 'Fredoka One', cursive; font-size: 24px;
        text-align: center; color: #c084fc;
    }
    .dw-continue {
        display: block; margin: 0 auto;
        background: linear-gradient(135deg, #9333ea, #db2777);
        border: none; border-radius: 999px; padding: 14px 34px;
        color: white; font-family: 'Fredoka One', cursive; font-size: 16px;
        cursor: pointer;
    }

    @media (prefers-reduced-motion: reduce) {
        .dw-trail-fill, .dw-boat, .dw-card { transition: none; animation: none; }
    }
</style>

<div class="dw-wrap">
    @if ($showInterstitial && $question !== null)
        <div class="dw-card">
            <p class="dw-cheer">You're doing brilliantly — keep going! 🌟</p>
            <button type="button" class="dw-continue"
                wire:click="continueFromInterstitial">Next question →</button>
        </div>

    @elseif ($question === null && $awaitingGuardian)
    {{-- Completion while a guardian decision is pending. The component issues a
         hard redirect to the waiting page; this renders only as a fallback if
         that redirect did not fire, and links there manually. [RR-11] --}}
    <div class="dw-card">
        <p class="dw-done">All done! 🎉</p>
        <p style="text-align:center; color:rgba(196,181,253,0.8); font-size:15px; line-height:1.6; margin:14px 0 24px;">
            Amazing work exploring every island. Ask your grown-up to finish
            setting up your map — they've got one quick thing to check.
        </p>
        <a href="{{ route('student.awaiting-guardian') }}" class="dw-continue" style="text-decoration:none; text-align:center;">
            Continue →
        </a>
    </div>

    @elseif ($question === null)
    <div class="dw-card">
        <p class="dw-done">You've completed the diagnostic! 🎉</p>
        <p style="text-align:center; color:rgba(196,181,253,0.8); font-size:15px; line-height:1.6; margin:14px 0 24px;">
            Your map is ready — let's see everything you explored.
        </p>
        <a href="{{ route('student.map') }}" class="dw-continue" style="text-decoration:none; text-align:center;">
            See your map →
        </a>
    </div>

    @else
        {{-- Island banner --}}
        <div class="dw-island">
            <span class="dw-island-name">{{ $islandIcon }} {{ $islandName }}</span>
            <span class="dw-island-strand">{{ $strand }}</span>
        </div>

        {{-- Voyage trail --}}
        @php
            $pct = $planTotal > 0 ? max(0, min(100, (($itemNumber - 1) / $planTotal) * 100)) : 0;
        @endphp
        <div class="dw-trail" aria-hidden="true">
            <div class="dw-trail-line"></div>
            <div class="dw-trail-fill" style="width: {{ $pct }}%;"></div>
            <div class="dw-boat" style="left: {{ $pct }}%;">⛵</div>
        </div>

        {{-- Question card --}}
        <div class="dw-card" wire:key="item-{{ $itemNumber }}">
            <p class="dw-prompt">{{ $prompt }}</p>
            <div class="dw-options">
                @foreach ($options as $index => $optionText)
                    <button
                        type="button"
                        class="dw-option"
                        wire:click="choose({{ $index }})"
                        wire:loading.attr="disabled"
                    >{{ $optionText }}</button>
                @endforeach
            </div>
        </div>
    @endif
</div>
</div>