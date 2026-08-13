<div>
@if ($guidedLocked)
    @include('partials.guided-locked', ['moduleId' => $moduleId])
@else
@include('partials.guided-heartbeat')
<livewire:smooth-guide guide="lesson" wire:key="guide-lesson" />
<style>
    .lw-wrap { min-height: 100vh; padding: 28px 20px 48px; max-width: 1120px; margin: 0 auto; }
    .lw-subject { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(196,181,253,0.7); margin-bottom: 6px; }
    .lw-topic { font-family: 'Fredoka One', cursive; font-size: 26px; color: #e6f2fb; margin-bottom: 22px; }
    .lw-layout { display: grid; grid-template-columns: 1.55fr 1fr; gap: 24px; align-items: start; }
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
    .lw-chat { position: sticky; top: 20px; height: calc(100vh - 40px); max-height: 640px; }
    @media (max-width: 860px) { .lw-layout { grid-template-columns: 1fr; } .lw-chat { position: static; height: 460px; } }
    @media (prefers-reduced-motion: reduce) { .lw-card, .lw-block, .lw-complete, .lw-complete img { animation: none; } }
</style>

<div class="lw-wrap">
    <p class="lw-subject">{{ $subject }}</p>
    <p class="lw-topic">{{ $topic }}</p>

    <div class="lw-layout">
        <div class="lw-card">
            @if ($lessonTitle)
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
                            @case('check')
                                @php $answered = array_key_exists($i, $checkResults); $correct = $checkResults[$i] ?? false; @endphp
                                <div class="lw-checkq">
                                    <span class="lw-check-tag">Your turn</span>
                                    <p class="lw-checkq-text">{{ $block['question'] ?? '' }}</p>
                                    <div class="lw-opts">
                                        @foreach ($block['options'] ?? [] as $oi => $opt)
                                            <button type="button" class="lw-opt {{ $correct && $oi === (int) ($block['answer'] ?? -1) ? 'is-right' : '' }}" wire:click="answerCheck({{ $i }}, {{ $oi }})" @disabled($correct)>{{ $opt }}</button>
                                        @endforeach
                                    </div>
                                    @if ($answered && $correct)
                                        <p class="lw-feedback ok">Yes! 🎉 {{ $block['explain'] ?? '' }}</p>
                                    @elseif ($answered)
                                        <p class="lw-feedback no">Not quite — have another look and try again. 🐢</p>
                                    @endif
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
                        <p>You worked through the whole thing — now let's practise it.</p>
                        <div class="lw-cta-row">
                            <button type="button" class="lw-start lw-secondary" wire:click="$dispatch('ask-smooth', { prompt: 'Can you show me another worked example for this?' })">Ask Smooth for more examples 🐢</button>
                            @if ($gatedSequence)
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

        <aside class="lw-chat">
            <livewire:clarify-chat :module-id="$moduleId" wire:key="clarify-{{ $moduleId }}" />
        </aside>
    </div>
</div>
@endif
</div>
