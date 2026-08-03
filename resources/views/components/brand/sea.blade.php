@props(['compass' => true, 'islands' => true])

{{-- Decorative nautical scene for any .ss-body page. Purely atmospheric
     (aria-hidden): a compass-rose watermark, a dashed sea-route, a sun, a ship
     and islands, and layered animated foam waves along the bottom. --}}
<div class="ss-sea" aria-hidden="true">
    <style>
        .ss-sea { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
        .ss-sun { position: absolute; top: 9%; left: 50%; transform: translateX(-50%);
            width: 180px; height: 180px; border-radius: 50%;
            background: radial-gradient(circle, rgba(246,183,30,0.5), rgba(246,183,30,0) 70%); }
        .ss-compass { position: absolute; top: 50%; left: 50%;
            width: min(78vw, 620px); height: min(78vw, 620px); transform: translate(-50%,-50%);
            opacity: 0.06; color: var(--ss-aqua); animation: ss-spin 120s linear infinite; }
        @keyframes ss-spin { to { transform: translate(-50%,-50%) rotate(360deg); } }
        .ss-route { position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0.5; }
        .ss-route path { fill: none; stroke: rgba(103,232,249,0.35); stroke-width: 2.5;
            stroke-dasharray: 3 16; stroke-linecap: round; }
        .ss-float { position: absolute; filter: drop-shadow(0 8px 12px rgba(0,0,0,0.45)); }
        .ss-ship  { top: 21%; left: 15%; font-size: clamp(30px, 4vw, 46px); animation: ss-bob 6s ease-in-out infinite; }
        .ss-isle-1 { top: 27%; right: 12%; font-size: clamp(30px, 4.4vw, 50px); animation: ss-bob 8s ease-in-out infinite; }
        .ss-isle-2 { bottom: 30%; left: 9%;  font-size: clamp(26px, 3.6vw, 40px); animation: ss-bob 7s ease-in-out infinite reverse; }
        @keyframes ss-bob { 0%,100% { transform: translateY(0) rotate(-2deg); } 50% { transform: translateY(-10px) rotate(2deg); } }

        .ss-waves { position: absolute; left: 0; right: 0; bottom: 0; height: 30vh; min-height: 180px; }
        .ss-waves svg { position: absolute; bottom: 0; left: 0; width: 200%; height: 100%; }
        .ss-waves .w1 { animation: ss-drift 14s linear infinite; opacity: 0.55; }
        .ss-waves .w2 { animation: ss-drift 22s linear infinite reverse; opacity: 0.4; }
        .ss-waves .w3 { animation: ss-drift 30s linear infinite; opacity: 0.3; }
        @keyframes ss-drift { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @media (prefers-reduced-motion: reduce) {
            .ss-ship, .ss-isle-1, .ss-isle-2, .ss-compass, .ss-waves .w1, .ss-waves .w2, .ss-waves .w3 { animation: none; }
        }
    </style>

    <div class="ss-sun"></div>

    @if($compass)
        <svg class="ss-compass" viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="0.6">
            <circle cx="50" cy="50" r="46"/><circle cx="50" cy="50" r="34"/>
            <polygon points="50,6 56,50 50,54 44,50" fill="currentColor" stroke="none"/>
            <polygon points="50,94 44,50 50,46 56,50" fill="currentColor" stroke="none" opacity="0.5"/>
            <polygon points="6,50 50,44 54,50 50,56" fill="currentColor" stroke="none" opacity="0.5"/>
            <polygon points="94,50 50,56 46,50 50,44" fill="currentColor" stroke="none" opacity="0.5"/>
            <g stroke="currentColor" stroke-width="0.5">
                <line x1="50" y1="4" x2="50" y2="12"/><line x1="50" y1="88" x2="50" y2="96"/>
                <line x1="4" y1="50" x2="12" y2="50"/><line x1="88" y1="50" x2="96" y2="50"/>
            </g>
        </svg>
    @endif

    <svg class="ss-route" viewBox="0 0 1200 800" preserveAspectRatio="none">
        <path d="M120,700 C300,650 320,470 480,440 S760,520 820,360 S1000,250 1120,170"/>
    </svg>

    @if($islands)
        <span class="ss-float ss-ship">⛵</span>
        <span class="ss-float ss-isle-1">🏝️</span>
        <span class="ss-float ss-isle-2">🌴</span>
    @endif

    <div class="ss-waves">
        <svg class="w1" viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path d="M0,50 C240,110 480,10 720,50 C960,90 1200,20 1440,50 L1440,120 L0,120 Z" fill="#0e7490"/>
        </svg>
        <svg class="w2" viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path d="M0,60 C240,20 480,100 720,60 C960,20 1200,100 1440,60 L1440,120 L0,120 Z" fill="#0d9488"/>
        </svg>
        <svg class="w3" viewBox="0 0 1440 120" preserveAspectRatio="none">
            <path d="M0,70 C240,110 480,40 720,70 C960,100 1200,40 1440,70 L1440,120 L0,120 Z" fill="#22d3ee"/>
        </svg>
    </div>
</div>
