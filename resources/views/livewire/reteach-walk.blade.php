<div>
@include('partials.guided-heartbeat')
<style>
    .rw-wrap { min-height: 100vh; padding: 28px 20px 48px; max-width: 720px; margin: 0 auto; }
    .rw-tag { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #fcd34d; margin-bottom: 6px; }
    .rw-topic { font-family: 'Fredoka One', cursive; font-size: 24px; color: #e6f2fb; margin-bottom: 6px; }
    .rw-lead { font-size: 16px; color: rgba(196,181,253,0.9); margin-bottom: 20px; }
    .rw-card { background: #0c2440; border: 1.5px solid rgba(246,183,30,0.35); border-radius: 24px; padding: 28px; }
    .rw-progress { font-size: 14px; color: rgba(196,181,253,0.85); margin-bottom: 16px; }
    .rw-q { font-size: 21px; font-weight: 700; color: #eaf3ff; margin: 0 0 16px; }
    .rw-input { width: 100%; background: rgba(255,255,255,0.06); border: 2px solid rgba(246,183,30,0.4); border-radius: 14px; padding: 14px 18px; color: #eaf3ff; font-size: 18px; box-sizing: border-box; }
    .rw-input:focus { outline: none; border-color: #f6b71e; }
    .rw-feedback { margin: 14px 0 0; font-size: 17px; line-height: 1.6; }
    .rw-feedback.ok { color: #86efac; }
    .rw-feedback.no { color: #fca5a5; }
    .rw-btn { display: inline-flex; align-items: center; gap: 8px; margin-top: 16px; background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 999px; padding: 13px 28px; color: #fff; font-family: 'Fredoka One', cursive; font-size: 16px; cursor: pointer; text-decoration: none; }
</style>

<div class="rw-wrap">
    <p class="rw-tag">Show you've got it 🐢</p>
    <p class="rw-topic">{{ $topic }}</p>
    <p class="rw-lead">Nice work revisiting the lesson. Now let's prove it — type the answers. 🐢</p>

    <div class="rw-card">
        <p class="rw-progress">Got it right: <b>{{ $proofsDone }}</b> of <b>{{ $proofTarget }}</b></p>
        @if ($question)
            <p class="rw-q">Type {{ $question['prompt'] }}</p>
            @if (! $feedback)
                <form wire:submit="submit">
                    <input type="text" class="rw-input" wire:model="typed" placeholder="Type your answer…" autocomplete="off" autofocus>
                    <button type="submit" class="rw-btn">Check →</button>
                </form>
            @elseif ($feedback['correct'])
                <p class="rw-feedback ok">Yes! 🎉 Nice work.</p>
                <button type="button" class="rw-btn" wire:click="nextQuestion">Next one →</button>
            @else
                <p class="rw-feedback no">Not yet — it's “{{ $feedback['answer'] }}”. {{ $feedback['rule'] }}</p>
                <button type="button" class="rw-btn" wire:click="nextQuestion">Try another →</button>
            @endif
        @else
            <p class="rw-lead">No practice words are ready for this lesson yet.</p>
            <a href="{{ route('student.voyage') }}" class="rw-btn">Back to my voyage →</a>
        @endif
    </div>
</div>
</div>
