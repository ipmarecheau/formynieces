{{-- resources/views/voyage/overworld.blade.php --}}
{{-- The Voyage overworld (tier 1): a hub of island-worlds. Standalone, gamified
     alternative to the dashboard. Mastery-gated, always kind — islands show a
     conquered COUNT, never a pace percentage. [AM] --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Voyage — ForMyNieces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            color: #f3e8ff;
            /* Placeholder RPG sea. Swap for Nano Banana art by setting
               background-image to url('/images/voyage/overworld.png'). */
            background:
                radial-gradient(circle at 20% 15%, rgba(129,140,248,0.35), transparent 45%),
                radial-gradient(circle at 80% 10%, rgba(236,72,153,0.25), transparent 40%),
                linear-gradient(180deg, #1b2a6b 0%, #223a8c 30%, #1f5fa8 70%, #1a7fb0 100%);
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .vy-nav {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 20px;
            background: rgba(12, 20, 50, 0.55);
            backdrop-filter: blur(8px);
            position: sticky; top: 0; z-index: 10;
        }
        .vy-brand { font-family: 'Fredoka One', cursive; font-size: 1.3rem; color: #f3e8ff; }
        .vy-nav-right { display: flex; align-items: center; gap: 10px; }
        .vy-switch, .vy-logout {
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.82rem;
            padding: 8px 16px; border-radius: 999px; cursor: pointer; text-decoration: none;
            border: 1.5px solid rgba(255,255,255,0.35); color: #f3e8ff;
            background: rgba(255,255,255,0.08);
        }
        .vy-switch:hover, .vy-logout:hover { background: rgba(255,255,255,0.18); }
        .vy-logout { border: none; }

        .vy-wrap { max-width: 900px; margin: 0 auto; padding: 32px 20px 56px; }
        .vy-title {
            font-family: 'Fredoka One', cursive; font-size: 1.9rem; text-align: center;
            margin-bottom: 6px; text-shadow: 0 2px 12px rgba(0,0,0,0.35);
        }
        .vy-sub { text-align: center; color: rgba(243,232,255,0.8); font-size: 0.95rem; margin-bottom: 32px; }

        .vy-islands {
            display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px;
        }
        @media (max-width: 560px) { .vy-islands { grid-template-columns: 1fr; } }

        .vy-island {
            display: block; text-decoration: none; color: inherit;
            background: rgba(20, 30, 66, 0.55);
            border: 1.5px solid rgba(147, 197, 253, 0.35);
            border-radius: 22px; padding: 26px 22px; text-align: center;
            transition: transform 0.18s, box-shadow 0.18s, border-color 0.18s;
        }
        .vy-island:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
            border-color: rgba(244,114,182,0.7);
        }
        .vy-island-icon { font-size: 3rem; line-height: 1; margin-bottom: 10px; filter: drop-shadow(0 3px 8px rgba(0,0,0,0.35)); }
        .vy-island-name { font-family: 'Fredoka One', cursive; font-size: 1.25rem; margin-bottom: 10px; }
        .vy-count {
            display: inline-block; font-weight: 800; font-size: 0.82rem;
            padding: 5px 14px; border-radius: 999px;
            background: rgba(255,255,255,0.12); color: #e0f2fe;
        }
        .vy-count-done { background: rgba(52, 211, 153, 0.25); color: #bbf7d0; }
    </style>
</head>
<body>
    <nav class="vy-nav">
        <span class="vy-brand">⛵ Your Voyage</span>
        <div class="vy-nav-right">
            <a href="{{ route('student.map') }}" class="vy-switch">📊 Dashboard</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="vy-logout">Log out</button>
            </form>
        </div>
    </nav>

    <div class="vy-wrap">
        <h1 class="vy-title">Choose your island, {{ $user->name }}! 🗺️</h1>
        <p class="vy-sub">Sail to an island and conquer its levels to unlock what lies ahead.</p>

        <div class="vy-islands">
            @foreach($hubs as $hub)
                {{-- Tier 2 (island interior) route arrives in the next loop; card is a hub placeholder for now. --}}
                <a href="#" class="vy-island" data-island-slug="{{ $hub['slug'] }}">
                    <div class="vy-island-icon">{{ $hub['icon'] }}</div>
                    <div class="vy-island-name">{{ $hub['name'] }}</div>
                    <span class="vy-count {{ $hub['conquered'] === $hub['total'] && $hub['total'] > 0 ? 'vy-count-done' : '' }}">
                        {{ $hub['conquered'] }} / {{ $hub['total'] }} conquered
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</body>
</html>
