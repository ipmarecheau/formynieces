{{-- resources/views/student/awaiting-guardian.blade.php --}}
{{-- RR-11: the waiting state a student sees while her guardian's reconciliation
     decision is pending. Persists across logins until the guardian decides or
     the 3-day hold times out. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All done — ForMyNieces</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: radial-gradient(circle at 30% 10%, #2a1250 0%, #150a2e 55%, #0d0620 100%);
            color: #f3e8ff;
            display: flex; align-items: center; justify-content: center;
            padding: 32px 20px;
        }

        .ag-card {
            background: #1a0d30;
            border: 1.5px solid rgba(147, 51, 234, 0.35);
            border-radius: 24px;
            padding: 40px 34px;
            width: 100%; max-width: 560px;
            text-align: center;
        }

        .ag-title {
            font-family: 'Fredoka One', cursive;
            font-size: 26px; color: #c084fc;
            margin-bottom: 14px;
        }

        .ag-lead {
            font-size: 16px; line-height: 1.6;
            color: rgba(196, 181, 253, 0.9);
            margin-bottom: 22px;
        }

        .ag-panel {
            background: rgba(147, 51, 234, 0.12);
            border: 1.5px solid rgba(147, 51, 234, 0.3);
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 22px;
        }

        .ag-panel-label {
            font-size: 12px; font-weight: 800; letter-spacing: 0.08em;
            text-transform: uppercase; color: rgba(196, 181, 253, 0.7);
            margin-bottom: 6px;
        }

        .ag-guardian-email {
            font-size: 18px; font-weight: 700; color: #f3e8ff;
            word-break: break-all;
        }

        .ag-support {
            font-size: 13px; line-height: 1.6;
            color: rgba(196, 181, 253, 0.75);
            margin-bottom: 26px;
        }

        .ag-support a { color: #f0abfc; text-decoration: none; }

        .ag-logout {
            background: rgba(255, 255, 255, 0.06);
            border: 2px solid rgba(147, 51, 234, 0.4);
            border-radius: 999px;
            padding: 12px 30px;
            color: #f3e8ff;
            font-family: 'Fredoka One', cursive; font-size: 15px;
            cursor: pointer;
        }

        .ag-logout:hover { background: rgba(147, 51, 234, 0.18); }
    </style>
</head>
<body>
    <div class="ag-card">
        <p class="ag-title">All done! 🎉</p>
        <p class="ag-lead">
            Amazing work exploring every island. Your map is nearly ready — we
            just need your grown-up to finish setting it up.
        </p>

        <div class="ag-panel">
            <p class="ag-panel-label">Ask your grown-up to log in</p>
            <p class="ag-guardian-email">{{ $guardianEmail ?? 'your guardian’s account' }}</p>
        </div>

        <p class="ag-support">
            Having trouble? Contact support at
            <a href="mailto:support@formynieces.com">support@formynieces.com</a>
            or +1 (868) 555-0100.
        </p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="ag-logout">Log Out</button>
        </form>
    </div>
</body>
</html>
