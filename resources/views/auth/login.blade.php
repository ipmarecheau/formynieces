<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — {{ config('app.name', 'SmoothSeas') }}</title>
    <x-brand.head />
    <style>
        body { display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 430px; margin: 20px; padding: 44px 40px;
            animation: ss-fade 0.5s ease both; }
        @keyframes ss-fade { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        .login-brand { text-align: center; margin-bottom: 30px; }
        .login-brand .ss-logo { justify-content: center; }
        .login-tag { color: var(--ss-muted); font-size: 14px; margin-top: 10px; }
        .meta-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; font-size: 14px; }
        .remember { display: flex; align-items: center; gap: 8px; cursor: pointer; color: var(--ss-muted); }
        .remember input { width: 16px; height: 16px; border-radius: 5px; accent-color: var(--ss-cyan); }
        .forgot { color: var(--ss-cyan); text-decoration: none; font-weight: 700; }
        .forgot:hover { color: var(--ss-aqua); }
        .foot { text-align: center; margin-top: 24px; font-size: 14px; color: var(--ss-muted); }
        .foot a { color: var(--ss-cyan); font-weight: 800; text-decoration: none; }
        .foot a:hover { color: var(--ss-aqua); }

        /* Parent login wears the light landing palette (the dark sea theme is for students). */
        .ss-sea { display: none !important; }
        body.ss-body { background: #fbf8f2; color: #12222e; }
        .ss-card { background: #ffffff; border-color: #e7ddcd;
            box-shadow: 0 1px 2px rgba(18,34,46,.05), 0 18px 40px -22px rgba(18,34,46,.26); }
        .ss-logo-word { color: #12222e; }
        .ss-label { color: #40566a; }
        .ss-input { background: #f6faf9; border-color: rgba(13,125,140,.35); color: #12222e; }
        .ss-input::placeholder { color: #9aabb5; }
        .ss-input:focus { border-color: #0d7d8c; box-shadow: 0 0 0 3px rgba(13,125,140,.2); }
        .login-tag, .remember, .foot { color: #40566a; }
        .forgot, .foot a { color: #0d7d8c; }
        .forgot:hover, .foot a:hover { color: #f2a900; }
    </style>
</head>
<body class="ss-body">

<x-brand.sea />

<div class="ss-card login-card">
    <div class="login-brand">
        <x-brand.logo />
        <p class="login-tag">Welcome back aboard — chart your course.</p>
    </div>

    @if (session('status'))
        <div class="ss-status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="ss-errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="ss-field">
            <label class="ss-label" for="email">Email address</label>
            <input class="ss-input" type="email" id="email" name="email"
                   value="{{ old('email') }}" placeholder="you@example.com"
                   required autofocus autocomplete="username">
        </div>

        <div class="ss-field">
            <label class="ss-label" for="password">Password</label>
            <input class="ss-input" type="password" id="password" name="password"
                   placeholder="••••••••" required autocomplete="current-password">
        </div>

        <div class="meta-row">
            <label class="remember">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Remember me
            </label>
            @if (Route::has('password.request'))
                <a class="forgot" href="{{ route('password.request') }}">Forgot password?</a>
            @endif
        </div>

        <button type="submit" class="ss-btn" style="width: 100%;">Set sail ⛵</button>
    </form>

    <div class="soc-divider">or</div>

    @include('auth.partials.social-buttons', ['consent' => false])

    @if (Route::has('register'))
        <p class="foot">
            New here? <a href="{{ route('register') }}">Create an account</a>
        </p>
    @endif
    <p class="foot" style="margin-top:10px;">
        Are you a student? <a href="{{ route('student.login') }}">Sign in here 🐢</a>
    </p>
</div>

</body>
</html>
