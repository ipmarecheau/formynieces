import { chromium } from 'playwright-core';
const BASE = 'http://127.0.0.1:8000';
const browser = await chromium.launch({
  executablePath: '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',
  headless: true,
});
const ctx = await browser.newContext({ viewport: { width: 1280, height: 860 } });
const page = await ctx.newPage();
const errors = [];
page.on('pageerror', (e) => errors.push('pageerror: ' + e.message));
page.on('response', (r) => { if (r.status() >= 500) errors.push('HTTP ' + r.status() + ' ' + r.url()); });

await page.goto(`${BASE}/login`, { waitUntil: 'networkidle' });
await page.fill('#email', 'demo-guardian@smoothseas.test');
await page.fill('#password', 'smoothseas');
await Promise.all([page.waitForLoadState('networkidle'), page.click('button[type="submit"]')]);
await page.goto(`${BASE}/guardian/dashboard`, { waitUntil: 'networkidle' });
await new Promise((r) => setTimeout(r, 3500)); // let wire:init AI summary settle
await page.screenshot({ path: 'scripts/reels/out/frames/g-overview.png', fullPage: false });

const sections = ['this-week', 'pace', 'estimator', 'rewards'];
for (const s of sections) {
  const clicked = await page.evaluate((sec) => {
    const el = [...document.querySelectorAll('[wire\\:click]')].find((e) =>
      (e.getAttribute('wire:click') || '').includes(`'section', '${sec}'`));
    if (el) { el.click(); return true; }
    return false;
  }, s);
  await new Promise((r) => setTimeout(r, 1600));
  await page.screenshot({ path: `scripts/reels/out/frames/g-${s}.png`, fullPage: false });
  console.log(`section ${s}: clicked=${clicked}`);
}
console.log('ERRORS:', errors.length ? JSON.stringify(errors) : 'none');
await browser.close();
