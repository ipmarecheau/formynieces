import { chromium } from 'playwright-core';
const SP='/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const b=await chromium.launch({executablePath:'/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',headless:true});
const p=await b.newPage({viewport:{width:1200,height:1000}});
const errs=[];p.on('pageerror',e=>errs.push('PE:'+e.message));
await p.goto('http://127.0.0.1:8000/?x='+Date.now(),{waitUntil:'networkidle'});
await p.locator('.sim-stage').scrollIntoViewIfNeeded();
await new Promise(r=>setTimeout(r,2600));   // scene 1 (voyage) loaded + shown
const info=await p.evaluate(()=>{const f=document.querySelector('.sim-frame'); return {frameSrc:f&&f.src?f.src.split('/').pop():'none', onFrames:document.querySelectorAll('.sim-frame.on').length};});
console.log(JSON.stringify(info),'errors:',errs.slice(0,2).join(' | ')||'none');
await p.locator('.sim-stage').screenshot({path:SP+'/simsite.png',timeout:40000});
await b.close();
