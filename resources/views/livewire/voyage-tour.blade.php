<div>
    <style>
        .tour-overlay { position: fixed; inset: 0; z-index: 2000; background: rgba(6,20,34,0.72); backdrop-filter: blur(2px); display: flex; align-items: flex-end; justify-content: center; padding: 18px; }
        @media (min-width: 720px) { .tour-overlay { align-items: center; } }
        .tour-card { width: 100%; max-width: 440px; background: linear-gradient(160deg, #0e2438, #14324a); border: 1.5px solid rgba(246,183,30,0.5); border-radius: 20px; box-shadow: 0 18px 50px rgba(0,0,0,0.5); padding: 20px 20px 18px; color: #e6f2fb; }
        .tour-head { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
        .tour-avatar { width: 58px; height: 58px; object-fit: contain; flex: none; }
        .tour-chapter { font-size: 0.72rem; font-weight: 800; color: #fde68a; text-transform: uppercase; letter-spacing: 0.08em; }
        .tour-title { font-size: 1.25rem; font-weight: 900; letter-spacing: -0.01em; }
        .tour-lines { margin: 4px 0 16px; padding: 0; list-style: none; }
        .tour-lines li { font-size: 0.98rem; font-weight: 600; line-height: 1.5; color: #cfe8fb; margin-bottom: 8px; }
        .tour-dots { display: flex; gap: 6px; justify-content: center; margin-bottom: 14px; }
        .tour-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.22); }
        .tour-dot.is-on { background: #f6b71e; }
        .tour-nav { display: flex; align-items: center; gap: 10px; }
        .tour-btn { flex: 1; border: none; cursor: pointer; font-size: 1rem; font-weight: 900; padding: 12px 16px; border-radius: 999px; }
        .tour-next { background: linear-gradient(135deg, #f97316, #f6b71e); color: #241a0a; box-shadow: 0 8px 20px rgba(246,183,30,0.35); }
        .tour-back { background: rgba(255,255,255,0.08); color: #bfe6ff; flex: 0 0 auto; padding: 12px 18px; }
        .tour-skip { display: block; margin: 12px auto 0; background: none; border: none; color: #8fb6d6; font-size: 0.82rem; font-weight: 700; cursor: pointer; text-decoration: underline; }
        .tour-spotlight { position: relative; z-index: 2001; outline: 3px solid #f6b71e; outline-offset: 4px; border-radius: 12px; box-shadow: 0 0 0 9999px rgba(6,20,34,0.55); transition: outline-color 0.2s; }
    </style>

    @php($tour = $this->tour())
    <div
        x-data="{
            open: @entangle('open'),
            i: 0,
            chapters: @js(collect($tour['chapters'])->map(fn ($c) => [
                'title' => $c['title'],
                'target' => $c['target'],
                'lines' => $c['lines'],
                'avatar' => $this->avatarUrl($c['pose']),
            ])->values()),
            get current() { return this.chapters[this.i] ?? null; },
            clearSpot() { document.querySelectorAll('.tour-spotlight').forEach(el => el.classList.remove('tour-spotlight')); },
            spot() {
                this.clearSpot();
                const sel = this.current && this.current.target;
                if (!sel) return;
                const el = document.querySelector(sel);
                if (el) { el.classList.add('tour-spotlight'); el.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
            },
            next() { if (this.i < this.chapters.length - 1) { this.i++; this.$nextTick(() => this.spot()); } else { this.done(); } },
            back() { if (this.i > 0) { this.i--; this.$nextTick(() => this.spot()); } },
            done() { this.clearSpot(); this.open = false; $wire.finish(); },
        }"
        x-init="$watch('open', v => { if (v) { i = 0; $nextTick(() => spot()); } else { clearSpot(); } }); if (open) $nextTick(() => spot());"
    >
        <template x-if="open && current">
            <div class="tour-overlay" @keydown.escape.window="done()">
                <div class="tour-card" @click.stop>
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

                    <div class="tour-nav">
                        <button type="button" class="tour-btn tour-back" x-show="i > 0" @click="back()">← Back</button>
                        <button type="button" class="tour-btn tour-next" @click="next()"
                                x-text="i < chapters.length - 1 ? 'Next →' : 'Set sail! ⛵'"></button>
                    </div>
                    <button type="button" class="tour-skip" x-show="i < chapters.length - 1" @click="done()">Skip the tour</button>
                </div>
            </div>
        </template>
    </div>
</div>
