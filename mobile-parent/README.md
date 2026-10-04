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
- **Android build:** JDK **21** (Capacitor 7's Android lib targets Java 21) + Android SDK
  (cmdline-tools, platform 36, build-tools 36). iOS: macOS + Xcode.

On this VPS the toolchain lives at `/opt/jdk/jdk-21.0.12.1+1` and `/opt/android-sdk`:
```bash
export JAVA_HOME=/opt/jdk/jdk-21.0.12.1+1
export ANDROID_HOME=/opt/android-sdk
export PATH=$JAVA_HOME/bin:$PATH
```

## Commands
```bash
npm install                 # restore deps
npx cap sync                # copy www + config into native projects after changes
npx cap sync android        # android only

# Android APK (env above set):
cd android && ./gradlew assembleDebug --no-daemon
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
