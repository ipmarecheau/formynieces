{{-- Simulated front-end demo: the REAL app screens (captured to public/demo/*.html),
     replayed in isolated iframes with a simulated cursor. Same-origin, so no sandbox
     limits; frames lazy-load so the hero stays light. --}}
<style>
    .sim-wrap { max-width: 960px; margin: clamp(30px, 5vw, 46px) auto 0; }
    .sim-stage { position: relative; border-radius: 22px; overflow: hidden; border: 1px solid var(--line);
        background: #08152c; aspect-ratio: 16 / 10; box-shadow: var(--shadow-lg); }
    .sim-scaler { position: absolute; top: 0; left: 0; width: 1280px; height: 800px; transform-origin: top left; }
    .sim-frame { position: absolute; inset: 0; width: 1280px; height: 800px; border: 0; opacity: 0;
        transition: opacity .55s ease; background: #08152c; }
    .sim-frame.on { opacity: 1; }
    .sim-cursor { position: absolute; left: 0; top: 0; width: 56px; height: 56px; z-index: 7;
        transform: translate(-40%,-30%); transition: left .95s cubic-bezier(.5,.05,.25,1), top .95s cubic-bezier(.5,.05,.25,1); pointer-events: none; }
    .sim-cursor svg { width: 100%; height: 100%; display: block; filter: drop-shadow(0 3px 4px rgba(0,0,0,.55)); }
    .sim-cursor.press { animation: simPress .5s cubic-bezier(.34,1.56,.5,1); }
    @keyframes simPress { 0%{transform:translate(-40%,-30%) scale(1)} 35%{transform:translate(-40%,-30%) scale(.55)} 100%{transform:translate(-40%,-30%) scale(1)} }
    .sim-ring { position: absolute; z-index: 5; border-radius: 50%; transform: translate(-50%,-50%) scale(.25); opacity: 0; pointer-events: none; }
    .sim-ring1 { width: 52px; height: 52px; border: 5px solid var(--amber); }
    .sim-ring2 { width: 52px; height: 52px; border: 4px solid #fff; }
    .sim-flash { position: absolute; z-index: 4; width: 40px; height: 40px; border-radius: 50%;
        background: radial-gradient(circle, rgba(245,181,68,.85), rgba(245,181,68,0) 70%); transform: translate(-50%,-50%) scale(.2); opacity: 0; pointer-events: none; }
    .sim-ring1.go { animation: simRp1 .62s ease-out; } .sim-ring2.go { animation: simRp2 .62s ease-out .06s; } .sim-flash.go { animation: simFl .5s ease-out; }
    @keyframes simRp1 { 0%{opacity:.95; transform:translate(-50%,-50%) scale(.25)} 100%{opacity:0; transform:translate(-50%,-50%) scale(4.6)} }
    @keyframes simRp2 { 0%{opacity:.85; transform:translate(-50%,-50%) scale(.2)} 100%{opacity:0; transform:translate(-50%,-50%) scale(3.2)} }
    @keyframes simFl  { 0%{opacity:.9; transform:translate(-50%,-50%) scale(.2)} 100%{opacity:0; transform:translate(-50%,-50%) scale(2.2)} }
    .sim-capbar { display: flex; align-items: center; gap: 14px; max-width: 820px; margin: 18px auto 0; }
    .sim-capbar .n { flex: none; width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center;
        background: linear-gradient(160deg,#ffd15c,#f2941f); color: #241505; font-family: 'Fredoka', sans-serif; font-weight: 800; font-size: 18px; box-shadow: 0 6px 16px rgba(242,169,0,.28); }
    .sim-capbar .t { font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: clamp(15px,2.4vw,21px); color: var(--ink); }
    .sim-captrack { max-width: 820px; margin: 14px auto 0; height: 6px; border-radius: 999px; background: var(--line); overflow: hidden; }
    .sim-captrack i { display: block; height: 100%; width: 0; background: var(--teal); border-radius: 999px; transition: width .5s ease; }
    @media (prefers-reduced-motion: reduce) { .sim-cursor, .sim-frame { transition: none; } }
</style>

<div class="sim-wrap">
    <div class="sim-stage" id="simStage">
        <div class="sim-scaler" id="simScaler">
            <iframe class="sim-frame" data-src="{{ asset('demo/voyage.html') }}" data-cap="Sail the map — every skill is an island to conquer" data-tx="269" data-ty="359" data-scroll="0" data-sh="806" scrolling="no" tabindex="-1" aria-hidden="true" title="Voyage map"></iframe>
            <iframe class="sim-frame" data-src="{{ asset('demo/lesson.html') }}" data-cap="Learn it — a hands-on lesson with interactive widgets" data-tx="544" data-ty="874" data-scroll="1" data-sh="1067" scrolling="no" tabindex="-1" aria-hidden="true" title="Lesson"></iframe>
            <iframe class="sim-frame" data-src="{{ asset('demo/check.html') }}" data-cap="Ace the six-question check" data-tx="786" data-ty="319" data-scroll="0" data-sh="800" scrolling="no" tabindex="-1" aria-hidden="true" title="Quick check"></iframe>
            <iframe class="sim-frame" data-src="{{ asset('demo/chat.html') }}" data-cap="Stuck? Ask Smooth — the AI re-teaches, right in the chat" data-tx="1223" data-ty="737" data-scroll="0" data-sh="800" scrolling="no" tabindex="-1" aria-hidden="true" title="Ask Smooth chat"></iframe>
            <div class="sim-flash" id="simFlash"></div>
            <div class="sim-ring sim-ring1" id="simRing1"></div>
            <div class="sim-ring sim-ring2" id="simRing2"></div>
            <svg class="sim-cursor" id="simCursor" viewBox="0 0 24 24" fill="none"><path d="M4 2 L4 20 L9 15 L12.5 22 L15 21 L11.5 14 L18 14 Z" fill="#fff" stroke="#12222e" stroke-width="1.4" stroke-linejoin="round"/></svg>
        </div>
    </div>
    <div class="sim-capbar"><span class="n" id="simCapN">1</span><span class="t" id="simCapT">Sign in — pick up where they left off</span></div>
    <div class="sim-captrack"><i id="simProg"></i></div>
</div>

<script>
    (function () {
        var W = 1280, H = 800;
        var stage = document.getElementById('simStage'), scaler = document.getElementById('simScaler');
        var frames = Array.prototype.slice.call(document.querySelectorAll('.sim-frame'));
        var cursor = document.getElementById('simCursor');
        var ring1 = document.getElementById('simRing1'), ring2 = document.getElementById('simRing2'), flash = document.getElementById('simFlash');
        var capN = document.getElementById('simCapN'), capT = document.getElementById('simCapT'), prog = document.getElementById('simProg');
        if (!frames.length) { return; }

        function fit() { scaler.style.transform = 'scale(' + (stage.clientWidth / W) + ')'; }
        window.addEventListener('resize', fit); fit();

        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        cursor.style.left = (W / 2) + 'px'; cursor.style.top = (H / 2) + 'px';
        function ensure(k) { var f = frames[k]; if (f && !f.src && f.dataset.src) { f.src = f.dataset.src; } }
        function retrigger(el, x, y) { el.style.left = x + 'px'; el.style.top = y + 'px'; el.classList.remove('go'); void el.offsetWidth; el.classList.add('go'); }
        function clickFx(x, y) { cursor.classList.remove('press'); void cursor.offsetWidth; cursor.classList.add('press'); retrigger(flash, x, y); retrigger(ring1, x, y); retrigger(ring2, x, y); }
        function iScroll(f, top) { try { f.contentWindow.scrollTo({ top: top, behavior: 'smooth' }); } catch (e) {} }

        // reduced motion: just show the map, no cursor motion
        if (reduce) { ensure(0); frames[0].classList.add('on'); return; }

        var i = -1;
        function step() {
            i = (i + 1) % frames.length;
            var f = frames[i], d = f.dataset;
            ensure(i); ensure((i + 1) % frames.length);
            frames.forEach(function (fr, k) { fr.classList.toggle('on', k === i); });
            capN.textContent = (i + 1); capT.textContent = d.cap;
            prog.style.width = Math.round(((i + 1) / frames.length) * 100) + '%';
            try { f.contentWindow.scrollTo(0, 0); } catch (e) {}
            var tx = +d.tx, ty = +d.ty;

            if (d.scroll === '1') {
                var maxScroll = Math.max(0, (+d.sh || H) - H);
                setTimeout(function () { iScroll(f, maxScroll); }, 1000);
                setTimeout(function () { cursor.style.left = tx + 'px'; cursor.style.top = Math.max(60, ty - maxScroll) + 'px'; }, 4400);
                setTimeout(function () { clickFx(tx, Math.max(60, ty - maxScroll)); }, 5600);
                setTimeout(step, 7400);
            } else {
                setTimeout(function () { cursor.style.left = tx + 'px'; cursor.style.top = ty + 'px'; }, 500);
                setTimeout(function () { clickFx(tx, ty); }, 1650);
                setTimeout(step, 4600);
            }
        }
        setTimeout(step, 500);
    })();
</script>
