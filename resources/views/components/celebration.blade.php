@props([
    'title',
    'sub' => null,
    'pose' => 'cheer',
])

@php
    $poseFile = match ($pose) {
        'chart' => 'smooth-chart.webp',
        'wave' => 'smooth.webp',
        default => 'smooth-cheer.webp',
    };
@endphp

{{-- CE-01: a big animated celebration moment, voiced by Smooth. Reduced-motion
     (CE-06) strips the confetti and pop, keeping the named achievement. --}}
<div class="ce-overlay" role="dialog" aria-modal="true" aria-label="{{ $title }}">
    <style>
        .ce-overlay { position: fixed; inset: 0; z-index: 80; display: flex; align-items: center; justify-content: center; padding: 20px; background: radial-gradient(circle at 50% 40%, rgba(20,60,110,0.85), rgba(4,14,30,0.94)); overflow: hidden; animation: ceFade 0.3s ease both; }
        .ce-confetti { position: absolute; top: -12px; width: 11px; height: 16px; border-radius: 2px; opacity: 0.9; animation: ceFall linear infinite; }
        .ce-card { position: relative; text-align: center; max-width: 460px; animation: cePop 0.5s cubic-bezier(0.18,0.9,0.3,1.4) both; }
        .ce-avatar { width: 150px; height: 150px; object-fit: contain; filter: drop-shadow(0 10px 22px rgba(0,0,0,0.5)); animation: ceBob 2.2s ease-in-out infinite; }
        .ce-title { font-family: 'Fredoka One', cursive; font-size: 34px; color: #fcd34d; margin: 6px 0 8px; text-shadow: 0 2px 16px rgba(244,114,182,0.5); }
        .ce-sub { font-size: 17px; line-height: 1.55; color: #e6f2fb; margin: 0 auto 26px; max-width: 340px; }
        .ce-cta a, .ce-cta button { display: inline-block; background: linear-gradient(135deg,#0e7490,#f6b71e); border: none; border-radius: 999px; padding: 15px 38px; color: #fff; font-family: 'Fredoka One', cursive; font-size: 17px; cursor: pointer; text-decoration: none; }
        @keyframes ceFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes cePop { from { opacity: 0; transform: scale(0.7); } to { opacity: 1; transform: scale(1); } }
        @keyframes ceBob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes ceFall { to { transform: translateY(102vh) rotate(540deg); } }
        @media (prefers-reduced-motion: reduce) {
            .ce-overlay, .ce-card, .ce-avatar { animation: none; }
            .ce-confetti { display: none; }
        }
    </style>

    @for ($i = 0; $i < 24; $i++)
        <span class="ce-confetti" style="left: {{ (int) ($i * 4.1) }}%; background: {{ ['#67e8f9','#fcd34d','#f0abfc','#86efac','#fca5a5'][$i % 5] }}; animation-duration: {{ 2.4 + ($i % 5) * 0.4 }}s; animation-delay: {{ ($i % 7) * 0.25 }}s;"></span>
    @endfor

    <div class="ce-card">
        <img class="ce-avatar" src="{{ asset('images/voyage/companion/'.$poseFile) }}" alt="Smooth the turtle, cheering">
        <h2 class="ce-title">{{ $title }}</h2>
        @if ($sub)
            <p class="ce-sub">{{ $sub }}</p>
        @endif
        <div class="ce-cta">{{ $slot }}</div>
    </div>
</div>
