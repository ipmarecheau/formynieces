<div>
@if ($guidedLocked)
    @include('partials.guided-locked', ['moduleId' => $moduleId])
@else
@include('partials.guided-heartbeat')
<x-loop-rail stage="lesson" />
<style>
    .tw-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 32px 20px 48px; }
    .tw-topic { font-family: 'Fredoka One', cursive; font-size: 20px; color: #e6f2fb; text-align: center; margin-bottom: 6px; }
    .tw-tag { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(196,181,253,0.75); margin-bottom: 18px; }
    .tw-card { background: #0c2440; border: 1.5px solid rgba(34,211,238,0.35); border-radius: 24px; padding: 30px 26px; width: 100%; max-width: 600px; }
    .tw-progress { display: flex; gap: 7px; justify-content: center; margin-bottom: 16px; }
    .tw-pip { width: 9px; height: 9px; border-radius: 50%; background: rgba(255,255,255,0.18); }
    .tw-pip.done { background: #57d6a0; } .tw-pip.now { background: #f6b71e; }
    .tw-smooth { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
    .tw-smooth img { width: 60px; height: 60px; object-fit: contain; flex: none; }
    .tw-smooth p { font-family: 'Fredoka One', cursive; font-size: 15px; color: #67e8f9; }
    .tw-problem { font-family: 'Fredoka One', cursive; font-size: 19px; line-height: 1.4; color: #e6f2fb; margin-bottom: 22px; text-align: center; }
    .tw-steps { list-style: none; display: flex; flex-direction: column; gap: 12px; margin-bottom: 22px; }
    .tw-step { display: flex; gap: 12px; align-items: flex-start; font-size: 16px; line-height: 1.5; color: #e6f2fb; animation: twIn 0.35s ease both; }
    .tw-step-num { flex: none; width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg,#0e7490,#f6b71e); color: #fff; font-family: 'Fredoka One', cursive; font-size: 13px; display: grid; place-items: center; }
    @keyframes twIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .tw-ask { font-family: 'Fredoka One', cursive; font-size: 16px; color: #fde68a; text-align: center; margin-bottom: 14px; }
    .tw-options { display: flex; flex-direction: column; gap: 10px; margin-bottom: 6px; }
    .tw-opt { text-align: left; background: rgba(255,255,255,0.05); border: 1.5px solid rgba(34,211,238,0.35); border-radius: 14px; padding: 13px 18px; color: #e6f2fb; font-size: 16px; font-weight: 700; cursor: pointer; transition: background .15s, border-color .15s; }
    .tw-opt:hover { background: rgba(34,211,238,0.12); border-color: rgba(34,211,238,0.6); }
    .tw-opt.picked-right { border-color: #57d6a0; background: rgba(87,214,160,0.16); }
    .tw-opt.picked-wrong { border-color: #c084fc; background: rgba(192,132,252,0.14); }
    .tw-opt.is-answer { border-color: #57d6a0; }
    .tw-remark { display: flex; align-items: center; gap: 12px; background: rgba(103,232,249,0.08); border: 1.5px solid rgba(103,232,249,0.3); border-radius: 16px; padding: 14px 16px; margin: 18px 0 6px; }
    .tw-remark img { width: 46px; height: 46px; flex: none; object-fit: contain; }
    .tw-remark p { font-size: 15.5px; font-weight: 700; color: #e6f2fb; line-height: 1.4; }
    .tw-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 18px; }
    .tw-btn { background: rgba(255,255,255,0.08); border: 2px solid rgba(34,211,238,0.4); border-radius: 999px; padding: 13px 26px; color: #e6f2fb; font-family: 'Fredoka One', cursive; font-size: 15px; cursor: pointer; text-decoration: none; }
    .tw-btn.primary { background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; color: #fff; }
    .tw-empty { font-family: 'Fredoka One', cursive; font-size: 18px; color: #67e8f9; text-align: center; }
    @media (prefers-reduced-motion: reduce) { .tw-step { animation: none; } }
</style>

<div class="tw-wrap">
    @include('partials.voyage-crumb')
    <p class="tw-topic">{{ $topic }}</p>
    <p class="tw-tag">Worked examples with Smooth</p>

    @if (count($examples) === 0)
        <div class="tw-card"><p class="tw-empty">No worked examples for this one yet — you're ready to practise! 🌱</p>
            <div class="tw-actions"><a href="{{ route('practice.walk', $moduleId) }}" class="tw-btn primary">Start practising →</a></div>
        </div>

    @elseif ($phase === 'done')
        <div class="tw-card">
            <div class="tw-smooth">
                <img src="{{ asset('images/voyage/companion/smooth-cheer.webp') }}" alt="Smooth the turtle">
                <p>Three examples done — you've got the idea! 🎉</p>
            </div>
            <p class="tw-problem" style="font-size:16px;">Now it's all yours. Ready to practise for real?</p>
            <div class="tw-actions"><a href="{{ route('practice.walk', $moduleId) }}" class="tw-btn primary">Start practising →</a></div>
        </div>

    @else
        @php($ex = $this->currentExample())
        <div class="tw-card" wire:key="ex-{{ $exampleIndex }}">
            <div class="tw-progress">
                @foreach ($examples as $i => $e)
                    <span class="tw-pip {{ $i < $exampleIndex ? 'done' : '' }} {{ $i === $exampleIndex ? 'now' : '' }}"></span>
                @endforeach
            </div>

            <div class="tw-smooth">
                <img src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth the turtle">
                <p>Example {{ $exampleIndex + 1 }} of {{ count($examples) }} — let's work it out!</p>
            </div>

            <p class="tw-problem">{{ $ex['problem'] }}</p>

            <ol class="tw-steps">
                @foreach (array_slice($ex['steps'], 0, $revealed) as $i => $step)
                    <li class="tw-step" wire:key="ex{{ $exampleIndex }}-step-{{ $i }}">
                        <span class="tw-step-num">{{ $i + 1 }}</span><span>{{ $step }}</span>
                    </li>
                @endforeach
            </ol>

            @if ($phase === 'walk')
                <div class="tw-actions">
                    <button type="button" class="tw-btn" wire:click="nextStep">
                        {{ $revealed < count($ex['steps']) - 1 ? 'Next step →' : 'My turn →' }}
                    </button>
                </div>

            @elseif ($phase === 'predict')
                <p class="tw-ask">Your turn — what's the answer? 🐢</p>
                <div class="tw-options">
                    @foreach ($ex['options'] as $i => $option)
                        <button type="button" class="tw-opt" wire:key="opt-{{ $exampleIndex }}-{{ $i }}" wire:click="predict({{ $i }})">{{ $option }}</button>
                    @endforeach
                </div>

            @elseif ($phase === 'reveal')
                <div class="tw-options">
                    @foreach ($ex['options'] as $i => $option)
                        <div class="tw-opt {{ $i === $ex['correctIndex'] ? 'is-answer' : '' }} {{ $i === $picked ? ($pickedCorrect ? 'picked-right' : 'picked-wrong') : '' }}">
                            {{ $option }} @if ($i === $ex['correctIndex']) ✓ @endif
                        </div>
                    @endforeach
                </div>
                <div class="tw-remark">
                    <img src="{{ asset('images/voyage/companion/'.($pickedCorrect ? 'smooth-cheer' : 'smooth-chart').'.webp') }}" alt="Smooth the turtle">
                    <p>{{ $remark }}</p>
                </div>
                <div class="tw-actions">
                    <button type="button" class="tw-btn primary" wire:click="continueExample">
                        {{ $exampleIndex < count($examples) - 1 ? 'Next example →' : 'On to practice →' }}
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
@endif

<livewire:loop-coach leg="examples" wire:key="loop-coach-examples" />
</div>
