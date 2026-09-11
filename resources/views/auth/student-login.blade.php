<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to your voyage · SmoothSeas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
            font-family:'Nunito',system-ui,sans-serif;color:#0b2a31;
            background:radial-gradient(1200px 600px at 50% -10%, #1aa7c0, #0d7d8c 45%, #0a5c68 100%);}
        .card{width:100%;max-width:380px;background:#fff;border-radius:26px;padding:30px 26px 26px;box-shadow:0 30px 70px -30px rgba(0,0,0,.5);text-align:center}
        .turtle{font-size:56px;line-height:1;margin-bottom:6px}
        h1{font-family:'Fredoka',sans-serif;font-weight:600;font-size:26px;margin:0 0 4px;color:#0a5c68}
        .sub{font-size:14px;color:#4b6670;font-weight:700;margin:0 0 22px}
        form{text-align:left;display:flex;flex-direction:column;gap:14px}
        label{font-size:12px;font-weight:800;color:#6e8890;text-transform:uppercase;letter-spacing:.05em;display:block;margin-bottom:5px}
        input[type=text],input[type=password]{width:100%;padding:14px 15px;border:1.5px solid #d2e0dc;border-radius:14px;
            font-family:'Nunito';font-weight:700;font-size:16px;color:#0b2a31;background:#f6faf9}
        input:focus{outline:none;border-color:#0d7d8c;box-shadow:0 0 0 3px rgba(13,125,140,.15)}
        .btn{margin-top:4px;background:linear-gradient(160deg,#f2a900,#d99400);color:#3a2600;border:0;border-radius:15px;
            padding:15px;font-family:'Fredoka';font-weight:600;font-size:19px;cursor:pointer}
        .btn:hover{filter:brightness(1.04)}
        .err{background:#fde8e6;color:#9a2b1e;border-radius:11px;padding:10px 12px;font-size:13px;font-weight:700;margin-bottom:14px}
        .foot{margin-top:20px;font-size:13px;color:#6e8890;font-weight:700}
        .foot a{color:#0d7d8c;font-weight:800;text-decoration:none}
    </style>
</head>
<body>
    <div class="card">
        <div class="turtle">🐢</div>
        <h1>Sign in to your voyage</h1>
        <p class="sub">Child sign-in — enter the login your parent gave you.</p>

        @auth
            @if (auth()->user()->isGuardian())
                @php($firstChild = auth()->user()->students()->orderBy('id')->first())
                <div style="background:#fff7ed;border:1.5px solid #f6b71e;border-radius:14px;padding:12px 14px;margin-bottom:18px;text-align:left;font-size:13px;color:#7a4b00;font-weight:700;line-height:1.5;">
                    You're signed in as a parent.
                    @if ($firstChild)
                        To let {{ $firstChild->name }} sign in on this device,
                        <a href="{{ route('student.handoff', $firstChild) }}" style="color:#0d7d8c;font-weight:800;">hand it over →</a>, or
                    @endif
                    <a href="{{ route('dashboard') }}" style="color:#0d7d8c;font-weight:800;">go to your dashboard</a>.
                </div>
            @endif
        @endauth

        @if ($errors->any())
            <div class="err">Hmm, that login didn't work. Check it with your parent and try again.</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div>
                <label for="email">Your login</label>
                <input type="text" id="email" name="email" value="{{ old('email', request('login')) }}"
                       required autofocus autocomplete="username" autocapitalize="none" spellcheck="false"
                       placeholder="name@smoothseas.org">
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                       placeholder="Your secret words">
            </div>
            <button type="submit" class="btn">Set sail →</button>
        </form>

        <p class="foot">Are you a parent? <a href="{{ route('login') }}">Sign in here</a></p>
    </div>
</body>
</html>
