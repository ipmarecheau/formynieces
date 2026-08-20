<div>
    @if ($open)
        <style>
            .lt-hole { position: fixed; z-index: 2000; border-radius: 12px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(20,6,34,0.55); outline: 3px solid #f0abfc; outline-offset: 4px; transition: all .25s ease; }
            .lt-scrim { position: fixed; inset: 0; z-index: 2000; background: rgba(20,6,34,0.5); pointer-events: none; }
            .lt-card { position: fixed; left: 50%; transform: translateX(-50%); bottom: 16px; z-index: 2002; width: min(94vw, 400px); background: linear-gradient(160deg, #241436, #3a1f52); border: 1.5px solid rgba(240,171,252,0.5); border-radius: 18px; box-shadow: 0 14px 40px rgba(0,0,0,0.5); padding: 15px 17px 14px; color: #f3e8ff; }
            .lt-head { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
            .lt-avatar { width: 48px; height: 48px; object-fit: contain; flex: none; }
            .lt-title { font-size: 1.1rem; font-weight: 900; color: #f0abfc; }
            .lt-lines { margin: 2px 0 10px; padding: 0; list-style: none; }
            .lt-lines li { font-size: 0.9rem; font-weight: 600; line-height: 1.45; color: #ecd9ff; margin-bottom: 6px; }
            .lt-lines b { color: #f0abfc; }
            .lt-do { text-align: center; font-size: 0.95rem; font-weight: 900; color: #fde68a; margin: 4px 0 2px; }
            .lt-skip { display: block; margin: 9px auto 0; background: none; border: none; color: #c4a3d6; font-size: 0.78rem; font-weight: 700; cursor: pointer; text-decoration: underline; }
            .lt-btn { width: 100%; border: none; cursor: pointer; font-size: 0.98rem; font-weight: 900; padding: 12px 16px; border-radius: 999px; background: linear-gradient(135deg, #a855f7, #f0abfc); color: #2a0a3a; box-shadow: 0 6px 16px rgba(168,85,247,0.35); }
        </style>

        {{-- The lesson tour coaches the REAL lesson: it spotlights the actual explainer,
             quick-check and outcome on the page and reacts to how the student's own
             answers land. No imitation — she does the real thing, with Smooth alongside. --}}
        <div x-data="{
                phase: 'explainer',        // explainer → check → outcome  (driven by the real page)
                mastered: false,
                copy: {
                    explainer: {
                        title: 'How this level works',
                        lines: [
                            'This is a real stop! First comes a quick check — three questions.',
                            'For this tour, let’s <b>miss one on purpose</b> so I can show you the whole learn-it-together loop.',
                        ],
                        do: 'Tap “Start the quick check” to begin 👆',
                    },
                    check: {
                        title: 'Pick a wrong one — on purpose!',
                        lines: [
                            'Normally you’d answer your best. But for the tour, tap the option I’m pointing to — it’s a <b>wrong</b> answer.',
                            'That takes us into the part where we learn it together. 🐢',
                        ],
                        do: 'Tap the highlighted (wrong) answer 👆',
                    },
                    outcomeMiss: {
                        title: 'Let’s learn it together',
                        lines: [
                            'Missed one? That’s totally fine — here’s how we master it.',
                            'Pick <b>Lesson</b> to learn it step by step, then <b>Worked examples</b>, then <b>Practice</b>.',
                            'If a question keeps slipping, <b>I step in one-on-one</b> and re-teach that exact rule with you until it clicks. 🐢',
                        ],
                        do: 'Tap “Lesson” to start learning it 👆',
                    },
                    outcomeMastered: {
                        title: 'You tested out! 🎉',
                        lines: [
                            'All three, first try — this level is <b>mastered</b>, no lesson needed. ⭐',
                            'That’s the whole loop! You can replay this tour any time from your Voyage.',
                        ],
                        do: '',
                    },
                },
                get view() {
                    if (this.phase === 'outcome') { return this.mastered ? this.copy.outcomeMastered : this.copy.outcomeMiss; }
                    return this.copy[this.phase];
                },
                targetEl() {
                    if (this.phase === 'explainer') { return document.querySelector('.me-card'); }
                    if (this.phase === 'check') {
                        // Point at a WRONG option so she can miss it on purpose (tour mode exposes it).
                        return document.querySelector('[data-tour-option][data-correct=\'0\']') || document.querySelector('.me-options');
                    }
                    if (this.phase === 'outcome' && !this.mastered) { return document.querySelector('.me-choices'); }
                    return null;   // mastered → no spotlight, just a centred celebration card
                },
                hasHole: false, holeStyle: '',
                place() {
                    const el = this.targetEl();
                    if (!el) { this.hasHole = false; return; }
                    el.scrollIntoView({ behavior: 'auto', block: 'center' });
                    this.$nextTick(() => { const r = el.getBoundingClientRect(); const p = 8;
                        this.holeStyle = `top:${r.top-p}px;left:${r.left-p}px;width:${r.width+p*2}px;height:${r.height+p*2}px;`; this.hasHole = true; });
                },
                setPhase(phase, mastered) {
                    this.phase = phase;
                    if (typeof mastered !== 'undefined') { this.mastered = !!mastered; }
                    // Let the real page finish re-rendering the new phase, then spotlight it.
                    this.$nextTick(() => setTimeout(() => this.place(), 120));
                },
             }"
             x-init="
                $nextTick(() => place());
                if (window.Livewire) {
                    window.Livewire.on('lesson-phase', (e) => { const d = Array.isArray(e) ? e[0] : e; setPhase(d.phase, d.mastered); });
                    // Re-spotlight the next wrong option as the real check advances question to question.
                    window.Livewire.hook('commit', ({ succeed }) => { succeed(() => { this.$nextTick(() => setTimeout(() => this.place(), 60)); }); });
                }
                window.addEventListener('resize', () => place());
             ">
            <div class="lt-hole" x-show="hasHole" :style="holeStyle"></div>
            <div class="lt-scrim" x-show="!hasHole"></div>

            <div class="lt-card">
                <div class="lt-head">
                    <img class="lt-avatar" src="{{ $this->avatarUrl() }}" alt="Smooth the turtle">
                    <div class="lt-title" x-text="view.title"></div>
                </div>
                <ul class="lt-lines">
                    <template x-for="(line, li) in view.lines" :key="li"><li x-html="line"></li></template>
                </ul>

                {{-- While the real flow is live, Smooth points to the next real action. --}}
                <template x-if="!(phase === 'outcome' && mastered)">
                    <div>
                        <p class="lt-do" x-show="view.do" x-text="view.do"></p>
                        <button type="button" class="lt-skip" wire:click="finish">Skip the tour</button>
                    </div>
                </template>

                {{-- Mastered ends the loop: a single button closes the tour for good. --}}
                <template x-if="phase === 'outcome' && mastered">
                    <button type="button" class="lt-btn" wire:click="finish">Got it — let’s sail! ⛵</button>
                </template>
            </div>
        </div>
    @endif
</div>
