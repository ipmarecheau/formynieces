// Pixel-diff harness: capture web + Flutter at an identical viewport, overlay with
// pixelmatch, write web/flutter/diff PNGs, print mismatch %. Usage:
//   node pxdiff.mjs <name> <webUrl> <flutterSteps>
// flutterSteps: 'login' logs into the flutter app first; '' just loads.
import { chromium } from 'playwright';
import pixelmatch from 'pixelmatch';
import { PNG } from 'pngjs';
import { readFileSync, writeFileSync } from 'fs';

const [, , name, webUrl, mode] = process.argv;
const VP = { width: 390, height: 844 };
const DSF = 2;
const dir = '/tmp/parity/diff';

async function shot(page, path) {
  await page.evaluate(() => { try { document.activeElement && document.activeElement.blur(); } catch (e) {} });
  await page.screenshot({ path, clip: { x: 0, y: 0, width: VP.width, height: VP.height } });
}

const browser = await chromium.launch();

// --- web --- (reduced motion pins the sea scene's animations to their initial frame)
const wc = await browser.newContext({ viewport: VP, deviceScaleFactor: DSF, reducedMotion: 'reduce' });
const wp = await wc.newPage();
if (mode === 'welcome' || mode === 'voyage') {
  await wp.goto('http://127.0.0.1:8000/go', { waitUntil: 'networkidle', timeout: 30000 });
  await wp.fill('#email', 'emu-child@smoothseas.test');
  await wp.fill('#password', 'password');
  await wp.click('button[type=submit]');
  await wp.waitForTimeout(1500);
}
await wp.goto(webUrl, { waitUntil: 'networkidle', timeout: 30000 });
await wp.waitForTimeout(1200);
await shot(wp, `${dir}/${name}-web.png`);
await wc.close();

// --- flutter ---
const fc = await browser.newContext({ viewport: VP, deviceScaleFactor: DSF });
const fp = await fc.newPage();
await fp.goto('http://127.0.0.1:8021', { waitUntil: 'load' });
await fp.waitForTimeout(6500);
if (mode === 'login' || mode === 'welcome' || mode === 'voyage') {
  await fp.mouse.click(195, 432); await fp.waitForTimeout(300);
  await fp.keyboard.type('emu-child@smoothseas.test', { delay: 10 });
  await fp.mouse.click(195, 516); await fp.waitForTimeout(300);
  await fp.keyboard.type('password', { delay: 10 });
  await fp.mouse.click(195, 582); await fp.waitForTimeout(5500);
}
if (mode === 'voyage') { await fp.mouse.click(195, 690); await fp.waitForTimeout(5000); }
await shot(fp, `${dir}/${name}-flutter.png`);
await fc.close();
await browser.close();

// --- diff ---
const a = PNG.sync.read(readFileSync(`${dir}/${name}-web.png`));
const b = PNG.sync.read(readFileSync(`${dir}/${name}-flutter.png`));
const { width, height } = a;
const diff = new PNG({ width, height });
const mismatch = pixelmatch(a.data, b.data, diff.data, width, height, { threshold: 0.12 });
writeFileSync(`${dir}/${name}-diff.png`, PNG.sync.write(diff));
const pct = (mismatch / (width * height) * 100).toFixed(2);
console.log(`${name}: ${mismatch} px differ of ${width * height}  =  ${pct}%  (diff -> ${name}-diff.png)`);
