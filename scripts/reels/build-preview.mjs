import { readFileSync, writeFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = dirname(fileURLToPath(import.meta.url));
const root = join(dir, '../..');
const childSrc = `data:video/mp4;base64,${readFileSync(join(root, 'public/reels/child-reel.mp4')).toString('base64')}`;
const parentSrc = `data:video/mp4;base64,${readFileSync(join(root, 'public/reels/parent-reel.mp4')).toString('base64')}`;

const childBeats = [
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
const parentBeats = [
  ['🔐', 'Your parent portal', 'The honest layer — no streaks, no spin.'],
  ['🧭', 'Four honest answers', 'On pace? Exam when? This week’s target? How much mastered?'],
  ['🎯', 'One clear next step', 'The exam agent picks the single thing to focus on.'],
  ['📅', 'This week', 'Exactly the topics, reading and writing she’s on.'],
  ['📊', 'Pace, labelled', 'Against her own plan — never a scary bare number.'],
  ['🗓️', 'The whole year', 'A month-by-month calendar of what’s mastered and next.'],
  ['🎓', 'Projected placement', 'An indicative SEA tier, with an honest confidence signal.'],
  ['🎛️', 'You’re in control', 'Pause, grant a reward, or request a diagnostic retake.'],
  ['🏫', 'School papers, too', 'Graded classroom work, kept beside our own picture.'],
];
const beatList = (beats) => beats.map(([ic, t, d]) =>
  `<li><span class="ic">${ic}</span><div><b>${t}</b><span>${d}</span></div></li>`).join('\n    ');

const html = `<title>SmoothSeas Demo Reels</title>
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
  .tabs { display: inline-flex; gap: 6px; margin: 0 auto clamp(18px,3vw,26px); padding: 6px;
    background: var(--ground-2); border: 1px solid var(--line); border-radius: 999px; }
  .tabwrap { text-align: center; }
  .tab { border: 0; background: transparent; color: var(--muted); font: inherit; font-weight: 700;
    font-size: 14px; padding: 10px 20px; border-radius: 999px; cursor: pointer; transition: all .18s; }
  .tab.on { background: var(--teal); color: #04222a; }
  .reel[hidden] { display: none; }
  @media (prefers-reduced-motion: reduce) { video { } }
</style>

<div class="wrap">
  <header>
    <span class="eyebrow">Landing-page demo reels</span>
    <h1>Two real sails — for <span class="accent">child</span> and <span class="accent">parent</span>.</h1>
    <p class="sub">Both recorded straight from the app and narrated. No mockups, no slideshow — this is exactly what each person sees.</p>
  </header>

  <div class="tabwrap">
    <div class="tabs" role="tablist">
      <button class="tab on" role="tab" aria-controls="reel-child" aria-selected="true">▶ Child reel</button>
      <button class="tab" role="tab" aria-controls="reel-parent" aria-selected="false">▶ Parent reel</button>
    </div>
  </div>

  <section class="reel" id="reel-child">
    <figure>
      <video autoplay muted loop playsinline controls src="${childSrc}"></video>
    </figure>
    <p class="cap">~43s, narrated. Tap the <kbd>speaker</kbd> in the controls to hear the voiceover and music.</p>
    <ul class="beats">
    ${beatList(childBeats)}
    </ul>
  </section>

  <section class="reel" id="reel-parent" hidden>
    <figure>
      <video muted loop playsinline controls src="${parentSrc}"></video>
    </figure>
    <p class="cap">~41s, narrated. The guardian "honest layer" — dashboard, pace, estimator, controls and school journal.</p>
    <ul class="beats">
    ${beatList(parentBeats)}
    </ul>
  </section>

  <footer>SmoothSeas · child &amp; parent demo reels · for review before they ship to the landing page</footer>
</div>

<script>
  (function () {
    var tabs = [].slice.call(document.querySelectorAll('.tab'));
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        tabs.forEach(function (t) { var on = t === tab; t.classList.toggle('on', on); t.setAttribute('aria-selected', on); });
        document.querySelectorAll('.reel').forEach(function (r) {
          var on = r.id === tab.getAttribute('aria-controls');
          r.hidden = !on;
          var v = r.querySelector('video');
          if (v) { on ? v.play().catch(function () {}) : v.pause(); }
        });
      });
    });
  })();
</script>`;

writeFileSync(join(dir, 'out/child-reel-preview.html'), html);
console.log('Wrote out/child-reel-preview.html —', (html.length / 1024 / 1024).toFixed(2), 'MB');
