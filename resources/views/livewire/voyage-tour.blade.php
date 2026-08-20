<div>
    <style>
        /* The dim is a single ring cut around the spotlighted element (hole-punch via
           box-shadow), so the highlighted area stays fully crisp — no heavy overlay,
           no blur. A plain scrim covers the no-target intro/outro chapters. */
        .tour-scrim { position: fixed; inset: 0; z-index: 2000; background: rgba(6,20,34,0.55); }
        .tour-hole {
            position: fixed; z-index: 2000; border-radius: 14px; pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(6,20,34,0.55);
            outline: 3px solid #f6b71e; outline-offset: 3px;
            transition: top .25s ease, left .25s ease, width .25s ease, height .25s ease;
        }
        .tour-clickcatch { position: fixed; inset: 0; z-index: 2001; }
        .tour-card {
            position: fixed; left: 50%; transform: translateX(-50%); bottom: 16px; z-index: 2002;
            width: min(92vw, 360px); background: linear-gradient(160deg, #0e2438, #14324a);
            border: 1.5px solid rgba(246,183,30,0.55); border-radius: 18px;
            box-shadow: 0 14px 40px rgba(0,0,0,0.5); padding: 14px 16px 13px; color: #e6f2fb;
        }
        .tour-head { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; }
        .tour-avatar { width: 46px; height: 46px; object-fit: contain; flex: none; }
        .tour-chapter { font-size: 0.66rem; font-weight: 800; color: #fde68a; text-transform: uppercase; letter-spacing: 0.07em; }
        .tour-title { font-size: 1.08rem; font-weight: 900; line-height: 1.15; }
        .tour-lines { margin: 2px 0 11px; padding: 0; list-style: none; }
        .tour-lines li { font-size: 0.9rem; font-weight: 600; line-height: 1.42; color: #cfe8fb; margin-bottom: 5px; }
        .tour-dots { display: flex; gap: 5px; justify-content: center; margin-bottom: 11px; }
        .tour-dot { width: 7px; height: 7px; border-radius: 50%; background: rgba(255,255,255,0.22); }
        .tour-dot.is-on { background: #f6b71e; }
        .tour-nav { display: flex; align-items: center; gap: 8px; }
        .tour-btn { flex: 1; border: none; cursor: pointer; font-size: 0.95rem; font-weight: 900; padding: 11px 14px; border-radius: 999px; }
        .tour-next { background: linear-gradient(135deg, #f97316, #f6b71e); color: #241a0a; box-shadow: 0 6px 16px rgba(246,183,30,0.35); }
        .tour-back { background: rgba(255,255,255,0.08); color: #bfe6ff; flex: 0 0 auto; padding: 11px 15px; }
        .tour-skip { display: block; margin: 9px auto 0; background: none; border: none; color: #8fb6d6; font-size: 0.78rem; font-weight: 700; cursor: pointer; text-decoration: underline; }
        .tour-handoff { text-align: center; font-size: 0.98rem; font-weight: 900; color: #fde68a; margin: 4px 0 2px; }
    </style>

    @php($tour = $this->tour())
    <div
        x-data="{
            open: @entangle('open'),
            i: 0,
            hasHole: false,
            holeStyle: '',
            chapters: @js(collect($tour['chapters'])->map(fn ($c) => [
                'key' => $c['key'],
                'title' => $c['title'],
                'target' => $c['target'],
                'lines' => $c['lines'],
                'interactive' => $c['interactive'] ?? false,
                'hint' => $c['hint'] ?? 'Tap the highlighted area to keep going 👆',
                'show_tab' => $c['show_tab'] ?? null,
                'avatar' => $this->avatarUrl($c['pose']),
            ])->values()),
            get current() { return this.chapters[this.i] ?? null; },
            place() {
                const sel = this.current && this.current.target;
                const el = sel ? document.querySelector(sel) : null;
                if (!el) { this.hasHole = false; return; }
                el.scrollIntoView({ behavior: 'auto', block: 'center' });
                this.$nextTick(() => {
                    const r = el.getBoundingClientRect();
                    const pad = 6;
                    this.holeStyle = `top:${r.top - pad}px;left:${r.left - pad}px;width:${r.width + pad * 2}px;height:${r.height + pad * 2}px;`;
                    this.hasHole = true;
                });
            },
            enter() {
                // Roll the orders back up before the island hand-off, then spotlight the island.
                if (this.current && this.current.key === 'sail') {
                    if (window.Livewire) window.Livewire.dispatch('tour-collapse-orders');
                    this.$nextTick(() => setTimeout(() => this.place(), 420));
                } else if (this.current && this.current.show_tab) {
                    // Walk the student through each Orders tab: switch to it, then spotlight it.
                    if (window.Livewire) window.Livewire.dispatch('tour-show-tab', { tab: this.current.show_tab });
                    this.$nextTick(() => setTimeout(() => this.place(), 260));
                } else {
                    this.$nextTick(() => this.place());
                }
            },
            next() { if (this.i < this.chapters.length - 1) { this.i++; this.enter(); } else { this.done(); } },
            back() { if (this.i > 0) { this.i--; this.enter(); } },
            done() { this.open = false; $wire.finish(); },
            onOrdersToggled(collapsed) { if (this.current && this.current.key === 'orders' && !collapsed) { this.next(); } },
        }"
        x-init="$watch('open', v => { if (v) { i = 0; $nextTick(() => place()); } }); if (open) $nextTick(() => place());"
        @orders-toggled.window="onOrdersToggled($event.detail.collapsed)"
    >
        <template x-if="open && current">
            <div>
                {{-- Dim: a hole cut around the target, or a full scrim for intro/outro. --}}
                <div class="tour-hole" x-show="hasHole" :style="holeStyle"></div>
                <div class="tour-scrim" x-show="!hasHole"></div>
                {{-- Catch stray taps on the dimmed area so nothing behind fires mid-tour —
                     except on an interactive hand-off, where she must tap the spotlit thing. --}}
                <div class="tour-clickcatch" x-show="!current.interactive"></div>

                <div class="tour-card">
                    <div class="tour-head">
                        <img class="tour-avatar" :src="current.avatar" alt="Smooth the turtle">
                        <div>
                            <div class="tour-chapter" x-text="'Stop ' + (i + 1) + ' of ' + chapters.length"></div>
                            <div class="tour-title" x-text="current.title"></div>
                        </div>
                    </div>

                    <ul class="tour-lines">
                        <template x-for="(line, li) in current.lines" :key="li">
                            <li x-text="line"></li>
                        </template>
                    </ul>

                    <div class="tour-dots">
                        <template x-for="(c, di) in chapters" :key="di">
                            <span class="tour-dot" :class="{ 'is-on': di === i }"></span>
                        </template>
                    </div>

                    {{-- Interactive hand-off: no Next — she taps the highlighted island. --}}
                    <template x-if="current.interactive">
                        <div>
                            <p class="tour-handoff" x-text="current.hint"></p>
                            <button type="button" class="tour-skip" @click="done()">Skip the tour</button>
                        </div>
                    </template>
                    <template x-if="!current.interactive">
                        <div>
                            <div class="tour-nav">
                                <button type="button" class="tour-btn tour-back" x-show="i > 0" @click="back()">← Back</button>
                                <button type="button" class="tour-btn tour-next" @click="next()" x-text="'Next →'"></button>
                            </div>
                            <button type="button" class="tour-skip" @click="done()">Skip the tour</button>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>
