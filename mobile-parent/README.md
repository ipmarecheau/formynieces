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

# Android APKs (env above set). Two flavours:
cd android && ./gradlew assembleProdDebug --no-daemon   # → smoothseas.org
cd android && ./gradlew assembleDevDebug  --no-daemon   # → dev.smoothseas.org (.dev id, "(DEV)" name)
#   prod → android/app/build/outputs/apk/prod/debug/app-prod-debug.apk
#   dev  → android/app/build/outputs/apk/dev/debug/app-dev-debug.apk
# The dev flavour's overrides live in android/app/src/dev/ (capacitor.config.json + res/strings).

# Publish for the download page (served by Caddy at https://dev.smoothseas.org/apps/):
cp app/build/outputs/apk/prod/debug/app-prod-debug.apk /opt/smoothseas-apps/smoothseas-parent.apk
cp app/build/outputs/apk/dev/debug/app-dev-debug.apk   /opt/smoothseas-apps/smoothseas-parent-dev.apk

# iOS (needs a Mac):
npx cap open ios            # opens Xcode; build/run from there
```

## Status
- [x] Scaffold: Android + iOS projects, config → https://smoothseas.org, offline fallback
- [ ] Android SDK install + first APK
- [ ] App icon + splash from brand assets
- [ ] Native plugins: push, biometric unlock, camera (school-journal), secure storage
- [ ] iOS build (Mac / macOS CI)
