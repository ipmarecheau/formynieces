// Captures real app screens as SELF-CONTAINED HTML (inlined CSS, images & fonts as
// data URIs, scripts stripped) for the simulated-frontend demo. The "backend" is the
// seeded DemoStudent state. Writes scripts/reels/out/sim/screens.json.
//
// Usage: node scripts/reels/capture-screens.mjs

import { chromium } from 'playwright-core';
import { mkdirSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = dirname(fileURLToPath(import.meta.url));
const OUT = join(dir, 'out', 'sim');
mkdirSync(OUT, { recursive: true });

const BASE = 'http://127.0.0.1:8000';
const VIEWPORT = { width: 1280, height: 800 };
const EXE = '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome';
const wait = (ms) => new Promise((r) => setTimeout(r, ms));

const SCREENS = [
  { name: 'voyage', path: '/voyage',             caption: 'Sail the map — every skill is an island to conquer', target: '.co-duty-do, .island' },
  { name: 'lesson', path: '/practice/55/lesson', caption: 'Learn it — a hands-on lesson with interactive widgets', reveal: true, scroll: true, target: 'button:has-text("Check"), .lw-input, .lw-opt input, input' },
  { name: 'check',  path: '/practice/55',        caption: 'Ace the six-question check', target: '.pw-option' },
  { name: 'chat',   path: '/practice/55/lesson', caption: 'Stuck? Ask Smooth — the AI re-teaches, right in the chat', chat: true, target: '.cc-send' },
];

async function safeClick(page, selector, timeout = 1000) {
  try { await page.locator(selector).first().click({ timeout }); return true; } catch { return false; }
}

/** In-page inliner. keepOverlays=true keeps the chat panel (cc- and scw- widgets). */
const inline = (page, keepOverlays) => page.evaluate(async (keepOverlays) => {
  const toDataUrl = async (url) => {
    try { const b = await (await fetch(url)).blob();
      return await new Promise((r) => { const fr = new FileReader(); fr.onload = () => r(fr.result); fr.onerror = () => r(url); fr.readAsDataURL(b); });
    } catch { return url; }
  };
  const inlineCssUrls = async (css, baseHref) => {
    for (const u of [...new Set([...css.matchAll(/url\(\s*['"]?([^'")]+)['"]?\s*\)/g)].map((m) => m[1]).filter((x) => x && !x.startsWith('data:')))]) {
      let abs; try { abs = new URL(u, baseHref).href; } catch { continue; }
      const d = await toDataUrl(abs); if (d.startsWith('data:')) css = css.split(u).join(d);
    }
    return css;
  };
  for (const link of [...document.querySelectorAll('link[rel="stylesheet"]')]) {
    try { const href = link.href; let css = await (await fetch(href)).text(); css = await inlineCssUrls(css, href);
      const s = document.createElement('style'); s.textContent = css; link.replaceWith(s); } catch {}
  }
  for (const style of [...document.querySelectorAll('style')]) {
    if (/url\(/.test(style.textContent)) style.textContent = await inlineCssUrls(style.textContent, location.href);
  }
  for (const img of [...document.querySelectorAll('img')]) {
    if (img.src && !img.src.startsWith('data:')) img.src = await toDataUrl(img.src);
    img.removeAttribute('srcset');
  }
  for (const el of [...document.querySelectorAll('[style*="url("]')]) el.setAttribute('style', await inlineCssUrls(el.getAttribute('style'), location.href));
  const kill = 'script, noscript, link[rel="modulepreload"], link[rel="preload"], link[as="script"]'
    + (keepOverlays ? '' : ', [class*="scw-"], [id^="scw"], [class^="cc-"], [class*=" cc-"], [aria-label*="Chat with Smooth"]');
  document.querySelectorAll(kill).forEach((el) => el.remove());
}, keepOverlays);

const browser = await chromium.launch({ executablePath: EXE, headless: true });
const context = await browser.newContext({ viewport: VIEWPORT });
const page = await context.newPage();

await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
await page.fill('#email', 'demo-student@smoothseas.test');
await page.fill('#password', 'smoothseas');
await Promise.all([page.waitForLoadState('networkidle'), page.click('button[type="submit"]')]);

const screens = [];
for (const s of SCREENS) {
  await page.goto(`${BASE}${s.path}`, { waitUntil: 'networkidle' });
  await wait(1400);
  for (let i = 0; i < 2; i++) { if (await safeClick(page, 'button:has-text("Got it")', 1000)) await wait(400); }

  if (s.reveal) {
    // walk the lesson forward so the whole thing (incl. interactive widgets) is on the page
    await safeClick(page, '.lw-start, button:has-text("Let\'s do it")', 1200); await wait(700);
    for (let step = 0; step < 14; step++) {
      if (await safeClick(page, '.lw-next', 900)) { await wait(650); continue; }
      const opts = page.locator('.lw-opt:not([disabled])');
      const n = await opts.count().catch(() => 0);
      if (n > 0) {
        for (let k = 0; k < n; k++) { try { await opts.nth(k).click({ timeout: 700 }); await wait(450); } catch {} if (await page.locator('.lw-next').count()) break; }
        continue;
      }
      break;
    }
    await page.evaluate(() => window.scrollTo(0, 0)); await wait(300);
  }

  if (s.chat) {
    await safeClick(page, '.cc-fab, [aria-label*="Chat with Smooth"]', 1500); await wait(700);
    // ask a real content question — with no active re-teach session the AI truly answers
    try { await page.fill('.cc-input', 'Why is it e before i in “receive”?'); await wait(300); await page.click('.cc-send'); } catch {}
    // wait for a substantive assistant reply (the real LLM answer, not a short nudge)
    await page.waitForFunction(() => {
      const m = [...document.querySelectorAll('.cc-msg.assistant')].pop();
      return m && m.textContent.trim().length > 70;
    }, { timeout: 30000 }).catch(() => {});
    await wait(1000);
    // force the panel visibly open in the static snapshot
    await page.evaluate(() => {
      document.querySelectorAll('[x-cloak]').forEach((e) => e.removeAttribute('x-cloak'));
      const w = document.querySelector('.cc-widget'); if (w) w.style.display = 'block';
      const p = document.querySelector('.cc-panel'); if (p) p.style.display = 'flex';
      const bd = document.querySelector('.cc-backdrop'); if (bd) bd.style.display = 'block';
    }).catch(() => {});
  } else {
    // remove floating overlays (chat FAB, banners) by computed position — keep top nav
    await page.evaluate(() => {
      const vh = innerHeight, vw = innerWidth;
      [...document.querySelectorAll('body *')].forEach((el) => {
        if (getComputedStyle(el).position !== 'fixed') return;
        const r = el.getBoundingClientRect();
        if (r.top > vh * 0.5 || (r.left > vw * 0.66 && r.top > vh * 0.35)) el.remove();
      });
    }).catch(() => {});
  }

  let target = null;
  try { const b = await page.locator(s.target).first().boundingBox({ timeout: 1500 }); if (b) target = { x: Math.round(b.x + b.width / 2), y: Math.round(b.y + b.height / 2) }; } catch {}

  await inline(page, !!s.chat);
  const html = await page.evaluate(() => '<!doctype html>\n' + document.documentElement.outerHTML);
  const scrollH = await page.evaluate(() => document.body.scrollHeight);
  screens.push({ name: s.name, caption: s.caption, target, scroll: !!s.scroll, scrollH, html });
  console.log(`captured ${s.name}: ${(html.length / 1024).toFixed(0)}KB, scrollH=${scrollH}, target=${target ? `${target.x},${target.y}` : 'none'}`);
}

writeFileSync(join(OUT, 'screens.json'), JSON.stringify({ viewport: VIEWPORT, screens }));
console.log(`\nWrote screens.json (${screens.length} screens)`);
await browser.close();
