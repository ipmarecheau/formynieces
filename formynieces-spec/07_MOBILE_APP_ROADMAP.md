# 07 — Mobile App Roadmap: Flutter Parent + Child Apps in Two Weeks

**Status:** Planning draft for a focused two-week build using Flutter, the existing Laravel backend, and three coding agents: 2 Claude Code agents and 1 Codex agent.

The goal is not to rebuild the whole web app. The goal is to ship a sticky mobile MVP that makes the two daily jobs easier:

- The child opens the app and immediately knows what to do next.
- The parent opens the app and immediately knows what matters this week.

SmoothSeas should enter the stores as one Flutter codebase with two role-based experiences, not two unrelated apps. A separate App Store / Play Store listing for parent and child can be decided later, but the first two-week build should share code, navigation, API client, theme, analytics and release pipeline.

---

## 1. Product thesis

The web app is useful but not sticky enough for SEA preparation. SEA prep happens in short, tired moments: after school, before lessons, in the car, while a parent is at work, or when a child has ten minutes before bed. Browser login, tabs and desktop-shaped dashboards add friction.

The mobile app should make SmoothSeas feel like this:

> The child gets a daily SEA game. The parent gets a calm SEA control room.

That means two interfaces over the same data:

| Experience | Primary user | Core promise | Main habit |
|---|---|---|---|
| Child app | Standard 3-5 student | “Smooth tells me my next mission.” | Daily 10-minute practice streak |
| Parent app | Parent/guardian | “I know what my child should practise next and why.” | Weekly progress check + nudges |

---

## 2. Two-week definition of done

By the end of two weeks, a tester should be able to install a dev build on iOS and Android and complete this loop:

1. Parent signs in.
2. Parent sees each child and the weekly action summary.
3. Parent can open weak topics, writing status, streak/activity and readiness signals.
4. Child signs in or enters through parent-created handoff.
5. Child lands on Today, sees Smooth and the next daily mission.
6. Child completes a short practice set from the existing question bank.
7. Child sees result, correction and progress feedback.
8. Parent sees the child’s latest activity reflected in the app.

Non-goals for the two-week MVP:

- Native Swift/Kotlin rebuilds.
- Offline-first content sync.
- Full payment implementation inside the app.
- App Store production approval as a guaranteed outcome.
- Rebuilding every Livewire screen.
- Full creative-writing grading UI if the backend endpoint is not already API-ready.
- Feature parity with the Laravel web app.

---

## 3. Architecture choice

Use Flutter for one shared codebase.

Recommended package shape:

```text
mobile/
  smoothseas_app/
    lib/
      app.dart
      theme/
      auth/
      api/
      child/
      parent/
      shared/
    test/
    integration_test/
```

Laravel remains the source of truth:

- Users, guardians, children and roles.
- Diagnostics and learning profile.
- Daily plans and weekly targets.
- Practice questions and attempts.
- Reteach sessions and explanations.
- Writing prompts/submissions.
- Streaks and rewards.
- Parent dashboard summaries.

Mobile should consume API endpoints. Avoid scraping Blade/Livewire pages.

---

## 4. MVP app surfaces

### Child app

| Screen | Purpose | MVP scope |
|---|---|---|
| Login / handoff | Get child into app without parent exhaustion | Email/password if already available; handoff token or child credentials if API-ready |
| Today | Main sticky surface | Smooth greeting, daily mission, streak, one primary “Start” button |
| Practice | Complete short SEA set | Question, answers, progress, submit, result |
| Fix mistake | Convert misses into learning | Show verified explanation / re-teach copy from backend |
| Result | Reward completion | Accuracy, XP/streak/progress copy, next step |
| Voyage preview | Motivation | Lightweight map/progress shell; can link to web view if full map is too large |

### Parent app

| Screen | Purpose | MVP scope |
|---|---|---|
| Login | Parent access | Existing auth via API token/session |
| Children | Select child | Child cards with latest status |
| Today / Overview | Calm control room | “Practise this next,” weekly action, streak/activity, writing status |
| Weak topics | What needs attention | Top 3 weak topics, subject, reason, suggested action |
| Readiness | Exam confidence | Timed practice status, completion trend, readiness copy if available |
| Writing | Composition visibility | Latest prompt/submission status and rubric summary if available |
| Account | Basic controls | Logout, support links, web fallback for billing/settings |

---

## 5. Required Laravel API endpoints

These are the API contracts the mobile team should target. If an endpoint already exists, wrap or adapt it. If not, create the smallest controller/resource needed.

| Endpoint | Method | User | Purpose |
|---|---:|---|---|
| `/api/mobile/login` | POST | parent/child | Issue mobile token and user role |
| `/api/mobile/me` | GET | both | Return user, role, linked children |
| `/api/mobile/children` | GET | parent | Child list with summary cards |
| `/api/mobile/children/{child}/overview` | GET | parent | Parent control-room data |
| `/api/mobile/children/{child}/weak-topics` | GET | parent | Actionable weak topic list |
| `/api/mobile/children/{child}/writing` | GET | parent | Latest writing status/feedback summary |
| `/api/mobile/children/{child}/readiness` | GET | parent | Readiness/timed practice summary |
| `/api/mobile/child/today` | GET | child | Daily mission, streak, next practice |
| `/api/mobile/child/practice/start` | POST | child | Start a practice session |
| `/api/mobile/child/practice/{session}` | GET | child | Current question/session state |
| `/api/mobile/child/practice/{session}/answer` | POST | child | Submit answer and return feedback |
| `/api/mobile/child/practice/{session}/finish` | POST | child | Finish session and update progress |
| `/api/mobile/logout` | POST | both | Revoke token |

MVP response rule: send mobile screens exactly what they need. Do not expose raw internal model graphs and force Flutter to reconstruct the web dashboard.

---

## 6. Two-week schedule

### Day 0 — planning lock, repo setup

- Confirm MVP scope above.
- Create `mobile/smoothseas_app` Flutter project.
- Choose state management: Riverpod is recommended for testability and low boilerplate.
- Create API contract stubs and mock JSON fixtures.
- Freeze scenario IDs in `mobile_child_app.feature` and `mobile_parent_app.feature`.

### Days 1-2 — backend mobile API slice

- Token auth for parent and child.
- `/me`, children list, child overview.
- Child Today endpoint with daily mission and streak.
- Practice start/answer/finish endpoints using existing question/attempt logic.
- API feature tests for the happy path and role separation.

### Days 2-3 — Flutter foundation

- App shell, theme, routing, auth state.
- API client with typed DTOs.
- Mock mode for screens while backend endpoints are finishing.
- Login screen and role-based routing.

### Days 4-6 — child app MVP

- Today screen.
- Practice session screen.
- Answer feedback screen.
- Result screen.
- Streak/progress display.
- Basic empty/error/loading states.

### Days 5-7 — parent app MVP

- Children switcher/list.
- Parent overview.
- Weak topics.
- Writing summary.
- Readiness summary.
- Support/account links.

### Days 8-9 — integration pass

- Wire Flutter screens to real dev API.
- Fix auth/session edge cases.
- Confirm child and parent role separation.
- Add instrumentation events for login, start practice, finish practice, parent overview open.

### Days 10-11 — polish and habit mechanics

- Push notification scaffolding, even if only local/test notifications ship.
- Mobile copy tightening.
- Smooth animation/lightweight illustration pass.
- Device QA on small Android, large Android, iPhone, iPad/tablet.

### Days 12-13 — beta packaging

- Android internal testing build.
- iOS TestFlight build if Apple account/certs are ready.
- Privacy labels / data safety draft.
- Tester instructions.
- Known limitations list.

### Day 14 — stabilization

- Regression run.
- Fix top bugs only.
- Confirm dev API and app builds point to the correct environment.
- Decide whether to continue beta hardening or expand scope.

---

## 7. Three-agent work allocation

The safest split is by ownership boundary, with one agent owning backend, one owning the child habit loop, and one owning the parent control room plus release QA.

### Claude Code Agent 1 — backend/API lead

Owns Laravel API implementation and tests.

Initial tickets:

- Mobile auth endpoints.
- Parent child-list and overview endpoints.
- Child Today endpoint.
- Practice start/answer/finish endpoints.
- API feature tests.

### Claude Code Agent 2 — Flutter child app lead

Owns the child-facing app experience and keeps it sticky.

Initial tickets:

- Child Today screen.
- Practice session UI.
- Feedback/result UI.
- Streak/voyage habit loop.
- Child navigation and empty/error states.
- Child app widget tests.

### Codex Agent 1 — Flutter parent app + QA lead

Owns the parent-facing app experience, shared release wiring, and QA checklist.

Initial tickets:

- Parent shell and child switcher.
- Parent overview.
- Weak topics screen.
- Writing/readiness summary screens.
- Shared theme/API client support with the child-app agent.
- Manual QA scripts, beta packaging checklist, and store-metadata draft.

### Human owner

Owns decisions that affect product positioning and release risk:

- Confirm pricing shown in app.
- Confirm whether there are separate store listings or one app with two role modes.
- Confirm child login/handoff preference.
- Provide Apple/Google developer account access or decide to stop at local/internal builds.
- Verify app flows on real devices.

---

## 8. Build risks and mitigations

| Risk | Why it matters | Mitigation |
|---|---|---|
| Existing logic is Livewire-shaped, not API-shaped | Flutter cannot safely consume server-rendered UI | Build a thin mobile API over existing services/models |
| Auth and child accounts are awkward | Parent exhaustion gets worse if login is hard | Prioritize parent-created child handoff or simple child credentials |
| Writing feedback may be async/expensive | Hard to promise in a two-week app | Show latest writing status first; deeper grading can stay web-backed |
| App Store review slows launch | Two weeks may not include approval | Target TestFlight + Play internal testing as the two-week deliverable |
| Too much gamification polish | Can consume the schedule | Ship Today + streak + simple progress before full Voyage map |
| Parent app becomes another dashboard | It defeats the mobile premise | Lead every parent screen with one action and one reason |

---

## 9. Suggested backlog after the two-week MVP

- Push notifications for parent nudges and child streak reminders.
- Parent-approved rewards and reward redemption.
- Full Voyage map in Flutter.
- Writing submission and rubric feedback in-app.
- Mock exam mode.
- School selector / placement planner.
- Tutor/school plan support.
- Offline review for missed questions.
- In-app purchase or subscription handoff once business model is settled.


---

## 10. Interactive prototype

A clickable mobile mockup lives at `formynieces-spec/mobile-prototype/index.html`. It covers the child Today/practice/reward loop and the parent overview/weak topics/readiness/writing loop. Use it to validate the UX before the Flutter scaffold is generated.
