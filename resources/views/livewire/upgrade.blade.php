<div class="up-root">
    <style>
        .up-root { font-family: 'Nunito', sans-serif; max-width: 560px; margin: 0 auto; padding: 40px 18px 80px; color: #e6f2fb; }
        .up-card { background: rgba(12, 20, 50, 0.55); border: 1px solid rgba(255,255,255,0.14); border-radius: 20px; padding: 30px 26px; text-align: center; }
        .up-crest { width: 64px; height: 64px; margin: 0 auto 14px; display: grid; place-items: center; font-size: 2rem; background: radial-gradient(circle at 35% 30%, #fbe9c0, #c9791f); border-radius: 50%; border: 3px solid #7a4a1a; }
        .up-eyebrow { font-size: 0.78rem; font-weight: 800; color: #bfe6ff; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px; }
        .up-title { font-family: 'Fredoka One', cursive; font-size: 1.5rem; color: #fde68a; line-height: 1.15; margin-bottom: 12px; }
        .up-blurb { font-size: 1.02rem; line-height: 1.6; color: #dbe9f7; margin-bottom: 8px; }
        .up-free { font-size: 0.92rem; color: #9fc7cd; margin: 14px 0 22px; }
        .up-cta { display: inline-block; background: linear-gradient(160deg, #ffd15c, #f2941f); color: #241505; font-weight: 800; font-size: 1.05rem; padding: 14px 28px; border-radius: 999px; text-decoration: none; box-shadow: 0 10px 24px rgba(242,169,0,.3); }
        .up-cta:hover { transform: translateY(-1px); }
        .up-back { display: inline-block; margin-top: 18px; color: #bfe6ff; font-weight: 700; text-decoration: underline; font-size: 0.92rem; }
        .up-perks { list-style: none; text-align: left; margin: 20px auto 4px; max-width: 340px; display: grid; gap: 8px; }
        .up-perks li { font-size: 0.95rem; color: #eaf4ff; padding-left: 26px; position: relative; }
        .up-perks li::before { content: '⚓'; position: absolute; left: 0; }
    </style>

    <div class="up-card">
        <div class="up-crest">🐢</div>
        <p class="up-eyebrow">{{ $s['eyebrow'] }}</p>
        <h1 class="up-title">{{ $s['title'] }}</h1>
        <p class="up-blurb">{{ $s['blurb'] }}</p>

        <ul class="up-perks">
            <li>Every lesson, taught step by step</li>
            <li>Smooth re-teaches whatever they miss</li>
            <li>Daily writing, vocabulary &amp; reading</li>
            <li>Pace &amp; projected first-choice placement</li>
        </ul>

        <p class="up-free">Start with a <strong>full month free</strong> — no card to begin.</p>

        <a class="up-cta" href="{{ route('guardian.account') }}" wire:navigate>Unlock the full voyage →</a>
        <div>
            <a class="up-back" href="{{ route($s['backRoute']) }}" wire:navigate>{{ $s['back'] }}</a>
        </div>
    </div>
</div>
