# Mobile CI — iOS build (`mobile-ios.yml`)

Builds the SmoothSeas **parent** (`org.smoothseas.app`) and **child** (`org.smoothseas.child`)
iOS apps on a GitHub-hosted macOS runner. Android is built on the VPS (JDK 21 + SDK 36); see
`mobile-parent/README.md`.

## What runs today (no Apple account needed)
The **`build`** job does an **unsigned** `xcodebuild` of both apps for `iphoneos`. It proves
the Capacitor iOS projects compile on macOS. Triggered on push to `capacitor-shell` (when
`mobile-*/**` changes) or via **Run workflow** (workflow_dispatch).

## Enabling signed TestFlight builds

The signed job uses **App Store Connect API-key, cloud-managed (automatic) signing** —
Xcode creates and renews the distribution certificate and both provisioning profiles
itself on the runner. You never export a `.p12` or `.mobileprovision`. Only **4 secrets**,
all derived from one API key + your Team ID.

### 1. Enroll (one-time, ~$99/yr)
Join the **Apple Developer Program** at <https://developer.apple.com/programs/>. Note your
**Team ID** (Membership details — a 10-char code like `AB12CD34EF`).

### 2. Register the two apps (App Store Connect → My Apps → +)
Create two app records, one per bundle id:
- `org.smoothseas.app`  → "SmoothSeas Parent"
- `org.smoothseas.child` → "SmoothSeas Kids"

(The first time you create a bundle id here, App Store Connect registers the matching
Identifier for you — no separate portal step needed for automatic signing.)

### 3. Create an App Store Connect API key
**Users and Access → Integrations → App Store Connect API → Keys → +**
- Access role: **App Manager** (needed so it can create signing assets *and* upload builds).
- Download the **`AuthKey_XXXXXX.p8`** (you can only download it once).
- Copy the **Key ID** and, at the top of the Keys page, the **Issuer ID**.

### 4. Add 4 GitHub repository secrets
Repo → **Settings → Secrets and variables → Actions → New repository secret**:

| Secret | What / how |
|---|---|
| `APPLE_TEAM_ID` | your 10-char Team ID |
| `ASC_KEY_ID` | the API Key ID |
| `ASC_ISSUER_ID` | the API Issuer ID |
| `ASC_KEY_P8_BASE64` | `base64 -i AuthKey_XXXXXX.p8 | pbcopy` (the whole key, base64) |

### 5. Turn the job on
In `mobile-ios.yml`, delete the `if: false` line in the **`signed-archive`** job, commit,
and push to `capacitor-shell`. The job then, for **each** app: archives with automatic
signing, exports an `app-store` `.ipa` (also uploaded as a build artifact), and ships it to
**TestFlight**.

### 6. Install on your iPhone
In App Store Connect the build appears under **TestFlight** (first upload takes a few
minutes to finish "Processing"). Add yourself as an internal tester, install the
**TestFlight** app from the App Store, and the build shows up there to install.

> First signed run only: Xcode registers the distribution cert against your team. If it
> reports the cert limit is reached, revoke an old "Apple Distribution" cert in the portal.

## No Mac? Alternatives
[Codemagic](https://codemagic.io) and [Expo EAS-style services] also build Capacitor iOS
apps with managed signing if you'd rather not manage certs in GitHub secrets.
