@props([
    'fx' => 50,     // focus point X as a % of the map canvas (her current stop)
    'fy' => 50,     // focus point Y as a % of the map canvas
    'mapW' => 2752, // the map canvas's intrinsic dimensions — used to DERIVE the
    'mapH' => 1536, // layer height (CW / aspect) rather than measure it.
])

{{-- AM-09: a bounded, pannable, zoomable window onto a large map.

     Centering is done by CSS: the viewport is a flex box that centres the layer,
     and the layer scales/translates from its CENTRE. JS only manages the pan OFFSET
     (tx, ty) from that centre, clamped symmetrically. So whenever the map is shorter
     than the window, the offset is clamped to 0 and flexbox centres it exactly —
     always, immediately, throughout the whole zoom process (no timing gap).

     Zoom range: min = image width fits the window exactly; max = 2x that. --}}
<div
    class="mv-viewport"
    x-data="{
        scale: 1, minScale: 1, maxScale: 2, tx: 0, ty: 0,
        fx: {{ (float) $fx }}, fy: {{ (float) $fy }},
        aspect: {{ (float) $mapW }} / {{ (float) $mapH }},
        dragging: false, moved: false, px: 0, py: 0, sx0: 0, sy0: 0,
        atEdge: false, edgeT: null, hint: true,
        init() {
            this.$nextTick(() => this.fit(true));
            setTimeout(() => this.fit(true), 60);
            setTimeout(() => this.fit(true), 250);
            window.addEventListener('resize', () => this.fit(true));
        },
        dims() {
            const vp = this.$el.getBoundingClientRect();
            const cw = this.$refs.layer.offsetWidth;   // reliable (100% of viewport)
            return { vw: vp.width, vh: vp.height, cw: cw, ch: cw / this.aspect };
        },
        fit(reset) {
            const d = this.dims();
            if (!d.cw) { return; }
            this.minScale = d.vw / d.cw;      // width fits exactly (max zoom-out)
            this.maxScale = this.minScale * 2; // max zoom-in = 2x
            if (reset || this.scale < this.minScale || this.scale > this.maxScale) {
                this.scale = this.minScale; this.tx = 0; this.ty = 0;
            }
            this.clampPan();
        },
        // Half the overflow past the window edge, per axis. Zero when the map is
        // smaller than the window on that axis — so the offset pins to 0 and CSS
        // flexbox centres it.
        clampPan() {
            const d = this.dims();
            const maxTx = Math.max(0, (d.cw * this.scale - d.vw) / 2);
            const maxTy = Math.max(0, (d.ch * this.scale - d.vh) / 2);
            const wx = this.tx, wy = this.ty;
            this.tx = Math.max(-maxTx, Math.min(maxTx, this.tx));
            this.ty = Math.max(-maxTy, Math.min(maxTy, this.ty));
            if (this.dragging && (wx !== this.tx || wy !== this.ty)) { this.flashEdge(); }
        },
        flashEdge() {
            this.atEdge = true;
            clearTimeout(this.edgeT);
            this.edgeT = setTimeout(() => { this.atEdge = false; }, 320);
        },
        zoom(f) {
            this.scale = Math.min(this.maxScale, Math.max(this.minScale, this.scale * f));
            this.clampPan();
        },
        findMe() {
            const d = this.dims();
            // Zoom enough to fill the window (no padding), capped at max zoom.
            const cover = Math.max(d.vw / d.cw, d.vh / d.ch);
            this.scale = Math.min(this.maxScale, Math.max(this.minScale, cover));
            // Offset so the focus point (a fraction of the map, measured from its
            // centre) lands at the window centre; clampPan keeps it within bounds.
            this.tx = -((this.fx / 100 - 0.5) * d.cw * this.scale);
            this.ty = -((this.fy / 100 - 0.5) * d.ch * this.scale);
            this.clampPan();
        },
        down(e) {
            this.dragging = true; this.moved = false; this.hint = false;
            this.px = e.clientX; this.py = e.clientY; this.sx0 = e.clientX; this.sy0 = e.clientY;
        },
        pan(e) {
            if (!this.dragging) { return; }
            this.tx += e.clientX - this.px; this.ty += e.clientY - this.py;
            this.px = e.clientX; this.py = e.clientY;
            if (Math.hypot(e.clientX - this.sx0, e.clientY - this.sy0) > 6) { this.moved = true; }
            this.clampPan();
        },
        up() { this.dragging = false; },
        onwheel(e) { this.hint = false; this.zoom(e.deltaY < 0 ? 1.15 : 0.87); },
    }"
    x-bind:class="{ 'mv-atedge': atEdge }"
    x-on:pointermove.window="pan($event)"
    x-on:pointerup.window="up()"
>
    <div
        class="mv-layer"
        x-ref="layer"
        x-bind:style="`transform: translate(${tx}px, ${ty}px) scale(${scale});`"
        x-on:pointerdown="down($event)"
        x-on:wheel.prevent="onwheel($event)"
        x-on:click.capture="if (moved) { $event.preventDefault(); $event.stopPropagation(); }"
    >
        {{ $slot }}
    </div>

    <div class="mv-nav-hint" x-show="hint" x-transition.opacity>✥ Drag to explore</div>

    <div class="mv-controls">
        <button type="button" class="mv-btn" x-on:click="zoom(1.3)" aria-label="Zoom in">＋</button>
        <button type="button" class="mv-btn" x-on:click="zoom(0.77)" aria-label="Zoom out">－</button>
        <button type="button" class="mv-btn mv-findme" x-on:click="findMe()" aria-label="Zoom to my progress">🐢 Find me</button>
    </div>

    <style>
        .mv-viewport {
            position: relative;
            width: 100%;
            height: 100%;
            min-height: 300px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 18px 50px rgba(0,0,0,0.45);
            background: linear-gradient(180deg, #12204d, #0d3a5a);
            touch-action: none;
            cursor: grab;
            /* Flexbox centres the layer on BOTH axes — this is what guarantees the
               map is centred whenever the pan offset is 0. */
            display: flex; align-items: center; justify-content: center;
            outline: 0 solid rgba(248,113,113,0);
            transition: outline 0.12s ease;
        }
        .mv-viewport:active { cursor: grabbing; }
        .mv-viewport.mv-atedge { outline: 3px solid rgba(248,113,113,0.9); outline-offset: -3px; animation: mvBounce 0.3s ease; }
        @keyframes mvBounce { 0%,100% { } 50% { box-shadow: inset 0 0 40px rgba(248,113,113,0.35), 0 18px 50px rgba(0,0,0,0.45); } }

        .mv-layer {
            flex: 0 0 auto;
            width: 100%;
            transform-origin: center center;
            will-change: transform;
        }
        .mv-layer > .vy-map {
            width: 100%; margin: 0; border-radius: 0; box-shadow: none;
            container-type: inline-size;
        }

        .mv-nav-hint {
            position: absolute; top: 12px; left: 12px; z-index: 5;
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.78rem;
            color: #e6f2fb; background: rgba(12,20,50,0.72);
            padding: 6px 12px; border-radius: 999px; pointer-events: none;
            border: 1.5px solid rgba(147,197,253,0.5);
        }

        .mv-controls {
            position: absolute; right: 12px; bottom: 12px; z-index: 5;
            display: flex; flex-direction: column; gap: 8px; align-items: flex-end;
        }
        .mv-btn {
            font-family: 'Fredoka One', cursive; font-size: 1.1rem; line-height: 1;
            width: 42px; height: 42px; border-radius: 50%;
            border: 2px solid rgba(147,197,253,0.6); color: #e6f2fb;
            background: rgba(12,20,50,0.8); cursor: pointer;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            display: grid; place-items: center;
        }
        .mv-btn:hover { background: rgba(30,50,100,0.9); }
        .mv-findme { width: auto; height: 42px; border-radius: 999px; padding: 0 16px; font-size: 0.85rem; gap: 6px; }
    </style>
</div>
