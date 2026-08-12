{{-- AG-05/07: credits active guided-learning time to the daily 2-hour pool. Driven by
     Alpine (x-init) so it reliably runs inside the Livewire component; wire:ignore keeps
     Livewire from morphing it away. Beats only while the tab is visible AND recently
     active, so idle never counts. On a spent pool the page reloads into the lock (AG-06).
     AG-11: when guided time runs low it shows a live "N minutes left" banner (a fixed
     element on <body>, outside the Livewire tree) that ticks down each beat. --}}
@php
    $guidedRemaining = app(\App\Services\GuidedTime::class)->remainingSecondsToday(auth()->id());
    $guidedWarn = app(\App\Services\GuidedTime::class)->warnSeconds();
@endphp
<div wire:ignore x-data x-init="
    (function () {
        var WARN = {{ $guidedWarn }};
        var lastActive = Date.now();

        function updateBanner(remaining) {
            var el = document.getElementById('guided-warn-banner');
            if (remaining > 0 && !(remaining > WARN)) {
                if (!el) {
                    el = document.createElement('div');
                    el.id = 'guided-warn-banner';
                    el.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:90;text-align:center;padding:9px 14px;font-family:Nunito,sans-serif;font-weight:800;font-size:14px;color:#0c2440;background:#fde68a;box-shadow:0 2px 12px rgba(0,0,0,0.35);';
                    document.body.appendChild(el);
                }
                el.textContent = '⏳ About ' + Math.ceil(remaining / 60) + ' minutes of guided learning left today.';
            } else if (el) {
                el.remove();
            }
        }

        updateBanner({{ $guidedRemaining }});

        ['mousemove', 'keydown', 'pointerdown', 'scroll', 'touchstart'].forEach(function (evt) {
            window.addEventListener(evt, function () { lastActive = Date.now(); }, { passive: true });
        });

        setInterval(function () {
            if (document.visibilityState !== 'visible') return;
            if (Date.now() - lastActive > 60000) return;
            fetch('{{ route('guided-time.beat', absolute: false) }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (!d) return;
                    if (!(d.remaining > 0)) { window.location.reload(); return; }
                    updateBanner(d.remaining);
                })
                .catch(function () {});
        }, {{ \App\Services\GuidedTime::BEAT_SECONDS }} * 1000);
    })();
"></div>
