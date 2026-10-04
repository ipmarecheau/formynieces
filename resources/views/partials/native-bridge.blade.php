{{--
    Native-app bridge — runs ONLY inside the Capacitor shell (no-op in a normal browser).
    - Schedules a daily on-device streak/progress reminder (works offline, no server).
    - Registers the device's push token with the signed-in user (for server push later).
    Pass $nativeApp ('parent'|'child') from the layout; defaults to 'child'.
--}}
@php($nativeApp = $nativeApp ?? 'child')
<script>
(function () {
  var Cap = window.Capacitor;
  if (!Cap || typeof Cap.isNativePlatform !== 'function' || !Cap.isNativePlatform()) return;

  var APP = @json($nativeApp);
  var P = Cap.Plugins || {};
  var LN = P.LocalNotifications;
  var PN = P.PushNotifications;

  var REMINDER = APP === 'parent'
    ? { title: 'Your captain needs you 🧭', body: "Check in on today's progress and keep them on course." }
    : { title: 'Keep your streak alive! 🔥', body: 'Set sail for a few minutes today to keep your voyage going.' };

  function csrf() {
    var m = document.querySelector('meta[name="csrf-token"]');
    return m ? m.getAttribute('content') : '';
  }

  // Daily on-device reminder at 5pm (local). Cancel our previous one first so it never stacks.
  async function scheduleReminder() {
    if (!LN) return;
    try {
      var perm = await LN.requestPermissions();
      if (perm.display !== 'granted') return;
      try { await LN.cancel({ notifications: [{ id: 1001 }] }); } catch (e) {}
      await LN.schedule({
        notifications: [{
          id: 1001,
          title: REMINDER.title,
          body: REMINDER.body,
          schedule: { on: { hour: 17, minute: 0 }, allowWhileIdle: true, repeats: true },
        }],
      });
    } catch (e) { /* non-fatal */ }
  }

  // Register for server push and hand the token to Laravel against the signed-in user.
  async function registerPush() {
    if (!PN) return;
    try {
      var perm = await PN.requestPermissions();
      if (perm.receive !== 'granted') return;
      PN.addListener('registration', function (token) {
        fetch('{{ route('device-tokens.store') }}', {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
          body: JSON.stringify({ token: token.value, platform: Cap.getPlatform(), app: APP }),
        }).catch(function () {});
      });
      PN.addListener('registrationError', function () {});
      await PN.register();
    } catch (e) { /* push may be unconfigured; local reminder still works */ }
  }

  function start() { scheduleReminder(); registerPush(); }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', start);
  } else {
    start();
  }
})();
</script>
