import { chromium } from 'playwright-core';
const SP='/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const b=await chromium.launch({executablePath:'/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',headless:true});
const p=await b.newPage({viewport:{width:1200,height:1050}});
await p.goto('http://127.0.0.1:8000/?x='+Date.now(),{waitUntil:'domcontentloaded'});
await new Promise(r=>setTimeout(r,3000));
await p.screenshot({path:SP+'/herotop3.png',animations:'disabled',timeout:45000});
await b.close();console.log('ok');
