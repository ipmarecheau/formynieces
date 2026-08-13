<div>
@include('partials.guided-heartbeat')
<style>
    .rw-wrap { min-height: 100vh; padding: 28px 20px 48px; max-width: 1120px; margin: 0 auto; }
    .rw-tag { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #fcd34d; margin-bottom: 6px; }
    .rw-topic { font-family: 'Fredoka One', cursive; font-size: 24px; color: #e6f2fb; margin-bottom: 6px; }
    .rw-lead { font-size: 16px; color: rgba(196,181,253,0.9); margin-bottom: 20px; }
    .rw-layout { display: grid; grid-template-columns: 1.55fr 1fr; gap: 24px; align-items: start; }
    .rw-card { background: #0c2440; border: 1.5px solid rgba(246,183,30,0.35); border-radius: 24px; padding: 28px; }
    .rw-title { font-family: 'Fredoka One', cursive; font-size: 22px; color: #fcd34d; margin: 0 0 18px; }
    .rw-block { margin: 0 0 16px; }
    .rw-head { font-family: 'Fredoka One', cursive; font-size: 18px; color: #f0abfc; margin: 6px 0 10px; }
    .rw-para { font-size: 18px; line-height: 1.8; color: #eaf3ff; margin: 0 0 12px; }
    .rw-key { background: rgba(246,183,30,0.12); border-left: 4px solid #f6b71e; border-radius: 10px; padding: 12px 16px; color: #fde68a; margin: 0 0 12px; }
    .rw-steps { margin: 8px 0 0 20px; color: #eaf3ff; font-size: 17px; line-height: 1.6; }
    .rw-review { background: rgba(34,211,238,0.06); border: 1px solid rgba(34,211,238,0.3); border-radius: 12px; padding: 12px 16px; color: #cffafe; margin: 0 0 12px; font-size: 16px; }
    .rw-progress { font-size: 14px; color: rgba(196,181,253,0.85); margin-bottom: 14px; }
    .rw-q { font-size: 19px; font-weight: 700; color: #eaf3ff; margin: 0 0 14px; }
    .rw-opts { display: flex; flex-direction: column; gap: 10px; }
    .rw-opt { text-align: left; background: rgba(255,255,255,0.06); border: 2px solid rgba(246,183,30,0.35); border-radius: 12px; padding: 13px 16px; color: #eaf3ff; font-size: 17px; cursor: pointer; }
    .rw-opt:hover:not(:disabled) { background: rgba(246,183,30,0.14); }
    .rw-opt:disabled { cursor: default; opacity: 0.85; }
    .rw-feedback { margin: 14px 0 0; font-size: 17px; line-height: 1.6; }
    .rw-feedback.ok { color: #86efac; }
    .rw-feedback.no { color: #fca5a5; }
    .rw-btn { display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 999px; padding: 13px 28px; color: #fff; font-family: 'Fredoka One', cursive; font-size: 16px; cursor: pointer; text-decoration: none; }
    .rw-escape { display: inline-block; margin-top: 14px; margin-left: 12px; color: rgba(196,181,253,0.85); font-size: 14px; text-decoration: underline; }
    .rw-chat { position: sticky; top: 20px; height: calc(100vh - 40px); max-height: 640px; }
    @media (max-width: 860px) { .rw-layout { grid-template-columns: 1fr; } .rw-chat { position: static; height: 460px; } }
</style>

<div class="rw-wrap">
    <p class="rw-tag">Let's revisit this together 🐢</p>
    <p class="rw-topic">{{ $topic }}</p>
    <p class="rw-lead">No worries — this bit is tricky. Let's go back over it, and Smooth is here to help. This is a re-teach, not a test.</p>

    <div class="rw-layout">
        <div class="rw-card">
            @if ($phase === 'relearn')
                @if ($lessonTitle)<h2 class="rw-title">{{ $lessonTitle }}</h2>@endif
                @foreach (array_slice($lessonBlocks, 0, $revealed) as $block)
                    @php $type = $block['type'] ?? 'text'; @endphp
                    <div class="rw-block">
                        @switch($type)
                            @case('heading') <p class="rw-head">{{ $block['content'] ?? '' }}</p> @break
                            @case('key') <div class="rw-key">{{ $block['content'] ?? '' }}</div> @break
                            @case('example')
                                <div class="rw-review">
                                    @if (($block['content'] ?? '') !== '')<p class="rw-para" style="margin:0">{{ $block['content'] }}</p>@endif
                                    @if (! empty($block['steps']))<ol class="rw-steps">@foreach ($block['steps'] as $s)<li>{{ $s }}</li>@endforeach</ol>@endif
                                </div>
                                @break
                            @case('check') <div class="rw-review">Recall: {{ $block['question'] ?? '' }}</div> @break
                            @case('fillblank') <div class="rw-review">Recall: {{ $block['prompt'] ?? '' }}</div> @break
                            @case('markwords')
                            @case('matchpairs')
                            @case('ordersteps') <div class="rw-review">Recall: {{ $block['instruction'] ?? '' }}</div> @break
                            @default <p class="rw-para">{{ $block['content'] ?? '' }}</p>
                        @endswitch
                    </div>
                @endforeach

                @if ($revealed < count($lessonBlocks))
                    <button type="button" class="rw-btn" wire:click="nextBlock">Got it — next →</button>
                @else
                    <button type="button" class="rw-btn" wire:click="startProving">I'm ready to try it →</button>
                @endif
            @else
                {{-- prove phase: D1 questions --}}
                <p class="rw-progress">Show you've got it: <b>{{ $proofsDone }}</b> of <b>{{ $proofTarget }}</b> right</p>
                @if ($question)
                    <p class="rw-q">{{ $question['prompt'] }}</p>
                    <div class="rw-opts">
                        @foreach ($question['options'] as $oi => $opt)
                            <button type="button" class="rw-opt" wire:click="choose({{ $oi }})" @disabled($feedback !== null)>{{ $opt }}</button>
                        @endforeach
                    </div>
                    @if ($feedback && $feedback['correct'])
                        <p class="rw-feedback ok">Yes! 🎉 {{ $feedback['explanation'] }}</p>
                        <button type="button" class="rw-btn" wire:click="nextQuestion">Next one →</button>
                    @elseif ($feedback)
                        <p class="rw-feedback no">Not yet — {{ $feedback['explanation'] }}</p>
                        @if ($teacherOffered)<p class="rw-feedback no">Ask Smooth on the right to walk it through with you. 🐢</p>@endif
                        <button type="button" class="rw-btn" wire:click="nextQuestion">Try another →</button>
                    @endif
                    <a href="{{ route('practice.tutorial', $moduleId) }}" class="rw-escape">Back to the worked examples</a>
                @else
                    <p class="rw-para">No practice questions are ready for this level yet.</p>
                    <a href="{{ route('practice.tutorial', $moduleId) }}" class="rw-btn">Back to the worked examples →</a>
                @endif
            @endif
        </div>

        <div class="rw-chat">
            <livewire:clarify-chat :module-id="$moduleId" wire:key="reteach-clarify-{{ $moduleId }}" />
        </div>
    </div>
</div>
</div>
