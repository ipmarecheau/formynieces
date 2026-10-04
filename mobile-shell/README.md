# SmoothSeas — native shell (Capacitor)

A thin native wrapper around the live SmoothSeas Laravel/Livewire web app. The UI is the
real web app at 100% fidelity; the native layer adds the app icon, splash, status-bar
styling, and (next) push, biometrics, camera, and secure token storage. All UI work stays
in the Laravel codebase — there is no second UI to keep in sync.

## How it works
`capacitor.config.ts` points `server.url` at the site, so the app loads it directly.
`www/index.html` is only the offline fallback. Switch `server.url` to a dev server
(`http://<lan-ip>:8000`, `cleartext: true`) while testing.

## Prerequisites
- Node 18+ (installed)
- **Android build:** JDK 17 + Android SDK (cmdline-tools, platform 34, build-tools). iOS: macOS + Xcode.

## Commands
```bash
npm install                 # restore deps
npx cap sync                # copy www + config into native projects after changes
npx cap sync android        # android only

# Android APK (needs JDK + Android SDK; ANDROID_HOME set):
cd android && ./gradlew assembleDebug
#   -> android/app/build/outputs/apk/debug/app-debug.apk   (sideload on a phone)

# iOS (needs a Mac):
npx cap open ios            # opens Xcode; build/run from there
```

## Status
- [x] Scaffold: Android + iOS projects, config → https://smoothseas.org, offline fallback
- [ ] Android SDK install + first APK
- [ ] App icon + splash from brand assets
- [ ] Native plugins: push, biometric unlock, camera (school-journal), secure storage
- [ ] iOS build (Mac / macOS CI)
