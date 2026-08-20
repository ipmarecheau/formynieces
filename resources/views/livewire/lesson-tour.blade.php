<div>
    @if ($open)
        <style>
            .lt-hole { position: fixed; z-index: 2000; border-radius: 12px; pointer-events: none; box-shadow: 0 0 0 9999px rgba(20,6,34,0.6); outline: 3px solid #f0abfc; outline-offset: 4px; transition: all .25s ease; }
            .lt-scrim { position: fixed; inset: 0; z-index: 2000; background: rgba(20,6,34,0.55); }
            .lt-card { position: fixed; left: 50%; transform: translateX(-50%); bottom: 16px; z-index: 2002; width: min(94vw, 400px); background: linear-gradient(160deg, #241436, #3a1f52); border: 1.5px solid rgba(240,171,252,0.5); border-radius: 18px; box-shadow: 0 14px 40px rgba(0,0,0,0.5); padding: 15px 17px 14px; color: #f3e8ff; }
            .lt-head { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
            .lt-avatar { width: 48px; height: 48px; object-fit: contain; flex: none; }
            .lt-title { font-size: 1.1rem; font-weight: 900; color: #f0abfc; }
            .lt-lines { margin: 2px 0 12px; padding: 0; list-style: none; }
            .lt-lines li { font-size: 0.9rem; font-weight: 600; line-height: 1.45; color: #ecd9ff; margin-bottom: 6px; }
            .lt-lines b { color: #f0abfc; }
            .lt-dots { display: flex; gap: 5px; justify-content: center; margin-bottom: 2px; }
            .lt-dot { width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,0.22); }
            .lt-dot.is-on { background: #f0abfc; }
            .lt-btn { width: 100%; border: none; cursor: pointer; font-size: 0.98rem; font-weight: 900; padding: 12px 16px; border-radius: 999px; background: linear-gradient(135deg, #a855f7, #f0abfc); color: #2a0a3a; box-shadow: 0 6px 16px rgba(168,85,247,0.35); }
            .lt-skip { display: block; margin: 9px auto 0; background: none; border: none; color: #c4a3d6; font-size: 0.78rem; font-weight: 700; cursor: pointer; text-decoration: underline; }

            /* sample-check demo */
            .lt-quiz-q { font-size: 0.95rem; font-weight: 800; color: #fff; margin: 2px 0 10px; }
            .lt-quiz-q i { color: #f0abfc; font-style: italic; }
            .lt-opts { display: flex; flex-direction: column; gap: 8px; margin-bottom: 4px; }
            .lt-opt { width: 100%; text-align: left; cursor: pointer; font-size: 0.92rem; font-weight: 800; padding: 11px 14px; border-radius: 12px; background: rgba(255,255,255,0.08); border: 1.5px solid rgba(240,171,252,0.35); color: #f3e8ff; transition: background .15s, border-color .15s; }
            .lt-opt:hover { background: rgba(240,171,252,0.18); }
            .lt-opt:disabled { cursor: default; }
            .lt-opt.is-right { background: rgba(52,211,153,0.22); border-color: #34d399; color: #d1fae5; }
            .lt-opt.is-wrong { background: rgba(248,113,113,0.2); border-color: #f87171; color: #fecaca; }
            .lt-opt .lt-mark { float: right; font-weight: 900; }
            .lt-flag { display: flex; gap: 8px; align-items: flex-start; border-radius: 12px; padding: 10px 12px; margin: 2px 0 12px; font-size: 0.86rem; font-weight: 700; line-height: 1.4; }
            .lt-flag.good { background: rgba(52,211,153,0.15); border: 1px solid rgba(52,211,153,0.4); color: #a7f3d0; }
            .lt-flag.help { background: rgba(96,165,250,0.15); border: 1px solid rgba(96,165,250,0.4); color: #bfdbfe; }
            .lt-flag .lt-flag-ico { font-size: 1.05rem; flex: none; }
            .lt-hint { font-size: 0.82rem; color: #c4a3d6; text-align: center; margin-top: 8px; }
        </style>

        <div x-data="{
                phase: 'intro',            // intro → quiz → wrong → practice → outro
                answered: null,            // 'right' | 'wrong'
                phases: ['intro','quiz','wrong','practice','outro'],
                introLines: ['Every stop starts the same friendly way — a quick check.', 'Ace it and you’ve already mastered the level. Let’s try one together!'],
                titles: { intro:'The learning loop', quiz:'Your turn — a quick check!', wrong:'A miss? No worries at all', practice:'Nailed it — off to Practice!', outro:'You’ve got the whole loop!' },
                pick(choice) {
                    if (this.answered) { return; }
                    this.answered = choice;
                    // A right answer jumps to the mastered→practice path; a miss shows the re-teach path first.
                    setTimeout(() => { this.phase = (choice === 'right') ? 'practice' : 'wrong'; }, 750);
                },
                dotIndex() { return this.phases.indexOf(this.phase); },
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
                    <div class="lt-title" x-text="titles[phase]"></div>
                </div>

                {{-- INTRO --}}
                <template x-if="phase === 'intro'">
                    <div>
                        <ul class="lt-lines"><template x-for="(line, li) in introLines" :key="li"><li x-text="line"></li></template></ul>
                        <button type="button" class="lt-btn" @click="phase = 'quiz'">Show me →</button>
                        <button type="button" class="lt-skip" wire:click="finish">Skip the tour</button>
                    </div>
                </template>

                {{-- QUIZ — a real, tiny sample check she answers --}}
                <template x-if="phase === 'quiz'">
                    <div>
                        <p class="lt-quiz-q">What’s the plural of <i>baby</i>?</p>
                        <div class="lt-opts">
                            <button type="button" class="lt-opt" :class="{ 'is-wrong': answered === 'wrong' }" @click="pick('wrong')" :disabled="answered">
                                babys <span class="lt-mark" x-show="answered === 'wrong'">✗</span>
                            </button>
                            <button type="button" class="lt-opt" :class="{ 'is-right': answered === 'right' }" @click="pick('right')" :disabled="answered">
                                babies <span class="lt-mark" x-show="answered === 'right'">✓</span>
                            </button>
                        </div>
                        <p class="lt-hint" x-show="!answered">Tap an answer to see what happens 👆</p>
                        <button type="button" class="lt-skip" wire:click="finish" x-show="!answered">Skip the tour</button>
                    </div>
                </template>

                {{-- WRONG PATH — lesson + worked examples + one-on-one AI re-teach --}}
                <template x-if="phase === 'wrong'">
                    <div>
                        <div class="lt-flag help">
                            <span class="lt-flag-ico">💛</span>
                            <span>A miss is just the start of learning — here’s what I do next.</span>
                        </div>
                        <ul class="lt-lines">
                            <li>First I give you a <b>lesson</b> and <b>worked examples</b> that walk the rule through step by step.</li>
                            <li>Still tricky after two tries? <b>I step in one-on-one</b> — we take the same rule together, word by word, until it clicks. 🐢</li>
                            <li>Then you re-answer and carry on. A miss never counts against you.</li>
                        </ul>
                        <button type="button" class="lt-btn" @click="phase = 'practice'">And if I ace it? →</button>
                        <button type="button" class="lt-skip" wire:click="finish">Skip the tour</button>
                    </div>
                </template>

                {{-- PRACTICE PATH — all correct → straight to practice --}}
                <template x-if="phase === 'practice'">
                    <div>
                        <div class="lt-flag good">
                            <span class="lt-flag-ico">🎉</span>
                            <span x-text="answered === 'right' ? 'All correct — mastered! You skip straight ahead.' : 'When you ace the check, you skip straight ahead.'"></span>
                        </div>
                        <ul class="lt-lines">
                            <li>Get the check right and you go <b>straight to Practice</b> — no lesson needed.</li>
                            <li>In Practice you climb from easy to tricky, always with a second try.</li>
                            <li>Land <b>three tricky ones in a row</b> and the level is yours! ⭐</li>
                        </ul>
                        <button type="button" class="lt-btn" @click="phase = 'outro'">Next →</button>
                        <button type="button" class="lt-skip" wire:click="finish">Skip the tour</button>
                    </div>
                </template>

                {{-- OUTRO --}}
                <template x-if="phase === 'outro'">
                    <div>
                        <ul class="lt-lines">
                            <li>That’s the whole loop: <b>check → lesson → examples → practice</b> — with me alongside the whole way.</li>
                            <li>Try this stop for real now, then tap <b>Back to the sea</b> to keep sailing. You can replay this tour any time from your Voyage.</li>
                        </ul>
                        <button type="button" class="lt-btn" wire:click="finish">Got it — let’s sail! ⛵</button>
                    </div>
                </template>

                {{-- progress dots --}}
                <div class="lt-dots" style="margin-top:11px;">
                    <template x-for="(p, di) in phases" :key="di"><span class="lt-dot" :class="{ 'is-on': di === dotIndex() }"></span></template>
                </div>
            </div>
        </div>
    @endif
</div>
