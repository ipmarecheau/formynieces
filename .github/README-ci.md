# Mobile CI — iOS build (`mobile-ios.yml`)

Builds the SmoothSeas **parent** (`org.smoothseas.app`) and **child** (`org.smoothseas.child`)
iOS apps on a GitHub-hosted macOS runner. Android is built on the VPS (JDK 21 + SDK 36); see
`mobile-parent/README.md`.

## What runs today (no Apple account needed)
The **`build`** job does an **unsigned** `xcodebuild` of both apps for `iphoneos`. It proves
the Capacitor iOS projects compile on macOS. Triggered on push to `capacitor-shell` (when
`mobile-*/**` changes) or via **Run workflow** (workflow_dispatch).

## Enabling signed TestFlight builds
You need an **Apple Developer Program** membership ($99/yr). Then:

1. Register both bundle IDs in the Apple Developer portal:
   `org.smoothseas.app` and `org.smoothseas.child`.
2. Create an **App Store Distribution** certificate (`.p12`) and an **App Store provisioning
   profile** for each bundle id.
3. Create an **App Store Connect API key** (Users and Access → Integrations → Keys).
4. Add these GitHub repository secrets:

   | Secret | What |
   |---|---|
   | `APPLE_TEAM_ID` | Your 10-char Apple Team ID |
   | `APPLE_DIST_CERT_P12_BASE64` | `base64 -i dist.p12` |
   | `APPLE_DIST_CERT_PASSWORD` | password for the .p12 |
   | `APPLE_PROVISIONING_PROFILE_BASE64` | `base64 -i profile.mobileprovision` |
   | `ASC_KEY_ID` | App Store Connect API Key ID |
   | `ASC_ISSUER_ID` | App Store Connect Issuer ID |
   | `ASC_KEY_P8_BASE64` | `base64 -i AuthKey_XXXX.p8` |

5. In `mobile-ios.yml`, remove `if: false` from the **`signed-archive`** job.

Each app then archives, exports an `.ipa`, and uploads to TestFlight on every push.

> Note: two provisioning profiles are needed (one per bundle id). The signed job's matrix
> already fans out per app; point each app's profile at its matching bundle id. For
> simplicity the template imports a single profile — extend it to a per-app profile secret
> (`..._PARENT` / `..._CHILD`) when you wire real signing.

## No Mac? Alternatives
[Codemagic](https://codemagic.io) and [Expo EAS-style services] also build Capacitor iOS
apps with managed signing if you'd rather not manage certs in GitHub secrets.
