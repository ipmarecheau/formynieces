import { chromium } from 'playwright-core';
const SP = '/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const b = await chromium.launch({ executablePath: '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome', headless: true });
const p = await b.newPage({ viewport: { width: 1040, height: 1120 } });
await p.goto('file:///root/dev/formynieces/scripts/reels/out/sim-demo.html', { waitUntil: 'domcontentloaded' });
await new Promise((r) => setTimeout(r, 2700));   // settled in scene 1 (fade-ins done, before next transition)
await p.screenshot({ path: `${SP}/sim3.png`, timeout: 50000 });
await b.close(); console.log('ok');
