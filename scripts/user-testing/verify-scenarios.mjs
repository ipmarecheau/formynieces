// scripts/user-testing/verify-scenarios.mjs
//
// Pre-publish Playwright check for the /user-testing guided walkthrough. It drives the real
// walkthrough in a headless browser exactly as a human tester would — signs in, opens each
// web feature, walks every scenario, and asserts the embedded preview lands on the screen
// under test rather than a login or email-verification gate. The user-testing feature is the
// one feature that cannot be verified through /user-testing itself (it can't test itself), so
// this script is how we prove, before releasing a scenario, that nothing gates the tester out.
//
// Usage:
//   node scripts/user-testing/verify-scenarios.mjs            # against http://127.0.0.1:8000
//   UT_BASE=https://dev.smoothseas.org node scripts/user-testing/verify-scenarios.mjs
//
// Requires the dev server running and USER_TESTING_IMPERSONATION enabled (default off prod).
// Device features (LR, PN) are intentionally excluded — they install a real app and cannot be
// exercised in an embedded preview.

import { chromium } from 'playwright-core';
import { existsSync } from 'node:fs';
import { globSync } from 'node:fs';

const BASE = process.env.UT_BASE || 'http://127.0.0.1:8000';

// Find the Playwright-managed Chromium without requiring a system install.
function chromiumPath() {
  if (process.env.UT_CHROMIUM && existsSync(process.env.UT_CHROMIUM)) return process.env.UT_CHROMIUM;
  const hits = globSync(`${process.env.HOME}/.cache/ms-playwright/chromium-*/chrome-linux*/chrome`);
  return hits.length ? hits.sort().at(-1) : undefined; // undefined -> Playwright's default
}

// Expected landing screen per web scenario — must match TestingCatalog::features() starts.
const EXPECT = {
  HC: { path: '/user-testing/hc', scenarios: {
    'HC-01': '/guardian/dashboard', 'HC-02': '/guardian/dashboard',
    'HC-03': '/voyage', 'HC-04': '/guardian/dashboard', 'HC-05': '/' } },
  GD: { path: '/user-testing/gd', scenarios: {
    'GD-01': '/guardian/dashboard', 'GD-02': '/guardian/progress', 'GD-05': '/guardian/dashboard' } },
  AL: { path: '/user-testing/al', scenarios: {
    'AL-01': '/admin/student-logs', 'AL-02': '/admin/student-logs', 'AL-03': '/admin/student-logs' } },
  SH: { path: '/user-testing/sh', scenarios: {
    'SH-01': '/voyage', 'SH-02': '/voyage', 'SH-03': '/voyage', 'SH-04': '/voyage' } },
  AM: { path: '/user-testing/am', scenarios: {
    'AM-01': '/voyage' } },
};

const b = await chromium.launch({ headless: true, executablePath: chromiumPath() });
const ctx = await b.newContext({ permissions: ['microphone'] });
const p = await ctx.newPage();

let failures = 0;
const check = (ok, msg) => { console.log(`  ${ok ? '✓' : '✗'} ${msg}`); if (!ok) failures++; };

// Sign in as a tester (email uniquely identifies them; persists their place).
await p.goto(`${BASE}/user-testing`, { waitUntil: 'networkidle' });
await p.fill('#email', 'verify@example.com');
await p.fill('#name', 'Verify Tester');
await p.click('button:has-text("Start testing")');
await p.waitForLoadState('networkidle');

for (const cfg of Object.values(EXPECT)) {
  const ids = Object.keys(cfg.scenarios);
  await p.goto(`${BASE}${cfg.path}`, { waitUntil: 'networkidle' });
  for (let i = 0; i < ids.length; i++) {
    const id = ids[i];
    if (i > 0) {
      await p.locator('button:has-text("Next")').first().click();
      await p.waitForFunction(
        sid => document.querySelector('.ut-tag')?.textContent.trim() === sid,
        id, { timeout: 10000 },
      ).catch(() => {});
    }
    const handle = await p.waitForSelector('iframe.ut-frame', { timeout: 10000 }).catch(() => null);
    await p.waitForTimeout(2500); // enter -> redirect -> target settle
    const child = handle ? await handle.contentFrame() : null;
    const url = child ? child.url().replace(BASE, '') : '(no frame)';
    const html = child ? await child.content().catch(() => '') : '';
    const onLogin = /\/login/.test(url) || /type=["']password["']/i.test(html);
    const onVerify = /verify/i.test(url);
    const want = cfg.scenarios[id];
    const ok = (url === want || (want === '/' && (url === '/' || url === ''))) && !onLogin && !onVerify;
    check(ok, `${id} lands on ${want} (no login/verify gate) — got ${url}`);
  }
}

await b.close();
console.log(failures === 0 ? '\nALL WEB SCENARIOS PASS' : `\n${failures} FAILURE(S)`);
process.exit(failures === 0 ? 0 : 1);
