import { chromium } from 'playwright-core';
const SP = '/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const browser = await chromium.launch({ executablePath: '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome', headless: true });
const page = await browser.newPage({ viewport: { width: 1000, height: 1000 } });
const errs = [];
page.on('console', (m) => { if (m.type() === 'error') errs.push(m.text()); });
await page.goto('file:///root/dev/formynieces/scripts/reels/out/sim-demo.html', { waitUntil: 'networkidle' });
await new Promise((r) => setTimeout(r, 1500));
const names = ['v', 'l', 'c', 'r'];
for (let i = 0; i < 4; i++) {
  await page.locator('#stage').screenshot({ path: `${SP}/sim-${names[i]}.png`, animations: 'disabled', timeout: 9000 }).catch((e) => console.log('shot', i, e.message));
  await new Promise((r) => setTimeout(r, 4200));
}
console.log('errors:', errs.length ? errs.slice(0, 3).join(' | ') : 'none');
await browser.close();
