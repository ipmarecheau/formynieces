// Records the narrated, infographic PARENT-PORTAL reel — the guardian "honest
// layer" — by driving the real app with Playwright. Same baked-in toolkit as the
// child reel: captioned story-bar, Ken-Burns zoom on scenes, spotlight callouts.
//
// Output: scripts/reels/out/parent-*.webm (real-time) + parent-beats.json.
// Encode/audio handled by the pipeline, mirroring the child reel.
//
// Usage:  node scripts/reels/record-parent-reel.mjs

import { chromium } from 'playwright-core';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { mkdirSync, writeFileSync } from 'node:fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT_DIR = join(__dirname, 'out', 'parent');
mkdirSync(OUT_DIR, { recursive: true });

const BASE = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const VIEWPORT = { width: 1280, height: 800 };
const EXECUTABLE = process.env.PW_CHROMIUM ??
  '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome';
const TOTAL = 9;

const wait = (ms) => new Promise((r) => setTimeout(r, ms));

const initScript = `
window.__reel = (function () {
  function layer() {
    const root = document.documentElement;
    let l = document.getElementById('__reel_layer');
    if (!l) {
      const style = document.createElement('style');
      style.textContent = \`
        #__reel_layer{position:fixed;inset:0;z-index:2147483000;pointer-events:none;
          font-family:system-ui,-apple-system,'Segoe UI',sans-serif;}
        #__reel_cap{position:fixed;left:50%;top:22px;transform:translateX(-50%);
          max-width:900px;width:calc(100% - 40px);box-sizing:border-box;
          background:rgba(5,14,30,.93);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);
          border:1.5px solid rgba(245,181,68,.55);border-radius:18px;padding:18px 24px 21px;
          display:flex;gap:18px;align-items:center;color:#fff;
          box-shadow:0 22px 64px rgba(0,0,0,.6);opacity:0;transition:opacity .45s ease;}
        #__reel_cap .step{flex:none;width:54px;height:54px;border-radius:15px;display:grid;place-items:center;
          background:linear-gradient(160deg,#ffd15c,#f2941f);color:#221507;font-weight:800;font-size:22px;
          box-shadow:0 6px 18px rgba(245,181,68,.4);}
        #__reel_cap .txt b{display:block;font-size:27px;font-weight:800;line-height:1.1;
          color:#ffd44d;text-shadow:0 2px 12px rgba(0,0,0,.5);letter-spacing:.2px;}
        #__reel_cap .txt span{display:block;color:#eef4ff;font-size:17.5px;margin-top:4px;line-height:1.3;
          font-weight:600;text-shadow:0 1px 6px rgba(0,0,0,.5);}
        #__reel_cap .prog{position:absolute;left:0;bottom:0;height:4px;background:#3fc0d4;
          border-radius:0 0 4px 4px;transition:width .5s ease;box-shadow:0 0 10px rgba(63,192,212,.7);}
        #__reel_spot{position:fixed;border-radius:16px;pointer-events:none;opacity:0;
          box-shadow:0 0 0 9999px rgba(6,14,30,.55), 0 0 0 3px #f5b544, 0 0 34px 6px rgba(245,181,68,.5);
          transition:opacity .5s ease, all .6s cubic-bezier(.22,.61,.36,1);}
        #__reel_lbl{position:fixed;pointer-events:none;opacity:0;transition:opacity .45s ease;
          background:#f5b544;color:#221507;font-weight:800;font-size:14.5px;padding:8px 13px;
          border-radius:10px;box-shadow:0 10px 26px rgba(0,0,0,.4);max-width:280px;line-height:1.25;}
      \`;
      root.appendChild(style);
      l = document.createElement('div'); l.id = '__reel_layer';
      l.innerHTML = '<div id="__reel_cap"><div class="step"></div>'
        + '<div class="txt"><b></b><span></span></div><div class="prog"></div></div>'
        + '<div id="__reel_spot"></div><div id="__reel_lbl"></div>';
      root.appendChild(l);
    }
    return l;
  }
  function caption(step, total, title, text) {
    layer();
    const c = document.getElementById('__reel_cap');
    c.querySelector('.step').textContent = step === 0 ? '⚓' : step;
    c.querySelector('.txt b').textContent = title;
    c.querySelector('.txt span').textContent = text;
    c.querySelector('.prog').style.width = Math.round((Math.max(step,0.4)/total)*100) + '%';
    requestAnimationFrame(() => { c.style.opacity = '1'; });
  }
  function kenBurns(toScale, ms) {
    const b = document.body;
    const ox = window.scrollX + window.innerWidth/2, oy = window.scrollY + window.innerHeight/2;
    b.style.transformOrigin = ox + 'px ' + oy + 'px';
    b.style.transition = 'none'; b.style.transform = 'scale(1.0001)'; b.getBoundingClientRect();
    requestAnimationFrame(() => { b.style.transition = 'transform ' + ms + 'ms linear'; b.style.transform = 'scale(' + toScale + ')'; });
  }
  function resetZoom(ms) { const b = document.body; b.style.transition = 'transform ' + (ms||500) + 'ms ease'; b.style.transform = 'scale(1)'; }
  function tagByText(text, climb) {
    const all = [...document.querySelectorAll('h1,h2,h3,h4,p,span,div,button,a')];
    let el = all.find((e) => e.textContent && e.textContent.trim().toUpperCase().includes(text.toUpperCase())
      && e.getBoundingClientRect().width > 40 && e.getBoundingClientRect().height > 12);
    if (!el) { return false; }
    for (let i = 0; i < (climb || 0) && el.parentElement; i++) { el = el.parentElement; }
    el.id = '__spot_target';
    el.scrollIntoView({ block: 'center', behavior: 'instant' });
    return true;
  }
  function spotlight(sel, label) {
    layer();
    const el = document.querySelector(sel);
    const spot = document.getElementById('__reel_spot'), lbl = document.getElementById('__reel_lbl');
    if (!el) { return false; }
    const r = el.getBoundingClientRect(), pad = 8;
    spot.style.left = (r.left - pad) + 'px'; spot.style.top = (r.top - pad) + 'px';
    spot.style.width = (r.width + pad*2) + 'px'; spot.style.height = (r.height + pad*2) + 'px';
    requestAnimationFrame(() => { spot.style.opacity = '1'; });
    if (label) {
      lbl.textContent = label;
      const belowSpace = window.innerHeight - r.bottom;
      const place = belowSpace > 120 ? 'below' : 'above';
      lbl.style.left = Math.min(Math.max(r.left, 16), window.innerWidth - 300) + 'px';
      lbl.style.top = (place === 'below' ? r.bottom + 18 : Math.max(90, r.top - 56)) + 'px';
      requestAnimationFrame(() => { lbl.style.opacity = '1'; });
    } else { lbl.style.opacity = '0'; }
    return true;
  }
  function clearSpot() {
    const spot = document.getElementById('__reel_spot'), lbl = document.getElementById('__reel_lbl');
    if (spot) spot.style.opacity = '0'; if (lbl) lbl.style.opacity = '0';
    const t = document.getElementById('__spot_target'); if (t) t.removeAttribute('id');
  }
  return { caption, kenBurns, resetZoom, spotlight, clearSpot, tagByText };
})();
`;

let T0 = 0;
const beatLog = [];
async function caption(page, step, title, text) {
  beatLog.push({ step, title, ms: Date.now() - T0 });
  await page.evaluate(({ s, t, ti, tx }) => window.__reel.caption(s, t, ti, tx), { s: step, t: TOTAL, ti: title, tx: text });
}
const kb = (page, to = 1.06, ms = 6500) => page.evaluate(({ to, ms }) => window.__reel.kenBurns(to, ms), { to, ms });
const resetZoom = (page, ms = 500) => page.evaluate((ms) => window.__reel.resetZoom(ms), ms);
const clearSpot = (page) => page.evaluate(() => window.__reel.clearSpot());
async function spotlightText(page, text, label, climb = 2) {
  const ok = await page.evaluate(({ text, climb }) => window.__reel.tagByText(text, climb), { text, climb });
  if (!ok) { return false; }
  await wait(250);
  return page.evaluate((label) => window.__reel.spotlight('#__spot_target', label), label);
}

async function glide(page, toFraction = 1, steps = 24, perStep = 45) {
  await page.evaluate(async ({ toFraction, steps, perStep }) => {
    const max = document.body.scrollHeight - window.innerHeight;
    const start = window.scrollY, end = Math.max(0, max) * toFraction;
    for (let i = 1; i <= steps; i++) { window.scrollTo(0, start + ((end - start) * i) / steps); await new Promise((r) => setTimeout(r, perStep)); }
  }, { toFraction, steps, perStep });
}
/** Click a sidebar section by its $set('section', 'X') binding. */
async function section(page, name) {
  await page.evaluate((sec) => {
    const el = [...document.querySelectorAll('[wire\\:click]')].find((e) =>
      (e.getAttribute('wire:click') || '').includes(`'section', '${sec}'`));
    if (el) { el.click(); }
  }, name);
  await wait(1300);
}
async function go(page, path) { await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle' }).catch(() => {}); }

const browser = await chromium.launch({ executablePath: EXECUTABLE, headless: true });
const context = await browser.newContext({ viewport: VIEWPORT, recordVideo: { dir: OUT_DIR, size: VIEWPORT }, reducedMotion: 'no-preference' });
await context.addInitScript(initScript);
const page = await context.newPage();
T0 = Date.now();

try {
  // === 1. Sign in as the guardian =======================================
  await go(page, '/login');
  await caption(page, 1, 'Your parent portal', 'The honest layer — no streaks, no spin, just where they stand.');
  await kb(page, 1.05, 5200);
  await wait(1400);
  await page.fill('#email', 'demo-guardian@smoothseas.test');
  await page.fill('#password', 'smoothseas');
  await wait(900);
  await Promise.all([page.waitForLoadState('networkidle'), page.click('button[type="submit"]')]);

  // === 2. Overview: the four Sunday questions ===========================
  await go(page, '/guardian/dashboard');
  await wait(3800); // let the exam-agent summary settle
  // Capture the guardian-journal link NOW, while the Overview (which holds it) is
  // the active section — later sections hide it from the DOM.
  const journalHref = await page.evaluate(() => {
    const a = [...document.querySelectorAll('a')]
      .find((x) => /\/guardian\/students\/\d+\/journal/.test(x.getAttribute('href') || ''));
    return a ? a.getAttribute('href') : null;
  });
  await caption(page, 2, 'Four honest answers', 'On pace? Exam when? This week’s target? How much mastered?');
  await spotlightText(page, "WHERE DEMO STUDENT STANDS", 'A plain-language verdict first', 1);
  await wait(4200);
  await clearSpot(page);

  // === 3. One clear next step ===========================================
  await caption(page, 3, 'One clear next step', 'Smooth’s exam agent picks the single thing to focus on next.');
  await spotlightText(page, 'RECOMMENDATION', 'Not a wall of data — one action', 2);
  await wait(4400);
  await clearSpot(page);

  // === 4. This week =====================================================
  await section(page, 'this-week');
  await caption(page, 4, 'Exactly this week’s work', 'The topics, the reading, and the writing they’re on right now.');
  await kb(page, 1.05, 5600);
  await wait(3800);
  await resetZoom(page, 400);

  // === 5. Pace — labelled, never alarming ===============================
  await section(page, 'pace');
  await caption(page, 5, 'Pace against her own plan', 'Every figure is labelled — modules mastered, not a scary score.');
  await spotlightText(page, 'TRAJECTORY', 'Where she is vs. where the plan expects', 1);
  await wait(4400);
  await clearSpot(page);

  // === 6. The whole year, month by month ================================
  await caption(page, 6, 'The whole year, mapped', 'A month-by-month calendar — mastered, working on, upcoming.');
  await glide(page, 0.75, 26, 45);
  await wait(3600);

  // === 7. Projected SEA placement =======================================
  await section(page, 'estimator');
  await caption(page, 7, 'A projected placement', 'An indicative SEA tier — with an honest confidence signal.');
  await spotlightText(page, 'PROJECTED COMPOSITE', 'Weighted 50 / 30 / 20 across papers', 1);
  await wait(4400);
  await clearSpot(page);

  // === 8. You're in control =============================================
  await section(page, 'rewards');
  await caption(page, 8, 'You’re in control', 'Pause the journey, grant a reward, or request a retake.');
  await kb(page, 1.06, 5200);
  await wait(4000);
  await resetZoom(page, 400);

  // === 9. School papers, too ============================================
  const journalUrl = journalHref
    ? (journalHref.startsWith('http') ? journalHref : `${BASE}${journalHref}`)
    : `${BASE}/guardian/students/36/journal`;
  await page.goto(journalUrl, { waitUntil: 'networkidle' }).catch(() => {});
  await caption(page, 9, 'School papers, too', 'Add graded classroom work — kept honestly beside our own picture.');
  await kb(page, 1.06, 5200);
  await wait(4200);

  // === Close ============================================================
  await caption(page, 0, 'SmoothSeas', 'The honest layer — so you never have to guess.');
  await wait(3200);
} finally {
  await context.close();
  await browser.close();
}

writeFileSync(join(OUT_DIR, 'parent-beats.json'), JSON.stringify(beatLog, null, 2));
console.log('Recorded parent reel + parent-beats.json in', OUT_DIR);
