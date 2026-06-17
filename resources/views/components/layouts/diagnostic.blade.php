{{-- resources/views/components/layouts/diagnostic.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Your Expedition — ForMyNieces' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple: #9333ea;
            --pink:   #db2777;
            --bg:     #0f0720;
            --card:   #1a0d30;
            --border: rgba(147,51,234,0.35);
            --text:   #f3e8ff;
            --muted:  #c4b5fd;
            --teal:   #0d9488;
        }

        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Nunito', sans-serif;
            color: var(--text);
            overflow-x: hidden;
            position: relative;
        }

        .dx-stars { position: fixed; inset: 0; pointer-events: none; z-index: 0; }
        .dx-star {
            position: absolute; background: white; border-radius: 50%;
            animation: dx-twinkle var(--d, 3s) ease-in-out infinite var(--delay, 0s);
        }
        @keyframes dx-twinkle {
            0%,100% { opacity: 0.15; transform: scale(1); }
            50%      { opacity: 0.9;  transform: scale(1.4); }
        }
        .dx-orb { position: fixed; border-radius: 50%; filter: blur(80px); pointer-events: none; z-index: 0; }
        .dx-orb-1 { width: 400px; height: 400px; background: rgba(147,51,234,0.22); top: -100px; left: -100px; }
        .dx-orb-2 { width: 300px; height: 300px; background: rgba(219,39,119,0.18);  bottom: -80px; right: -80px; }
        .dx-orb-3 { width: 280px; height: 280px; background: rgba(13,148,136,0.14); top: 45%; left: 60%; }

        .dx-content { position: relative; z-index: 1; }
    </style>
</head>
<body>
    <div class="dx-stars" id="dx-stars"></div>
    <div class="dx-orb dx-orb-1"></div>
    <div class="dx-orb dx-orb-2"></div>
    <div class="dx-orb dx-orb-3"></div>

    <div class="dx-content">
        {{ $slot }}
    </div>

    <script>
        const dxStars = document.getElementById('dx-stars');
        for (let i = 0; i < 120; i++) {
            const s = document.createElement('div');
            s.className = 'dx-star';
            const size = Math.random() * 2.5 + 1;
            s.style.cssText = `width:${size}px;height:${size}px;top:${Math.random()*100}%;left:${Math.random()*100}%;--d:${(Math.random()*4+2).toFixed(1)}s;--delay:-${(Math.random()*5).toFixed(1)}s`;
            dxStars.appendChild(s);
        }
    </script>
    @livewireScripts
</body>
</html>