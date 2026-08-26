// Livewire v3 ships and starts its own Alpine (via @livewireScripts), and exposes
// it globally as window.Alpine. Importing and starting a second Alpine here made two
// instances fight over any tree that mixes x-data with Livewire directives
// (wire:click / $wire / @entangle) — silently breaking those bindings. The tour
// components are exactly such trees, which is why "Skip the tour" did nothing and the
// lesson tour stayed stuck after re-login. Rely on Livewire's Alpine; register any
// Alpine plugins here via a `livewire:init` listener + Alpine.plugin(), never a second start.

// Interactive lesson widgets (ported from the Measurement Studio prototype). Registered on
// Livewire's Alpine instance so a lesson block can do x-data="clockWidget({...})". Each is a
// self-contained draggable manipulative — the concept the child DRIVES, not a static picture.
document.addEventListener('alpine:init', () => {
    const A = window.Alpine;
    if (!A) {
        return;
    }

    const svgNS = 'http://www.w3.org/2000/svg';
    const el = (name, attrs = {}) => {
        const n = document.createElementNS(svgNS, name);
        for (const [k, v] of Object.entries(attrs)) {
            n.setAttribute(k, v);
        }
        return n;
    };
    // Convert a client point to the svg's viewBox coordinates.
    const svgPoint = (svg, cx, cy) => {
        const r = svg.getBoundingClientRect();
        const vb = svg.viewBox.baseVal;
        return { x: (cx - r.left) / r.width * vb.width, y: (cy - r.top) / r.height * vb.height };
    };
    const draggable = (node, onMove) => {
        const move = (e) => { const p = e.touches ? e.touches[0] : e; onMove(p.clientX, p.clientY); };
        const up = () => { window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up); };
        node.style.cursor = 'grab';
        node.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            window.addEventListener('pointermove', move);
            window.addEventListener('pointerup', up);
        });
    };

    // ── Clock: drag the gold tips to set the time; reads 12-hour + 24-hour. ──
    A.data('clockWidget', (cfg = {}) => ({
        hh: cfg.hour ?? 12,
        mm: cfg.minute ?? 0,
        pm: cfg.pm ?? true,
        readout: '',
        result: '',
        hasTarget: cfg.targetH != null,
        init() {
            const svg = this.$refs.clk;
            const C = 100, R = 92;
            svg.appendChild(el('circle', { cx: C, cy: C, r: R, class: 'clk-face' }));
            for (let i = 0; i < 60; i++) {
                const a = i * 6 * Math.PI / 180, big = i % 5 === 0;
                const r1 = big ? R - 12 : R - 7, r2 = R - 2;
                svg.appendChild(el('line', { x1: C + r1 * Math.sin(a), y1: C - r1 * Math.cos(a), x2: C + r2 * Math.sin(a), y2: C - r2 * Math.cos(a), class: 'clk-tick' + (big ? ' big' : '') }));
            }
            for (let n = 1; n <= 12; n++) {
                const a = n * 30 * Math.PI / 180, rr = R - 26;
                const t = el('text', { x: C + rr * Math.sin(a), y: C - rr * Math.cos(a), class: 'clk-num' });
                t.textContent = n; svg.appendChild(t);
            }
            this.handH = el('line', { class: 'clk-hand-h' });
            this.handM = el('line', { class: 'clk-hand-m' });
            this.grabH = el('circle', { r: 9, class: 'clk-grab' });
            this.grabM = el('circle', { r: 8, class: 'clk-grab' });
            svg.append(this.handH, this.handM, this.grabH, this.grabM);
            svg.appendChild(el('circle', { cx: C, cy: C, r: 5, class: 'clk-cap' }));
            const angleOf = (cx, cy) => { const p = svgPoint(svg, cx, cy); let a = Math.atan2(p.x - C, -(p.y - C)) * 180 / Math.PI; if (a < 0) a += 360; return a; };
            // Each hand drags on its own; the minute hand does NOT drive the hour hand.
            draggable(this.grabH, (x, y) => { const h = Math.round(angleOf(x, y) / 30) % 12; this.hh = h === 0 ? 12 : h; this.result = ''; this.draw(); });
            draggable(this.grabM, (x, y) => { this.mm = (Math.round(angleOf(x, y) / 6 / 5) * 5) % 60; this.result = ''; this.draw(); });
            this.draw();
        },
        toggleAmPm() { this.pm = !this.pm; this.result = ''; this.draw(); },
        check() {
            if (!this.hasTarget) { return; }
            const h12 = this.hh % 12 === 0 ? 12 : this.hh % 12;
            const ok = h12 === cfg.targetH && this.mm === cfg.targetM && (cfg.targetPm == null || this.pm === cfg.targetPm);
            this.result = ok ? 'yes' : 'no';
        },
        draw() {
            const C = 100, lh = 48, lm = 72;
            // Decoupled: the hour hand points at its whole hour, the minute hand at its minute.
            const ah = (this.hh % 12) * 30 * Math.PI / 180, am = this.mm * 6 * Math.PI / 180;
            this.handH.setAttribute('x1', C); this.handH.setAttribute('y1', C);
            this.handH.setAttribute('x2', C + lh * Math.sin(ah)); this.handH.setAttribute('y2', C - lh * Math.cos(ah));
            this.handM.setAttribute('x1', C); this.handM.setAttribute('y1', C);
            this.handM.setAttribute('x2', C + lm * Math.sin(am)); this.handM.setAttribute('y2', C - lm * Math.cos(am));
            this.grabH.setAttribute('cx', C + lh * Math.sin(ah)); this.grabH.setAttribute('cy', C - lh * Math.cos(ah));
            this.grabM.setAttribute('cx', C + lm * Math.sin(am)); this.grabM.setAttribute('cy', C - lm * Math.cos(am));
            const h12 = this.hh % 12 === 0 ? 12 : this.hh % 12, mmS = String(this.mm).padStart(2, '0');
            const h24 = String((this.pm ? (h12 % 12) + 12 : h12 % 12)).padStart(2, '0');
            this.readout = `${h12}:${mmS} ${this.pm ? 'PM' : 'AM'}  ·  24-hour ${h24}:${mmS}`;
        },
    }));
});
