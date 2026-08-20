<div>
    <style>
        .wa-wrap { max-width: 560px; margin: 0 auto; padding: 32px 18px 48px; color: #e6f2fb; text-align: center; }
        .wa-avatar { width: 132px; height: 132px; object-fit: contain; filter: drop-shadow(0 8px 20px rgba(0,0,0,0.35)); }
        .wa-title { font-size: 1.9rem; font-weight: 900; margin: 12px 0 6px; letter-spacing: -0.02em; }
        .wa-sub { font-size: 1.02rem; font-weight: 600; color: #bfe6ff; margin-bottom: 24px; line-height: 1.5; }
        .wa-gift-head { font-size: 0.9rem; font-weight: 800; color: #fde68a; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 12px; }
        .wa-perks { list-style: none; margin: 0 0 30px; padding: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .wa-perk { background: rgba(255,255,255,0.06); border: 1.5px solid rgba(246,183,30,0.4); border-radius: 14px; padding: 14px 12px; text-align: left; display: flex; gap: 10px; align-items: flex-start; }
        .wa-perk-ico { font-size: 1.6rem; line-height: 1; flex: none; }
        .wa-perk-name { font-size: 0.95rem; font-weight: 800; color: #fff7ed; }
        .wa-perk-blurb { font-size: 0.78rem; font-weight: 600; color: #bfe6ff; margin-top: 2px; line-height: 1.35; }
        .wa-cta { display: inline-block; background: linear-gradient(135deg, #f97316, #f6b71e); color: #241a0a; font-size: 1.05rem; font-weight: 900; padding: 14px 34px; border-radius: 999px; text-decoration: none; box-shadow: 0 8px 22px rgba(246,183,30,0.35); }
        .wa-cta:hover { filter: brightness(1.05); }
        @media (max-width: 420px) { .wa-perks { grid-template-columns: 1fr; } }
    </style>

    <div class="wa-wrap">
        <img class="wa-avatar" src="{{ $this->avatarUrl() }}" alt="Smooth the turtle, cheering">
        <h1 class="wa-title">Welcome aboard, {{ auth()->user()->name }}! 🎉</h1>
        <p class="wa-sub">
            I’m Smooth, your first mate. Your ship is ready and your sea is waiting —
            but first, a little gift for joining the crew.
        </p>

        <p class="wa-gift-head">🎁 Your joining bonus — one of each perk</p>
        <ul class="wa-perks">
            @foreach ($perks as $perk)
                <li class="wa-perk">
                    <span class="wa-perk-ico">{{ $perk['icon'] }}</span>
                    <span>
                        <span class="wa-perk-name">{{ $perk['label'] }}</span>
                        <span class="wa-perk-blurb">{{ $perk['blurb'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>

        {{-- A full-page load (no wire:navigate) so the tour reliably auto-opens on
             arrival — an SPA swap can lose the tour's initial open state (hydration race). --}}
        <a href="{{ route('student.voyage') }}" class="wa-cta">Set sail →</a>
    </div>
</div>
