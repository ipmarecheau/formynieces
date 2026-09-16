// Weighted parity scorecard: web (source of truth) vs Flutter replica.
// Categories & weights (deviation = weighted sum, PASS if <= 5%):
//   Functionality & Behaviour 45% | Content & Data 20% | Layout 20% | Visual 15%
// Visual uses MSSIM (structural, glow/AA-tolerant); raw pixel% is a secondary tripwire.
//
// Usage: node scorecard.mjs <screen>    (screen key from SCREENS below)
import { chromium } from 'playwright';
import pixelmatch from 'pixelmatch';
import { PNG } from 'pngjs';
import { readFileSync, writeFileSync } from 'fs';

const VP = { width: 390, height: 844 };
const DSF = 2;
const dir = '/tmp/parity/diff';
const FLUTTER = 'http://127.0.0.1:8021';
const WEB = 'http://127.0.0.1:8000';
const CRED = { email: 'emu-child@smoothseas.test', password: 'password' };

// ---- per-screen specs -------------------------------------------------------
const SCREENS = {
  go: {
    webUrl: `${WEB}/go`, auth: false,
    state: async () => {}, // capture on the fresh login screen
    content: ['Sign in to your voyage', 'Child sign-in', 'Your login', 'Password', 'Set sail', 'parent'],
    layout: [ // web selector -> flutter semantic label matcher (interactive = distinct nodes)
      { name: 'loginField', web: '#email', fl: /\[field\]/, pickTop: true },
      { name: 'button', web: '.btn', fl: /set sail/i },
    ],
    async func(page, sem) {
      const r = [];
      r.push(['login screen renders', (await sem()).some(n => /sign in to your voyage/i.test(n.label))]);
      // invalid creds -> error
      await typeLogin(page, 'bad@x.com', 'wrong');
      await page.mouse.click(195, 582); await page.waitForTimeout(2800);
      r.push(['invalid login shows error', (await sem()).some(n => /didn.t work|not match|invalid/i.test(n.label))]);
      // valid creds -> navigates to welcome
      await page.reload({ waitUntil: 'load' }); await page.waitForTimeout(6000); await enableSemantics(page);
      await typeLogin(page, CRED.email, CRED.password);
      await page.mouse.click(195, 582); await page.waitForTimeout(5500);
      r.push(['valid login navigates onward', (await sem()).some(n => /welcome back aboard/i.test(n.label))]);
      return r;
    },
  },
  welcome: {
    webUrl: `${WEB}/welcome-back`, auth: true,
    state: async (page) => { await loginFlutter(page); },
    content: ['Welcome back aboard', 'Fair winds', 'practice streak', 'login streak', 'Continue to my voyage', 'Keep the streak alive'],
    layout: [
      { name: 'title', web: '.splash-title', fl: /welcome back aboard/i },
      { name: 'button', web: '.ss-btn', fl: /continue to my voyage/i },
    ],
    async func(page, sem) {
      const r = [];
      await loginFlutter(page); await enableSemantics(page);
      const nodes = await sem();
      r.push(['welcome renders after login', nodes.some(n => /welcome back aboard/i.test(n.label))]);
      r.push(['greeting shows child name', nodes.some(n => /ava/i.test(n.label))]);
      const api = await (await fetch(`http://127.0.0.1:8011/api/mobile/child/welcome-back`, { headers: { Authorization: `Bearer ${await token()}`, Accept: 'application/json' } })).json();
      const practice = api.streaks?.practice ?? 0;
      r.push(['practice streak value matches API', nodes.some(n => new RegExp(`${practice} day practice`, 'i').test(n.label))]);
      const btn = nodes.find(n => /continue to my voyage/i.test(n.label));
      r.push(['continue button present', !!btn]);
      // Nav check on a fresh page WITHOUT semantics (the semantics overlay swallows taps).
      await page.reload({ waitUntil: 'load' }); await page.waitForTimeout(6500);
      await loginFlutter(page);
      const before = await page.screenshot({ clip: { x: 0, y: 0, ...VP } });
      await page.mouse.click(195, 611); await page.waitForTimeout(6500);
      const after = await page.screenshot({ clip: { x: 0, y: 0, ...VP } });
      const A = PNG.sync.read(before), B = PNG.sync.read(after);
      const changed = pixelmatch(A.data, B.data, null, A.width, A.height, { threshold: 0.1 }) / (A.width * A.height);
      if (process.env.DEBUG) console.log('  [nav screen change]', (changed * 100).toFixed(1) + '%');
      r.push(['continue navigates away to voyage', changed > 0.15]);
      return r;
    },
  },
  island: {
    webUrl: `${WEB}/voyage/feather-isle`, auth: true,
    webPrep: async (page) => { try { await page.click('text=Got it!', { timeout: 2500 }); await page.waitForTimeout(700); } catch (e) {} },
    state: async (page) => { await loginFlutter(page); await page.mouse.click(195, 611); await page.waitForTimeout(7000); await page.mouse.click(80, 262); await page.waitForTimeout(5000); },
    content: ['Feather Isle', 'levels conquered', 'Stops on this island', 'Place Value', 'Back to the sea'],
    layout: [{ name: 'title', web: '.vy-title', fl: /^feather isle$/i }],
    async func(page, sem) {
      const r = [];
      await loginFlutter(page); await page.mouse.click(195, 611); await page.waitForTimeout(7000);
      await page.mouse.click(80, 262); await page.waitForTimeout(5000);
      await enableSemantics(page);
      const nodes = await sem();
      r.push(['island renders', nodes.some(n => /stops on this island/i.test(n.label))]);
      const api = await (await fetch(`http://127.0.0.1:8011/api/mobile/child/island/feather-isle`, { headers: { Authorization: `Bearer ${await token()}`, Accept: 'application/json' } })).json();
      const first = (api.levels?.[0]?.topic || 'Place Value').split(':').pop().trim().split(' ').slice(0, 2).join(' ');
      r.push(['level list matches API', nodes.some(n => new RegExp(first, 'i').test(n.label))]);
      r.push(['shows conquered count', nodes.some(n => /levels conquered|of \d+ levels/i.test(n.label))]);
      r.push(['back-to-the-sea present', nodes.some(n => /back to the sea/i.test(n.label))]);
      // behaviour: back navigates to the voyage
      await page.reload({ waitUntil: 'load' }); await page.waitForTimeout(6500);
      await loginFlutter(page); await page.mouse.click(195, 611); await page.waitForTimeout(7000);
      await page.mouse.click(80, 262); await page.waitForTimeout(5000);
      const before = await page.screenshot({ clip: { x: 0, y: 0, ...VP } });
      await page.mouse.click(330, 40); await page.waitForTimeout(4000); // back pill
      const after = await page.screenshot({ clip: { x: 0, y: 0, ...VP } });
      const A = PNG.sync.read(before), B = PNG.sync.read(after);
      const changed = pixelmatch(A.data, B.data, null, A.width, A.height, { threshold: 0.1 }) / (A.width * A.height);
      if (process.env.DEBUG) console.log('  [back change]', (changed * 100).toFixed(1) + '%');
      r.push(['back navigates to the sea', changed > 0.15]);
      return r;
    },
  },
  voyage: {
    webUrl: `${WEB}/voyage`, auth: true,
    state: async (page) => { await loginFlutter(page); await page.mouse.click(195, 611); await page.waitForTimeout(7000); },
    content: ['Your Voyage', 'Islands', 'Feather Isle', 'Lantern Rock', 'conquered', 'Captain'],
    layout: [
      { name: 'panelTitle', web: '.co-title-main', fl: /captain.s orders/i },
    ],
    async func(page, sem) {
      const r = [];
      await loginFlutter(page); await page.mouse.click(195, 611); await page.waitForTimeout(7000);
      await enableSemantics(page);
      const nodes = await sem();
      r.push(['voyage renders after continue', nodes.some(n => /your voyage/i.test(n.label))]);
      const api = await (await fetch(`http://127.0.0.1:8011/api/mobile/child/voyage`, { headers: { Authorization: `Bearer ${await token()}`, Accept: 'application/json' } })).json();
      const first = api.islands?.[0]?.name || 'Feather Isle';
      r.push(['island list matches API (first island)', nodes.some(n => new RegExp(first, 'i').test(n.label))]);
      r.push(['shows island count', nodes.some(n => /\d+\s*\/\s*\d+|conquered/i.test(n.label))]);
      r.push(['captain’s orders open by default', nodes.some(n => /captain/i.test(n.label))]);
      // behaviour: collapsing the open panel changes the screen (fresh page — no semantics overlay)
      await page.reload({ waitUntil: 'load' }); await page.waitForTimeout(6500);
      await loginFlutter(page); await page.mouse.click(195, 611); await page.waitForTimeout(7000);
      const before = await page.screenshot({ clip: { x: 0, y: 0, ...VP } });
      await page.mouse.click(365, 475); await page.waitForTimeout(1500); // collapse ▶ toggle
      const after = await page.screenshot({ clip: { x: 0, y: 0, ...VP } });
      const A = PNG.sync.read(before), B = PNG.sync.read(after);
      const changed = pixelmatch(A.data, B.data, null, A.width, A.height, { threshold: 0.1 }) / (A.width * A.height);
      if (process.env.DEBUG) console.log('  [collapse change]', (changed * 100).toFixed(1) + '%');
      r.push(['captain’s orders collapses', changed > 0.10]);
      return r;
    },
  },
};

async function typeLogin(page, email, pass) {
  await page.mouse.click(195, 432); await page.waitForTimeout(200); await page.keyboard.type(email, { delay: 8 });
  await page.mouse.click(195, 516); await page.waitForTimeout(200); await page.keyboard.type(pass, { delay: 8 });
}

let _token;
async function token() {
  if (_token) return _token;
  const res = await fetch('http://127.0.0.1:8011/api/mobile/login', {
    method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ ...CRED, device_name: 'scorecard' }),
  });
  _token = (await res.json()).token; return _token;
}

async function loginFlutter(page) {
  await page.mouse.click(195, 432); await page.waitForTimeout(200); await page.keyboard.type(CRED.email, { delay: 8 });
  await page.mouse.click(195, 516); await page.waitForTimeout(200); await page.keyboard.type(CRED.password, { delay: 8 });
  await page.mouse.click(195, 582); await page.waitForTimeout(5500);
}

// Turn on Flutter's semantics tree, then read labelled, positioned nodes.
async function enableSemantics(page) {
  await page.evaluate(() => {
    const ph = document.querySelector('flt-semantics-placeholder') || document.querySelector('[aria-label="Enable accessibility"]');
    if (ph) ph.click();
  });
  await page.waitForTimeout(600);
}
async function semantics(page) {
  return page.$$eval('flt-semantics', els => els.map(el => {
    const r = el.getBoundingClientRect();
    const input = el.querySelector('input,textarea');
    let label = (el.getAttribute('aria-label') || (input && input.value) || el.textContent || '').trim();
    if (!label && input) label = '[field]';
    return { label, cx: r.x + r.width / 2, cy: r.y + r.height / 2, w: r.width, h: r.height };
  }).filter(n => n.label));
}

// ---- MSSIM (mean structural similarity) over grayscale 8x8 windows ----------
function gray(png) {
  const g = new Float64Array(png.width * png.height);
  for (let i = 0; i < g.length; i++) {
    const d = png.data; const o = i * 4;
    g[i] = 0.299 * d[o] + 0.587 * d[o + 1] + 0.114 * d[o + 2];
  }
  return g;
}
// Canonical SSIM (Wang et al. 2004): 11-tap Gaussian window (sigma 1.5), separable
// convolution. Gaussian weighting is the published standard and correctly discounts
// text anti-aliasing that a uniform block window over-penalizes.
function gaussKernel() {
  const r = 5, sigma = 1.5, k = [];
  let sum = 0;
  for (let i = -r; i <= r; i++) { const v = Math.exp(-(i * i) / (2 * sigma * sigma)); k.push(v); sum += v; }
  return k.map(v => v / sum);
}
function blur(src, W, H, k) {
  const r = (k.length - 1) / 2;
  const tmp = new Float64Array(W * H), out = new Float64Array(W * H);
  for (let y = 0; y < H; y++) for (let x = 0; x < W; x++) {
    let s = 0; for (let j = -r; j <= r; j++) { const xx = Math.min(W - 1, Math.max(0, x + j)); s += src[y * W + xx] * k[j + r]; }
    tmp[y * W + x] = s;
  }
  for (let y = 0; y < H; y++) for (let x = 0; x < W; x++) {
    let s = 0; for (let j = -r; j <= r; j++) { const yy = Math.min(H - 1, Math.max(0, y + j)); s += tmp[yy * W + x] * k[j + r]; }
    out[y * W + x] = s;
  }
  return out;
}
function mssim(a, b, W, H) {
  const C1 = (0.01 * 255) ** 2, C2 = (0.03 * 255) ** 2, k = gaussKernel();
  const aa = new Float64Array(W * H), bb = new Float64Array(W * H), ab = new Float64Array(W * H);
  for (let i = 0; i < a.length; i++) { aa[i] = a[i] * a[i]; bb[i] = b[i] * b[i]; ab[i] = a[i] * b[i]; }
  const muA = blur(a, W, H, k), muB = blur(b, W, H, k);
  const sA = blur(aa, W, H, k), sB = blur(bb, W, H, k), sAB = blur(ab, W, H, k);
  let sum = 0;
  for (let i = 0; i < a.length; i++) {
    const ma = muA[i], mb = muB[i];
    const va = sA[i] - ma * ma, vb = sB[i] - mb * mb, cov = sAB[i] - ma * mb;
    sum += ((2 * ma * mb + C1) * (2 * cov + C2)) / ((ma * ma + mb * mb + C1) * (va + vb + C2));
  }
  return sum / (W * H);
}

// ---- run --------------------------------------------------------------------
const key = process.argv[2];
const spec = SCREENS[key];
if (!spec) { console.error('unknown screen', key); process.exit(1); }

const browser = await chromium.launch();

// WEB capture + measured element boxes (reduced motion = deterministic scene).
const wc = await browser.newContext({ viewport: VP, deviceScaleFactor: DSF, reducedMotion: 'reduce' });
const wp = await wc.newPage();
if (spec.auth) {
  await wp.goto(`${WEB}/go`, { waitUntil: 'networkidle' });
  await wp.fill('#email', CRED.email); await wp.fill('#password', CRED.password);
  await wp.click('button[type=submit]'); await wp.waitForTimeout(1500);
}
await wp.goto(spec.webUrl, { waitUntil: 'networkidle' });
await wp.waitForTimeout(1200);
if (spec.webPrep) await spec.webPrep(wp);
await wp.evaluate(() => document.activeElement && document.activeElement.blur());
await wp.screenshot({ path: `${dir}/${key}-web.png`, clip: { x: 0, y: 0, ...VP } });
const webBoxes = {};
for (const el of spec.layout) {
  webBoxes[el.name] = await wp.$eval(el.web, e => { const r = e.getBoundingClientRect(); return { cx: r.x + r.width / 2, cy: r.y + r.height / 2 }; }).catch(() => null);
}
await wc.close();

// FLUTTER capture pass: bring the app to the target screen, read semantics + shot.
const cc = await browser.newContext({ viewport: VP, deviceScaleFactor: DSF });
const cp = await cc.newPage();
await cp.goto(FLUTTER, { waitUntil: 'load' });
await cp.waitForTimeout(6500);
await spec.state(cp);
await enableSemantics(cp);
const flNodes = await semantics(cp);
await cp.evaluate(() => { try { document.activeElement && document.activeElement.blur(); } catch (e) {} });
await cp.screenshot({ path: `${dir}/${key}-flutter.png`, clip: { x: 0, y: 0, ...VP } });
await cc.close();

// FLUTTER functional pass: separate page that drives the flow.
const fc = await browser.newContext({ viewport: VP, deviceScaleFactor: DSF });
const fp = await fc.newPage();
await fp.goto(FLUTTER, { waitUntil: 'load' });
await fp.waitForTimeout(6500);
await enableSemantics(fp);
const funcResults = await spec.func(fp, () => semantics(fp));
await fc.close();
await browser.close();

// ---- score ------------------------------------------------------------------
// Functionality
const funcPass = funcResults.filter(r => r[1]).length;
const funcDev = 1 - funcPass / funcResults.length;
// Content
const contentHit = spec.content.filter(s => flNodes.some(n => n.label.toLowerCase().includes(s.toLowerCase()))).length;
const contentDev = 1 - contentHit / spec.content.length;
// Layout: mean normalized center error (>24px = full deviation for that element)
let layoutErr = [];
const matchNode = (el) => {
  const hits = flNodes.filter(n => el.fl.test(n.label));
  if (!hits.length) return null;
  return el.pickTop ? hits.sort((a, b) => a.cy - b.cy)[0] : hits.sort((a, b) => a.w * a.h - b.w * b.h)[0];
};
const dead = 6, full = 40; // <=6px imperceptible, >=40px = full deviation
for (const el of spec.layout) {
  const wb = webBoxes[el.name]; const fn = matchNode(el);
  if (process.env.DEBUG) console.log(`  [layout ${el.name}] web=${JSON.stringify(wb)} fl=${fn ? JSON.stringify({ cx: Math.round(fn.cx), cy: Math.round(fn.cy), label: fn.label.slice(0, 24) }) : 'NOT FOUND'}`);
  if (wb && fn) { const e = Math.hypot(wb.cx - fn.cx, wb.cy - fn.cy); layoutErr.push(Math.max(0, Math.min(1, (e - dead) / (full - dead)))); }
  else layoutErr.push(1);
}
const layoutDev = layoutErr.reduce((a, b) => a + b, 0) / layoutErr.length;
// Visual: MSSIM + pixel tripwire
const wa = PNG.sync.read(readFileSync(`${dir}/${key}-web.png`));
const fb = PNG.sync.read(readFileSync(`${dir}/${key}-flutter.png`));
const W = wa.width, H = wa.height;
const visualDev = Math.max(0, 1 - mssim(gray(wa), gray(fb), W, H));
const diff = new PNG({ width: W, height: H });
const px = pixelmatch(wa.data, fb.data, diff.data, W, H, { threshold: 0.12 });
const pixelPct = (px / (W * H) * 100);

const composite = 45 * funcDev + 20 * contentDev + 20 * layoutDev + 15 * visualDev; // in %
const pct = x => (x * 100).toFixed(1) + '%';
console.log(`\n=== SCORECARD: ${key} ===`);
console.log(`Functionality & Behaviour (45%)  dev ${pct(funcDev)}   [${funcPass}/${funcResults.length} checks]`);
for (const [name, ok] of funcResults) console.log(`    ${ok ? 'PASS' : 'FAIL'}  ${name}`);
console.log(`Content & Data parity     (20%)  dev ${pct(contentDev)}   [${contentHit}/${spec.content.length} strings]`);
console.log(`Layout & Structure        (20%)  dev ${pct(layoutDev)}   [${spec.layout.map((e, i) => e.name + ':' + pct(layoutErr[i])).join(', ')}]`);
console.log(`Visual appearance         (15%)  dev ${pct(visualDev)}   [MSSIM ${(1 - visualDev).toFixed(3)}; raw pixel ${pixelPct.toFixed(1)}%]`);
console.log(`-----------------------------------------------`);
console.log(`COMPOSITE DEVIATION: ${composite.toFixed(2)}%   ${composite <= 5 ? 'PASS (<=5%)' : 'FAIL (>5%)'}`);
