import type { CapacitorConfig } from '@capacitor/cli';

/**
 * SmoothSeas — CHILD app (Capacitor native shell).
 *
 * Opens straight to the kid-branded student sign-in (/go), never the marketing homepage.
 * The UI is the real Laravel/Livewire web app at 100% fidelity; the native layer adds the
 * icon, splash, dark status bar (child surfaces stay dark), local streak reminders, and
 * (pending Firebase) push.
 *
 * Separate app / separate install from the parent app (distinct appId). While testing
 * against the dev server, set `server.url` to http://<lan-ip>:8000/go and `cleartext: true`.
 */
const config: CapacitorConfig = {
  appId: 'org.smoothseas.child',
  appName: 'SmoothSeas Kids',
  webDir: 'www',
  server: {
    url: 'https://smoothseas.org/go',
    cleartext: false,
    allowNavigation: ['smoothseas.org', 'www.smoothseas.org'],
  },
  backgroundColor: '#06182e',
  android: { backgroundColor: '#06182e' },
  ios: { backgroundColor: '#06182e', contentInset: 'always' },
};

export default config;
