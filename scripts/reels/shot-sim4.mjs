import { chromium } from 'playwright-core';
const SP = '/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const b = await chromium.launch({ executablePath: '/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome', headless: true });
const p = await b.newPage({ viewport: { width: 1040, height: 1120 } });
const errs=[]; p.on('console',m=>{if(m.type()==='error')errs.push(m.text());});
await p.goto('file:///root/dev/formynieces/scripts/reels/out/sim-demo.html', { waitUntil: 'domcontentloaded' });
await new Promise((r) => setTimeout(r, 8700));   // lesson scrolled
await p.screenshot({ path: `${SP}/sim-lesson-scroll.png`, timeout: 50000 });
console.log('errors:', errs.slice(0,2).join(' | ')||'none');
await b.close();
