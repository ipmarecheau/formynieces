import { chromium } from 'playwright-core';
const browser = await chromium.launch({
  executablePath: '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',
  headless: true,
});
const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });
await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle' });
await page.locator('#see-it').scrollIntoViewIfNeeded();
await new Promise((r) => setTimeout(r, 1500));
const info = await page.evaluate(() => {
  const v = document.querySelector('#see-it video');
  return v ? { hasVideo: true, paused: v.paused, w: v.videoWidth, h: v.videoHeight, src: v.currentSrc } : { hasVideo: false };
});
console.log(JSON.stringify(info));
await page.locator('#see-it').screenshot({ path: 'scripts/reels/out/landing-section.png' });
await browser.close();
