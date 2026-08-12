{{-- AG-05/07: credits active guided-learning time to the daily 2-hour pool. Driven by
     Alpine (x-init) so it reliably runs inside the Livewire component; wire:ignore keeps
     Livewire from morphing it away. Beats only while the tab is visible AND recently
     active, so idle never counts. On a spent pool the page reloads into the lock (AG-06). --}}
<div wire:ignore x-data x-init="
    (function () {
        var lastActive = Date.now();
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
                .then(function (d) { if (d && !(d.remaining > 0)) window.location.reload(); })
                .catch(function () {});
        }, {{ \App\Services\GuidedTime::BEAT_SECONDS }} * 1000);
    })();
"></div>
