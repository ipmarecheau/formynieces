import { chromium } from 'playwright-core';
const SP='/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const b=await chromium.launch({executablePath:'/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',headless:true});
const p=await b.newPage({viewport:{width:1200,height:900}});
const errs=[]; p.on('console',m=>{if(m.type()==='error')errs.push(m.text());}); p.on('pageerror',e=>errs.push('PE:'+e.message));
await p.goto('http://127.0.0.1:8000/?nocache='+Date.now(),{waitUntil:'networkidle'});
await p.locator('.hero-anim').scrollIntoViewIfNeeded();
await new Promise(r=>setTimeout(r,1200));   // natural, scene 1
const vis = await p.evaluate(()=>{const a=document.querySelector('.hero-anim'); const s1=document.querySelector('.hero-anim .s1'); return {animOpacity:getComputedStyle(a).opacity, hasIn:a.classList.contains('in'), s1Opacity:getComputedStyle(s1).opacity, anims:document.getAnimations().length};});
console.log(JSON.stringify(vis), 'errors:', errs.slice(0,3).join(' | ')||'none');
await p.locator('.hero-anim').screenshot({path:SP+'/heroanim-natural.png'});
await b.close();
