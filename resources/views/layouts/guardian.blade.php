<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Guardian Summary · ForMyNieces</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Fredoka+One&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Nunito', sans-serif;
            background: #fdf4ff;
            margin: 0;
            min-height: 100vh;
            color: #1f2937;
        }
        .fmn-nav {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(10px);
            border-bottom: 1.5px solid #f3e8ff;
            padding: 0 1rem; height: 58px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .fmn-nav-brand {
            font-family: 'Fredoka One', cursive;
            font-size: 1.4rem; color: #9333ea; text-decoration: none;
        }
        .fmn-nav-right { display: flex; align-items: center; gap: 12px; }
        .fmn-nav-greeting { font-size: 0.85rem; font-weight: 700; color: #a78bfa; }
        @media (max-width: 480px) { .fmn-nav-greeting { display: none; } }
        .fmn-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 16px; border-radius: 999px;
            font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.8rem;
            cursor: pointer; border: none; text-decoration: none;
            transition: background 0.15s;
        }
        .fmn-btn-ghost { background: white; color: #9333ea; border: 1.5px solid #e9d5ff; }
        .fmn-btn-ghost:hover { background: #fdf4ff; }
        .fmn-page { max-width: 720px; margin: 0 auto; padding: 1.5rem 1rem 3rem; }
    </style>
</head>
<body>
    <nav class="fmn-nav">
        <span class="fmn-nav-brand">✨ ForMyNieces</span>
        <div class="fmn-nav-right">
            <span class="fmn-nav-greeting">Guardian view</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="fmn-btn fmn-btn-ghost">Log out</button>
            </form>
        </div>
    </nav>

    <div class="fmn-page">
        {{ $slot }}
    </div>
</body>
</html>
