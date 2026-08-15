<div class="mt-root">
    <style>
        .mt-root { font-family: 'Nunito', sans-serif; max-width: 640px; margin: 0 auto; padding: 18px 16px 60px; color: #e6f2fb; }
        .mt-head { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .mt-crest { width: 42px; height: 42px; display: grid; place-items: center; font-size: 1.5rem; background: radial-gradient(circle at 35% 30%, #fbe9c0, #c9791f); border-radius: 50%; border: 2px solid #7a4a1a; }
        .mt-kicker { font-family: 'Fredoka One', cursive; font-size: 1.35rem; color: #fde68a; line-height: 1; }
        .mt-sub { font-size: 0.8rem; font-weight: 800; color: #bfe6ff; text-transform: uppercase; letter-spacing: 1px; }
        .mt-card { background: rgba(12, 20, 50, 0.55); border: 1px solid rgba(255,255,255,0.14); border-radius: 16px; padding: 18px; }
        .mt-passage-title { font-family: 'Fredoka One', cursive; font-size: 1.3rem; color: #f8fafc; margin-bottom: 10px; }
        .mt-passage-body { font-size: 1.08rem; line-height: 1.7; color: #eaf4ff; white-space: pre-line; }
        .mt-q { font-family: 'Fredoka One', cursive; font-size: 1.1rem; color: #f8fafc; margin-bottom: 14px; }
        .mt-opt { display: flex; align-items: center; gap: 10px; padding: 12px 14px; margin-bottom: 8px; border-radius: 12px; background: rgba(255,255,255,0.06); border: 1.5px solid rgba(255,255,255,0.18); cursor: pointer; font-weight: 700; }
        .mt-opt:hover { background: rgba(255,255,255,0.12); }
        .mt-write { width: 100%; min-height: 96px; border-radius: 12px; border: 1.5px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.08); color: #fff; padding: 12px; font-family: inherit; font-size: 1rem; }
        .mt-word { font-family: 'Fredoka One', cursive; font-size: 1.8rem; color: #fde68a; }
        .mt-def { font-size: 1rem; color: #eaf4ff; margin: 6px 0 10px; }
        .mt-ctx { font-style: italic; color: #bfe6ff; background: rgba(255,255,255,0.06); border-left: 3px solid #fde68a; padding: 8px 12px; border-radius: 8px; }
        .mt-btn { display: inline-block; margin-top: 16px; cursor: pointer; border: none; border-radius: 999px; padding: 12px 26px; font-family: 'Fredoka One', cursive; font-size: 1rem; color: #fff7ed; background: linear-gradient(135deg, #f97316, #f6b71e); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .mt-progress { font-size: 0.8rem; font-weight: 800; color: #bfe6ff; margin-bottom: 12px; }
        .mt-compass { font-family: 'Fredoka One', cursive; font-size: 3rem; color: #34d399; text-align: center; }
        .mt-done-txt { text-align: center; font-size: 1.05rem; color: #eaf4ff; margin: 8px 0 4px; }
        .mt-link { display: inline-block; margin-top: 18px; color: #fde68a; font-weight: 800; text-decoration: none; }
        .mt-goal { text-align: center; font-size: 0.85rem; color: #bfe6ff; }
    </style>

    <div class="mt-head">
        <div class="mt-crest">🐢</div>
        <div>
            <div class="mt-kicker">The Morning Tide</div>
            <div class="mt-sub">Reading &amp; words</div>
        </div>
    </div>

    @if ($noPassage)
        <div class="mt-card" style="text-align:center;">
            <p style="font-size:1.05rem;">Calm seas today, Captain — no new reading has washed in. Sail on! ⛵</p>
            <a href="{{ route('student.voyage') }}" class="mt-link">← Back to the Voyage</a>
        </div>

    @elseif ($phase === 'read')
        <div class="mt-card">
            <div class="mt-passage-title">{{ $passage->title }}</div>
            <div class="mt-passage-body">{{ $passage->body }}</div>
            <button class="mt-btn" wire:click="startCheck">I've read it →</button>
        </div>

    @elseif ($phase === 'check')
        @php($q = $questions[$qIndex] ?? null)
        <div class="mt-card">
            <div class="mt-progress">Question {{ $qIndex + 1 }} of {{ count($questions) }}</div>
            <div class="mt-q">{{ $q['prompt'] ?? '' }}</div>

            @if (($q['type'] ?? 'mc') === 'mc')
                @foreach ($q['options'] ?? [] as $i => $option)
                    <label class="mt-opt">
                        <input type="radio" wire:model="currentAnswer" value="{{ $i }}">
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            @else
                <textarea class="mt-write" wire:model="currentAnswer" placeholder="Write your answer in your own words…"></textarea>
                <p class="mt-goal" style="text-align:left;margin-top:6px;">This one's writing practice — there's no wrong answer.</p>
            @endif

            <button class="mt-btn" wire:click="nextQuestion">
                {{ $qIndex < count($questions) - 1 ? 'Next →' : 'Chart it →' }}
            </button>
        </div>

    @elseif ($phase === 'vocab')
        <div class="mt-card">
            <div class="mt-progress">Word {{ $vocabIndex + 1 }} of {{ $vocabTotal }} washed ashore</div>
            <div class="mt-word">{{ $currentWord?->word }}</div>
            <div class="mt-def">{{ $currentWord?->definition }}</div>
            <div class="mt-ctx">“{{ $currentWord?->context_sentence }}”</div>
            <textarea class="mt-write" style="margin-top:12px;" wire:model="currentSentence" placeholder="Now use it in a sentence of your own…"></textarea>
            <button class="mt-btn" wire:click="nextWord">
                {{ $vocabIndex < $vocabTotal - 1 ? 'Next word →' : 'Finish the tide →' }}
            </button>
        </div>

    @else
        <div class="mt-card" style="text-align:center;">
            @if ($score !== null)
                <div class="mt-compass">{{ $score }}%</div>
                <p class="mt-goal">charted — goal is 95%</p>
            @endif
            <p class="mt-done-txt">
                @if ($score !== null && $score >= 95)
                    A perfect reading, Captain! Treasure earned. 🌟
                @else
                    Fine sailing today — every voyage makes you sharper. 🌊
                @endif
            </p>
            <a href="{{ route('student.voyage') }}" class="mt-link">← Back to the Voyage</a>
        </div>
    @endif
</div>
