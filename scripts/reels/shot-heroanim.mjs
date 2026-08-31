import { chromium } from 'playwright-core';
const SP='/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const b=await chromium.launch({executablePath:'/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',headless:true});
const p=await b.newPage({viewport:{width:1200,height:900}});
await p.goto('http://127.0.0.1:8000/',{waitUntil:'networkidle'});
await p.locator('.hero-anim').scrollIntoViewIfNeeded();
// seek all animations to scene 2 (~6s) and pause for a crisp shot
await new Promise(r=>setTimeout(r,600));
await p.evaluate(()=>document.getAnimations().forEach(a=>{try{a.currentTime=6000;a.pause();}catch(e){}}));
await new Promise(r=>setTimeout(r,150));
await p.locator('.hero-anim').screenshot({path:SP+'/heroanim.png'});
await b.close();console.log('ok');
