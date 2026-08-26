<div>
@if ($guidedLocked)
    @include('partials.guided-locked', ['moduleId' => $moduleId])
@else
@include('partials.guided-heartbeat')
<x-loop-rail stage="lesson" />
{{-- No how-to guide FAB here: on lesson pages the chat widget (clarify-chat) owns the bottom-right. --}}
<style>
    .lw-wrap { min-height: 100vh; padding: 28px 20px 48px; max-width: 1120px; margin: 0 auto; }
    .lw-hero { display: flex; gap: 20px; align-items: center; background: linear-gradient(135deg, rgba(246,183,30,0.18), rgba(192,132,252,0.12)); border: 1.5px solid rgba(246,183,30,0.42); border-radius: 22px; padding: 20px 24px; margin-bottom: 24px; }
    .lw-hero-img { width: 96px; height: 96px; object-fit: contain; flex-shrink: 0; animation: lwBob 2.4s ease-in-out infinite; }
    .lw-hero-tag { font-family: 'Fredoka One', cursive; font-size: 13px; text-transform: uppercase; letter-spacing: 0.08em; color: #f6b71e; margin: 0 0 6px; }
    .lw-hero-head { font-family: 'Fredoka One', cursive; font-size: 23px; line-height: 1.3; color: #fde68a; margin: 0 0 8px; }
    .lw-hero-sub { font-size: 15.5px; line-height: 1.6; color: rgba(243,232,255,0.92); margin: 0; }
    .lw-hero-sub strong { color: #67e8f9; }
    .lw-modal-backdrop { position: fixed; inset: 0; z-index: 90; background: rgba(4,14,30,0.72); display: flex; align-items: center; justify-content: center; padding: 20px; }
    .lw-modal { background: #0c2440; border: 2px solid rgba(246,183,30,0.5); border-radius: 24px; padding: 32px 28px; max-width: 440px; text-align: center; animation: lwFade 0.35s ease both; box-shadow: 0 16px 50px rgba(0,0,0,0.5); }
    .lw-modal-img { width: 112px; height: 112px; object-fit: contain; margin: 0 auto 14px; display: block; }
    .lw-modal-head { font-family: 'Fredoka One', cursive; font-size: 26px; color: #fcd34d; margin: 0 0 12px; }
    .lw-modal-sub { font-size: 16.5px; line-height: 1.6; color: rgba(243,232,255,0.92); margin: 0 0 24px; }
    .lw-handoff { text-align: center; padding: 12px 0; animation: lwFade 0.35s ease both; }
    .lw-handoff-img { width: 108px; height: 108px; object-fit: contain; margin: 0 auto 14px; display: block; animation: lwBob 2.2s ease-in-out infinite; }
    .lw-handoff-head { font-family: 'Fredoka One', cursive; font-size: 23px; color: #f0abfc; margin: 0 0 12px; line-height: 1.25; }
    .lw-handoff-sub { font-size: 16.5px; line-height: 1.65; color: rgba(243,232,255,0.92); margin: 0 auto 24px; max-width: 42ch; }
    @media (max-width: 560px) { .lw-hero { flex-direction: column; text-align: center; padding: 22px 18px; } }
    .lw-subject { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(196,181,253,0.7); margin-bottom: 6px; }
    .lw-topic { font-family: 'Fredoka One', cursive; font-size: 26px; color: #e6f2fb; margin-bottom: 22px; }
    .lw-card { background: #0c2440; border: 1.5px solid rgba(34,211,238,0.35); border-radius: 24px; padding: 30px; animation: lwFade 0.4s ease both; }
    @keyframes lwFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    .lw-progress { height: 8px; border-radius: 999px; background: rgba(255,255,255,0.08); overflow: hidden; margin-bottom: 22px; }
    .lw-progress span { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg,#0e7490,#67e8f9,#f6b71e); transition: width 0.4s ease; }
    .lw-title { font-family: 'Fredoka One', cursive; font-size: 25px; color: #67e8f9; margin: 0 0 20px; line-height: 1.3; }
    .lw-no-lesson { font-size: 18px; line-height: 1.7; color: rgba(196,181,253,0.9); margin-bottom: 18px; }
    .lw-block { animation: lwFade 0.35s ease both; }
    .lw-para { font-size: 19px; line-height: 1.9; letter-spacing: 0.01em; color: #eaf3ff; margin: 0 0 18px; max-width: 62ch; }
    .lw-block-head { font-family: 'Fredoka One', cursive; font-size: 19px; color: #f0abfc; margin: 8px 0 12px; }
    .lw-example { background: rgba(34,211,238,0.06); border: 1.5px solid rgba(34,211,238,0.3); border-radius: 16px; padding: 18px 20px; margin: 0 0 18px; }
    .lw-example-tag, .lw-check-tag, .lw-key-tag { display: inline-block; font-family: 'Fredoka One', cursive; font-size: 13px; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 10px; }
    .lw-example-tag { color: #67e8f9; }
    .lw-steps { margin: 10px 0 0 22px; display: flex; flex-direction: column; gap: 9px; color: #eaf3ff; font-size: 18px; line-height: 1.65; }
    .lw-key { background: rgba(246,183,30,0.12); border-left: 4px solid #f6b71e; border-radius: 10px; padding: 14px 18px; margin: 0 0 18px; font-size: 18px; color: #fde68a; line-height: 1.75; }
    .lw-key-tag { color: #f6b71e; display: block; }
    .lw-visual { max-width: 100%; border-radius: 14px; margin: 0 0 18px; background: #fff; padding: 6px; }
    .lw-checkq { background: rgba(134,239,172,0.08); border: 1.5px solid rgba(134,239,172,0.35); border-radius: 16px; padding: 18px 20px; margin: 0 0 18px; }
    .lw-check-tag { color: #86efac; display: block; }
    .lw-checkq-text { font-size: 19px; line-height: 1.6; color: #eaf3ff; margin: 0 0 14px; font-weight: 700; }
    .lw-opts { display: flex; flex-direction: column; gap: 10px; }
    .lw-opt { text-align: left; background: rgba(255,255,255,0.06); border: 2px solid rgba(134,239,172,0.35); border-radius: 12px; padding: 13px 16px; color: #eaf3ff; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.15s; }
    .lw-opt:hover:not(:disabled) { background: rgba(134,239,172,0.14); }
    .lw-opt.is-right { background: rgba(52,211,153,0.25); border-color: #34d399; color: #d1fae5; }
    .lw-opt:disabled { cursor: default; }
    .lw-feedback { margin: 14px 0 0; font-size: 17px; font-weight: 700; }
    .lw-feedback.ok { color: #86efac; }
    .lw-feedback.no { color: #fca5a5; }
    .lw-inter { background: rgba(103,232,249,0.06); border: 1.5px solid rgba(103,232,249,0.3); border-radius: 16px; padding: 18px 20px; margin: 0 0 18px; }
    .lw-inter-tag { color: #67e8f9; display: block; }
    .lw-inter-text { font-size: 19px; line-height: 1.8; color: #eaf3ff; margin: 0 0 14px; font-weight: 700; }
    .lw-tokens, .lw-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
    .lw-token { background: rgba(255,255,255,0.06); border: 2px solid rgba(103,232,249,0.3); border-radius: 10px; padding: 8px 12px; color: #eaf3ff; font-size: 17px; cursor: pointer; }
    .lw-token.is-picked { background: rgba(103,232,249,0.25); border-color: #67e8f9; color: #cffafe; }
    .lw-chip { background: rgba(255,255,255,0.06); border: 2px solid rgba(103,232,249,0.35); border-radius: 999px; padding: 9px 16px; color: #eaf3ff; font-size: 16px; cursor: pointer; }
    .lw-chip.is-picked { background: rgba(103,232,249,0.25); border-color: #67e8f9; }
    .lw-blank { display: inline-block; min-width: 70px; border-bottom: 2px dashed #67e8f9; color: #cffafe; padding: 0 6px; font-weight: 700; text-align: center; }
    .lw-input { background: rgba(255,255,255,0.06); border: 2px solid rgba(103,232,249,0.35); border-radius: 10px; padding: 10px 14px; color: #eaf3ff; font-size: 17px; width: 200px; }
    .lw-pairs { display: flex; gap: 14px; margin-bottom: 12px; }
    .lw-col { display: flex; flex-direction: column; gap: 8px; flex: 1; }
    .lw-match { text-align: left; background: rgba(255,255,255,0.06); border: 2px solid rgba(103,232,249,0.3); border-radius: 10px; padding: 11px 14px; color: #eaf3ff; font-size: 16px; cursor: pointer; }
    .lw-match.is-sel { border-color: #f6b71e; background: rgba(246,183,30,0.15); }
    .lw-match.is-done { border-color: #34d399; background: rgba(52,211,153,0.18); color: #d1fae5; cursor: default; }
    .lw-order { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
    .lw-order-row { display: flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.06); border: 2px solid rgba(103,232,249,0.3); border-radius: 10px; padding: 10px 14px; color: #eaf3ff; font-size: 16px; }
    .lw-order-row span { flex: 1; }
    .lw-arrow { background: rgba(255,255,255,0.1); border: none; border-radius: 8px; width: 34px; height: 34px; color: #eaf3ff; font-size: 15px; cursor: pointer; }
    .lw-verify { margin-top: 4px; background: linear-gradient(135deg,#0e7490,#67e8f9); border: none; border-radius: 999px; padding: 11px 24px; color: #06263a; font-family: 'Fredoka One', cursive; font-size: 15px; cursor: pointer; }
    .lw-verify:disabled { opacity: 0.45; cursor: default; }
    .lw-next { display: inline-flex; align-items: center; gap: 8px; margin-top: 6px; background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 999px; padding: 14px 32px; color: #fff; font-family: 'Fredoka One', cursive; font-size: 17px; cursor: pointer; }
    .lw-complete { text-align: center; padding: 8px 0 4px; animation: lwFade 0.4s ease both; }
    .lw-complete img { width: 96px; height: 96px; object-fit: contain; margin: 0 auto 8px; display: block; animation: lwBob 2.2s ease-in-out infinite; }
    @keyframes lwBob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
    .lw-complete h3 { font-family: 'Fredoka One', cursive; font-size: 22px; color: #fcd34d; margin: 0 0 8px; }
    .lw-complete p { font-size: 17px; color: rgba(243,232,255,0.9); margin: 0 0 20px; }
    .lw-deeper { margin: 8px 0 20px; }
    .lw-deeper summary { cursor: pointer; color: rgba(196,181,253,0.75); font-size: 14px; }
    .lw-resources { list-style: none; padding: 0; margin: 12px 0 0; display: flex; flex-direction: column; gap: 10px; }
    .lw-resource { background: rgba(255,255,255,0.05); border: 1.5px solid rgba(34,211,238,0.3); border-radius: 12px; padding: 12px 16px; }
    .lw-resource a, .lw-resource span { color: #e6f2fb; font-size: 15px; font-weight: 600; text-decoration: none; }
    .lw-resource a:hover { color: #f0abfc; text-decoration: underline; }
    .lw-cta-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .lw-start { flex: 1; min-width: 200px; background: linear-gradient(135deg, #0e7490, #f6b71e); border: none; border-radius: 999px; padding: 15px 30px; color: white; font-family: 'Fredoka One', cursive; font-size: 16px; cursor: pointer; text-decoration: none; text-align: center; }
    .lw-secondary { background: rgba(255,255,255,0.08); border: 2px solid rgba(34,211,238,0.4); color: #e6f2fb; }
    .lw-obj { position: relative; display: inline-flex; align-items: center; gap: 6px; margin: -12px 0 20px; font-size: 12px; font-weight: 800; color: #f6b71e; background: rgba(246,183,30,0.12); border: 1px solid rgba(246,183,30,0.5); border-radius: 999px; padding: 5px 12px; cursor: help; }
    .lw-obj-tip { position: absolute; top: calc(100% + 6px); left: 0; z-index: 20; width: max(240px, 60vw); max-width: 340px; background: #0a1f38; border: 1px solid rgba(34,211,238,0.4); border-radius: 12px; padding: 11px 13px; font-size: 12.5px; font-weight: 600; line-height: 1.5; color: #e6f2fb; opacity: 0; visibility: hidden; transition: opacity .15s; }
    .lw-obj-tip b { color: #67e8f9; }
    .lw-obj:hover .lw-obj-tip, .lw-obj:focus .lw-obj-tip { opacity: 1; visibility: visible; }
    @media (prefers-reduced-motion: reduce) { .lw-card, .lw-block, .lw-complete, .lw-complete img { animation: none; } }
</style>

<div class="lw-wrap">
    @include('partials.voyage-crumb')
    {{-- Isolated, empty Alpine scope for scroll-on-handoff — must NOT wrap any wire:click content,
         or (with this app's multiple-Alpine quirk) it swallows Livewire clicks inside it. --}}
    <div x-data
        @reteach-splash.window="setTimeout(() => { const el = document.getElementById('lw-lesson'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 80)"
        @lesson-resumed.window="setTimeout(() => { const el = document.getElementById('lw-lesson'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 80)"
        style="display:none"></div>
    @if ($reteach)
        <div class="lw-hero">
            <img src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth the turtle" class="lw-hero-img">
            <div class="lw-hero-text">
                <p class="lw-hero-tag">Revisiting together 🐢</p>
                <h1 class="lw-hero-head">Let's relearn this one — no worries, it's not a test!</h1>
                <p class="lw-hero-sub">We'll walk through the lesson again, one step at a time. Whenever something feels fuzzy, tap <strong>Ask Smooth</strong> — she's right here to help you understand every part.</p>
            </div>
        </div>
    @endif
    <p class="lw-subject">{{ $subject }}</p>
    <p class="lw-topic">{{ $topic }}</p>
    @if (! empty($objectivesDirect))
        <div class="lw-obj" tabindex="0">🎯 Objectives
            <div class="lw-obj-tip">
                <b>Taught directly:</b> {{ implode(', ', $objectivesDirect) }}
                @if (! empty($objectivesIndirect))<br><b>Reinforces:</b> {{ implode(', ', $objectivesIndirect) }}@endif
            </div>
        </div>
    @endif

    <div class="lw-card" id="lw-lesson">
            @if ($lessonInProgress)
                <div class="lw-handoff">
                    <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle" class="lw-handoff-img">
                    <h3 class="lw-handoff-head">Great effort today! 🐢</h3>
                    <p class="lw-handoff-sub">This one's a bit tricky — and that's okay. @if ($inProgressAnswer !== '')The answer was <strong>{{ $inProgressAnswer }}</strong>. @endif We'll keep this lesson open and come back to it together another day. You can carry on with your other lessons now!</p>
                    <a href="{{ route('student.voyage') }}" class="lw-start">Back to my voyage →</a>
                </div>
            @elseif ($handoffSplash)
                <div class="lw-handoff">
                    <img src="{{ asset('images/voyage/companion/smooth.webp') }}" alt="Smooth the turtle" class="lw-handoff-img">
                    <h3 class="lw-handoff-head">Not quite — let's figure it out together!</h3>
                    <p class="lw-handoff-sub">No worries at all 🐢 Smooth is going to help you understand this one, step by step. Ready?</p>
                    <button type="button" class="lw-start" wire:click="enterRemediation">Let's do it with Smooth →</button>
                </div>
            @elseif ($lessonTitle)
                <h2 class="lw-title">{{ $lessonTitle }}</h2>
                @php $total = count($lessonBlocks); @endphp
                <div class="lw-progress"><span style="width: {{ $total ? round($revealed / $total * 100) : 100 }}%"></span></div>

                @foreach (array_slice($lessonBlocks, 0, $revealed) as $i => $block)
                    @php $type = $block['type'] ?? 'text'; $content = $block['content'] ?? ''; @endphp
                    <div class="lw-block" wire:key="block-{{ $i }}">
                        @switch($type)
                            @case('heading') <p class="lw-block-head">{{ $content }}</p> @break
                            @case('key') <div class="lw-key"><span class="lw-key-tag">Remember this</span>{{ $content }}</div> @break
                            @case('example')
                                <div class="lw-example">
                                    <p class="lw-example-tag">Worked example</p>
                                    @if ($content !== '')<p class="lw-para" style="margin-bottom:0">{{ $content }}</p>@endif
                                    @if (! empty($block['steps']))<ol class="lw-steps">@foreach ($block['steps'] as $step)<li>{{ $step }}</li>@endforeach</ol>@endif
                                </div>
                                @break
                            @case('visual') <img class="lw-visual" src="{{ $content }}" alt="Lesson diagram"> @break
                            @case('numberline')
                                @php
                                    $low = (float) ($block['low'] ?? 0);
                                    $high = (float) ($block['high'] ?? 0);
                                    $hasVal = array_key_exists('value', $block);
                                    $val = (float) ($block['value'] ?? $low);
                                    $val2 = array_key_exists('value2', $block) ? (float) $block['value2'] : null;
                                    $marks = is_array($block['marks'] ?? null) ? $block['marks'] : null;
                                    // Halfway only in single-value rounding mode (no second value, no custom marks).
                                    $showHalf = ($block['halfway'] ?? true) && $val2 === null && $marks === null;
                                    $mid = ($low + $high) / 2;
                                    $span = max(1e-9, $high - $low);
                                    $px = fn ($v) => 45 + ($v - $low) / $span * 430;
                                    $fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
                                    $ticks = $marks ?: [$low, $high];
                                    $nearer = $val < $mid ? $low : $high;
                                    $tapMode = $showHalf && ! empty($block['question']);
                                @endphp
                                <div class="lw-numberline" x-data="{ picked: null }">
                                    <p class="lw-example-tag">See it on the line</p>
                                    <svg viewBox="0 0 520 120" style="width:100%;max-width:520px;height:auto;display:block;margin:.25rem auto" role="img"
                                         aria-label="Number line from {{ $fmt($low) }} to {{ $fmt($high) }}.">
                                        <line x1="45" y1="74" x2="475" y2="74" stroke="currentColor" stroke-width="2" />
                                        @if ($showHalf)
                                            <line x1="{{ $px($mid) }}" y1="44" x2="{{ $px($mid) }}" y2="84" stroke="#94a3b8" stroke-width="2" stroke-dasharray="4 3" />
                                            <text x="{{ $px($mid) }}" y="36" text-anchor="middle" font-size="11" fill="#94a3b8">halfway ({{ $fmt($mid) }})</text>
                                        @endif
                                        @foreach ($ticks as $tv)
                                            <line x1="{{ $px($tv) }}" y1="66" x2="{{ $px($tv) }}" y2="84" stroke="currentColor" stroke-width="2" />
                                            <text x="{{ $px($tv) }}" y="102" text-anchor="middle" font-size="13" fill="currentColor">{{ $fmt($tv) }}</text>
                                        @endforeach
                                        @if ($hasVal)
                                            <line x1="{{ $px($val) }}" y1="74" x2="{{ $px($val) }}" y2="60" stroke="#0d9488" stroke-width="2" />
                                            <circle cx="{{ $px($val) }}" cy="74" r="7" fill="#0d9488" />
                                            <text x="{{ $px($val) }}" y="54" text-anchor="middle" font-size="14" font-weight="700" fill="#0d9488">{{ $fmt($val) }}</text>
                                        @endif
                                        @if ($val2 !== null)
                                            <line x1="{{ $px($val2) }}" y1="74" x2="{{ $px($val2) }}" y2="60" stroke="#d97706" stroke-width="2" />
                                            <circle cx="{{ $px($val2) }}" cy="74" r="7" fill="#d97706" />
                                            <text x="{{ $px($val2) }}" y="54" text-anchor="middle" font-size="14" font-weight="700" fill="#d97706">{{ $fmt($val2) }}</text>
                                        @endif
                                    </svg>
                                    @if ($tapMode)
                                        <p class="lw-para" style="margin:.35rem 0 .5rem">{{ $block['question'] }}</p>
                                        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                                            <button type="button" class="lw-opt" @click="picked = {{ $low }}" :class="picked === {{ $low }} ? 'is-picked' : ''">{{ $fmt($low) }}</button>
                                            <button type="button" class="lw-opt" @click="picked = {{ $high }}" :class="picked === {{ $high }} ? 'is-picked' : ''">{{ $fmt($high) }}</button>
                                        </div>
                                        <template x-if="picked === {{ $nearer }}"><p class="lw-para" style="margin-top:.4rem;color:#0d9488">Yes! The dot is on that side of the halfway line, so it is closer. 🐢</p></template>
                                        <template x-if="picked !== null && picked !== {{ $nearer }}"><p class="lw-para" style="margin-top:.4rem">Not yet — look which side of the halfway line the dot sits on.</p></template>
                                    @elseif (! empty($block['question']))
                                        <p class="lw-para" style="margin:.35rem 0">{{ $block['question'] }}</p>
                                    @endif
                                    @if ($content !== '')<p class="lw-para" style="margin-top:.4rem">{{ $content }}</p>@endif
                                </div>
                                @break
                            @case('check')
                                @php $answered = array_key_exists($i, $checkResults); $correct = $checkResults[$i] ?? false; @endphp
                                <div class="lw-checkq">
                                    <span class="lw-check-tag">Your turn</span>
                                    <p class="lw-checkq-text">{{ $block['question'] ?? '' }}</p>
                                    <div class="lw-opts">
                                        @foreach ($block['options'] ?? [] as $oi => $opt)
                                            <button type="button" class="lw-opt {{ $correct && $oi === (int) ($block['answer'] ?? -1) ? 'is-right' : '' }}" wire:click="answerCheck({{ $i }}, {{ $oi }})" @disabled($correct || $paused)>{{ $opt }}</button>
                                        @endforeach
                                    </div>
                                    @if ($answered && $correct)
                                        <p class="lw-feedback ok">Yes! 🎉 {{ $block['explain'] ?? '' }}</p>
                                    @elseif ($answered)
                                        <p class="lw-feedback no">Not quite — have another look and try again. 🐢</p>
                                    @endif
                                </div>
                                @break
                            @case('fillblank')
                                @php $answered = array_key_exists($i, $checkResults); $correct = $checkResults[$i] ?? false;
                                     $parts = explode('___', $block['prompt'] ?? ''); $hasBank = ! empty($block['options']); @endphp
                                <div class="lw-inter" x-data="{ val: '' }" wire:key="fb-{{ $i }}">
                                    <span class="lw-inter-tag lw-check-tag">Fill in the blank</span>
                                    <p class="lw-inter-text">{{ $parts[0] ?? '' }}<span class="lw-blank" x-text="val || '____'"></span>{{ $parts[1] ?? '' }}</p>
                                    @if ($hasBank)
                                        <div class="lw-chips">
                                            @foreach ($block['options'] as $opt)
                                                <button type="button" class="lw-chip" :class="{ 'is-picked': val === @js($opt) }" @click="val = @js($opt); $wire.answerFillBlank({{ $i }}, @js($opt))" @disabled($correct || $paused)>{{ $opt }}</button>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="display:flex; gap:10px; margin-bottom:12px; flex-wrap:wrap;">
                                            <input type="text" class="lw-input" x-model="val" @disabled($correct || $paused)>
                                            <button type="button" class="lw-verify" @click="$wire.answerFillBlank({{ $i }}, val)" x-bind:disabled="! val.trim()">Check</button>
                                        </div>
                                    @endif
                                    @if ($answered && $correct)<p class="lw-feedback ok">Yes! 🎉 {{ $block['explain'] ?? '' }}</p>
                                    @elseif ($answered)<p class="lw-feedback no">Not quite — try again. 🐢</p>@endif
                                </div>
                                @break

                            @case('markwords')
                                @php $answered = array_key_exists($i, $checkResults); $correct = $checkResults[$i] ?? false;
                                     $tokens = preg_split('/\s+/', trim($block['text'] ?? '')) ?: []; @endphp
                                <div class="lw-inter" x-data="{ picked: [] }" wire:key="mw-{{ $i }}">
                                    <span class="lw-inter-tag lw-check-tag">{{ $block['instruction'] ?? 'Tap the words' }}</span>
                                    <div class="lw-tokens">
                                        @foreach ($tokens as $ti => $tok)
                                            <button type="button" class="lw-token" :class="{ 'is-picked': picked.includes({{ $ti }}) }" @click="picked.includes({{ $ti }}) ? picked = picked.filter(x => x !== {{ $ti }}) : picked.push({{ $ti }})" @disabled($correct || $paused)>{{ trim($tok, '*') }}</button>
                                        @endforeach
                                    </div>
                                    <button type="button" class="lw-verify" @click="$wire.answerMarkWords({{ $i }}, picked)" x-bind:disabled="picked.length === 0">Check</button>
                                    @if ($answered && $correct)<p class="lw-feedback ok">Yes! 🎉 {{ $block['explain'] ?? '' }}</p>
                                    @elseif ($answered)<p class="lw-feedback no">Not quite — look again and re-tap. 🐢</p>@endif
                                </div>
                                @break

                            @case('matchpairs')
                                @php $answered = array_key_exists($i, $checkResults); $correct = $checkResults[$i] ?? false;
                                     $pairs = array_values($block['pairs'] ?? []);
                                     $lefts = array_map(fn ($p) => $p['left'] ?? '', $pairs);
                                     $rights = array_map(fn ($p) => $p['right'] ?? '', $pairs); @endphp
                                <div class="lw-inter" wire:key="mp-{{ $i }}"
                                    x-data="{ selLeft: null, matched: {},
                                        order: [...Array({{ count($rights) }}).keys()].sort(() => Math.random() - 0.5),
                                        used(v) { return Object.values(this.matched).includes(v); },
                                        pickRight(v) { if (this.selLeft === null || this.used(v)) return; this.matched[this.selLeft] = v; this.selLeft = null;
                                            if (Object.keys(this.matched).length === {{ count($pairs) }}) $wire.answerMatchPairs({{ $i }}, this.matched); } }">
                                    <span class="lw-inter-tag lw-check-tag">{{ $block['instruction'] ?? 'Match the pairs' }}</span>
                                    <div class="lw-pairs">
                                        <div class="lw-col">
                                            @foreach ($lefts as $li => $left)
                                                <button type="button" class="lw-match" :class="{ 'is-sel': selLeft === {{ $li }}, 'is-done': matched[{{ $li }}] !== undefined }" @click="matched[{{ $li }}] === undefined && (selLeft = {{ $li }})" @disabled($correct || $paused)>{{ $left }}</button>
                                            @endforeach
                                        </div>
                                        <div class="lw-col">
                                            <template x-for="ri in order" :key="ri">
                                                <button type="button" class="lw-match" :class="{ 'is-done': used(@js($rights)[ri]) }" @click="pickRight(@js($rights)[ri])" x-text="@js($rights)[ri]"></button>
                                            </template>
                                        </div>
                                    </div>
                                    @if ($answered && $correct)<p class="lw-feedback ok">Yes! 🎉 All matched.</p>
                                    @elseif ($answered)<p class="lw-feedback no">Not quite — tap “start over” and try again. 🐢</p>
                                        <button type="button" class="lw-verify" @click="matched = {}; selLeft = null">Start over</button>@endif
                                </div>
                                @break

                            @case('ordersteps')
                                @php $answered = array_key_exists($i, $checkResults); $correct = $checkResults[$i] ?? false;
                                     $items = array_values($block['items'] ?? []); @endphp
                                <div class="lw-inter" wire:key="os-{{ $i }}"
                                    x-data="{ order: @js($items).slice().sort(() => Math.random() - 0.5),
                                        up(k) { if (k > 0) { [this.order[k-1], this.order[k]] = [this.order[k], this.order[k-1]]; } },
                                        down(k) { if (k < this.order.length - 1) { [this.order[k+1], this.order[k]] = [this.order[k], this.order[k+1]]; } } }">
                                    <span class="lw-inter-tag lw-check-tag">{{ $block['instruction'] ?? 'Put them in order' }}</span>
                                    <div class="lw-order">
                                        <template x-for="(it, k) in order" :key="it">
                                            <div class="lw-order-row">
                                                <span x-text="it"></span>
                                                <button type="button" class="lw-arrow" @click="up(k)" @disabled($correct || $paused)>▲</button>
                                                <button type="button" class="lw-arrow" @click="down(k)" @disabled($correct || $paused)>▼</button>
                                            </div>
                                        </template>
                                    </div>
                                    <button type="button" class="lw-verify" @click="$wire.answerOrderSteps({{ $i }}, order)" @disabled($correct || $paused)>Check</button>
                                    @if ($answered && $correct)<p class="lw-feedback ok">Yes! 🎉 That's the right order.</p>
                                    @elseif ($answered)<p class="lw-feedback no">Not yet — try a different order. 🐢</p>@endif
                                </div>
                                @break

                            @default <p class="lw-para">{{ $content }}</p>
                        @endswitch
                    </div>
                @endforeach

                @if ($lessonComplete)
                    <div class="lw-complete">
                        <img src="{{ asset('images/voyage/companion/smooth-cheer.webp') }}" alt="Smooth cheering">
                        <h3>Lesson complete! 🎉</h3>
                        @if ($reteach && ! $finalDone)
                            <p>Great work getting through it! 🐢 Finish the 3 examples with Smooth in the chat, then you can prove it.</p>
                        @elseif ($reteach)
                            <p>Brilliant — you're all warmed up. Let's prove it!</p>
                        @else
                            <p>You worked through the whole thing — now let's practise it.</p>
                        @endif
                        <div class="lw-cta-row">
                            @unless ($reteach)
                                <button type="button" class="lw-start lw-secondary" wire:click="$dispatch('ask-smooth', { prompt: 'Can you show me another worked example for this?' })">Ask Smooth for more examples 🐢</button>
                            @endunless
                            @if ($reteach)
                                @if ($finalDone)
                                    <a href="{{ route('practice.reteach', $moduleId) }}" class="lw-start">I'm ready to try it →</a>
                                @else
                                    <span class="lw-start lw-secondary" style="opacity:.6;cursor:default;">Smooth has 3 examples for you 🐢</span>
                                @endif
                            @elseif ($gatedSequence)
                                <a href="{{ route('practice.tutorial', $moduleId) }}" class="lw-start">See worked examples →</a>
                            @else
                                <a href="{{ route('practice.walk', $moduleId) }}" class="lw-start">Start practising →</a>
                            @endif
                        </div>
                    </div>
                @elseif ($revealed < $total && $this->canAdvance())
                    <button type="button" class="lw-next" wire:click="next">Got it — next →</button>
                @endif
            @else
                <p class="lw-no-lesson">✨ An interactive lesson for this skill is coming soon. Here's what to know — and Smooth is on the right to help you make sense of it.</p>
                @if ($description)<p class="lw-para">{{ $description }}</p>@endif
                <div class="lw-cta-row">
                    <a href="{{ route('practice.tutorial', $moduleId) }}" class="lw-start lw-secondary">See worked examples →</a>
                    <a href="{{ route('practice.walk', $moduleId) }}" class="lw-start">Start practising →</a>
                </div>
            @endif

            @if (count($resources) > 0)
                <details class="lw-deeper">
                    <summary>Want to go deeper? (optional)</summary>
                    <ul class="lw-resources">
                        @foreach ($resources as $resource)
                            @php $label = is_array($resource) ? ($resource['title'] ?? $resource['label'] ?? null) : $resource; $url = is_array($resource) ? ($resource['url'] ?? null) : null; @endphp
                            <li class="lw-resource">@if ($url)<a href="{{ $url }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>@else<span>{{ $label }}</span>@endif</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </div>


    {{-- Re-teach done: a completion popup takes over (no scroll) and sends her back to practice (LL-15). --}}
    @if ($reteach && $finalDone)
        <div class="lw-modal-backdrop">
            <div class="lw-modal">
                <img src="{{ asset('images/voyage/companion/smooth-cheer.webp') }}" alt="Smooth cheering" class="lw-modal-img">
                <h3 class="lw-modal-head">Lesson complete! 🎉</h3>
                <p class="lw-modal-sub">Awesome work relearning this one. Now let's head back into practice to lock it in!</p>
                <a href="{{ route('practice.reteach', $moduleId) }}" class="lw-start">Let's practise it →</a>
            </div>
        </div>
    @endif

    {{-- The tutor/re-teach chat is a floating widget (bubble → panel), fixed bottom-right. --}}
    <livewire:clarify-chat :module-id="$moduleId" wire:key="clarify-{{ $moduleId }}" />
</div>
@endif

<livewire:loop-coach :leg="$reteach ? 'reteach' : 'learn'" wire:key="loop-coach-{{ $reteach ? 'reteach' : 'learn' }}" />
</div>
