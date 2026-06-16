# ForMyNieces — Handoff: Diagnostic Engine Complete → Diagnostic UI

**Date:** 16 June 2026
**Milestone reached:** Slice 2 complete — the entire diagnostic engine is built,
tested, and reproducibly seedable. Next: Slice 2f, the diagnostic UI.

---

## 1. TL;DR

The diagnostic's entire backend works end to end. A student can (in logic) go
from "start" through an adaptive ~30-question walk to a fully populated mastery
map, with conservative inference across a 150-edge prerequisite graph. There is
no UI yet — that is the next slice. 86 tests pass; the only red tests are stale
Breeze scaffolding unrelated to the diagnostic.

**Database state (verified):** 90 modules, 150 prerequisite edges, 120 anchor
questions (65 Math / 43 ELA / 12 Writing).

---

## 2. What was built this phase (Slice 2)

| Slice | Deliverable | Status |
|---|---|---|
| 2a | Prerequisite graph seeder + structural test | ✅ green |
| 2b | Math anchor bank (65 Q) + seeder + test | ✅ green |
| 2c | ELA bank (43 Q) + Writing bank (12 Q) + seeders + test | ✅ green |
| — | `diagnostic.feature` revised for MCQ-Writing | ✅ |
| Engine 1 | `MasteryInference` (conservative propagation) | ✅ green |
| Engine 2a | `SessionPlanner` (item allocation) | ✅ green |
| Engine 2b | `ItemWalk` (adaptive loop) | ✅ green |
| Engine 2e | `SessionLifecycle` (start/resume/complete) | ✅ green |
| — | Migration: widen `student_progress.status` | ✅ applied |
| — | `DatabaseSeeder` wired; `migrate:fresh --seed` verified | ✅ |

See `ARCHITECTURE.md` for how these fit together.

---

## 3. Files added this phase

**Services** (`app/Services/Diagnostic/`):
- `MasteryInference.php`
- `SessionPlanner.php`
- `ItemWalk.php`
- `SessionLifecycle.php`

**Seeders** (`database/seeders/`):
- `ModulePrerequisiteSeeder.php`
- `MathAnchorQuestionSeeder.php`
- `ElaAnchorQuestionSeeder.php`
- `WritingAnchorQuestionSeeder.php`

**Seed data** (`database/data/`):
- `math_anchor_bank.yaml`, `ela_anchor_bank.yaml`, `writing_anchor_bank.yaml`

**Migration** (`database/migrations/`):
- `2026_06_16_133103_widen_student_progress_status.php`

**Tests** (`tests/Feature/`):
- `ModulePrerequisiteSeederTest`, `MathAnchorQuestionSeederTest`,
  `ElaWritingAnchorQuestionSeederTest`, `MasteryInferenceTest`,
  `SessionPlannerTest`, `ItemWalkTest`, `SessionLifecycleTest`

**Spec:** `tests/Feature/` or `features/` — `diagnostic.feature` (revised)

**Edited:** `tests/Feature/Auth/AuthenticationTest.php` (redirect assertion →
`diagnostic.intro`), `database/seeders/DatabaseSeeder.php` (registered seeders).

---

## 4. Commit plan

Suggested to commit this milestone as a coherent unit. From the repo root:

```bash
# Review what changed
git status
git add app/Services/Diagnostic/ \
        database/seeders/ \
        database/data/ \
        database/migrations/2026_06_16_133103_widen_student_progress_status.php \
        tests/Feature/ \
        ARCHITECTURE.md HANDOFF.md

# (Adjust paths if diagnostic.feature lives elsewhere; add it too.)

git commit -m "feat(diagnostic): complete diagnostic engine (Slice 2)

- Prerequisite graph seeder (150 edges, acyclic) + structural test
- Anchor banks: 65 Math, 43 ELA, 12 Writing (120 total, >=3x coverage)
- Engine services: MasteryInference, SessionPlanner, ItemWalk, SessionLifecycle
- Conservative propagation with writing-node firewall and walk-back
- Widen student_progress.status to 4-status vocabulary
- diagnostic.feature revised for MCQ-based Writing
- 86 tests passing

Engine is backend-complete; diagnostic UI (2f) is next."
```

**Before committing, confirm:**
- `php artisan test` — expect 86 passing (6 Breeze failures are pre-existing,
  see §6).
- `php artisan migrate:fresh --seed` — clean rebuild, no FK errors.

If you prefer smaller commits, split by: (1) seeders+data, (2) engine services,
(3) migration+test fixes, (4) docs.

---

## 5. Next step: Diagnostic UI (Slice 2f)

The only remaining piece of the diagnostic. This is **frontend work** — a
different mode from the logic we've been writing — and benefits from the
`frontend-design` skill and your Filament 4 / Livewire 3 stack.

**What it must do (from `diagnostic.feature`):**
- Adventure-framed intro screen (no "test" language, no timer, no score).
- Question screen: prompt + 4 options + progress dots only. No running score,
  no correctness history.
- Difficulty/adaptation invisible to the child.
- Encouragement interstitial every 8th item.
- Resume an interrupted session.
- On completion, hand off to the (future) adventure map.

**What it builds on (already done):**
- `SessionLifecycle::startOrResume($studentId)` → session id.
- `ItemWalk::currentQuestion($sessionId)` → the question to render (or null).
- `ItemWalk::submitAnswer($sessionId, $anchorId, $chosenIndex)` → records + adapts.
- `ItemWalk::interstitialDue($sessionId)` → bool.
- `SessionLifecycle::isReadyToComplete()` / `complete()` → finish + write map.

The route stub already exists: `GET /diagnostic` → `diagnostic.intro`
(`web.php`), currently returning `view('student.diagnostic-intro')`. The UI
slice replaces that stub with a real Livewire flow.

**Suggested approach:** a single Livewire component (`DiagnosticSession`) holding
the session id, calling the engine methods above, rendering intro → questions →
interstitials → completion. Keep all adaptation logic in the engine; the
component only presents and forwards answers.

**Open UI decisions to settle when starting:** visual style of the adventure
framing (the "adventure map" metaphor from earlier UX work), how progress dots
represent ~30 items without feeling long, interstitial copy/tone, and the
completion transition.

---

## 6. Known issues / cleanup backlog

Neither blocks the UI work.

1. **Stale Breeze tests (6 failing).** `RegistrationTest` (1) and `ProfileTest`
   (5) test default Breeze behaviour the app has moved past:
   - `ProfileTest` → 404; there is **no `/profile` route**. The feature doesn't
     exist in ForMyNieces. *Recommendation: delete the file unless a profile
     page is planned.*
   - `RegistrationTest` → registration no longer auto-authenticates (likely
     verification-gated). *Recommendation: update assertions to match the real
     flow — needs `RegisteredUserController` review. Don't delete; registration
     is real.*

2. **Dead column `diagnostic_sessions.writing_sample`.** Legacy from the
   abandoned free-text Writing design. Writing is now MCQ. Pending a one-line
   drop migration.

3. **`role` column defaults to `'student'`.** Any user created without an
   explicit role becomes a student (this is why the login test routed to
   `/diagnostic`). Intentional-ish given guardians create students, but worth a
   conscious confirm — accidental student creation is a possible footgun.

4. **Provenance in JSON, not columns.** Fine for now; revisit if anchor
   filtering by source/license is ever needed.

---

## 7. How to verify everything works (sanity checklist)

```bash
php artisan migrate:fresh --seed          # 90 modules / 150 edges / 120 anchors
php artisan test                          # 86 pass, 6 stale Breeze fails
php artisan test --filter=MasteryInference   # engine core
php artisan test --filter=SessionLifecycle   # full session loop
```

Tinker spot-check of a full session is possible but the `SessionLifecycleTest`
("writes the mastery map ... on completion") already exercises start → walk →
complete → student_progress end to end.

---

## 8. Working preferences (for continuity)

- Short, direct responses; step-by-step; one slice per loop.
- Strict BDD: a scenario, then the code to make it green.
- Decisions are questioned before acceptance — rationale matters.
- Verify against the real graph/DB before writing tests (caught real bugs:
  the writing-firewall leak, the status-constraint mismatch).
