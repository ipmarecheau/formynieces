import { chromium } from 'playwright-core';
const SP='/tmp/claude-0/-root-dev-formynieces/94033ef5-0d34-4bf7-800c-4a9632257575/scratchpad';
const b=await chromium.launch({executablePath:'/root/.cache/ms-playwright/chromium-1223/chrome-linux64/chrome',headless:true});
for(const [name,w,h] of [['bar-desktop',1200,520],['bar-mobile',390,760]]){
  const p=await b.newPage({viewport:{width:w,height:h}});
  await p.goto('http://127.0.0.1:8000/?x='+Date.now(),{waitUntil:'domcontentloaded'});
  await new Promise(r=>setTimeout(r,1500));
  await p.screenshot({path:SP+'/'+name+'.png',animations:'disabled',timeout:40000});
  await p.close();
}
await b.close();console.log('ok');
