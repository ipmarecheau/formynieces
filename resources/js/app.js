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

    // ── Cuboid: change length/width/height with +/−; stacks unit cubes; volume = l×w×h. ──
    A.data('cuboidWidget', (cfg = {}) => ({
        l: cfg.l ?? 3, w: cfg.w ?? 2, h: cfg.h ?? 2, readout: '',
        init() { this.build(); },
        bump(dim, d) {
            if (dim === 'l') { this.l = Math.max(1, Math.min(6, this.l + d)); }
            else if (dim === 'w') { this.w = Math.max(1, Math.min(5, this.w + d)); }
            else { this.h = Math.max(1, Math.min(5, this.h + d)); }
            this.build();
        },
        build() {
            const cub = this.$refs.cub, cs = 22, dx = 9, dy = -13;
            cub.innerHTML = '';
            for (let k = this.h - 1; k >= 0; k--) {
                const layer = document.createElement('div');
                layer.className = 'cube-layer';
                layer.style.gridTemplateColumns = `repeat(${this.l},${cs}px)`;
                layer.style.transform = `translate(${k * dx}px,${k * dy}px)`;
                for (let i = 0; i < this.l * this.w; i++) {
                    const c = document.createElement('div');
                    c.className = 'cube' + (k > 0 ? ' ghost' : '');
                    layer.appendChild(c);
                }
                cub.appendChild(layer);
            }
            const base = this.l * this.w, vol = this.l * this.w * this.h;
            this.readout = `base = ${this.l} × ${this.w} = ${base} cm²  ·  height ${this.h}  ·  volume = ${vol} cm³`;
        },
    }));

    // ── Jug: pour with the slider; reads mL and litres. ──
    A.data('jugWidget', (cfg = {}) => ({
        ml: cfg.start ?? 0, max: cfg.max ?? 1000, readout: '', result: '', hasTarget: cfg.target != null,
        init() {
            const jug = this.$refs.jug, H = 187;
            [0, 250, 500, 750, 1000].filter((v) => v <= this.max).forEach((v) => {
                const y = H - (v / this.max * H);
                const m = document.createElement('div'); m.className = 'jug-mark'; m.style.top = y + 'px'; jug.appendChild(m);
                const l = document.createElement('div'); l.className = 'jug-mlab'; l.style.top = y + 'px'; l.textContent = v; jug.appendChild(l);
            });
            this.draw();
        },
        pour(v) { this.ml = +v; this.result = ''; this.draw(); },
        check() { if (this.hasTarget) { this.result = this.ml === cfg.target ? 'yes' : 'no'; } },
        draw() {
            const H = 187;
            this.$refs.fill.style.height = (this.ml / this.max * H) + 'px';
            const litres = (this.ml / 1000).toFixed(3).replace(/0+$/, '').replace(/\.$/, '');
            this.readout = `poured: ${this.ml} mL = ${litres || '0'} L`;
        },
    }));

    // ── Balance: click weights onto the pan; reads grams/kg; balances at the target. ──
    A.data('balanceWidget', (cfg = {}) => ({
        total: 0, target: cfg.target ?? 350, weights: cfg.weights ?? [100, 50, 20, 10], tilt: 0, readout: '', result: '',
        init() { this.draw(); },
        add(w) { this.total += w; this.result = ''; this.draw(); },
        reset() { this.total = 0; this.result = ''; this.draw(); },
        check() { this.result = this.total === this.target ? 'yes' : (this.total > this.target ? 'over' : 'under'); },
        draw() {
            this.tilt = Math.max(-11, Math.min(11, (this.total - this.target) / 40));
            this.readout = `right pan: ${this.total} g  ·  ${(this.total / 1000).toFixed(3).replace(/0+$/, '').replace(/\.$/, '') || '0'} kg`;
        },
    }));

    // ── Ruler: drag the handle; reads cm, mm and m; Check against a target. ──
    A.data('rulerWidget', (cfg = {}) => ({
        len: cfg.start ?? 3.0, cm: cfg.cm ?? 15, readout: '', result: '', hasTarget: cfg.target != null,
        init() {
            const rul = this.$refs.rul, PX = 30;
            for (let mmi = 0; mmi <= this.cm * 10; mmi++) {
                const x = mmi / 10 * PX;
                const t = document.createElement('div'); t.className = 'rul-tick ' + (mmi % 10 === 0 ? 'cm' : 'mm'); t.style.left = x + 'px'; rul.appendChild(t);
                if (mmi % 10 === 0) { const l = document.createElement('div'); l.className = 'rul-lab'; l.style.left = x + 'px'; l.textContent = mmi / 10; rul.appendChild(l); }
            }
            this.rib = document.createElement('div'); this.rib.className = 'rul-ribbon'; rul.appendChild(this.rib);
            this.h = document.createElement('div'); this.h.className = 'rul-handle'; rul.appendChild(this.h);
            draggable(this.h, (cx) => { const r = rul.getBoundingClientRect(); let px = cx - r.left; px = Math.max(0, Math.min(this.cm * PX, px)); this.len = Math.round(px / PX * 10) / 10; this.result = ''; this.draw(); });
            this.draw();
        },
        check() { if (this.hasTarget) { this.result = Math.abs(this.len - cfg.target) < 0.05 ? 'yes' : 'no'; } },
        draw() {
            const PX = 30, px = this.len * PX;
            this.rib.style.width = px + 'px'; this.h.style.left = px + 'px';
            this.readout = `length: ${this.len.toFixed(1)} cm = ${Math.round(this.len * 10)} mm = ${(this.len / 100).toFixed(3)} m`;
        },
    }));

    // ── Angle: drag the ray to a target angle in a real context; Check. ──
    A.data('angleWidget', (cfg = {}) => ({
        deg: cfg.start ?? 45, readout: '', result: '', hasTarget: cfg.target != null,
        init() {
            const svg = this.$refs.ang, V = { x: 130, y: 130 }, L = 100;
            svg.appendChild(el('line', { x1: V.x, y1: V.y, x2: V.x + L, y2: V.y, class: 'ang-base' }));
            [[90, '¼'], [180, '½'], [270, '¾']].forEach(([d, t]) => {
                const a = d * Math.PI / 180, r1 = L * 0.55, r2 = L * 0.55 + 9;
                svg.appendChild(el('line', { x1: V.x + r1 * Math.cos(a), y1: V.y - r1 * Math.sin(a), x2: V.x + r2 * Math.cos(a), y2: V.y - r2 * Math.sin(a), stroke: '#7d8a97', 'stroke-width': 2, 'stroke-dasharray': '2 3' }));
                const lt = el('text', { x: V.x + (L * 0.55 + 20) * Math.cos(a), y: V.y - (L * 0.55 + 20) * Math.sin(a) + 5, class: 'ang-num' }); lt.textContent = t; svg.appendChild(lt);
            });
            this.arc = el('path', { class: 'ang-arc' });
            this.ray = el('line', { class: 'ang-ray' });
            this.grab = el('circle', { r: 9, class: 'clk-grab' });
            this.lab = el('text', { class: 'ang-lab' });
            svg.append(this.arc, this.ray, el('circle', { cx: V.x, cy: V.y, r: 5, class: 'clk-cap' }), this.grab, this.lab);
            this.V = V; this.L = L;
            draggable(this.grab, (cx, cy) => { const p = svgPoint(svg, cx, cy); let a = Math.atan2(V.y - p.y, p.x - V.x) * 180 / Math.PI; if (a < 0) a += 360; this.deg = (Math.round(a / 5) * 5) % 360; this.result = ''; this.draw(); });
            this.draw();
        },
        check() { if (this.hasTarget) { this.result = this.deg === cfg.target ? 'yes' : 'no'; } },
        draw() {
            const V = this.V, L = this.L, a = this.deg * Math.PI / 180;
            const ex = V.x + L * Math.cos(a), ey = V.y - L * Math.sin(a);
            this.ray.setAttribute('x1', V.x); this.ray.setAttribute('y1', V.y); this.ray.setAttribute('x2', ex); this.ray.setAttribute('y2', ey);
            this.grab.setAttribute('cx', ex); this.grab.setAttribute('cy', ey);
            const ar = 34, bx = V.x + ar, ax2 = V.x + ar * Math.cos(a), ay2 = V.y - ar * Math.sin(a);
            this.arc.setAttribute('d', `M ${bx} ${V.y} A ${ar} ${ar} 0 ${this.deg > 180 ? 1 : 0} 0 ${ax2} ${ay2}`);
            this.lab.setAttribute('x', V.x + 50 * Math.cos(a / 2)); this.lab.setAttribute('y', V.y - 50 * Math.sin(a / 2) + 5); this.lab.textContent = this.deg + '°';
            const name = this.deg === 0 ? 'no turn yet' : this.deg < 90 ? 'acute — less than a right angle' : this.deg === 90 ? 'a RIGHT angle — a quarter turn' : this.deg < 180 ? 'obtuse — more than a right angle' : this.deg === 180 ? 'a straight angle — a half turn' : 'a reflex angle';
            this.readout = `turn: ${this.deg}° — ${name}`;
        },
    }));

    // ── Solids: tap a solid to SEE it and read its faces, edges and vertices. ──
    A.data('solidsWidget', () => ({
        sel: null, readout: '',
        icons: {
            cube: '<svg width="72" height="66" viewBox="0 0 48 44"><g class="solid-ic"><rect x="8" y="14" width="24" height="24"/><path class="b" d="M8 14 L16 6 L40 6 L32 14"/><path class="b" d="M32 14 L40 6 L40 30 L32 38"/></g></svg>',
            cuboid: '<svg width="72" height="66" viewBox="0 0 48 44"><g class="solid-ic"><rect x="6" y="18" width="30" height="18"/><path class="b" d="M6 18 L14 10 L44 10 L36 18"/><path class="b" d="M36 18 L44 10 L44 28 L36 36"/></g></svg>',
            cylinder: '<svg width="72" height="66" viewBox="0 0 48 44"><g class="solid-ic"><ellipse cx="24" cy="10" rx="12" ry="5"/><path d="M12 10 V34 a12 5 0 0 0 24 0 V10"/></g></svg>',
            cone: '<svg width="72" height="66" viewBox="0 0 48 44"><g class="solid-ic"><path d="M24 6 L36 34 a12 5 0 0 1 -24 0 Z"/><ellipse class="b" cx="24" cy="34" rx="12" ry="5"/></g></svg>',
            sphere: '<svg width="72" height="66" viewBox="0 0 48 44"><g class="solid-ic"><circle cx="24" cy="22" r="14"/><ellipse class="b" cx="24" cy="22" rx="14" ry="5"/></g></svg>',
            pyramid: '<svg width="72" height="66" viewBox="0 0 48 44"><g class="solid-ic"><path d="M24 6 L40 36 L8 36 Z"/><path class="b" d="M8 36 L18 30 L44 30 L40 36"/><path class="b" d="M24 6 L44 30"/></g></svg>',
            prism: '<svg width="72" height="66" viewBox="0 0 48 44"><g class="solid-ic"><path d="M14 34 L24 12 L34 34 Z"/><path class="b" d="M14 34 L22 40 L42 40 L34 34"/><path class="b" d="M24 12 L32 18 L42 40"/><path class="b" d="M34 34 L42 40"/></g></svg>',
        },
        solids: [
            { id: 'cube', n: 'Cube', f: 6, e: 12, v: 8, note: '6 square faces' },
            { id: 'cuboid', n: 'Cuboid', f: 6, e: 12, v: 8, note: '6 rectangular faces' },
            { id: 'cylinder', n: 'Cylinder', f: 3, e: 2, v: 0, note: '2 flat circles + 1 curved face' },
            { id: 'cone', n: 'Cone', f: 2, e: 1, v: 1, note: '1 circle + 1 curved face, 1 apex' },
            { id: 'sphere', n: 'Sphere', f: 1, e: 0, v: 0, note: '1 curved surface' },
            { id: 'pyramid', n: 'Square pyramid', f: 5, e: 8, v: 5, note: 'a square base + 4 triangles' },
            { id: 'prism', n: 'Triangular prism', f: 5, e: 9, v: 6, note: '2 triangles + 3 rectangles' },
        ],
        iconFor(id) { return this.icons[id] ?? ''; },
        selIcon() { return this.sel ? this.icons[this.sel] : ''; },
        pick(s) { this.sel = s.id; this.readout = `${s.n} — ${s.f} faces, ${s.e} edges, ${s.v} vertices. ${s.note}.`; },
    }));

    // ── Lines of symmetry: GUIDED, one shape at a time. Tap the fold lines, Check, advance. ──
    A.data('symmetryWidget', () => ({
        order: ['square', 'rectangle', 'triangleEq'],
        idx: 0, chosen: [], readout: '', prompt: '',
        shapes: {
            square: { name: 'square', correct: ['V', 'H', 'D1', 'D2'] },
            rectangle: { name: 'rectangle', correct: ['V', 'H'] },
            triangleEq: { name: 'triangle', correct: ['V'] },
        },
        lines: { V: [80, 12, 80, 148], H: [12, 80, 148, 80], D1: [24, 24, 136, 136], D2: [136, 24, 24, 136] },
        init() { this.setPrompt(); this.draw(); },
        cur() { return this.order[this.idx]; },
        setPrompt() { this.prompt = `Shape ${this.idx + 1} of ${this.order.length}: tap every fold line that makes a mirror image of the ${this.shapes[this.cur()].name}, then Check.`; },
        toggle(dir) { const i = this.chosen.indexOf(dir); if (i >= 0) { this.chosen.splice(i, 1); } else { this.chosen.push(dir); } this.readout = ''; this.draw(); },
        check() {
            const S = this.shapes[this.cur()];
            const a = [...this.chosen].sort().join(','), b = [...S.correct].sort().join(',');
            if (a !== b) { this.readout = 'no:Not quite — for each dashed line, would the two halves match if you folded on it? Try again.'; return; }
            const done = this.idx === this.order.length - 1;
            this.readout = `yes:The ${S.name} has ${S.correct.length} line${S.correct.length > 1 ? 's' : ''} of symmetry.` + (done ? ' You found them all!' : ' Now the next shape…');
            if (!done) { setTimeout(() => { this.idx++; this.chosen = []; this.readout = ''; this.setPrompt(); this.draw(); }, 1400); }
        },
        draw() {
            const svg = this.$refs.sym; svg.innerHTML = '';
            const shape = this.cur();
            if (shape === 'triangleEq') { svg.appendChild(el('polygon', { points: '80,20 140,140 20,140', class: 'los-shape' })); }
            else if (shape === 'rectangle') { svg.appendChild(el('rect', { x: 26, y: 54, width: 108, height: 52, rx: 6, class: 'los-shape' })); }
            else { svg.appendChild(el('rect', { x: 40, y: 40, width: 80, height: 80, rx: 6, class: 'los-shape' })); }
            const dirs = shape === 'triangleEq' ? ['V'] : ['V', 'H', 'D1', 'D2'];
            dirs.forEach((dir) => {
                const c = this.lines[dir];
                svg.appendChild(el('line', { x1: c[0], y1: c[1], x2: c[2], y2: c[3], class: 'los-cand' + (this.chosen.includes(dir) ? ' on' : '') }));
                const hit = el('line', { x1: c[0], y1: c[1], x2: c[2], y2: c[3], class: 'los-hit' });
                hit.style.cursor = 'pointer';
                hit.addEventListener('click', () => this.toggle(dir));
                svg.appendChild(hit);
            });
        },
    }));
});
