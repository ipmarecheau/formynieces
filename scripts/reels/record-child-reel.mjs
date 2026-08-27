// Records the narrated, INFOGRAPHIC child demo reel by driving the real app with
// Playwright and baking in: a captioned story-bar, slow Ken-Burns zooms on scenes,
// and spotlight + ring + label callouts on the detail beats.
//
// Output: scripts/reels/out/*.webm (real-time). A separate `-itsscale` ffmpeg pass
// speeds it up (~1.7x). Audio (Piper voiceover + music) is muxed in a later step.
//
// Usage:  node scripts/reels/record-child-reel.mjs

import { chromium } from 'playwright-core';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { mkdirSync } from 'node:fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT_DIR = join(__dirname, 'out');
mkdirSync(OUT_DIR, { recursive: true });

const BASE = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const VIEWPORT = { width: 1280, height: 800 };
const EXECUTABLE = process.env.PW_CHROMIUM ??
  '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome';
const TOTAL = 10;

const wait = (ms) => new Promise((r) => setTimeout(r, ms));

// ---------------------------------------------------------------------------
// The in-page animation toolkit, injected before every navigation.
// ---------------------------------------------------------------------------
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
          box-shadow:0 0 0 9999px rgba(6,14,30,.62), 0 0 0 3px #f5b544, 0 0 34px 6px rgba(245,181,68,.55);
          transition:opacity .5s ease, all .6s cubic-bezier(.22,.61,.36,1);}
        #__reel_lbl{position:fixed;pointer-events:none;opacity:0;transition:opacity .45s ease;
          background:#f5b544;color:#221507;font-weight:800;font-size:14.5px;padding:8px 13px;
          border-radius:10px;box-shadow:0 10px 26px rgba(0,0,0,.4);max-width:260px;line-height:1.25;}
        #__reel_lbl:after{content:'';position:absolute;width:12px;height:12px;background:#f5b544;
          transform:rotate(45deg);}
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
    c.querySelector('.step').textContent = step === 0 ? '⛵' : step;
    c.querySelector('.txt b').textContent = title;
    c.querySelector('.txt span').textContent = text;
    c.querySelector('.prog').style.width = Math.round((Math.max(step,0.4)/total)*100) + '%';
    requestAnimationFrame(() => { c.style.opacity = '1'; });
  }
  function kenBurns(toScale, ms) {
    const b = document.body;
    const ox = window.scrollX + window.innerWidth/2;
    const oy = window.scrollY + window.innerHeight/2;
    b.style.transformOrigin = ox + 'px ' + oy + 'px';
    b.style.transition = 'none';
    b.style.transform = 'scale(1.0001)';
    b.getBoundingClientRect();
    requestAnimationFrame(() => {
      b.style.transition = 'transform ' + ms + 'ms linear';
      b.style.transform = 'scale(' + toScale + ')';
    });
  }
  function resetZoom(ms) {
    const b = document.body;
    b.style.transition = 'transform ' + (ms||500) + 'ms ease';
    b.style.transform = 'scale(1)';
  }
  function spotlight(sel, label) {
    layer();
    const el = document.querySelector(sel);
    const spot = document.getElementById('__reel_spot');
    const lbl = document.getElementById('__reel_lbl');
    if (!el) { return false; }
    const r = el.getBoundingClientRect();
    const pad = 8;
    spot.style.left = (r.left - pad) + 'px';
    spot.style.top = (r.top - pad) + 'px';
    spot.style.width = (r.width + pad*2) + 'px';
    spot.style.height = (r.height + pad*2) + 'px';
    requestAnimationFrame(() => { spot.style.opacity = '1'; });
    if (label) {
      lbl.textContent = label;
      const belowSpace = window.innerHeight - r.bottom;
      const place = belowSpace > 120 ? 'below' : 'above';
      lbl.style.left = Math.min(Math.max(r.left, 16), window.innerWidth - 280) + 'px';
      lbl.style.top = (place === 'below' ? r.bottom + 20 : r.top - 58) + 'px';
      lbl.style.setProperty('--x', '0');
      requestAnimationFrame(() => { lbl.style.opacity = '1'; });
      lbl.querySelector ? null : null;
      // position the little diamond
      const after = lbl;
      after.style.setProperty('position','fixed');
    } else {
      lbl.style.opacity = '0';
    }
    return true;
  }
  function clearSpot() {
    const spot = document.getElementById('__reel_spot');
    const lbl = document.getElementById('__reel_lbl');
    if (spot) spot.style.opacity = '0';
    if (lbl) lbl.style.opacity = '0';
  }
  return { caption, kenBurns, resetZoom, spotlight, clearSpot };
})();
`;

let T0 = 0;
const beatLog = [];
async function caption(page, step, title, text) {
  beatLog.push({ step, title, ms: Date.now() - T0 });
  await page.evaluate(({ s, t, ti, tx }) => window.__reel.caption(s, t, ti, tx),
    { s: step, t: TOTAL, ti: title, tx: text });
}
const kb = (page, to = 1.07, ms = 6500) =>
  page.evaluate(({ to, ms }) => window.__reel.kenBurns(to, ms), { to, ms });
const resetZoom = (page, ms = 500) =>
  page.evaluate((ms) => window.__reel.resetZoom(ms), ms);
const spotlight = (page, sel, label = '') =>
  page.evaluate(({ sel, label }) => window.__reel.spotlight(sel, label), { sel, label });
const clearSpot = (page) => page.evaluate(() => window.__reel.clearSpot());

async function glide(page, toFraction = 1, steps = 24, perStep = 45) {
  await page.evaluate(async ({ toFraction, steps, perStep }) => {
    const max = document.body.scrollHeight - window.innerHeight;
    const start = window.scrollY, end = Math.max(0, max) * toFraction;
    for (let i = 1; i <= steps; i++) {
      window.scrollTo(0, start + ((end - start) * i) / steps);
      await new Promise((r) => setTimeout(r, perStep));
    }
  }, { toFraction, steps, perStep });
}
async function safeClick(page, selector, timeout = 2000) {
  try { await page.locator(selector).first().click({ timeout }); return true; }
  catch { return false; }
}
async function go(page, path) {
  await page.goto(`${BASE}${path}`, { waitUntil: 'networkidle' }).catch(() => {});
}

const browser = await chromium.launch({ executablePath: EXECUTABLE, headless: true });
const context = await browser.newContext({
  viewport: VIEWPORT,
  recordVideo: { dir: OUT_DIR, size: VIEWPORT },
  reducedMotion: 'no-preference',
});
await context.addInitScript(initScript);
const page = await context.newPage();
T0 = Date.now(); // video-time zero, for beat timing

try {
  // === 1. Sign in ========================================================
  await go(page, '/login');
  await caption(page, 1, 'Sign in', 'Pick up exactly where they left off — no setup, no fuss.');
  await kb(page, 1.05, 5200);
  await wait(1400);
  await page.fill('#email', 'demo-student@smoothseas.test');
  await page.fill('#password', 'smoothseas');
  await wait(900);
  await Promise.all([page.waitForLoadState('networkidle'), page.click('button[type="submit"]')]);
  await wait(700);

  // === 2. Daily warm-up ==================================================
  await go(page, '/morning-tide');
  await caption(page, 2, 'Every day starts warm', 'A short reading passage and the day’s vocabulary.');
  await kb(page, 1.08, 6800);
  await wait(3600);

  // === 3. Writing stop ===================================================
  await go(page, '/writing');
  await wait(900);
  await safeClick(page, 'button:has-text("Got it")', 1500);
  await caption(page, 3, 'A daily writing stop', 'One focused prompt, marked with kind, specific feedback.');
  await spotlight(page, 'textarea', 'Their own words');
  await wait(3800);
  await clearSpot(page);

  // === 4. The map ========================================================
  await go(page, '/voyage');
  await caption(page, 4, 'Your curriculum is a map', 'Every skill is an island to sail to and conquer.');
  await wait(2400);
  await safeClick(page, 'button:has-text("Got it")', 1500);
  await caption(page, 4, 'Your curriculum is a map', 'Every skill is an island to sail to and conquer.');
  await kb(page, 1.07, 5200);
  await wait(3000);
  await resetZoom(page, 500);
  await wait(300);

  // === 5. Perks in the Locker ===========================================
  await safeClick(page, 'button:has-text("Locker"), .co-tab:has-text("Locker")', 2500);
  await wait(500);
  await caption(page, 5, 'Earn perks as you sail', 'Rewards collect in the Captain’s Locker — yours to spend.');
  await spotlight(page, '.co-reward', 'Real, spendable rewards');
  await wait(4000);
  await clearSpot(page);

  // === 6. Life happens — protections ====================================
  await caption(page, 6, 'Life happens — you’re covered', 'Take a day off, skip a duty, or rescue a lost streak.');
  const anchorSpot = await page.evaluate(() => {
    const card = [...document.querySelectorAll('.co-reward')]
      .find((c) => /Anchor/i.test(c.textContent));
    if (card) { card.id = '__anchor_card'; return true; }
    return false;
  });
  await spotlight(page, anchorSpot ? '#__anchor_card' : '.co-reward', 'Freeze every streak for a day');
  await wait(4400);
  await clearSpot(page);

  // === 7. Three ways to master ==========================================
  await go(page, '/practice/55/enter');
  await caption(page, 7, 'Three ways to master', 'Ace it cold, learn it, or let Smooth re-teach it.');
  await kb(page, 1.06, 6200);
  await wait(4400);

  // === 8. Ace the check =================================================
  await go(page, '/practice/55');
  await wait(1100);
  await safeClick(page, 'button:has-text("Got it")', 1500);
  await caption(page, 8, 'Ace the quick check', 'Six right — two easy, two medium, two tricky — and it’s mastered.');
  await spotlight(page, '.pw-options', 'Pick the right spelling');
  await wait(4200);
  await clearSpot(page);

  // === 9. Or learn it ===================================================
  await go(page, '/practice/55/lesson');
  await caption(page, 9, 'Or learn it, hands-on', 'A short interactive lesson, one idea at a time.');
  await kb(page, 1.06, 6000);
  await wait(3200);
  await safeClick(page, '.lw-next');
  await wait(2400);

  // === 10. Or Smooth re-teaches =========================================
  await go(page, '/practice/55/reteach');
  await caption(page, 10, 'Or Smooth re-teaches', 'Miss a rule? Take it again together, then prove you’ve got it.');
  await spotlight(page, 'input[type="text"], input:not([type])', 'Prove you’ve got it');
  await wait(4400);
  await clearSpot(page);

  // === Close ============================================================
  await caption(page, 0, 'SmoothSeas', 'They always know where they’re going.');
  await wait(3200);
} finally {
  await context.close();
  await browser.close();
}

const { writeFileSync } = await import('node:fs');
writeFileSync(join(OUT_DIR, 'beats.json'), JSON.stringify(beatLog, null, 2));
console.log('Recorded. Raw video + beats.json in', OUT_DIR);
