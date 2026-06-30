# ForMyNieces — Handoff: Learning Loop (Slices 3a + 3b) Complete → Lesson View / Content / Adventure Map

**Date:** 18 June 2026
**Milestone reached:** The learning loop is built, wired, and reachable end to end.
A student lands on `/my-map`, taps a `needs_work` module, climbs a three-rung
adaptive practice ladder, masters it, and sees a celebration. Engine, UI, seeded
content, and map entry-link all connect. Test suite fully green: **122 passing,
0 failing** (up from 108 at the start of the session).

---

## 1. TL;DR

The diagnostic produces a mastery map; this session built the feature that
*consumes* it — the learning loop, where a student actually moves a module from
`needs_work` toward `mastered`.

`/my-map` → tap "Practice →" on a needs_work module → `/practice/{module}` →
three-rung climb (3 distinct correct in a row per rung) → mastery celebration →
back to map.

Built across two slice families, each loop committed separately, BDD throughout
(failing test → min code → commit), verified live in the browser.

**Decision made early:** built the learning loop FIRST, deliberately ahead of the
adventure map. Rationale: the diagnostic produced a map the student couldn't act
on; the loop is the thing that delivers educational value. The adventure map is a
navigation skin over content that didn't exist yet, so it was deferred.

---

## 2. What was built this phase

### Slice 3a — the engine (domain services + schema)

| Sub-loop | Deliverable | Verified |
|---|---|---|
| 3a.1 | `practice_questions` table + model + factory (hint, explanation, difficulty) | ✅ |
| 3a.2 | `PracticeQuestions` resolver seam — module questions, difficulty climb, active-only | ✅ |
| 3a.3 | `practice_attempts` table + `current_rung`/`current_streak` cols on student_progress | ✅ |
| 3a.4 | `RecordPracticeAttempt` mechanic — rung climb, distinct-streak, mastery at rung 3 | ✅ |

### Slice 3b — the UI + content

| Sub-loop | Deliverable | Verified |
|---|---|---|
| 3b.1 | `PracticeWalk` Livewire component + `/practice/{module}` route | ✅ live |
| 3b.2 | `choose()` wiring — records attempt, shows explanation, no-failure framing, climb indicator; fixed question re-serve within streak | ✅ live |
| 3b.3 | Practice question bank (4 modules, 36 questions) + YAML-driven seeder | ✅ live |
| 3b.4 | Mastery state — full ladder colour at mastery, celebration screen replaces "coming soon" | ✅ live |
| 3b.5 | Practice entry link from `/my-map` on needs_work modules | ✅ live |

---

## 3. The mastery rule (LOCKED — but flagged for revisit)

A student practices a module one difficulty rung at a time, bottom-up:

- Three rungs: difficulty 1 (easy) → 2 (medium) → 3 (hard).
- Clear a rung by answering **3 DISTINCT questions correctly IN A ROW** at that rung.
- A repeat of a question already in the live streak does NOT count (and does not
  break it).
- A WRONG answer resets the streak to 0 but KEEPS the rung (no demotion).
- Clearing rung 3 = module becomes `mastered`.
- `score` = derived progress %: `((rung-1)*3 + streak) / 9 * 100`, 100 at mastery.

**Content floor this rule imposes:** every rung needs ≥3 distinct questions, so a
fully masterable module needs **9 questions minimum** (3 per rung × 3 rungs).

**OPEN RULE QUESTION (parked, deliberate):** Isaac raised changing the rule to
"5 of the last 7 correct OR 3 in a row" with a visible performance bar. Parked to
first confirm 3-in-a-row works on screen (it now does). Decision pending: keep
3-in-a-row, or rewrite to the rolling-window rule. The rolling-window rule would
need a stored window (not just an int streak), rewritten mechanic tests, and a
re-reconciled feature file — bigger than it looks. Revisit with eyes open.

---

## 4. Key files added / changed this phase

**Domain services (pure-ish, match the Slice 2 engine pattern):**
- `app/Services/Practice/PracticeQuestions.php` — the question-source SEAM. Everything
  downstream depends on this, never on the table directly. `forModule($id)` returns
  active questions easiest-first (difficulty climb, sequence_order tiebreak, id final).
  `countForModule($id)` gates practiceability. Swapping question source later =
  changing this class only.
- `app/Services/Practice/RecordPracticeAttempt.php` — the mechanic. `handle(studentId,
  questionId, chosenIndex)` records a PracticeAttempt (the diary), updates the climb,
  projects rung/streak/score/status onto student_progress (the read-model the UI reads),
  returns fresh StudentProgress. Distinctness enforced via `streak_question_ids` list on
  student_progress (NOT by replaying attempts — an earlier reconstruction approach was
  buggy and was replaced with an explicit list).

**Models:**
- `app/Models/PracticeQuestion.php` — casts `options`→array, direct `module_id` FK (no pivot).
- `app/Models/PracticeAttempt.php` — the per-answer diary; casts is_correct→bool.
- `app/Models/StudentProgress.php` — UPDATED: added `current_rung`, `current_streak`,
  `streak_question_ids` to $fillable; added $casts block (these + score/previous_score
  as integer, streak_question_ids as array). NOTE: a fragment-merge error happened here
  mid-session (key-value pairs landed in $fillable). The file is correct now; in future
  give full-file replacements for model edits, not line fragments.

**Livewire component + view:**
- `app/Livewire/PracticeWalk.php` — `#[Layout('components.layouts.diagnostic')]`. Resolves
  the student's own climb in mount() (rung/streak/isMastered), serves current-rung questions
  skipping ones already in the streak, `choose()` records + shows feedback, `next()` advances.
- `resources/views/livewire/practice-walk.blade.php` — `pw-` prefixed (own stylesheet, can
  diverge from diagnostic's `dw-`). Reuses the diagnostic layout (stars/orbs/tokens). States:
  question / feedback (explanation + Next) / mastery celebration / coming-soon (thin rung).
  Ladder pips + streak dots are the visible climb indicator.

**Content (YAML-driven, mirrors the anchor seeders):**
- `database/data/practice_question_bank.yaml` — 36 questions, 4 modules (ids 1, 3, 52, 73),
  9 each (3 per rung). EDITABLE without touching PHP. Calibration is a living thing — Isaac
  treats it as actively revisited over the product lifecycle, not a one-time gate.
- `database/seeders/PracticeQuestionSeeder.php` — thin loader, same shape as
  MathAnchorQuestionSeeder (DB::table, JSON_UNESCAPED_UNICODE, validate 4 options + valid
  correct_index + valid module id, idempotent clear-then-insert). Wired into DatabaseSeeder
  after the anchor seeders.

**Migrations added:**
- `..._create_practice_questions_table.php`
- `..._create_practice_attempts_table.php`
- `..._add_practice_climb_to_student_progress.php` (current_rung default 1, current_streak default 0)
- `..._add_streak_questions_to_student_progress.php` (streak_question_ids JSON, nullable)

**Routes (`routes/web.php`, inside the `auth`-only group, never `verified`):**
- `practice.walk` (GET /practice/{module}) — full-page PracticeWalk, route-model bound on id.

**Controller + map view:**
- `app/Http/Controllers/DashboardController.php` — `buildRoadmap()` now includes `'id' =>
  $item->module->id` in each roadmap item (needed for the practice link).
- `resources/views/dashboard.blade.php` — needs_work leaves render a pink "Practice →" pill
  linking to `practice.walk`. `.fmn-practice-link` CSS added.

**Tests added (all green):**
- `PracticeQuestionTest`, `PracticeResolverTest`, `PracticeSchemaTest`,
  `RecordPracticeAttemptTest` (4 scenarios — the engine), `PracticeWalkTest` (component,
  choose, no-failure framing, re-serve fix, mastery state), plus a new scenario in
  `StudentMapTest` (needs_work module links to practice).

---

## 5. Bugs found + fixed this phase (lessons)

1. **Question re-serve within a streak.** `loadQuestion()` used `firstWhere('difficulty',
   rung)` — always the same question. Caught by a deliberately-written failing test. Fixed by
   skipping ids in `streak_question_ids`. The engine's distinct rule was already correct; the
   UI was re-serving and the engine was correctly refusing to count it — two halves of one symptom.

2. **Streak distinctness reconstruction was buggy.** First `RecordPracticeAttempt` tried to
   reconstruct the live streak by replaying attempt rows after inserting the current one — the
   skip-the-just-inserted logic was wrong (a repeat counted, streak went to 2 not 1). A failing
   test caught it. Replaced with an explicit `streak_question_ids` list stored on student_progress.
   Simpler, can't lie.

3. **Ladder didn't fully colour at mastery.** Pip was `done` only when `r < currentRung`; rung 3
   could never become done (no rung 4). Fixed: `done` when `isMastered || r < currentRung`.

4. **"Coming soon" showed at mastery.** Mastered module hit the empty-rung state. Now `isMastered`
   short-circuits to the celebration screen.

5. **Stale-state confusion during manual testing.** Repeated manual climbs accumulated
   student_progress, so re-visiting resumed mid-climb and looked like "ladder advanced early."
   It was old state, not a bug. → motivates a `practice:reset` command (see §7).

6. **Model edit fragment-merge.** Splitting a StudentProgress $fillable/$casts edit across two
   "add this line" messages caused both to land in $fillable as key-value pairs. → full-file
   replacements for model edits.

7. **Misnamed view file.** Saved as `livewire.practice-walk.php` instead of
   `practice-walk.blade.php`. The `livewire.` is the path, not the filename; `.blade` was missing.

---

## 6. Important environment / infra facts (corrected this session)

- **Project is Laravel 13, PHP 8.3, SQLite, Filament 4, Livewire 3, Pest.** (Earlier notes said
  Laravel 11 — that was wrong.)
- **`.feature` files live at `formynieces-spec/features/`** (a subfolder of the app, not a
  separate repo, no own composer.json).
- **Gherkin validation is the Node `gherkin-official` CLI** (JavaScript). `behat/gherkin` (PHP)
  is NOT installed.
- **Tests use in-memory SQLite (`:memory:`) via RefreshDatabase** — migrations run there every
  test. The DEV database (`database/database.sqlite`) is separate and needs `php artisan migrate`
  (or `migrate:fresh --seed`) run against it explicitly. This caused a "no such table" during
  manual testing — tests were green but dev DB was un-migrated.
- **Verify checklist:** `php artisan migrate:fresh --seed` → 90 modules / 150 edges / 120 anchors
  / 36 practice questions. `php artisan test` → 122 passing.
- Boost MCP (Claude Desktop, Windows) can introspect the DEV database live — used heavily this
  session to verify schema and seeded data without guessing.

---

## 7. Next step options (pick up here)

In rough value order:

- **Lesson view** — Scenario 1 of the learning_loop feature: show a module's `description` +
  `resources` (every module already has both in the DB) BEFORE practice. The loop works without
  it, but it's what makes it a *learning* loop, not a quiz loop. Cheap (read-only view, content
  exists). Would slot in front of `/practice/{module}` (e.g. `/practice/{module}/lesson` → "Start
  practising").

- **`practice:reset {student} {module?}` Artisan command** — small, but saves the manual tinker
  resets done repeatedly this session. Worth doing early next session for testing sanity.

- **Tier-two content** — only 4 modules seeded (ids 1, 3, 52, 73). The climb only works on those.
  Scale to the ~30-40 modules a student reaches in early pacing weeks. ≥9 questions/module floor.
  Generate in reviewable batches (curriculum-accuracy is Isaac's review, not auto-trusted). Add
  to `practice_question_bank.yaml`.

- **The 5-of-7 rule decision** (see §3) — now that 3-in-a-row works on screen, decide whether to
  keep it or rewrite to the rolling-window rule.

- **Feature file reconciliation** — the learning_loop.feature was rewritten in-conversation to
  match the built rung/streak rule (climb scenarios, /my-map entry, no-failure framing folded into
  the wrong-answer scenario, @v1.1 decay untouched). This needs to be SAVED to the actual file and
  re-validated with the Node gherkin-official CLI. NOT yet written to disk.

- **PARKED: `specs:trace` tracking tool** — an Artisan command to reconcile Gherkin scenarios with
  Pest tests (presence/orphan/mistagged-pending deltas). Convention designed: `@scenario:<id>` +
  `@pending` tags on scenarios, `->group('scenario:<id>')` on tests, dependency-free tag parser,
  `pest --list-groups` format is `- name (N tests)`. All 122 tests currently untagged (in
  `default`). To resume: decide presence-only vs pass/fail report, then tag scenarios + tests.
  Stored in Claude's memory too.

- **Adventure map** — the deferred game-like week-by-week navigation. Now that the loop it would
  navigate exists, this is a more informed build. Still `@v1.1`/`@roadmap` territory, not MVP.

---

## 8. Working preferences (for continuity)

- Short, direct; step-by-step; one scenario per loop (failing test → min code → commit).
- Strict BDD; verify against the real DB/engine (via Boost) before writing tests.
- **Always specify exact file paths for code changes; break multi-file guidance out per file.**
- **Full-file replacements for model/class edits** — line-fragment "add this" edits caused a
  merge error this session.
- Commit boundaries verified live in the browser before moving on.
- Calibration of question difficulty is a LIVING process, revisited over the lifecycle — not a
  blocking gate. YAML bank chosen specifically so content is editable without touching PHP.
- Manual testing reality: Vite (`npm run dev`) must run for styling; dev DB needs explicit
  migration; student needs onboarding_completed_at set and a needs_work progress row to see
  the map populate.
