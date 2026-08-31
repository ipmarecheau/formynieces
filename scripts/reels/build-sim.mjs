// Builds the simulated-frontend demo artifact from captured real screens.
// Reads out/sim/screens.json, emits out/sim-demo.html — isolated iframes of the real
// app UI, crossfaded and driven by a simulated cursor. Usage: node scripts/reels/build-sim.mjs

import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = dirname(fileURLToPath(import.meta.url));
const { viewport, screens } = JSON.parse(readFileSync(join(dir, 'out/sim/screens.json'), 'utf8'));

// clamp targets into the visible frame
const W = viewport.width, H = viewport.height;
for (const s of screens) {
  if (!s.target) { s.target = { x: W / 2, y: H / 2 }; }
  s.target.x = Math.max(40, Math.min(W - 40, s.target.x));
  s.target.y = Math.max(40, Math.min(H - 60, s.target.y));
}

const data = JSON.stringify(screens);

const html = `<title>SmoothSeas, Simulated</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap">
<style>
  :root { --paper:#fbf8f2; --ink:#12222e; --ink-soft:#40566a; --ink-faint:#6b8199; --line:#e7ddcd; --teal:#0d7d8c; --gold:#f2a900; }
  @media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) { --paper:#0c1a24; --ink:#eaf2f4; --ink-soft:#b6c8d0; --ink-faint:#8aa2ad; --line:#22343d; --teal:#35c0d4; } }
  :root[data-theme="dark"] { --paper:#0c1a24; --ink:#eaf2f4; --ink-soft:#b6c8d0; --ink-faint:#8aa2ad; --line:#22343d; --teal:#35c0d4; }
  * { box-sizing:border-box; }
  body { margin:0; background:var(--paper); color:var(--ink); font-family:'Nunito',system-ui,sans-serif; -webkit-font-smoothing:antialiased; }
  .wrap { max-width:960px; margin:0 auto; padding:clamp(24px,5vw,52px) 18px 56px; }
  header { text-align:center; margin-bottom:clamp(20px,4vw,30px); }
  .eyebrow { font-weight:800; font-size:12px; letter-spacing:.16em; text-transform:uppercase; color:var(--teal); }
  h1 { font-family:'Fredoka',system-ui,sans-serif; font-weight:700; font-size:clamp(26px,5vw,42px); margin:10px 0 10px; text-wrap:balance; }
  .sub { color:var(--ink-soft); font-size:clamp(14px,2.2vw,17px); max-width:56ch; margin:0 auto; }

  .stage { position:relative; border-radius:20px; overflow:hidden; box-shadow:0 30px 70px rgba(6,20,40,.28); border:1px solid var(--line); background:#08152c; aspect-ratio:16/10; }
  .scaler { position:absolute; top:0; left:0; width:${W}px; height:${H}px; transform-origin:top left; }
  .frame { position:absolute; inset:0; width:${W}px; height:${H}px; border:0; opacity:0; transition:opacity .55s ease; background:#08152c; }
  .frame.on { opacity:1; }
  .cursor { position:absolute; left:0; top:0; width:56px; height:56px; z-index:7; transform:translate(-40%,-30%); transition:left .95s cubic-bezier(.5,.05,.25,1), top .95s cubic-bezier(.5,.05,.25,1); pointer-events:none; }
  .cursor svg { width:100%; height:100%; display:block; filter:drop-shadow(0 3px 4px rgba(0,0,0,.55)); }
  .cursor.press { animation:press .5s cubic-bezier(.34,1.56,.5,1); }
  @keyframes press { 0%{transform:translate(-40%,-30%) scale(1)} 35%{transform:translate(-40%,-30%) scale(.55)} 100%{transform:translate(-40%,-30%) scale(1)} }
  /* exaggerated click: two rings + a filled flash */
  .ring { position:absolute; z-index:5; border-radius:50%; transform:translate(-50%,-50%) scale(.25); opacity:0; pointer-events:none; }
  .ring1 { width:52px; height:52px; border:5px solid var(--gold); }
  .ring2 { width:52px; height:52px; border:4px solid #fff; }
  .flash { position:absolute; z-index:4; width:40px; height:40px; border-radius:50%; background:radial-gradient(circle, rgba(245,181,68,.85), rgba(245,181,68,0) 70%); transform:translate(-50%,-50%) scale(.2); opacity:0; pointer-events:none; }
  .ring1.go { animation:rp1 .62s ease-out; } .ring2.go { animation:rp2 .62s ease-out .06s; } .flash.go { animation:fl .5s ease-out; }
  @keyframes rp1 { 0%{opacity:.95; transform:translate(-50%,-50%) scale(.25)} 100%{opacity:0; transform:translate(-50%,-50%) scale(4.6)} }
  @keyframes rp2 { 0%{opacity:.85; transform:translate(-50%,-50%) scale(.2)} 100%{opacity:0; transform:translate(-50%,-50%) scale(3.2)} }
  @keyframes fl  { 0%{opacity:.9; transform:translate(-50%,-50%) scale(.2)} 100%{opacity:0; transform:translate(-50%,-50%) scale(2.2)} }

  /* caption + progress live BELOW the screen so they never hide the UI */
  .capbar { display:flex; align-items:center; gap:14px; max-width:820px; margin:18px auto 0; }
  .capbar .n { flex:none; width:42px; height:42px; border-radius:12px; display:grid; place-items:center; background:linear-gradient(160deg,#ffd15c,#f2941f); color:#241505; font-family:'Fredoka',sans-serif; font-weight:800; font-size:18px; box-shadow:0 6px 16px rgba(242,169,0,.28); }
  .capbar .t { font-family:'Fredoka',sans-serif; font-weight:600; font-size:clamp(16px,2.5vw,22px); color:var(--ink); }
  .captrack { max-width:820px; margin:14px auto 0; height:6px; border-radius:999px; background:var(--line); overflow:hidden; }
  .captrack i { display:block; height:100%; width:0; background:var(--teal); border-radius:999px; transition:width .5s ease; }

  .note { text-align:center; color:var(--ink-faint); font-size:13px; margin-top:16px; }
  .badge { display:inline-flex; align-items:center; gap:7px; background:color-mix(in srgb,var(--teal) 12%,transparent); border:1px solid var(--line); color:var(--ink-soft); border-radius:999px; padding:5px 12px; font-size:12.5px; font-weight:700; }
  @media (prefers-reduced-motion: reduce) { .cursor, .frame { transition:none; } }
</style>

<div class="wrap">
  <header>
    <span class="eyebrow">The real app, simulated</span>
    <h1>SmoothSeas, driven for you.</h1>
    <p class="sub">This is the app's <em>actual</em> front-end — real screens, real styling — captured with a seeded account and replayed with a simulated cursor. No video: it's the live HTML.</p>
  </header>

  <div class="stage" id="stage">
    <div class="scaler" id="scaler">
      <!-- iframes injected here -->
      <div class="flash" id="flash"></div>
      <div class="ring ring1" id="ring1"></div>
      <div class="ring ring2" id="ring2"></div>
      <svg class="cursor" id="cursor" viewBox="0 0 24 24" fill="none"><path d="M4 2 L4 20 L9 15 L12.5 22 L15 21 L11.5 14 L18 14 Z" fill="#fff" stroke="#12222e" stroke-width="1.4" stroke-linejoin="round"/></svg>
    </div>
  </div>

  <div class="capbar"><span class="n" id="capN">1</span><span class="t" id="capT">…</span></div>
  <div class="captrack"><i id="prog"></i></div>
  <p class="note"><span class="badge">🖱️ auto-playing · real UI · seeded data</span></p>
</div>

<script id="screens" type="application/json">${data.replace(/</g, '\\u003c')}</script>
<script>
  (function () {
    var screens = JSON.parse(document.getElementById('screens').textContent);
    var W = ${W}, H = ${H};
    var scaler = document.getElementById('scaler'), stage = document.getElementById('stage');
    var cursor = document.getElementById('cursor');
    var ring1 = document.getElementById('ring1'), ring2 = document.getElementById('ring2'), flash = document.getElementById('flash');
    var capN = document.getElementById('capN'), capT = document.getElementById('capT'), prog = document.getElementById('prog');
    function retrigger(el, cls, x, y) { el.style.left = x + 'px'; el.style.top = y + 'px'; el.classList.remove(cls); void el.offsetWidth; el.classList.add(cls); }

    // build iframes
    var frames = screens.map(function (s, i) {
      var f = document.createElement('iframe');
      f.className = 'frame'; f.setAttribute('scrolling', 'no'); f.setAttribute('tabindex', '-1'); f.setAttribute('aria-hidden', 'true');
      f.srcdoc = s.html;
      scaler.insertBefore(f, scaler.firstChild);
      return f;
    });

    function fit() { var s = stage.clientWidth / W; scaler.style.transform = 'scale(' + s + ')'; }
    window.addEventListener('resize', fit); fit();

    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    cursor.style.left = (W / 2) + 'px'; cursor.style.top = (H / 2) + 'px';

    function moveCursor(x, y) { cursor.style.left = x + 'px'; cursor.style.top = y + 'px'; }
    function clickFx(x, y) {
      cursor.classList.remove('press'); void cursor.offsetWidth; cursor.classList.add('press');
      retrigger(flash, 'go', x, y); retrigger(ring1, 'go', x, y); retrigger(ring2, 'go', x, y);
    }
    function iframeScroll(f, top) { try { f.contentWindow.scrollTo({ top: top, behavior: 'smooth' }); } catch (e) {} }

    var i = -1;
    function step() {
      i = (i + 1) % screens.length;
      var s = screens[i], f = frames[i];
      frames.forEach(function (fr, k) { fr.classList.toggle('on', k === i); });
      capN.textContent = (i + 1); capT.textContent = s.caption;
      prog.style.width = Math.round(((i + 1) / screens.length) * 100) + '%';
      // reset every frame to the top as we arrive
      try { f.contentWindow.scrollTo(0, 0); } catch (e) {}

      if (reduce) { setTimeout(step, 2800); return; }

      if (s.scroll) {
        // reveal the whole page by scrolling it, then point at the interactive widget
        var maxScroll = Math.max(0, (s.scrollH || H) - H);
        setTimeout(function () { iframeScroll(f, maxScroll); }, 1000);
        setTimeout(function () { moveCursor(s.target.x, Math.max(60, s.target.y - maxScroll)); }, 4400);
        setTimeout(function () { clickFx(s.target.x, Math.max(60, s.target.y - maxScroll)); }, 5600);
        setTimeout(step, 7400);
      } else {
        setTimeout(function () { moveCursor(s.target.x, s.target.y); }, 500);
        setTimeout(function () { clickFx(s.target.x, s.target.y); }, 1650);
        setTimeout(step, 4600);
      }
    }
    // start once first iframe has painted
    setTimeout(step, 400);
  })();
</script>`;

writeFileSync(join(dir, 'out/sim-demo.html'), html);
console.log('Wrote out/sim-demo.html —', (html.length / 1024 / 1024).toFixed(2), 'MB');
