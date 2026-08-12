<div>
@if ($guidedLocked)
    @include('partials.guided-locked', ['moduleId' => $moduleId])
@else
@include('partials.guided-heartbeat')
<style>
    .tw-wrap { min-height: 100vh; display: flex; flex-direction: column; align-items: center; padding: 32px 20px 48px; }
    .tw-topic { font-family: 'Fredoka One', cursive; font-size: 20px; color: #e6f2fb; text-align: center; margin-bottom: 6px; }
    .tw-tag { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(196,181,253,0.75); margin-bottom: 18px; }
    .tw-card { background: #0c2440; border: 1.5px solid rgba(34,211,238,0.35); border-radius: 24px; padding: 30px 26px; width: 100%; max-width: 600px; }
    .tw-smooth { display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
    .tw-smooth img { width: 60px; height: 60px; object-fit: contain; }
    .tw-smooth p { font-family: 'Fredoka One', cursive; font-size: 15px; color: #67e8f9; }
    .tw-problem { font-family: 'Fredoka One', cursive; font-size: 19px; line-height: 1.4; color: #e6f2fb; margin-bottom: 22px; text-align: center; }
    .tw-steps { list-style: none; display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; }
    .tw-step { display: flex; gap: 12px; align-items: flex-start; font-size: 16px; line-height: 1.5; color: #e6f2fb; animation: twIn 0.35s ease both; }
    .tw-step-num { flex: none; width: 26px; height: 26px; border-radius: 50%; background: linear-gradient(135deg,#0e7490,#f6b71e); color: #fff; font-family: 'Fredoka One', cursive; font-size: 13px; display: grid; place-items: center; }
    @keyframes twIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
    .tw-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    .tw-btn { background: rgba(255,255,255,0.08); border: 2px solid rgba(34,211,238,0.4); border-radius: 999px; padding: 13px 26px; color: #e6f2fb; font-family: 'Fredoka One', cursive; font-size: 15px; cursor: pointer; text-decoration: none; }
    .tw-btn.primary { background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; color: #fff; }
    .tw-empty { font-family: 'Fredoka One', cursive; font-size: 18px; color: #67e8f9; text-align: center; }
    @media (prefers-reduced-motion: reduce) { .tw-step { animation: none; } }
</style>

<div class="tw-wrap">
    <p class="tw-topic">{{ $topic }}</p>
    <p class="tw-tag">Worked example with Smooth</p>

    @if ($problem === null)
        <div class="tw-card"><p class="tw-empty">No worked example for this one yet — you're ready to practise! 🌱</p>
            <div class="tw-actions" style="margin-top:20px;">
                <a href="{{ route('practice.walk', $moduleId) }}" class="tw-btn primary">Start practising →</a>
            </div>
        </div>
    @else
        <div class="tw-card">
            <div class="tw-smooth">
                <img src="{{ asset('images/voyage/companion/smooth-chart.webp') }}" alt="Smooth the turtle">
                <p>Let's work through one together!</p>
            </div>

            <p class="tw-problem">{{ $problem }}</p>

            <ol class="tw-steps">
                @foreach (array_slice($steps, 0, $revealed) as $i => $step)
                    <li class="tw-step" wire:key="step-{{ $i }}">
                        <span class="tw-step-num">{{ $i + 1 }}</span>
                        <span>{{ $step }}</span>
                    </li>
                @endforeach
            </ol>

            <div class="tw-actions">
                @if ($revealed < count($steps))
                    <button type="button" class="tw-btn" wire:click="nextStep">Next step →</button>
                @else
                    <a href="{{ route('practice.walk', $moduleId) }}" class="tw-btn primary">Start practising →</a>
                @endif
            </div>
        </div>
    @endif
</div>
@endif
</div>
