# ForMyNieces — Handoff: Diagnostic UI (Slice 2f) Complete → Adventure Map / Learning Loop

**Date:** 17 June 2026
**Milestone reached:** Slice 2f complete — the entire diagnostic UI is built, wired,
and verified end to end. A student now goes from the expedition intro through an
adaptive walk to a populated, browsable mastery map. Test suite is fully green
(108 passing, 0 failing) for the first time since the engine landed.

---

## 1. TL;DR

The diagnostic is functionally closed end to end:

intro (Set sail) → start/resume session → adaptive question walk → encouragement
interstitial every 8th item → completion (writes mastery map) → student roadmap
(`/my-map`) showing every informed module in a collapsible hierarchy with a
three-heart status gauge.

All of Slice 2f shipped across nine sub-loops, each committed separately and
verified live in the browser. Engine was untouched — 2f is pure UI + wiring on
top of the Slice 2 engine.

---

## 2. What was built this phase (Slice 2f)

| Sub-loop | Deliverable | Verified |
|---|---|---|
| 2f.1 | Adventure-framed intro screen (dark cosmic, expedition metaphor) | ✅ live |
| 2f.2 | `DiagnosticWalk` Livewire component — question render + answer forward | ✅ live |
| 2f.3 | Wiring: Set sail → `startOrResume` → full-page walk via diagnostic layout | ✅ live |
| 2f.4 | Messaging reframe (intro/completion) + wire every-8th interstitial | ✅ live |
| 2f.5 | Question-page redesign: island banner, voyage-trail boat, tappable cards | ✅ live |
| 2f.6 | Completion-on-walk-end: `complete()` writes mastery map, fixes zombie sessions | ✅ live |
| 2f.7 | `/my-map` student route (auth-only, never verified) + vocabulary reconciliation | ✅ live |
| 2f.8 | Collapsible roadmap hierarchy (Subject→prefix→module) + three-heart gauge + legend | ✅ live |
| 2f.9 | Breeze test cleanup (delete ProfileTest, fix RegistrationTest) → suite fully green | ✅ |

---

## 3. Key files added / changed this phase

**Livewire component:**
- `app/Livewire/DiagnosticWalk.php` — NOT to be confused with `App\Models\DiagnosticSession`.
  Resolves the student's own session via `startOrResume(auth()->id())` in `mount()`
  (no session id in the URL — security). Holds question/options/strand/island/plan-total.
  Calls `complete()` when the walk ends (guarded on status != 'completed', idempotent).
  Uses `#[Layout('components.layouts.diagnostic')]`.

**Views:**
- `resources/views/components/layouts/diagnostic.blade.php` — shared dark cosmic layout
  (stars, orbs, fonts, `@livewireScripts`) for all diagnostic screens.
- `resources/views/livewire/diagnostic-walk.blade.php` — three-state view:
  interstitial / completion / question. Island banner + voyage-trail boat + cards.
- `resources/views/student/diagnostic-intro.blade.php` — reframed copy ("not a test to
  pass or fail / how far you can go"). Set sail → `diagnostic.start`.
- `resources/views/dashboard.blade.php` — student roadmap rewritten: collapsible
  Subject→prefix→module hierarchy, 3-column grid, three-heart gauge, legend.

**Controller:**
- `app/Http/Controllers/DashboardController.php` — `studentDashboard()` now builds a
  `$roadmap` hierarchy (`buildRoadmap()` / `splitTopic()`) grouping modules by the
  topic prefix (text before the colon) with per-group status tallies. Passes
  `masteredCount` / `likelyCount` / `needsCount`.

**Routes (`routes/web.php`, all inside the `auth`-only group):**
- `diagnostic.intro` (GET /diagnostic) — intro view
- `diagnostic.start` (GET /diagnostic/start) — startOrResume, catches DomainException
  (onboarding incomplete) → back to intro; else redirect to walk
- `diagnostic.walk` (GET /diagnostic/walk) — full-page DiagnosticWalk component
- `student.map` (GET /my-map) — DashboardController@index, auth-only (NOT verified)

**Tests added:**
- `DiagnosticIntroTest`, `DiagnosticQuestionTest`, `DiagnosticWiringTest`,
  `DiagnosticMessagingTest`, `DiagnosticRedesignTest`, `DiagnosticCompletionTest`,
  `StudentMapTest`, `DiagnosticMapTest`, `RoadmapHierarchyTest`.

**Tests removed/fixed (2f.9):**
- Deleted `tests/Feature/ProfileTest.php` (no `/profile` route exists).
- Fixed `tests/Feature/Auth/RegistrationTest.php` to match real flow: requires
  `age_attestation`, asserts guardian role + age_attested_at + unverified +
  redirect to `verification.notice` (NOT dashboard auto-login).

---

## 4. Important decisions / facts locked this phase

- **Status vocabulary is the engine's four-status model** (canonical): `mastered`,
  `inferred_mastered`, `needs_work`, `not_started`. The old dashboard's
  `diagnostic_passed` vocabulary was stale and was reconciled AWAY. Display labels:
  Mastered / Likely Known / Needs Work. `not_started` rows are never written by
  `complete()` — only informed modules get rows (so "Upcoming" bucket was dropped).
- **Subjects in the modules table are only `Math` and `ELA`.** There is NO `Writing`
  subject at module level — "ELA Writing" is a topic prefix inside ELA. The engine
  treats Writing as a third subject for scoring/planning only. Roadmap tabs are
  therefore **Math / ELA** (decision: option A).
- **Topic naming convention is clean:** every one of the 90 topics is `Prefix: Specific`.
  Hierarchy splits on the first colon — prefix = group header, remainder = short leaf.
- **Student routes are auth-only, never verified** (synthetic emails never verify) —
  this is why `/my-map` exists separately from the verified-gated `/dashboard`.
- **Diagnostic shows NO correctness feedback** (no ticks/X). Decision reaffirmed:
  per-item correctness would punish strong students (engine climbs difficulty on
  correct answers). Encouragement is performance-independent (interstitial + reframe).
- **Heart gauge:** ❤️❤️❤️ Mastered / ❤️❤️🤍 Likely Known / ❤️🤍🤍 Needs Work.
  Broken-heart idea was rejected (morale: a child seeing 💔 on missed topics).

---

## 5. Known issues / cleanup backlog (none blocking)

1. **Dead CSS in `dashboard.blade.php`.** The old flat-roadmap classes are unused now:
   `.fmn-roadmap`, `.fmn-roadmap-line`, `.fmn-node-*`, `.dot-*`, `.sdot-mastered/
   diagnostic/notstarted`, `.fmn-node-score`. Safe to delete. (`.fmn-node-score`
   caused a test false-positive earlier — assertions on this file must target
   rendered TEXT, not class names, because all CSS is inlined.)

2. **Four-vs-three islands.** The walk shows "Writer's Bay 🪶" for Writing strands,
   but the intro only names three islands (Number Isle, Word Harbour, Story Cove).
   Either add Writer's Bay to the intro strip or collapse Writing into an existing
   island. Cosmetic.

3. **Completion percentage semantics.** The `/my-map` hero "% mastered" counts only
   `mastered`, excluding `inferred_mastered`. Product decision pending: should
   "syllabus completion" include likely-known?

4. **`DiagnosticMapTest` test name** still says "four status buckets" though there
   are now three. Harmless; rename when convenient.

5. **`x-collapse`** is used on group bodies — relies on the Alpine collapse plugin.
   If not bundled, it falls back to instant toggle (harmless). Confirm in browser.

6. **Parent dashboard** still uses the old `English Editing`/`badge-editing` subject
   vocabulary in its weekly-target badge match (`DashboardController` parentDashboard
   path + parent section of dashboard.blade.php). Not reconciled this phase — only
   the student section was. Worth a pass when parent dashboard is next touched.

7. **Emoji rendering:** 🤍 (white heart) and some banner emoji may render as outlines/
   tofu on the dev machine; they render full-colour on a child's device. Text labels
   (aria-label/title) carry meaning regardless. If 🤍 looks bad in production, swap to
   a CSS-drawn heart.

---

## 6. Verify everything works (sanity checklist)

```bash
php artisan migrate:fresh --seed     # 90 modules / 150 edges / 120 anchors
php artisan test                     # expect 108 passing, 0 failing
```

Manual end-to-end (needs `npm run dev` running for Vite):
- Create a student with onboarding complete (or via guardian child-setup).
- /diagnostic → Set sail → walk ~30 items → interstitial at item 8 →
  "You've completed the diagnostic!" → "See your map" → /my-map shows
  hierarchy with heart gauge, three stat buckets summing correctly.

Note: a walked-but-not-completed session used to "zombie" (block restart). Fixed in
2f.6 — `complete()` marks status `completed`, so `startOrResume` correctly starts
fresh next time.

---

## 7. Next step options (pick up here)

The diagnostic produces a mastery map; the natural next slices consume it:

- **Adventure map (the week-based progression, Option A from earlier UX work).**
  The roadmap on /my-map is the calm review view; the adventure map is the
  game-like week-by-week journey. This is the larger next feature.
- **Learning loop** — per the engine docblock, the learning loop later OVERWRITES
  `student_progress.score` with its own. This is where practice/teaching happens.
- **Weekly targets** — `WeeklyTarget` model exists and the dashboard renders "This
  Week's Target" but nothing populates it yet ("No target set" shows). Wiring target
  generation is a self-contained slice.
- **Cleanup loop** — knock out the backlog in §5 (dead CSS, islands, parent-dashboard
  vocabulary) for a tidy base before the next feature.

---

## 8. Working preferences (for continuity)

- Short, direct; step-by-step; one scenario per loop (failing test → min code → commit).
- Strict BDD; verify against the real DB/engine before writing tests.
- **Always specify exact file paths for code changes; break multi-file guidance out per file.**
- Recurring test trap this phase: `assertSee`/`assertDontSee` on the CSS-inlined
  dashboard matches the stylesheet and percentages. Use `assertSeeText` or assert
  distinctive rendered content — never bare class names or percentages.
- Commit boundaries verified live in the browser before moving on.
