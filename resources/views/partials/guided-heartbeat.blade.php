{{-- AG-05/07: credits active guided-learning time to the daily 2-hour pool. Beats only
     while the tab is visible AND she has been active recently, so idle never counts. When
     the pool is spent, the page reloads so the guided lock (AG-06) takes over. --}}
<script>
(function () {
    var BEAT_MS = {{ \App\Services\GuidedTime::BEAT_SECONDS }} * 1000;
    var BEAT_URL = {!! json_encode(route('guided-time.beat'), JSON_UNESCAPED_SLASHES) !!};
    var CSRF = {!! json_encode(csrf_token()) !!};
    var lastActive = Date.now();

    ['mousemove', 'keydown', 'pointerdown', 'scroll', 'touchstart'].forEach(function (evt) {
        window.addEventListener(evt, function () { lastActive = Date.now(); }, { passive: true });
    });

    setInterval(function () {
        if (document.visibilityState !== 'visible') return;   // not looking → no count
        if (Date.now() - lastActive > 60000) return;          // idle > 60s → no count
        fetch(BEAT_URL, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) { if (d && !(d.remaining > 0)) window.location.reload(); })
            .catch(function () {});
    }, BEAT_MS);
})();
</script>
