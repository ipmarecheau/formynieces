// Parity capture — screenshots the web app at a phone viewport for each surface, so the
// Flutter build has a visual reference. Pairs with parity.yml / mobile:parity-audit.
//
// Run from a checkout that has Playwright installed (the main worktree), against the web
// dev server (default http://127.0.0.1:8000). Provision demo accounts first:
//   php artisan onboard:demo --state=complete   (prints a student + guardian login)
//
// Usage:
//   node parity-capture.mjs <studentEmail> <studentPass> <guardianEmail> <guardianPass> [baseUrl] [outDir]
//
// Screens are listed here (route + tag); add rows as web surfaces are captured.

import { chromium } from 'playwright';

const [, , sEmail, sPass, gEmail, gPass, baseArg, outArg] = process.argv;
const base = baseArg || 'http://127.0.0.1:8000';
const outDir = outArg || 'formynieces-spec/mobile/parity/web';

const STUDENT = [
  ['/voyage', 'voyage'],
  ['/voyage/feather-isle', 'island'],
  ['/morning/reading', 'morning-reading'],
  ['/morning/vocabulary', 'morning-vocabulary'],
  ['/writing', 'writing'],
  ['/welcome-back', 'welcome-back'],
];
const GUARDIAN = [
  ['/guardian/dashboard', 'guardian-dashboard'],
  ['/guardian/progress', 'guardian-progress'],
  ['/guardian/agent', 'guardian-agent'],
  ['/guardian/writing', 'guardian-writing'],
];

async function run(browser, email, pass, screens, label) {
  const ctx = await browser.newContext({ viewport: { width: 390, height: 844 }, deviceScaleFactor: 2 });
  const page = await ctx.newPage();
  await page.goto(base + '/login', { waitUntil: 'networkidle' });
  await page.fill('input[type=email], #email', email);
  await page.fill('input[type=password], #password', pass);
  await page.click('button[type=submit]');
  await page.waitForTimeout(1800);
  for (const [route, tag] of screens) {
    try {
      await page.goto(base + route, { waitUntil: 'networkidle' });
      await page.waitForTimeout(1200);
      await page.screenshot({ path: `${outDir}/${label}-${tag}.png`, fullPage: true });
      console.log(`ok  ${label}-${tag}  <- ${page.url()}`);
    } catch (e) {
      console.log(`ERR ${label}-${tag}: ${e.message}`);
    }
  }
  await ctx.close();
}

const browser = await chromium.launch();
if (sEmail) await run(browser, sEmail, sPass, STUDENT, 'child');
if (gEmail) await run(browser, gEmail, gPass, GUARDIAN, 'parent');
await browser.close();
console.log('done →', outDir);
