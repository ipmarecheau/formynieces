import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = dirname(fileURLToPath(import.meta.url));
const root = join(dir, '../..');
const mp4 = readFileSync(join(root, 'public/reels/child-reel.mp4')).toString('base64');
const videoSrc = `data:video/mp4;base64,${mp4}`;

const beats = [
  ['🔑', 'Sign in', 'Pick up exactly where they left off — no setup.'],
  ['🌅', 'Daily warm-up', 'A short reading passage and the day’s vocabulary.'],
  ['✍️', 'Writing stop', 'One focused prompt, marked with kind, specific feedback.'],
  ['🗺️', 'The island map', 'The whole curriculum — skills to sail to and conquer.'],
  ['🏆', 'Perks in the Locker', 'Rewards collect as you sail, yours to spend.'],
  ['🛟', 'Life happens', 'Take a day off, skip a duty, or rescue a lost streak.'],
  ['🎯', 'Three ways to master', 'Ace it cold, learn it, or let Smooth re-teach it.'],
  ['✅', 'Ace the quick check', 'Six right and it’s mastered — no lesson needed.'],
  ['📖', 'Or learn it', 'A short, hands-on interactive lesson.'],
  ['💛', 'Or Smooth re-teaches', 'Miss a rule? Take it again together, then prove it.'],
];

const html = `<title>Child Voyage Reel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;600;700&display=swap">
<style>
  :root {
    --ground: #0a1730;
    --ground-2: #0d1c39;
    --surface: #12244a;
    --line: rgba(120, 170, 220, .18);
    --ink: #eaf1fb;
    --muted: #93a8c8;
    --teal: #3fc0d4;
    --gold: #f5b544;
    --shadow: 0 30px 80px rgba(3, 12, 30, .6);
  }
  * { box-sizing: border-box; }
  body {
    margin: 0;
    background:
      radial-gradient(1100px 620px at 50% -12%, #17315f 0%, transparent 60%),
      var(--ground);
    color: var(--ink);
    font-family: 'Nunito', system-ui, sans-serif;
    line-height: 1.55;
    -webkit-font-smoothing: antialiased;
  }
  .wrap {
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(28px, 6vw, 64px) 20px 64px;
  }
  header { text-align: center; margin-bottom: clamp(22px, 4vw, 34px); }
  .eyebrow {
    display: inline-block;
    font-weight: 700;
    font-size: 12px;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: var(--teal);
    margin-bottom: 12px;
  }
  h1 {
    font-family: 'Baloo 2', system-ui, sans-serif;
    font-weight: 800;
    font-size: clamp(30px, 6vw, 46px);
    line-height: 1.05;
    margin: 0 0 12px;
    text-wrap: balance;
  }
  h1 .accent { color: var(--gold); }
  .sub { color: var(--muted); font-size: clamp(15px, 2.4vw, 17px); max-width: 52ch; margin: 0 auto; }
  figure {
    margin: 0;
    border-radius: 20px;
    overflow: hidden;
    border: 1px solid var(--line);
    box-shadow: var(--shadow);
    background: #08152c;
    aspect-ratio: 16 / 10;
  }
  video { display: block; width: 100%; height: 100%; object-fit: cover; }
  .cap {
    text-align: center;
    color: var(--muted);
    font-size: 13px;
    margin: 14px auto 0;
  }
  .cap kbd {
    font-family: inherit;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: 6px;
    padding: 1px 7px;
    color: var(--ink);
  }
  .beats {
    list-style: none;
    padding: 0;
    margin: clamp(30px, 5vw, 44px) 0 0;
    display: grid;
    gap: 10px;
  }
  .beats li {
    display: grid;
    grid-template-columns: 40px 1fr;
    gap: 14px;
    align-items: start;
    background: linear-gradient(180deg, var(--ground-2), var(--ground));
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 14px 16px;
  }
  .beats .ic {
    font-size: 22px;
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    background: var(--surface);
    border-radius: 10px;
  }
  .beats b { font-family: 'Baloo 2', sans-serif; font-weight: 700; display: block; font-size: 15.5px; }
  .beats span { color: var(--muted); font-size: 14px; }
  footer { text-align: center; color: var(--muted); font-size: 13px; margin-top: 36px; }
  @media (prefers-reduced-motion: reduce) { video { } }
</style>

<div class="wrap">
  <header>
    <span class="eyebrow">Landing-page demo · child reel</span>
    <h1>A real sail through the <span class="accent">Voyage</span>.</h1>
    <p class="sub">Recorded straight from the app, sped up for the landing page. No mockups, no slideshow — this is what a child actually sees.</p>
  </header>

  <figure>
    <video autoplay muted loop playsinline controls src="${videoSrc}"></video>
  </figure>
  <p class="cap">~43s, narrated. Tap the <kbd>speaker</kbd> in the controls to hear the voiceover and music.</p>

  <ul class="beats">
    ${beats.map(([ic, t, d]) => `<li><span class="ic">${ic}</span><div><b>${t}</b><span>${d}</span></div></li>`).join('\n    ')}
  </ul>

  <footer>SmoothSeas · child gameplay reel · for review before it ships to the landing page</footer>
</div>`;

writeFileSync(join(dir, 'out/child-reel-preview.html'), html);
console.log('Wrote out/child-reel-preview.html —', (html.length / 1024 / 1024).toFixed(2), 'MB');
