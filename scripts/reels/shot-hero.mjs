import { chromium } from 'playwright-core';
const browser = await chromium.launch({
  executablePath: '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',
  headless: true,
});
const page = await browser.newPage({ viewport: { width: 1280, height: 860 } });
await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
await new Promise((r) => setTimeout(r, 1500));
// Child tab (default)
await page.locator('.hero').screenshot({ path: 'scripts/reels/out/hero-child.png' });
const child = await page.evaluate(() => {
  const v = document.querySelector('#pane-child video');
  return { hasVideo: !!v, paused: v ? v.paused : null, src: v ? v.currentSrc : null };
});
// Switch to Parent tab
await page.click('#tab-parent');
await new Promise((r) => setTimeout(r, 900));
await page.locator('.hero').screenshot({ path: 'scripts/reels/out/hero-parent.png' });
const parent = await page.evaluate(() => ({
  parentVisible: !document.getElementById('pane-parent').hidden,
  childHidden: document.getElementById('pane-child').hidden,
  childPaused: document.querySelector('#pane-child video').paused,
}));
console.log(JSON.stringify({ child, parent }));
await browser.close();
