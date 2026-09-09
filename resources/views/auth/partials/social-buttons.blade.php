@php($socialProviders = \App\Services\Auth\SocialProviders::enabled())

@if (! empty($socialProviders))
    @php($consent = $consent ?? false)
    <style>
        .soc-divider { display:flex; align-items:center; gap:12px; margin:20px 0 16px; color:var(--muted,#94a3b8); font-size:13px; font-weight:700; }
        .soc-divider::before, .soc-divider::after { content:""; flex:1; height:1px; background:currentColor; opacity:.3; }
        .soc-btns { display:flex; flex-direction:column; gap:10px; }
        .soc-btn { display:flex; align-items:center; justify-content:center; gap:10px; width:100%; padding:13px 16px; border-radius:12px; border:1.5px solid rgba(148,163,184,.4); background:#fff; color:#1f2937; font-weight:800; font-size:15px; text-decoration:none; cursor:pointer; transition:filter .15s, opacity .15s; }
        .soc-btn:hover { filter:brightness(.97); }
        .soc-btn[aria-disabled="true"] { opacity:.5; pointer-events:none; }
        .soc-btn svg { width:20px; height:20px; flex:none; }
        .soc-consent { display:flex; gap:9px; align-items:flex-start; margin:16px 0 4px; font-size:13px; line-height:1.4; color:var(--muted,#475569); font-weight:600; text-align:left; }
        .soc-consent input { width:18px; height:18px; margin-top:1px; flex:none; }
        .soc-consent a { font-weight:800; text-decoration:underline; }
    </style>

    @if ($consent)
        <label class="soc-consent">
            <input type="checkbox" id="soc-agree">
            <span>I confirm I am 18 or older and accept the
                <a href="{{ route('terms') }}" target="_blank" rel="noopener">Terms &amp; Conditions</a>.</span>
        </label>
    @endif

    <div class="soc-btns">
        @foreach ($socialProviders as $key => $provider)
            <a class="soc-btn"
               data-base="{{ route('social.redirect', $key) }}"
               href="{{ route('social.redirect', $key) }}"
               @if ($consent) aria-disabled="true" @endif>
                @if ($provider['icon'] === 'google')
                    <svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                @elseif ($provider['icon'] === 'microsoft')
                    <svg viewBox="0 0 48 48" aria-hidden="true"><rect x="3" y="3" width="20" height="20" fill="#F25022"/><rect x="25" y="3" width="20" height="20" fill="#7FBA00"/><rect x="3" y="25" width="20" height="20" fill="#00A4EF"/><rect x="25" y="25" width="20" height="20" fill="#FFB900"/></svg>
                @elseif ($provider['icon'] === 'facebook')
                    <svg viewBox="0 0 48 48" aria-hidden="true"><path fill="#1877F2" d="M48 24C48 10.7 37.3 0 24 0S0 10.7 0 24c0 12 8.8 21.9 20.2 23.7V31H14.1v-7h6.1v-5.3c0-6 3.6-9.3 9-9.3 2.6 0 5.3.5 5.3.5v5.8h-3c-2.9 0-3.8 1.8-3.8 3.7V24h6.5l-1 7h-5.5v16.7C39.2 45.9 48 36 48 24z"/></svg>
                @elseif ($provider['icon'] === 'linkedin')
                    <svg viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="6" fill="#0A66C2"/><path fill="#fff" d="M14.6 18.9h-5.2V38h5.2V18.9zM12 10.5a3 3 0 100 6 3 3 0 000-6zM38.6 38h-5.2v-9.3c0-2.2-.8-3.7-2.8-3.7-1.5 0-2.4 1-2.8 2-.1.3-.2.8-.2 1.3V38h-5.2s.1-16.4 0-19.1h5.2v2.7c.7-1.1 1.9-2.6 4.7-2.6 3.4 0 6.1 2.2 6.1 7.1V38z"/></svg>
                @elseif ($provider['icon'] === 'yahoo')
                    <svg viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="6" fill="#6001D2"/><text x="24" y="34" font-family="Arial, sans-serif" font-size="28" font-weight="700" fill="#fff" text-anchor="middle">Y!</text></svg>
                @elseif ($provider['icon'] === 'tiktok')
                    <svg viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="10" fill="#010101"/><path fill="#25F4EE" d="M33.5 12.6c-1.9-1.2-3.1-3.2-3.4-5.4h.02-3.9v20.6c0 2.3-1.9 4.2-4.2 4.2-1.4 0-2.6-.7-3.4-1.7 1.2.6 2.6.6 3.9-.1a4.2 4.2 0 002.1-3.6V6.1h3.9c0 .3 0 .7.1 1 .3 2.2 1.5 4.2 3.4 5.4 0 0-.1 0-.1.1z"/><path fill="#FE2C55" d="M35.9 16.6v3.9c-2.6 0-5-.8-7-2.2v10c0 5-4.1 9.1-9.1 9.1-1.9 0-3.7-.6-5.2-1.6a9.1 9.1 0 0015.8-6.1v-10c2 1.4 4.4 2.2 7 2.2v-3.9c-.5 0-1-.1-1.5-.2z"/><path fill="#fff" d="M28.9 18.3c-2-1.4-3.2-3.4-3.5-5.6h.01v-.6h-3.9v20.6a4.2 4.2 0 01-4.2 4.2c-.6 0-1.2-.1-1.7-.4a4.2 4.2 0 01-2.5-3.8c0-2.3 1.9-4.2 4.2-4.2.5 0 .9.1 1.3.2v-4a9.1 9.1 0 00-8 9c0 2.7 1.2 5.2 3.1 6.9a9.1 9.1 0 0014.3-7.5v-10c2 1.4 4.4 2.2 7 2.2v-3.9c-1.5 0-2.9-.4-4.2-1.1z"/></svg>
                @endif
                <span>Continue with {{ $provider['label'] }}</span>
            </a>
        @endforeach
    </div>

    @if ($consent)
        <script>
            (function () {
                var box = document.getElementById('soc-agree');
                var btns = document.querySelectorAll('.soc-btn[data-base]');
                if (!box) { return; }
                function sync() {
                    btns.forEach(function (b) {
                        if (box.checked) { b.setAttribute('href', b.dataset.base + '?agree=1'); b.removeAttribute('aria-disabled'); }
                        else { b.setAttribute('href', b.dataset.base); b.setAttribute('aria-disabled', 'true'); }
                    });
                }
                box.addEventListener('change', sync);
                sync();
            })();
        </script>
    @endif
@endif
