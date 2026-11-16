# Guided user testing (`/user-testing`)

A low-friction, mobile-first walkthrough that lets a **non-technical tester** (e.g. a family
member) exercise real features and leave a verdict, one scenario at a time, without any
login, seeding, or setup on their part. It exists so verification evidence can be gathered by
many people, while final sign-off (`specs:verify`) stays with the maintainer.

Routes live in `routes/web.php`; the UI is the Livewire component `App\Livewire\UserTesting`
with layout `components/layouts/testing.blade.php`.

!!! note "This is the one feature not tested *through* `/user-testing`"
    Every other feature is verified by walking it inside this tool. The tool cannot test
    itself, so it is verified instead by the Playwright harness
    `scripts/user-testing/verify-scenarios.mjs` (see [Pre-publish verification](#pre-publish-verification)).

## How it works

1. **Sign in** — the tester enters an email + name. The email uniquely identifies them, so
   verdicts persist across sessions and devices (`session('tester_email')`). No password,
   no real account.
2. **Pick a feature** — the chooser lists each feature with a progress pill.
3. **Walk scenarios** — plain-English steps on the left; the **real screen embedded in an
   iframe** on the right, already signed in as the right persona, at a **Web / Tablet /
   Mobile** viewport. Back/forward move freely.
4. **Record a verdict** — *It worked* / *Something was off* / *Skip*, plus an optional note.
   Each is an upsert keyed on `(tester_email, scenario_id)` into **`verification_runs`**.

`verification_runs` rows are **evidence for review**, never a `specs:verify`. The maintainer
reviews them and records the real verification separately.

### Auto-login (hard-gated)

`GET /user-testing/enter/{scenario}` (`UserTestingController`) seeds the scenario's state via
`App\Services\UserTesting\ScenarioFixtures`, signs the tester in as a **dedicated `is_test`
account**, and redirects to the screen under test. It is refused in production unless
`USER_TESTING_IMPERSONATION` is set, and it will only ever log into an account flagged
`is_test` (`config/user_testing.php`).

Each scenario gets its own stable, idempotent account on the `testdrive.smoothseas.test`
domain (`td-<scenario>-<persona>@…`). All AL (activity-log) scenarios deliberately share
**one** child named exactly `Test Explorer`, so the admin picker is unambiguous; every other
scenario's child is name-qualified (`Test Explorer (GD-01)`) to avoid collisions.

## Gates & blockers audit

Every screen a tester lands on may be guarded by middleware or in-page requirements. Any
gate that a seeded tester cannot satisfy would bounce them *out of the feature under test* —
so each was identified and handled. Device features cannot be embedded at all and use a
separate QR-install track.

| Feature | Scenarios | Start screen | Gate / blocker | Resolution |
| --- | --- | --- | --- | --- |
| **HC** Help | HC-01/02/04 | `/guardian/dashboard` | `Authenticate` + `EnsureEmailIsVerified` + `EnsureRole:guardian` | Seeded guardian is verified + age-attested |
| | HC-03 | `/voyage` | `EnsureRole:student`; **microphone** for the voice note | Seeded onboarded student; iframe granted `allow="microphone; camera"` |
| | HC-05 | `/` | none (guest) | Signed out on entry |
| **GD** Dashboard | GD-01/05 | `/guardian/dashboard` | guardian + verified | Verified seeded guardian |
| | GD-02 | `/guardian/progress` | guardian + verified | same |
| **AL** Activity log | AL-01/02/03 | `/admin/student-logs` | **Filament panel**: `Authenticate` + **`AuthenticateSession`** + `canAccessPanel` (admin) | Seeded `is_test` admin; **session password-hash stamped** on login (see below) |
| **LR** Reminders | LR-01/07 | *device* (kids app) | physical device; notification + alarm permissions | QR-install track + permission walkthrough |
| **PN** Push | PN-01 | *device* (parent app) | physical device; notification permission; **push server off** | QR-install track; scenario limited to device registration, with a note |

### Blockers found and fixed

- **Email-verification gate (all guardian/student screens).** `email_verified_at` is
  intentionally *not* mass-assignable on `User`, so `updateOrCreate(['email_verified_at' => …])`
  silently dropped it and every seeded account landed unverified → bounced to the verify gate.
  Fixed by force-setting it in `ScenarioFixtures::markVerified()`.
- **Filament login bounce (AL).** The admin panel runs `AuthenticateSession`, which logs a
  user straight back out to `/admin/login` unless the session carries a password hash matching
  the current user — which a programmatic `Auth::login()` never sets. Fixed in
  `UserTestingController::enter()` by stamping `password_hash_<guard>` into the session after
  login. (This was the "login page blocking me" symptom inside the AL iframe.)
- **Duplicate "Test Explorer" in the admin picker.** Every scenario minted its own child with
  the same name. Fixed by sharing one canonical AL child and name-qualifying the rest.
- **Microphone blocked in the iframe (HC-03).** `getUserMedia` is denied inside an iframe
  unless the embed grants it. Fixed with `allow="microphone; camera; clipboard-write"` on the
  preview iframe.

### Why embedding works at all

The app sends **no** `X-Frame-Options` / CSP `frame-ancestors`, and the preview is
**same-origin**, so the real screens embed and share the session cookie. A cross-origin
preview would not receive the `SameSite=Lax` session cookie — keep the preview same-origin.

### Device features (cannot be embedded)

LR and PN depend on a physically installed app and OS-level permissions, so they are not
embeddable. They render an **install QR** for the dev APK (served un-gated from
`/apps` on the dev host) plus a permissions walkthrough. PN additionally needs the
self-hosted push server running to deliver live alerts; until then PN-01 is scoped to
confirming the device *registers*, with an explanatory note.

## Pre-publish verification

Because this feature can't test itself, **before adding or changing a scenario** run the
Playwright harness against a running dev server. It signs in as a tester, walks every web
scenario, and asserts the embedded preview lands on the correct screen — not a login or
verification gate:

```bash
# dev server running + USER_TESTING_IMPERSONATION enabled
node scripts/user-testing/verify-scenarios.mjs
# or against the dev host:
UT_BASE=https://dev.smoothseas.org node scripts/user-testing/verify-scenarios.mjs
```

Server-side behaviour (auto-login, verification, fixture seeding, the upsert) is covered by
`tests/Feature/UserTestingTest.php`.

## Adding a scenario

1. Add it to `App\Services\UserTesting\TestingCatalog::features()` with `id`, `title`,
   `goal`, `persona`, `start`, `steps`, `expect`.
2. Ensure `ScenarioFixtures` seeds whatever the `start` screen needs to render as a real
   user would see it.
3. Confirm the `start` screen's middleware gates are all satisfiable by the seeded persona
   (consult the table above).
4. Run `node scripts/user-testing/verify-scenarios.mjs` and confirm it passes **before**
   sharing the link.
