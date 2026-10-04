import type { CapacitorConfig } from '@capacitor/cli';

/**
 * SmoothSeas native shell (Capacitor).
 *
 * The app loads the live Laravel/Livewire site directly, so the UI is the real web
 * app at 100% fidelity — all development stays in the Laravel codebase. The native
 * layer adds the app icon, splash, status-bar styling, push, biometrics, camera, and
 * secure token storage.
 *
 * `server.url` is the target site. Swap it to the dev server (http://<lan-ip>:8000 with
 * `cleartext: true`) while testing, or to https://smoothseas.org for the real app.
 * `www/` holds a branded offline fallback shown when the site is unreachable.
 */
const config: CapacitorConfig = {
  appId: 'org.smoothseas.app',
  appName: 'SmoothSeas',
  webDir: 'www',
  server: {
    url: 'https://smoothseas.org',
    cleartext: false,
    // Keep in-domain navigation inside the app; everything else opens in the system browser.
    allowNavigation: ['smoothseas.org', 'www.smoothseas.org'],
  },
  backgroundColor: '#0d7d8c',
  android: {
    backgroundColor: '#0d7d8c',
  },
  ios: {
    backgroundColor: '#0d7d8c',
    contentInset: 'always',
  },
};

export default config;
