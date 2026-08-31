import { chromium } from 'playwright-core';
const SP = '/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const browser = await chromium.launch({ executablePath: '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome', headless: true });
const page = await browser.newPage({ viewport: { width: 900, height: 1100 }, deviceScaleFactor: 1 });
await page.goto('file://' + SP + '/how-it-works.html', { waitUntil: 'networkidle' });
// pause the CSS clock at chosen scene centers by seeking via animation currentTime
const centers = { s1: 2000, s2: 6000, s3: 10000, s4: 14000, s5: 18000, s6: 22000 };
for (const [name, t] of Object.entries(centers)) {
  await page.evaluate((t) => {
    document.getAnimations().forEach((a) => { a.currentTime = t; a.pause(); });
  }, t);
  await new Promise((r) => setTimeout(r, 120));
  await page.locator('.stage').screenshot({ path: `${SP}/svg-${name}.png` });
}
await browser.close();
console.log('done');
