{{-- AG-05/07/11: credits active guided-learning time to the daily pool and warns before
     the lock. Driven by Alpine (x-init) + wire:ignore so it runs reliably inside Livewire.
     - Every 30s an authoritative BEAT accrues active time on the server and re-syncs the
       local countdown (idle/hidden time never beats, so it never counts — AG-07).
     - A 1s display countdown drives a playful "less than a minute left" banner with a live
       timer that turns red in the final 5 seconds (AG-11); at zero it locks (AG-06). --}}
@php
    $guidedRemaining = app(\App\Services\GuidedTime::class)->remainingSecondsToday(auth()->id());
    $guidedWarn = app(\App\Services\GuidedTime::class)->warnSeconds();
@endphp
<div wire:ignore x-data x-init="
    (function () {
        var WARN = {{ $guidedWarn }};
        var remaining = {{ $guidedRemaining }};
        var lastActive = Date.now();
        var banner = null, timerEl = null;

        function active() {
            return document.visibilityState === 'visible' && (Date.now() - lastActive) < 60000;
        }

        function fmt(s) {
            s = Math.max(0, s);
            return Math.floor(s / 60) + ':' + (s % 60 < 10 ? '0' : '') + (s % 60);
        }

        function ensureBanner() {
            if (banner) return;
            banner = document.createElement('div');
            banner.id = 'guided-warn-banner';
            banner.style.cssText = 'position:fixed;top:14px;left:50%;transform:translateX(-50%);z-index:95;display:flex;align-items:center;gap:12px;padding:11px 20px;border-radius:999px;font-family:Nunito,sans-serif;font-weight:800;font-size:14.5px;color:#0c2440;background:#fde68a;box-shadow:0 10px 30px rgba(0,0,0,0.4);white-space:nowrap;';
            banner.innerHTML = '<span>⏳ Less than a minute of guided time left!</span><span id=&quot;guided-warn-timer&quot; style=&quot;font-family:Fredoka One,cursive;font-size:17px;min-width:44px;text-align:center;background:rgba(12,36,64,0.12);border-radius:10px;padding:2px 8px;&quot;>0:00</span>';
            document.body.appendChild(banner);
            timerEl = banner.querySelector('#guided-warn-timer');
            banner.animate(
                [{ transform: 'translateX(-50%) translateY(-140%)', opacity: 0 },
                 { transform: 'translateX(-50%) translateY(12%)', opacity: 1, offset: 0.7 },
                 { transform: 'translateX(-50%) translateY(0)', opacity: 1 }],
                { duration: 620, easing: 'cubic-bezier(0.18,0.9,0.3,1.4)' }
            );
        }

        function removeBanner() {
            if (banner) { banner.remove(); banner = null; timerEl = null; }
        }

        function render() {
            if (remaining > 0 && !(remaining > WARN)) {
                ensureBanner();
                timerEl.textContent = fmt(remaining);
                timerEl.style.color = !(remaining > 5) ? '#dc2626' : '#0c2440';
                if (!(remaining > 5)) { banner.style.background = '#fca5a5'; }
            } else {
                removeBanner();
            }
        }

        function beat(thenReload) {
            fetch('{{ route('guided-time.beat', absolute: false) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (thenReload) { window.location.reload(); return; }
                    if (!d) return;
                    if (!(d.remaining > 0)) { window.location.reload(); return; }
                    remaining = d.remaining;   // re-sync the display to the server truth
                    render();
                })
                .catch(function () { if (thenReload) window.location.reload(); });
        }

        ['mousemove', 'keydown', 'pointerdown', 'scroll', 'touchstart'].forEach(function (evt) {
            window.addEventListener(evt, function () { lastActive = Date.now(); }, { passive: true });
        });

        render();

        setInterval(function () {          // 1s display countdown (active only)
            if (!active()) return;
            remaining = remaining - 1;
            if (!(remaining > 0)) { beat(true); return; }   // lock, crediting the final seconds
            render();
        }, 1000);

        setInterval(function () {          // authoritative accrual + re-sync
            if (active()) beat(false);
        }, {{ \App\Services\GuidedTime::BEAT_SECONDS }} * 1000);
    })();
"></div>
