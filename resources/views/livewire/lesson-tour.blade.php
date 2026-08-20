<div>
    @if ($open)
        <style>
            .lt-hole { position: fixed; z-index: 2000; border-radius: 12px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(20,6,34,0.6); outline: 3px solid #f0abfc; outline-offset: 4px; transition: all .25s ease; }
            .lt-scrim { position: fixed; inset: 0; z-index: 2000; background: rgba(20,6,34,0.55); }
            .lt-card { position: fixed; left: 50%; transform: translateX(-50%); bottom: 16px; z-index: 2002; width: min(92vw, 380px); background: linear-gradient(160deg, #241436, #3a1f52); border: 1.5px solid rgba(240,171,252,0.5); border-radius: 18px; box-shadow: 0 14px 40px rgba(0,0,0,0.5); padding: 15px 17px 14px; color: #f3e8ff; }
            .lt-head { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
            .lt-avatar { width: 48px; height: 48px; object-fit: contain; flex: none; }
            .lt-title { font-size: 1.1rem; font-weight: 900; color: #f0abfc; }
            .lt-lines { margin: 2px 0 12px; padding: 0; list-style: none; }
            .lt-lines li { font-size: 0.9rem; font-weight: 600; line-height: 1.45; color: #ecd9ff; margin-bottom: 6px; }
            .lt-lines b { color: #f0abfc; }
            .lt-dots { display: flex; gap: 5px; justify-content: center; margin-bottom: 11px; }
            .lt-dot { width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,0.22); }
            .lt-dot.is-on { background: #f0abfc; }
            .lt-btn { width: 100%; border: none; cursor: pointer; font-size: 0.98rem; font-weight: 900; padding: 12px 16px; border-radius: 999px; background: linear-gradient(135deg, #a855f7, #f0abfc); color: #2a0a3a; box-shadow: 0 6px 16px rgba(168,85,247,0.35); }
            .lt-skip { display: block; margin: 9px auto 0; background: none; border: none; color: #c4a3d6; font-size: 0.78rem; font-weight: 700; cursor: pointer; text-decoration: underline; }
        </style>

        <div x-data="{
                i: 0,
                steps: [
                    { t: 'The learning loop', lines: ['Every stop follows the same friendly loop — here it is on screen.', 'First a quick check: ace three questions and you have already mastered it!'] },
                    { t: 'Lesson and examples', lines: ['If not, no worries. You get a lesson and worked examples that walk you through it step by step.'] },
                    { t: 'Practice and master', lines: ['Then you practise, climbing from easy to tricky — always a second try.', 'Get three tricky ones right in a row and the level is yours! 🎉'] },
                    { t: 'You are ready!', lines: ['That is the whole loop. Try this stop now, then tap Back to the sea to keep sailing.', 'You can replay the tour any time from your Voyage.'] },
                ],
                hasHole: false, holeStyle: '',
                place() {
                    const el = document.querySelector('.me-steps');
                    if (!el) { this.hasHole = false; return; }
                    el.scrollIntoView({ behavior: 'auto', block: 'center' });
                    this.$nextTick(() => { const r = el.getBoundingClientRect(); const p = 8;
                        this.holeStyle = `top:${r.top-p}px;left:${r.left-p}px;width:${r.width+p*2}px;height:${r.height+p*2}px;`; this.hasHole = true; });
                },
             }"
             x-init="$nextTick(() => place())">
            <div class="lt-hole" x-show="hasHole" :style="holeStyle"></div>
            <div class="lt-scrim" x-show="!hasHole"></div>
            <div class="lt-card">
                <div class="lt-head">
                    <img class="lt-avatar" src="{{ $this->avatarUrl() }}" alt="Smooth the turtle">
                    <div class="lt-title" x-text="steps[i].t"></div>
                </div>
                <ul class="lt-lines">
                    <template x-for="(line, li) in steps[i].lines" :key="li"><li x-text="line"></li></template>
                </ul>
                <div class="lt-dots">
                    <template x-for="(s, di) in steps" :key="di"><span class="lt-dot" :class="{ 'is-on': di === i }"></span></template>
                </div>
                {{-- Next steps locally (plain Alpine); the final button persists via Livewire. --}}
                <button type="button" class="lt-btn" x-show="i < steps.length - 1" @click="i++; $nextTick(() => place())" x-text="'Next →'"></button>
                <button type="button" class="lt-btn" x-show="i >= steps.length - 1" wire:click="finish">Got it — let’s sail! ⛵</button>
                <button type="button" class="lt-skip" x-show="i < steps.length - 1" wire:click="finish">Skip the tour</button>
            </div>
        </div>
    @endif
</div>
