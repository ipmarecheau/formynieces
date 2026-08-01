<div class="wl-wrap">
    <style>
        .wl-wrap { max-width: 760px; margin: 0 auto; padding: 1.5rem 1rem 4rem; font-family: 'Nunito', sans-serif; }
        .wl-crumb { display: inline-flex; align-items: center; gap: .4rem; color: #b45309; text-decoration: none; font-weight: 700; font-size: .95rem; }
        .wl-crumb:hover { text-decoration: underline; }
        .wl-hero { margin-top: 1rem; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 1.25rem; padding: 1.5rem 1.5rem 1.75rem; box-shadow: 0 10px 30px rgba(180,83,9,.15); }
        .wl-badge { font-size: 2rem; }
        .wl-kicker { font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: #b45309; font-size: .8rem; }
        .wl-title { font-family: 'Fredoka One', cursive; font-size: 1.6rem; color: #78350f; margin: .25rem 0 .5rem; }
        .wl-prompt { color: #92400e; font-size: 1.1rem; line-height: 1.5; }
        .wl-type { display: inline-block; margin-top: .75rem; background: #fbbf24; color: #78350f; font-weight: 700; border-radius: 999px; padding: .2rem .8rem; font-size: .85rem; text-transform: capitalize; }
        .wl-card { margin-top: 1.25rem; background: #fff; border-radius: 1.25rem; padding: 1.5rem; box-shadow: 0 6px 20px rgba(0,0,0,.06); }
        .wl-label { font-weight: 800; color: #78350f; margin-bottom: .5rem; display: block; }
        .wl-textarea { width: 100%; min-height: 220px; border: 2px solid #fcd34d; border-radius: .9rem; padding: 1rem; font-family: 'Nunito', sans-serif; font-size: 1.05rem; line-height: 1.6; resize: vertical; box-sizing: border-box; }
        .wl-textarea:focus { outline: none; border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,.2); }
        .wl-error { color: #b91c1c; font-weight: 700; margin-top: .5rem; }
        .wl-btn { margin-top: 1rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none; border-radius: 999px; padding: .8rem 2rem; font-family: 'Fredoka One', cursive; font-size: 1.1rem; cursor: pointer; box-shadow: 0 6px 16px rgba(217,119,6,.35); }
        .wl-btn:hover { filter: brightness(1.05); }
        .wl-queued { margin-top: 1.25rem; background: #fffbeb; border: 2px dashed #fcd34d; border-radius: 1rem; padding: 1.25rem 1.5rem; color: #92400e; }
        .wl-queued-title { font-family: 'Fredoka One', cursive; color: #b45309; font-size: 1.2rem; }
        .wl-rubric { margin-top: 1.5rem; }
        .wl-crit { margin-bottom: 1rem; }
        .wl-crit-head { display: flex; justify-content: space-between; font-weight: 700; color: #78350f; }
        .wl-bar { height: 12px; background: #fef3c7; border-radius: 999px; overflow: hidden; margin-top: .35rem; }
        .wl-bar-fill { height: 100%; background: linear-gradient(90deg, #fbbf24, #f59e0b); border-radius: 999px; }
        .wl-fb { margin-top: 1.25rem; }
        .wl-well { background: #ecfdf5; border-radius: .9rem; padding: 1rem 1.25rem; margin-bottom: .75rem; color: #065f46; }
        .wl-well-title { font-weight: 800; color: #047857; margin-bottom: .35rem; }
        .wl-try { background: #eff6ff; border-radius: .9rem; padding: 1rem 1.25rem; color: #1e40af; }
        .wl-try-title { font-weight: 800; color: #1d4ed8; margin-bottom: .35rem; }
    </style>

    <a href="{{ route('student.voyage') }}" class="wl-crumb">⛵ Back to my voyage</a>

    @if($prompt === null)
        <div class="wl-hero">
            <div class="wl-badge">✍️</div>
            <div class="wl-kicker">Writer's Log</div>
            <h1 class="wl-title">No prompt this week</h1>
            <p class="wl-prompt">Your next writing adventure will appear here soon. Check back in a little while!</p>
        </div>
    @else
        <div class="wl-hero">
            <div class="wl-badge">✍️</div>
            <div class="wl-kicker">Writer's Log · This week's prompt</div>
            <h1 class="wl-title">{{ $prompt->title }}</h1>
            <p class="wl-prompt">{{ $prompt->prompt }}</p>
            <span class="wl-type">{{ $prompt->type }}</span>
        </div>

        {{-- The rubric feedback, once scored. Never a grade or a pass/fail. --}}
        @if($submission && $submission->isScored())
            <div class="wl-card">
                <span class="wl-label">Your writing profile</span>
                <div class="wl-rubric">
                    @foreach($submission->rubricProfile() as $criterion => $score)
                        <div class="wl-crit">
                            <div class="wl-crit-head"><span>{{ $criterion }}</span><span>{{ $score }} / 10</span></div>
                            <div class="wl-bar"><div class="wl-bar-fill" style="width: {{ $score * 10 }}%;"></div></div>
                        </div>
                    @endforeach
                </div>

                <div class="wl-fb">
                    <div class="wl-well">
                        <div class="wl-well-title">✨ Two things you did well</div>
                        <ul>
                            @foreach($submission->did_well ?? [] as $well)
                                <li>{{ $well }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="wl-try">
                        <div class="wl-try-title">🎯 One thing to try next time</div>
                        <p>{{ $submission->try_next }}</p>
                    </div>
                </div>
            </div>
        @elseif($queued)
            {{-- WR-03: saved and queued while the scorer is unavailable. --}}
            <div class="wl-queued">
                <div class="wl-queued-title">Got it — your writing is saved! 🌟</div>
                <p>Your feedback is on its way. Check back in a little while to see your writing profile.</p>
            </div>
        @endif

        {{-- The draft form. Hidden once scored so she reads her feedback; always
             available before that. --}}
        @if(! $submission || ! $submission->isScored())
            <div class="wl-card">
                <label for="wl-body" class="wl-label">Your writing</label>
                <textarea id="wl-body" class="wl-textarea" wire:model="body"
                          placeholder="Start writing your response here…"></textarea>
                @error('body') <div class="wl-error">{{ $message }}</div> @enderror
                <button type="button" class="wl-btn" wire:click="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Send it in</span>
                    <span wire:loading wire:target="submit">Reading your writing…</span>
                </button>
            </div>
        @endif
    @endif
</div>
