import type { CapacitorConfig } from '@capacitor/cli';

/**
 * SmoothSeas — PARENT app (Capacitor native shell).
 *
 * Opens straight to the guardian sign-in (/login), never the marketing homepage. The UI
 * is the real Laravel/Livewire web app at 100% fidelity; the native layer adds the icon,
 * splash, status-bar styling, local streak/progress reminders, and (pending Firebase) push.
 *
 * Navigation is locked to the app's own domain; off-domain links open in the system
 * browser. While testing against the dev server, set `server.url` to
 * http://<lan-ip>:8000/login and add `cleartext: true`.
 */
const config: CapacitorConfig = {
  appId: 'org.smoothseas.app',
  appName: 'SmoothSeas Parent',
  webDir: 'www',
  server: {
    url: 'https://smoothseas.org/login',
    cleartext: false,
    allowNavigation: ['smoothseas.org', 'www.smoothseas.org'],
  },
  backgroundColor: '#0d7d8c',
  android: { backgroundColor: '#0d7d8c' },
  ios: { backgroundColor: '#0d7d8c', contentInset: 'always' },
};

export default config;
