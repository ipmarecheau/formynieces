<div class="pr-root">
    <style>
        .pr-root { max-width: 620px; margin: 0 auto; padding: clamp(28px, 6vw, 64px) 20px 80px; }
        .pr-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 26px; }
        .pr-anchor { width: 38px; height: 38px; border-radius: 11px; display: grid; place-items: center; background: linear-gradient(155deg, var(--teal), var(--teal-deep)); color: #fff; font-size: 19px; }
        .pr-brand b { font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 19px; }
        .pr-brand span { display: block; font-size: 10.5px; letter-spacing: .2em; text-transform: uppercase; color: var(--teal); font-weight: 800; margin-top: -2px; }
        .pr-eyebrow { font-size: 12px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: var(--teal); margin: 0 0 10px; }
        .pr-h1 { font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: clamp(27px, 5vw, 40px); line-height: 1.08; margin: 0 0 14px; text-wrap: balance; }
        .pr-h1 em { font-style: normal; color: var(--teal); }
        .pr-lede { font-size: clamp(15px, 2.1vw, 18px); color: var(--ink-soft); margin: 0 0 24px; }
        .pr-card { background: var(--paper-2); border: 1px solid var(--line); border-radius: 18px; padding: 24px; box-shadow: 0 18px 44px rgba(15,40,55,.08); }
        .pr-field { margin-bottom: 16px; }
        .pr-field label { display: block; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--ink-soft); margin-bottom: 6px; }
        .pr-field input, .pr-field select { width: 100%; font-family: inherit; font-size: 15px; color: var(--ink); background: var(--paper); border: 1px solid var(--line); border-radius: 10px; padding: 12px 14px; }
        .pr-field .opt { font-weight: 600; color: var(--ink-soft); text-transform: none; letter-spacing: 0; }
        .pr-err { color: #c0392b; font-size: 12.5px; font-weight: 700; margin-top: 5px; }
        .pr-cta { width: 100%; background: linear-gradient(160deg, #ffd15c, #f2941f); color: #241505; font-family: 'Fredoka', sans-serif; font-weight: 600; font-size: 17px; border: 0; border-radius: 12px; padding: 15px; cursor: pointer; box-shadow: 0 10px 24px rgba(242,169,0,.3); }
        .pr-cta:hover { filter: brightness(1.03); }
        .pr-perks { list-style: none; margin: 0 0 22px; padding: 0; display: grid; gap: 8px; }
        .pr-perks li { font-size: 14.5px; color: var(--ink); padding-left: 26px; position: relative; }
        .pr-perks li::before { content: '✓'; position: absolute; left: 0; color: var(--teal); font-weight: 900; }
        .pr-fine { font-size: 12.5px; color: var(--ink-soft); margin: 14px 0 0; text-align: center; }
        .pr-syllabus { display: inline-block; margin-top: 14px; font-size: 12.5px; font-weight: 800; color: var(--teal-deep); background: var(--amber-tint); border: 1px solid #f2d69a; border-radius: 999px; padding: 6px 13px; }
    </style>

    <div class="pr-brand">
        <div class="pr-anchor">⚓</div>
        <div><b>SmoothSeas</b><span>Guardian Bridge</span></div>
    </div>

    @if ($phase === 'capture')
        <p class="pr-eyebrow">Free · for SEA 2027 families</p>
        <h1 class="pr-h1">Will your child make their <em>first-choice school</em>?</h1>
        <p class="pr-lede">Take a free SEA mock and get a personalised <strong>first-choice placement report</strong> — where they stand, the three strands to fix first, and the one next step. Built for the T&amp;T SEA syllabus.</p>

        <div class="pr-card">
            <ul class="pr-perks">
                <li>A short, AI-graded SEA mock</li>
                <li>Your child's projected first-choice readiness</li>
                <li>Their 3 weakest strands + the one next step</li>
                <li>A full month free + an AI practice pack</li>
            </ul>

            <form wire:submit="beginMock">
                <div class="pr-field">
                    <label for="pr-email">Your email</label>
                    <input id="pr-email" type="email" wire:model="email" placeholder="you@example.com" autocomplete="email">
                    @error('email') <p class="pr-err">{{ $message }}</p> @enderror
                </div>
                <div class="pr-field">
                    <label for="pr-level">Your child's class</label>
                    <select id="pr-level" wire:model="childLevel">
                        <option value="">Choose a class…</option>
                        <option value="Standard 3">Standard 3</option>
                        <option value="Standard 4">Standard 4</option>
                        <option value="Standard 5">Standard 5</option>
                        <option value="Not sure yet">Not sure yet</option>
                    </select>
                    @error('childLevel') <p class="pr-err">{{ $message }}</p> @enderror
                </div>
                <div class="pr-field">
                    <label for="pr-wa">WhatsApp number <span class="opt">(optional — we'll send the report there too)</span></label>
                    <input id="pr-wa" type="text" wire:model="whatsapp" placeholder="+1 868 …" autocomplete="tel">
                    @error('whatsapp') <p class="pr-err">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="pr-cta">Start the free mock →</button>
            </form>
            <p class="pr-fine">No card. No spam. Your child's report is ready in minutes.</p>
        </div>
        <div style="text-align:center;"><span class="pr-syllabus">🇹🇹 Built for the T&amp;T SEA syllabus</span></div>

    @elseif ($phase === 'mock')
        <p class="pr-eyebrow">Step 2 of 3 · the mock</p>
        <h1 class="pr-h1">Let's see where they stand.</h1>
        <p class="pr-lede">A short, timed SEA mock. Answer honestly — this is what powers the placement report.</p>
        <div class="pr-card" data-mock-placeholder>
            <p class="pr-lede" style="margin:0;">Your mock is being prepared…</p>
        </div>

    @elseif ($phase === 'report')
        <div class="pr-card" data-report-placeholder>
            <p class="pr-lede" style="margin:0;">Your placement report is ready.</p>
        </div>
    @endif
</div>
