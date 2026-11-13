{{-- Local-reminders native bridge (LR-01/LR-07). Inert on the web and a safe no-op until
     @capacitor/local-notifications is compiled into the Android app: it only runs inside the
     native shell AND only when the plugin is present. On launch it reads /reminders/feed,
     cancels the previously-scheduled SmoothSeas reminders, and schedules the fresh set on the
     device so they fire offline with the app closed. --}}
<script>
(function () {
    // Run once per launch even if this partial appears on more than one page.
    if (window.__ssRemindersInit) { return; }
    window.__ssRemindersInit = true;

    var Cap = window.Capacitor;
    if (!Cap || typeof Cap.isNativePlatform !== 'function' || !Cap.isNativePlatform()) {
        return; // web, or no Capacitor bridge — nothing to do
    }

    var LocalNotifications = Cap.Plugins && Cap.Plugins.LocalNotifications;
    if (!LocalNotifications) { return; } // plugin not compiled into this build yet

    var FEED = '{{ route('reminders.feed') }}';

    function toNotification(r) {
        return {
            id: r.id,
            title: r.title,
            body: r.body,
            schedule: { at: new Date(r.at), allowWhileIdle: true },
            extra: { type: r.type, conditional: !!r.conditional },
        };
    }

    async function sync() {
        try {
            var perm = await LocalNotifications.requestPermissions();
            if (perm && perm.display && perm.display !== 'granted') { return; }

            var res = await fetch(FEED, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
            if (!res.ok) { return; }
            var data = await res.json();
            var reminders = (data && data.reminders) || [];

            // Replace: cancel the reminders we previously scheduled, then schedule the fresh set.
            var pending = await LocalNotifications.getPending();
            var ours = ((pending && pending.notifications) || []).map(function (n) { return { id: n.id }; });
            if (ours.length) { await LocalNotifications.cancel({ notifications: ours }); }

            if (reminders.length) {
                await LocalNotifications.schedule({ notifications: reminders.map(toNotification) });
            }
        } catch (e) {
            // Reminders are best-effort; never let a scheduling hiccup disrupt the app.
        }
    }

    // Expose a refresh hook so the app can re-sync after the child finishes the day
    // (which drops today's conditional reminders on the next feed build).
    window.SmoothSeasReminders = { refresh: sync };
    window.addEventListener('reminders:refresh', sync);

    sync();
})();
</script>
