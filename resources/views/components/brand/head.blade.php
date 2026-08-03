{{-- SmoothSeas brand layer — fonts + design tokens + shared helpers.
     Gender-neutral ocean palette: deep navy → teal sea, cyan + gold accents,
     warm sand neutrals. Drop <x-brand.head /> in any page <head>, add class
     "ss-body" to <body>, and optionally <x-brand.sea /> for the sea scene. --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⛵</text></svg>">
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        /* Ocean depths */
        --ss-abyss:  #06182e;
        --ss-navy:   #0b2a4a;
        --ss-ocean:  #0e4d6e;
        --ss-teal:   #0e7490;
        --ss-sea:    #0d9488;
        /* Bright water + sky accents */
        --ss-cyan:   #22d3ee;
        --ss-aqua:   #67e8f9;
        --ss-foam:   #ecfeff;
        /* Treasure / sun */
        --ss-gold:      #f6b71e;
        --ss-gold-deep: #d97706;
        --ss-sand:      #e8d5a8;
        /* Text */
        --ss-ink:   #e6f2fb;
        --ss-muted: #93b2cc;
        /* Surfaces */
        --ss-card:   rgba(9, 30, 54, 0.62);
        --ss-card-2: rgba(9, 30, 54, 0.9);
        --ss-border: rgba(103, 232, 249, 0.28);

        --ss-sea-gradient:   linear-gradient(180deg, #06182e 0%, #0b2a4a 38%, #0e4d6e 72%, #0e7490 100%);
        --ss-accent-gradient: linear-gradient(135deg, #22d3ee, #0e7490);
        --ss-gold-gradient:  linear-gradient(135deg, #fcd34d, #f59e0b);

        --ss-font-head: 'Fredoka One', cursive;
        --ss-font-body: 'Nunito', sans-serif;
    }

    /* Deep-ocean background with a faint nautical chart grid + a warm horizon glow. */
    .ss-body {
        min-height: 100vh;
        font-family: var(--ss-font-body);
        color: var(--ss-ink);
        background:
            radial-gradient(70% 45% at 50% 8%, rgba(246,183,30,0.14), transparent 62%),
            repeating-linear-gradient(0deg,  rgba(103,232,249,0.045) 0 1px, transparent 1px 64px),
            repeating-linear-gradient(90deg, rgba(103,232,249,0.045) 0 1px, transparent 1px 64px),
            var(--ss-sea-gradient);
        background-attachment: fixed;
        position: relative;
        overflow-x: hidden;
    }

    /* Glass card */
    .ss-card {
        position: relative; z-index: 2;
        background: var(--ss-card);
        border: 1.5px solid var(--ss-border);
        border-radius: 22px;
        backdrop-filter: blur(12px);
        box-shadow: 0 20px 55px rgba(0,0,0,0.45);
    }

    /* Buttons — primary is gold treasure; accent is cyan sea; ghost is outline. */
    .ss-btn {
        font-family: var(--ss-font-head); font-size: 17px; letter-spacing: 0.02em;
        border: none; border-radius: 999px; padding: 13px 30px; cursor: pointer;
        color: #06182e; background: var(--ss-gold-gradient); text-decoration: none;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 8px 22px rgba(217,119,6,0.35);
        transition: transform 0.12s, box-shadow 0.15s, filter 0.2s;
    }
    .ss-btn:hover  { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(217,119,6,0.5); filter: brightness(1.03); }
    .ss-btn:active { transform: scale(0.98); }
    .ss-btn-accent { background: var(--ss-accent-gradient); color: var(--ss-foam); box-shadow: 0 8px 22px rgba(14,116,144,0.45); }
    .ss-btn-accent:hover { box-shadow: 0 12px 30px rgba(34,211,238,0.4); }
    .ss-btn-ghost { background: transparent; color: var(--ss-ink); border: 1.5px solid var(--ss-border); box-shadow: none; }
    .ss-btn-ghost:hover { background: rgba(103,232,249,0.10); box-shadow: none; }

    /* Brand wordmark (paired with <x-brand.logo />) */
    .ss-logo { display: inline-flex; align-items: center; gap: 11px; text-decoration: none; }
    .ss-logo-mark {
        display: inline-grid; place-items: center;
        width: 46px; height: 46px; border-radius: 13px; font-size: 24px;
        background: var(--ss-accent-gradient);
        box-shadow: 0 0 24px rgba(34,211,238,0.4);
    }
    .ss-logo-word { font-family: var(--ss-font-head); font-size: 24px; color: var(--ss-foam); letter-spacing: 0.01em; }
    .ss-logo-word b { font-weight: inherit;
        background: linear-gradient(135deg, #67e8f9, #22d3ee 55%, #0d9488);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

    /* Form controls */
    .ss-field { margin-bottom: 18px; }
    .ss-label { display: block; font-size: 13px; font-weight: 800; color: var(--ss-muted);
        margin-bottom: 7px; letter-spacing: 0.05em; text-transform: uppercase; }
    .ss-input {
        width: 100%; background: rgba(6,24,46,0.55);
        border: 1.5px solid var(--ss-border); border-radius: 12px;
        padding: 12px 16px; color: var(--ss-ink);
        font-family: var(--ss-font-body); font-size: 15px; outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .ss-input:focus { border-color: var(--ss-cyan); box-shadow: 0 0 0 3px rgba(34,211,238,0.22); }
    .ss-input::placeholder { color: rgba(147,178,204,0.55); }

    /* Alert boxes */
    .ss-errors, .ss-status { border-radius: 12px; padding: 12px 16px; margin-bottom: 18px; font-size: 13px; }
    .ss-errors { background: rgba(239,68,68,0.12); border: 1.5px solid rgba(239,68,68,0.35); color: #fca5a5; }
    .ss-errors ul { padding-left: 16px; }
    .ss-status { background: rgba(16,185,129,0.14); border: 1.5px solid rgba(16,185,129,0.35); color: #6ee7b7; }

    /* Gradient accent text helper */
    .ss-accent-text {
        background: linear-gradient(135deg, #67e8f9, #22d3ee 55%, #f6b71e);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
</style>
