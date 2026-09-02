// v2 PARENT-PORTAL reel — walks the CURRENT Guardian Bridge portal, including the
// screens added since the original reel: Progress drill-down, Family (tree +
// co-parent invite), Children's logins (reveal/reset), and Account. Silent capture;
// on-screen captions are baked in. Reuses the original recorder's overlay toolkit
// (caption / spotlight / kenBurns) by lifting its initScript verbatim.
//
// Output: scripts/reels/out/parent2/*.webm (real-time) + parent2-beats.json.
// Usage:  node scripts/reels/record-parent-reel-v2.mjs

import { chromium } from 'playwright-core';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import { mkdirSync, writeFileSync, readFileSync } from 'node:fs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT_DIR = join(__dirname, 'out', 'parent2');
mkdirSync(OUT_DIR, { recursive: true });

const BASE = process.env.BASE_URL ?? 'http://127.0.0.1:8000';
const VIEWPORT = { width: 1280, height: 800 };
const EXECUTABLE = process.env.PW_CHROMIUM ??
  '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome';
const TOTAL = 11;

const wait = (ms) => new Promise((r) => setTimeout(r, ms));

// Lift the overlay toolkit (window.__reel) verbatim from the original recorder.
const original = readFileSync(join(__dirname, 'record-parent-reel.mjs'), 'utf8');
const m = original.match(/const initScript = `([\s\S]*?)(?<!\\)`;\n/);
if (!m) { throw new Error('Could not extract initScript from record-parent-reel.mjs'); }
// The source keeps inner backticks escaped (\`) because initScript is itself a
// template literal in the original; un-escape them so the injected JS is valid.
const initScript = m[1].replace(/\\`/g, '`');

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

  // === 2. Overview: the four honest questions ===========================
  await go(page, '/guardian/dashboard');
  await wait(3800); // let the exam-agent summary settle
  await caption(page, 2, 'Four honest answers', 'On pace? Exam when? This week’s target? How much mastered?');
  await spotlightText(page, 'WHERE DEMO STUDENT STANDS', 'A plain-language verdict first', 1);
  await wait(4200);
  await clearSpot(page);

  // === 3. One clear next step ===========================================
  await caption(page, 3, 'One clear next step', 'Smooth’s exam agent names the single thing to work on next.');
  await spotlightText(page, 'RECOMMENDATION', 'Not a wall of data — one action', 2);
  await wait(4200);
  await clearSpot(page);

  // === 4. Progress drill-down (the whole syllabus) ======================
  await go(page, '/guardian/progress');
  await wait(1600);
  await caption(page, 4, 'Every topic, tracked', 'Mastered, working on, and upcoming — the whole syllabus, honestly.');
  await spotlightText(page, 'MASTERED', 'What they’ve truly locked in', 1);
  await wait(3800);
  await clearSpot(page);
  await glide(page, 0.55, 22, 45);
  await wait(2400);

  // === 5. This week =====================================================
  await go(page, '/guardian/dashboard');
  await wait(1400);
  await section(page, 'this-week');
  await caption(page, 5, 'Exactly this week’s work', 'The topics, the reading, and the writing they’re on right now.');
  await kb(page, 1.05, 5200);
  await wait(3600);
  await resetZoom(page, 400);

  // === 6. Pace, against her own plan ====================================
  await section(page, 'pace');
  await caption(page, 6, 'Pace against the plan', 'Every figure is labelled — modules mastered, not a scary score.');
  await spotlightText(page, 'TRAJECTORY', 'Where they are vs. where the plan expects', 1);
  await wait(3800);
  await clearSpot(page);
  await glide(page, 0.7, 24, 45);
  await wait(2600);

  // === 7. Projected SEA placement =======================================
  await section(page, 'estimator');
  await caption(page, 7, 'A projected placement', 'An indicative SEA tier — with an honest confidence signal.');
  await spotlightText(page, 'PROJECTED', 'Weighted across the papers', 1);
  await wait(3800);
  await clearSpot(page);

  // === 8. You're in control =============================================
  await section(page, 'rewards');
  await caption(page, 8, 'You’re in control', 'Pause the journey, grant a reward, or request a retake.');
  await kb(page, 1.06, 5000);
  await wait(3600);
  await resetZoom(page, 400);

  // === 9. Family — one place for the whole household ====================
  await go(page, '/family');
  await wait(1600);
  await caption(page, 9, 'The whole family', 'Your children’s details — and invite the other parent to join.');
  await spotlightText(page, 'OTHER PARENT', 'Add a co-parent with one email', 1);
  await wait(3800);
  await clearSpot(page);
  await glide(page, 0.6, 20, 45);
  await wait(2200);

  // === 10. Children's logins — reveal / reset ===========================
  await go(page, '/guardian/children');
  await wait(1600);
  await caption(page, 10, 'Their logins, in your hands', 'Reveal or reset a child’s password anytime — lost or shared device.');
  await spotlightText(page, 'LOGIN ID', 'You always hold the keys', 1);
  await wait(4000);
  await clearSpot(page);

  // === 11. Account — profile, billing, control ==========================
  await go(page, '/account');
  await wait(1600);
  await caption(page, 11, 'Your account', 'Profile, password, billing and invoices — all in one calm place.');
  await kb(page, 1.05, 5000);
  await wait(3600);
  await resetZoom(page, 400);

  // === Close ============================================================
  await caption(page, 0, 'SmoothSeas', 'The honest layer — so you never have to guess.');
  await wait(3000);
} finally {
  await context.close();
  await browser.close();
}

writeFileSync(join(OUT_DIR, 'parent2-beats.json'), JSON.stringify(beatLog, null, 2));
console.log('Recorded parent reel v2 + parent2-beats.json in', OUT_DIR);
