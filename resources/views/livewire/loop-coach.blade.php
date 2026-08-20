<div>
    @if ($open)
        @php
            $copy = [
                'learn' => [
                    'title' => 'Step 1 — the Lesson',
                    'lines' => [
                        'This is the lesson: I walk you through the rule one step at a time.',
                        'Read each part and tap <b>Next</b>. Where there’s a mini-question, answer it to carry on.',
                        'Finish the lesson and the <b>worked examples</b> open up next.',
                    ],
                ],
                'examples' => [
                    'title' => 'Step 2 — Worked examples',
                    'lines' => [
                        'Now watch the rule done for real, then try one yourself.',
                        'These build your confidence — and finishing them unlocks <b>Practice</b>.',
                    ],
                ],
                'practice' => [
                    'title' => 'Step 3 — Practice',
                    'lines' => [
                        'This is Practice! You climb from easy to tricky, and every question gives you a second try.',
                        'Get <b>three tricky ones right in a row</b> and the level is yours. ⭐',
                        'That’s the learning loop, all the way to Practice — brilliant sailing! 🐢',
                    ],
                ],
            ][$leg] ?? ['title' => 'Keep going!', 'lines' => ['You’re doing great.']];
            $isEnd = $leg === 'practice';
        @endphp

        <style>
            .lc-card { position: fixed; left: 50%; transform: translateX(-50%); bottom: 16px; z-index: 2002; width: min(94vw, 400px); background: linear-gradient(160deg, #241436, #3a1f52); border: 1.5px solid rgba(240,171,252,0.5); border-radius: 18px; box-shadow: 0 14px 40px rgba(0,0,0,0.5); padding: 15px 17px 14px; color: #f3e8ff; }
            .lc-card.is-min { padding: 10px 14px; width: auto; }
            .lc-head { display: flex; align-items: center; gap: 10px; }
            .lc-avatar { width: 44px; height: 44px; object-fit: contain; flex: none; }
            .lc-title { font-size: 1.05rem; font-weight: 900; color: #f0abfc; flex: 1; }
            .lc-toggle { background: none; border: none; color: #c4a3d6; font-size: 1.1rem; cursor: pointer; line-height: 1; }
            .lc-lines { margin: 8px 0 10px; padding: 0; list-style: none; }
            .lc-lines li { font-size: 0.9rem; font-weight: 600; line-height: 1.45; color: #ecd9ff; margin-bottom: 6px; }
            .lc-lines b { color: #f0abfc; }
            .lc-btn { width: 100%; border: none; cursor: pointer; font-size: 0.95rem; font-weight: 900; padding: 11px 16px; border-radius: 999px; background: linear-gradient(135deg, #a855f7, #f0abfc); color: #2a0a3a; box-shadow: 0 6px 16px rgba(168,85,247,0.35); }
            .lc-skip { display: block; margin: 9px auto 0; background: none; border: none; color: #c4a3d6; font-size: 0.78rem; font-weight: 700; cursor: pointer; text-decoration: underline; }
        </style>

        <div x-data="{ min: false }" class="lc-card" :class="{ 'is-min': min }">
            <div class="lc-head">
                <img class="lc-avatar" src="{{ $this->avatarUrl() }}" alt="Smooth the turtle">
                <div class="lc-title" x-text="min ? 'Smooth’s tour' : @js($copy['title'])"></div>
                <button type="button" class="lc-toggle" @click="min = !min" x-text="min ? '▲' : '▾'" :aria-label="min ? 'Expand tip' : 'Minimise tip'"></button>
            </div>

            <div x-show="!min">
                <ul class="lc-lines">
                    @foreach ($copy['lines'] as $line)
                        <li>{!! $line !!}</li>
                    @endforeach
                </ul>
                @if ($isEnd)
                    <button type="button" class="lc-btn" wire:click="finish">Got it — I’ve got this! ⛵</button>
                @else
                    <button type="button" class="lc-skip" wire:click="finish">Skip the tour</button>
                @endif
            </div>
        </div>
    @endif
</div>
