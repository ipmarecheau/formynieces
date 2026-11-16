{{-- resources/views/components/layouts/testing.blade.php — friendly, light, mobile-first chrome for the /user-testing guided walkthrough. Deliberately plain so a non-technical tester is never lost. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Test Drive — SmoothSeas' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --ink:    #0f2a3f;
            --muted:  #5b7187;
            --line:   #dbe6ee;
            --bg:     #eef4f8;
            --card:   #ffffff;
            --teal:   #0d9488;
            --teal-d: #0b7d72;
            --sun:    #f6b71e;
            --good:   #15803d;
            --good-bg:#dcfce7;
            --warn:   #b45309;
            --warn-bg:#fef3c7;
            --skip:   #475569;
            --skip-bg:#e2e8f0;
        }
        body {
            min-height: 100vh;
            background: var(--bg);
            font-family: 'Nunito', system-ui, sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            padding: 0 16px calc(32px + env(safe-area-inset-bottom));
        }
        .ut-wrap { max-width: 1280px; margin: 0 auto; }
        .ut-narrow { max-width: 620px; margin: 0 auto; }
        .ut-top {
            display: flex; align-items: center; justify-content: space-between;
            padding: 18px 2px 14px; position: sticky; top: 0; background: var(--bg); z-index: 5;
        }
        .ut-brand { font-weight: 800; font-size: 1.05rem; letter-spacing: -0.01em; }
        .ut-brand span { color: var(--teal); }
        .ut-who { font-size: 0.85rem; color: var(--muted); }
        .ut-who a { color: var(--teal-d); font-weight: 700; text-decoration: none; cursor: pointer; }

        .ut-card {
            background: var(--card); border: 1px solid var(--line); border-radius: 18px;
            padding: 22px; box-shadow: 0 6px 22px rgba(15,42,63,0.06); margin-bottom: 16px;
        }
        h1 { font-size: 1.5rem; line-height: 1.2; letter-spacing: -0.02em; margin-bottom: 8px; }
        h2 { font-size: 1.25rem; line-height: 1.25; letter-spacing: -0.01em; }
        p.lead { color: var(--muted); font-size: 1rem; line-height: 1.5; }

        .ut-input {
            width: 100%; font: inherit; font-size: 1.05rem; padding: 14px 16px;
            border: 2px solid var(--line); border-radius: 12px; background: #fff; color: var(--ink);
        }
        .ut-input:focus { outline: none; border-color: var(--teal); }
        textarea.ut-input { min-height: 84px; resize: vertical; }

        .ut-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            width: 100%; font: inherit; font-weight: 800; font-size: 1.05rem;
            padding: 15px 18px; border-radius: 13px; border: none; cursor: pointer;
            background: var(--teal); color: #fff; transition: transform .05s ease, filter .15s ease;
        }
        .ut-btn:active { transform: translateY(1px); }
        .ut-btn:hover { filter: brightness(1.05); }
        .ut-btn.ghost { background: #fff; color: var(--teal-d); border: 2px solid var(--teal); }

        .ut-steps { list-style: none; counter-reset: s; margin: 6px 0 2px; }
        .ut-steps li {
            counter-increment: s; position: relative; padding: 10px 0 10px 42px;
            border-bottom: 1px dashed var(--line); font-size: 1.02rem; line-height: 1.45;
        }
        .ut-steps li:last-child { border-bottom: none; }
        .ut-steps li::before {
            content: counter(s); position: absolute; left: 0; top: 9px;
            width: 28px; height: 28px; border-radius: 50%; background: var(--teal); color: #fff;
            font-weight: 800; font-size: 0.9rem; display: grid; place-items: center;
        }
        .ut-expect {
            margin-top: 14px; padding: 13px 15px; border-radius: 12px;
            background: #f0f9f7; border: 1px solid #cdeae5; font-size: 0.98rem;
        }
        .ut-expect b { color: var(--teal-d); }

        .ut-verdicts { display: grid; gap: 10px; margin-top: 16px; }
        .ut-verdict { font-size: 1.05rem; font-weight: 800; padding: 15px; border-radius: 13px; border: 2px solid transparent; cursor: pointer; width: 100%; }
        .ut-verdict:active { transform: translateY(1px); }
        .v-good { background: var(--good-bg); color: var(--good); border-color: #bbf7d0; }
        .v-warn { background: var(--warn-bg); color: var(--warn); border-color: #fde68a; }
        .v-skip { background: var(--skip-bg); color: var(--skip); border-color: #cbd5e1; }

        .ut-progress-track { height: 10px; background: #dce7ef; border-radius: 99px; overflow: hidden; margin: 10px 0 4px; }
        .ut-progress-fill { height: 100%; background: linear-gradient(90deg, var(--teal), var(--sun)); border-radius: 99px; transition: width .3s ease; }
        .ut-count { font-size: 0.85rem; color: var(--muted); font-weight: 700; }

        .ut-feature {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            text-decoration: none; color: inherit; padding: 16px 18px; border: 1px solid var(--line);
            border-radius: 14px; background: #fff; margin-bottom: 10px;
        }
        .ut-feature:hover { border-color: var(--teal); }
        .ut-feature .fname { font-weight: 800; font-size: 1.08rem; }
        .ut-feature .fblurb { font-size: 0.88rem; color: var(--muted); line-height: 1.4; margin-top: 2px; }
        .ut-pill { font-size: 0.8rem; font-weight: 800; padding: 5px 10px; border-radius: 99px; background: var(--skip-bg); color: var(--skip); white-space: nowrap; }
        .ut-pill.done { background: var(--good-bg); color: var(--good); }
        .ut-pill.waiting { background: var(--warn-bg); color: var(--warn); }
        .ut-pill.retest { background: #e0e7ff; color: #4338ca; }
        /* "Updated since your answer" banner on a scenario that changed — indigo. */
        .ut-retest {
            margin-top: 12px; padding: 12px 14px; border-radius: 12px;
            background: #eef2ff; border: 1px solid #c7d2fe; color: #3730a3;
            font-size: 0.92rem; line-height: 1.5;
        }
        .ut-group:first-child .ut-group-head { margin-top: 6px; }
        .ut-group-head {
            display: flex; align-items: center; justify-content: space-between; gap: 10px;
            width: 100%; background: none; border: 0; border-bottom: 1px solid var(--line);
            cursor: pointer; text-align: left;
            font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em;
            color: var(--muted); margin: 22px 2px 8px; padding: 0 0 6px;
        }
        .ut-group-head:hover { color: var(--ink); }
        .ut-group-count { font-weight: 700; opacity: 0.6; }
        .ut-group-caret { transition: transform 0.15s ease; font-size: 0.9rem; }
        .ut-group-caret.open { transform: rotate(180deg); }
        [x-cloak] { display: none !important; }
        .ut-tag { display:inline-block; font-size: 0.72rem; font-weight: 800; letter-spacing: .04em; color: var(--muted); background: #eef4f8; border:1px solid var(--line); padding: 3px 8px; border-radius: 6px; }
        .ut-note-hint { font-size: 0.82rem; color: var(--muted); margin: 4px 2px 0; }
        .ut-done-emoji { font-size: 3rem; text-align: center; margin-bottom: 8px; }
        .ut-err { font-size: 0.85rem; color: #b91c1c; margin: -8px 2px 12px; font-weight: 700; }
        .ut-verdict.sel { box-shadow: 0 0 0 3px rgba(13,148,136,0.35); }

        /* ── Split: steps beside the live preview ───────────────── */
        .ut-split { display: grid; grid-template-columns: minmax(360px, 440px) 1fr; gap: 18px; align-items: start; }
        .ut-pane-preview { position: sticky; top: 74px; }

        .ut-nav { display: grid; grid-template-columns: 1fr auto 1fr; gap: 8px; margin: 14px 0 4px; }
        .ut-navbtn {
            font: inherit; font-weight: 800; font-size: 0.95rem; padding: 12px 10px; border-radius: 11px;
            border: 2px solid var(--line); background: #fff; color: var(--ink); cursor: pointer; text-align: center;
            text-decoration: none; display: inline-flex; align-items: center; justify-content: center;
        }
        .ut-navbtn:hover:not(:disabled) { border-color: var(--teal); }
        .ut-navbtn:disabled { opacity: 0.4; cursor: default; }
        .ut-navbtn.ghost { color: var(--muted); }

        .ut-devbar { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; flex-wrap: wrap; }
        .ut-devlabel { font-size: 0.82rem; font-weight: 700; color: var(--muted); margin-right: 2px; }
        .ut-dev {
            font: inherit; font-weight: 700; font-size: 0.86rem; padding: 7px 11px; border-radius: 9px;
            border: 2px solid var(--line); background: #fff; color: var(--ink); cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center;
        }
        .ut-dev.on { background: var(--teal); color: #fff; border-color: var(--teal); }
        .ut-dev.ghost { color: var(--muted); }

        .ut-stage {
            background: #d8e3ec; border: 1px solid var(--line); border-radius: 16px; padding: 14px;
            height: calc(100vh - 150px); min-height: 420px; overflow: auto; display: flex; justify-content: center;
        }
        .ut-device {
            background: #fff; border-radius: 14px; box-shadow: 0 8px 28px rgba(15,42,63,0.14);
            overflow: hidden; height: 100%; max-width: 100%; flex: 0 0 auto;
        }
        .ut-device.is-mobile, .ut-device.is-tablet { align-self: flex-start; }
        .ut-frame { width: 100%; height: 100%; border: 0; display: block; background: #fff; }

        /* ── Device (install-the-app) scenarios ─────────────────── */
        .ut-phonebadge, .ut-chip {
            display: inline-block; font-size: 0.74rem; font-weight: 800; letter-spacing: .02em;
            color: var(--teal-d); background: #e6f6f3; border: 1px solid #c7eae4; padding: 4px 9px; border-radius: 999px;
        }
        .ut-chip { margin-left: 6px; vertical-align: middle; }
        .ut-qrbox { text-align: center; margin: 16px 0 4px; }
        .ut-qr {
            width: 210px; height: 210px; max-width: 70%; background: #fff; padding: 10px;
            border: 1px solid var(--line); border-radius: 14px; image-rendering: pixelated;
        }
        .ut-perm { display: flex; gap: 12px; align-items: flex-start; padding: 10px 0; border-top: 1px dashed var(--line); }
        .ut-perm-ic { font-size: 1.5rem; line-height: 1; flex: 0 0 auto; }
        /* One-time setup laid out as ordered steps: install → sign in → permissions. */
        .ut-setup-step { margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--line); }
        .ut-setup-step:first-of-type { margin-top: 14px; }
        .ut-setup-head { display: flex; align-items: center; gap: 9px; font-weight: 800; margin-bottom: 8px; }
        .ut-setup-num {
            display: inline-flex; align-items: center; justify-content: center;
            width: 24px; height: 24px; flex: 0 0 auto; border-radius: 50%;
            background: var(--teal); color: #fff; font-size: 0.82rem; font-weight: 800;
        }
        .ut-cred {
            display: flex; justify-content: space-between; align-items: center; gap: 10px;
            padding: 9px 12px; margin-top: 8px; border-radius: 10px; background: var(--bg);
        }
        .ut-cred span { font-weight: 700; color: var(--muted); }
        .ut-cred code { font-weight: 800; user-select: all; word-break: break-all; text-align: right; }
        /* The test login, framed inside the scenario panel beside its steps. */
        .ut-login-box {
            margin-top: 14px; padding: 14px 16px; border-radius: 14px;
            background: var(--bg); border: 1px solid var(--line);
        }
        /* Deferred-check prompt on a delayed scenario, and the "waiting on" list on the chooser. */
        .ut-defer {
            margin-top: 14px; padding: 14px 16px; border-radius: 14px;
            background: var(--warn-bg); border: 1px solid #f6e4b8; color: #7a5b12;
            font-size: 0.92rem; line-height: 1.5;
        }
        .ut-defer .ut-btn { width: 100%; }
        .ut-waiting {
            margin: 14px 0 20px; padding: 16px 18px; border-radius: 16px;
            background: var(--card); border: 1px solid var(--line);
        }
        .ut-waiting-head { font-weight: 800; font-size: 1.05rem; margin-bottom: 4px; }
        .ut-waiting-item {
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            text-decoration: none; color: inherit; padding: 12px 0;
            border-top: 1px dashed var(--line);
        }
        .ut-note-box {
            margin-top: 14px; padding: 12px 14px; border-radius: 12px;
            background: #fef9ec; border: 1px solid #f6e4b8; font-size: 0.92rem; color: #7a5b12;
        }

        @media (max-width: 920px) {
            .ut-split { grid-template-columns: 1fr; }
            .ut-pane-preview { position: static; order: -1; }
            .ut-stage { height: 60vh; min-height: 340px; }
            .ut-device.is-tablet { width: 100% !important; }
        }
    </style>
</head>
<body>
    <div class="ut-wrap">
        {{ $slot }}
    </div>
    @livewireScripts
</body>
</html>
