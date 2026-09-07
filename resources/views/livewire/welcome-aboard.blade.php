<div>
    <style>
        .wa-wrap { max-width: 500px; margin: 0 auto; padding: 32px 18px 48px; color: #e6f2fb; text-align: center; }
        .wa-avatar { width: 128px; height: 128px; object-fit: contain; filter: drop-shadow(0 8px 20px rgba(0,0,0,0.35)); }
        .wa-title { font-size: 1.7rem; font-weight: 900; margin: 12px 0 8px; letter-spacing: -0.02em; }
        .wa-sub { font-size: 1.02rem; font-weight: 600; color: #bfe6ff; margin: 0 auto 26px; line-height: 1.55; max-width: 400px; }
        .wa-card { animation: waFade 0.35s ease both; }
        @keyframes waFade { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        .wa-gift-head { font-size: 0.82rem; font-weight: 800; color: #fde68a; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 14px; }
        .wa-perk-big { background: rgba(255,255,255,0.06); border: 1.5px solid rgba(246,183,30,0.45); border-radius: 18px; padding: 26px 22px; max-width: 360px; margin: 0 auto 26px; }
        .wa-perk-ico-big { font-size: 3.4rem; line-height: 1; }
        .wa-perk-name-big { font-size: 1.25rem; font-weight: 900; color: #fff7ed; margin: 10px 0 6px; }
        .wa-perk-blurb-big { font-size: 0.95rem; font-weight: 600; color: #bfe6ff; line-height: 1.45; }

        .wa-dots { display: flex; gap: 7px; justify-content: center; margin: 0 0 20px; }
        .wa-dot { width: 8px; height: 8px; border-radius: 999px; background: rgba(191,230,255,0.28); }
        .wa-dot.is-on { background: #fde68a; }

        .wa-btn { display: inline-block; background: linear-gradient(135deg, #f97316, #f6b71e); color: #241a0a; font-size: 1.05rem; font-weight: 900; padding: 14px 36px; border: none; border-radius: 999px; text-decoration: none; box-shadow: 0 8px 22px rgba(246,183,30,0.35); cursor: pointer; }
        .wa-btn:hover { filter: brightness(1.05); }
        @media (prefers-reduced-motion: reduce) { .wa-avatar, .wa-card { animation: none; } }
    </style>

    <div class="wa-wrap">
        {{-- Progress dots across every step. --}}
        <div class="wa-dots" aria-hidden="true">
            @for ($i = 0; $i <= $this->lastStep(); $i++)
                <span class="wa-dot {{ $i <= $step ? 'is-on' : '' }}"></span>
            @endfor
        </div>

        @if ($step === 0)
            {{-- Greeting --}}
            <div class="wa-card" wire:key="wa-greeting">
                <img class="wa-avatar" src="{{ $this->avatarUrl() }}" alt="Smooth the turtle, cheering">
                <h1 class="wa-title">Welcome aboard, {{ auth()->user()->name }}! 🎉</h1>
                <p class="wa-sub">
                    I'm Smooth, your first mate. Your ship is ready and your sea is waiting —
                    but first, a little gift for joining the crew.
                </p>
                <button type="button" class="wa-btn" wire:click="next">See my gift →</button>
            </div>

        @elseif ($step <= count($perks))
            {{-- One joining perk at a time --}}
            @php ($perk = $perks[$step - 1])
            <div class="wa-card" wire:key="wa-perk-{{ $step }}">
                <p class="wa-gift-head">🎁 Joining gift {{ $step }} of {{ count($perks) }}</p>
                <div class="wa-perk-big">
                    <div class="wa-perk-ico-big">{{ $perk['icon'] }}</div>
                    <p class="wa-perk-name-big">{{ $perk['label'] }}</p>
                    <p class="wa-perk-blurb-big">{{ $perk['blurb'] }}</p>
                </div>
                <button type="button" class="wa-btn" wire:click="next">
                    {{ $step < count($perks) ? 'Next gift →' : 'Got them all! →' }}
                </button>
            </div>

        @else
            {{-- Send-off: introduce the tour, THEN sail (the Voyage auto-opens the tour). --}}
            <div class="wa-card" wire:key="wa-sailoff">
                <img class="wa-avatar" src="{{ $this->avatarUrl() }}" alt="Smooth the turtle, cheering">
                <h1 class="wa-title">Everything's in your locker! 🧰</h1>
                <p class="wa-sub">
                    Now let's set sail. I'll come along and show you around your Voyage —
                    your map, your first island, and how we learn together. Ready?
                </p>
                {{-- A full-page load (no wire:navigate) so the tour reliably auto-opens on
                     arrival — an SPA swap can lose the tour's initial open state (hydration race). --}}
                <a href="{{ route('student.voyage') }}" class="wa-btn">Set sail — show me around →</a>
            </div>
        @endif
    </div>
</div>
