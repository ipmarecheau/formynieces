// Component-level STATE parity: drives an interactive component through its own
// state machine (not the page's) and verifies each state renders the web's content.
// Example: the voyage Captain's Orders panel — Orders/Locker/Journal/Logs tabs +
// collapse⇄expand. DOM-clicks the components' semantic nodes (works for setState).
import { chromium } from 'playwright';
import { PNG } from 'pngjs';

const VP = { width: 390, height: 844 }, DSF = 2;
const FLUTTER = 'http://127.0.0.1:8021';
const CRED = { email: 'emu-child@smoothseas.test', password: 'password' };

// The Captain's Orders component state machine (control label → expected content).
const COMPONENT = {
  name: 'captains-orders',
  states: [
    { name: 'orders (default)', click: null, sig: /today.?s orders|clear them|morning tide|sail the map/i },
    { name: 'locker', click: 'Locker', sig: /locker is empty|rewards to sail|shore leave|lifebuoy|tailwind|anchor/i },
    { name: 'journal', click: 'Journal', sig: /voyage streak|day voyage|master your first|conquer/i },
    { name: 'logs', click: 'Logs', sig: /voyage log|log begins|sail on|shore leave|cleared|at sea/i },
    { name: 'back to orders', click: 'Orders', sig: /today.?s orders|clear them|morning tide/i },
    { name: 'collapsed', click: '▶', sig: /captain.?s orders/i },
    { name: 're-expanded', click: '▸', sig: /morning muster|evening watch|today.?s orders/i },
  ],
};

async function enableSemantics(page) {
  await page.evaluate(() => { const ph = document.querySelector('flt-semantics-placeholder') || document.querySelector('[aria-label="Enable accessibility"]'); if (ph) ph.click(); });
  await page.waitForTimeout(500);
}
async function labels(page) {
  return page.$$eval('flt-semantics', els => els.map(e => (e.getAttribute('aria-label') || e.textContent || '').trim()).filter(Boolean).join(' | '));
}
async function domClick(page, label) {
  return page.evaluate((l) => { const el = [...document.querySelectorAll('flt-semantics')].find(e => new RegExp('^' + l + '$', 'i').test((e.getAttribute('aria-label') || e.textContent || '').trim())); if (el) { el.click(); return true; } return false; }, label);
}
async function tapGold(page) {
  const png = PNG.sync.read(await page.screenshot({ clip: { x: 0, y: 0, ...VP } }));
  let best = -1, bc = 0;
  for (let y = Math.floor(png.height * 0.35); y < png.height - 4; y += 2) {
    let c = 0; for (let x = 0; x < png.width; x += 4) { const o = (y * png.width + x) * 4; if (png.data[o] > 215 && png.data[o + 1] > 150 && png.data[o + 2] < 95) c++; }
    if (c > bc) { bc = c; best = y; }
  }
  if (best > 0 && bc > 20) await page.mouse.click(195, best / DSF);
}

const b = await chromium.launch();
const p = await (await b.newContext({ viewport: VP, deviceScaleFactor: DSF })).newPage();
await p.goto(FLUTTER, { waitUntil: 'load' }); await p.waitForTimeout(6500);
await p.mouse.click(195, 432); await p.waitForTimeout(200); await p.keyboard.type(CRED.email, { delay: 8 });
await p.mouse.click(195, 516); await p.waitForTimeout(200); await p.keyboard.type(CRED.password, { delay: 8 });
await p.mouse.click(195, 582); await p.waitForTimeout(5500);
await tapGold(p); await p.waitForTimeout(7000); // continue -> voyage
await enableSemantics(p);

const results = [];
for (const st of COMPONENT.states) {
  if (st.click) { const ok = await domClick(p, st.click); if (!ok) { results.push([st.name, false, 'control not found']); continue; } await p.waitForTimeout(1200); }
  const txt = await labels(p);
  results.push([st.name, st.sig.test(txt), '']);
}
await b.close();

const pass = results.filter(r => r[1]).length;
const dev = 1 - pass / results.length;
console.log(`\n=== COMPONENT-STATE PARITY: ${COMPONENT.name} ===`);
for (const [name, ok, note] of results) console.log(`   ${ok ? 'MATCH  ' : 'MISSING'} ${name}${note ? '  (' + note + ')' : ''}`);
console.log(`\nstates reproduced: ${pass}/${results.length}`);
console.log(`COMPONENT-STATE DEVIATION: ${(dev * 100).toFixed(1)}%`);
