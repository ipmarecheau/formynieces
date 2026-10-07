# Native apps (Capacitor)

The SmoothSeas native apps are a thin **[Capacitor](https://capacitorjs.com) shell around
the live Laravel/Livewire web app**. The shell loads the site in a native WebView, so the
UI is the real web app at **100 % fidelity** — there is no second UI to build or keep in
sync. The native layer only adds the app icon, splash, status-bar styling, local
streak/progress reminders, and (pending Firebase) push.

!!! note "Where this lives"
    All native work is on the **`capacitor-shell`** branch (worktree
    `/root/dev/formynieces-capacitor`), **never on `main`**, until a deliberate merge.
    `main` stays free of app code; web changes it carries reach the apps automatically
    (they just load the updated site).

## The two apps

Separate installs, distinct app IDs, each opening straight to **its own sign-in** (no
marketing homepage; navigation locked to the app domain):

| App | Directory | App ID | Opens | Android name |
|---|---|---|---|---|
| **Parent** | `mobile-parent/` | `org.smoothseas.app` | `/login` | SmoothSeas Parent |
| **Kids** | `mobile-child/` | `org.smoothseas.child` | `/go` | SmoothSeas Kids |

The app ID is the store identity and lets both apps (and, below, a dev flavour) coexist on
one device.

## Environments & testing — the important part

A wrapping app has no code of its own to deploy — **"which environment" is just which URL
the shell loads.** So the web app's three environments (see [Environments](../operations/environments.md))
map to the app by its target URL:

| Flavour | App ID | Loads | Who |
|---|---|---|---|
| **Prod** (store build) | `org.smoothseas.app` / `.child` | `https://smoothseas.org` | real users |
| **Dev** (test build) | `org.smoothseas.app.dev` / `.child.dev` | `https://dev.smoothseas.org` | you, testing |

!!! tip "Best practice: a separate, watermarked dev build"
    Keep a **dev flavour** with a `.dev` app-ID suffix and a visibly different name
    (e.g. *“SmoothSeas Parent (DEV)”*) that points at `dev.smoothseas.org`. Because the app
    ID differs, it **installs side-by-side** with the prod app and can't be confused with
    it. Test on the dev flavour against dev/seeded data; ship the prod flavour to the
    stores. This is the native equivalent of the `dev` vs `prod` web domains.

**Why there's no separate "test domain" like the web had:** there is — it's still
`dev.smoothseas.org`. The difference is that a *browser* just visits a different URL,
whereas an *installed app* has its target URL baked in at build time, so testing a
different environment means installing the **dev-flavour build** rather than typing a
different address.

### How changes reach users

This is the payoff of the wrapper model:

- **Web changes** (Livewire UI, features, fixes — ~95 % of work): deploy the normal way
  (**push to `main` → prod**, see [Deployment](../operations/deployment.md)). They appear
  in the installed apps **instantly on next load** — no rebuild, no store resubmission.
- **Native changes only** (app icon, splash, plugins, permissions, the target URL, a new
  Capacitor version): require a **rebuild and a store resubmission** of the prod flavour.

!!! warning "Test flow vs the web"
    1. Make the change (web-side: on a branch → `main`; native-side: on `capacitor-shell`).
    2. It lands on **dev** (`dev.smoothseas.org`); test it in the **dev-flavour app**.
    3. Promote: web change → merge to `main` (auto-deploys to prod, live in the app);
       native change → rebuild the **prod flavour** and submit to TestFlight / Play.

## Downloads (Android sideload)

Debug APKs for both apps are served publicly over HTTPS (no password):

**<https://dev.smoothseas.org/apps/>** → `smoothseas-parent.apk`, `smoothseas-kids.apk`

Served by Caddy from `/opt/smoothseas-apps/` (the APKs are **copied** there after each
build — see Build below). To install: open the page on an Android phone, tap an app, allow
“install from unknown sources”.

## Build toolchain

| Platform | Where | Needs |
|---|---|---|
| **Android** | the VPS | JDK 21 (`/opt/jdk/jdk-21.*`) + Android SDK 36 (`/opt/android-sdk`) |
| **iOS** | GitHub Actions **macOS** runner | — (unsigned); Apple Developer acct for signed |

```bash
# Android APK (per app):
export JAVA_HOME=/opt/jdk/jdk-21.0.12.1+1 ANDROID_HOME=/opt/android-sdk
export PATH=$JAVA_HOME/bin:$PATH
cd mobile-parent/android && ./gradlew assembleDebug --no-daemon
#   -> app/build/outputs/apk/debug/app-debug.apk
# then publish for download:
cp app/build/outputs/apk/debug/app-debug.apk /opt/smoothseas-apps/smoothseas-parent.apk
```

iOS builds on a macOS CI runner — see the workflow `.github/workflows/mobile-ios.yml` and
its setup guide `.github/README-ci.md` (App Store Connect API-key automatic signing →
TestFlight).

## Notifications

- **On-device reminders** (daily streak/progress nudge) via Capacitor Local Notifications —
  work offline, no server, no Firebase.
- **Server push** (FCM → Android + iOS-via-APNs): `device_tokens` table + `POST /device-tokens`
  registration + `FcmSender` + the `notify:streak-reminders` command. No-ops cleanly until
  `FCM_PROJECT_ID` + `FCM_CREDENTIALS` are set. (Lives on `capacitor-shell`.)

## Pushing to production (the stores)

“Production” for the **binary** means submitting the prod-flavour build to the stores:

- **Android:** build the release AAB, upload to Play Console (internal testing → production).
- **iOS:** the macOS CI archives + uploads to **TestFlight**; promote to the App Store there.

Remember: you only do this for **native-layer** changes. Everything web deploys through
`main` as usual and is live in the apps immediately.
