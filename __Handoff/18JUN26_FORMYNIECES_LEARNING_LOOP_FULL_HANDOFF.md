# ForMyNieces — Full Handoff: Learning Loop Complete (Slices 3a + 3b)

**Date:** 18 June 2026
**This session's milestone:** The learning loop is built, wired, and reachable end
to end. A student lands on `/my-map`, taps a `needs_work` module, climbs a
three-rung adaptive practice ladder, masters it, and sees a celebration. Engine,
UI, seeded content, and map entry-link all connect. **Test suite: 122 passing,
0 failing** (108 at session start).

This is a standalone handoff for the 18 June session, with a grounded recap of the
prior build lineage so it stands on its own.

---

## 0. Project at a glance

ForMyNieces is a Laravel SEA (Secondary Entrance Assessment) exam-prep web app for
primary students in Trinidad & Tobago. Stack: **Laravel 13.x, PHP 8.3, SQLite,
Filament 4, Livewire 3, Breeze auth, Pest 4** (+ Playwright browser tests). Local
dev via Laravel Herd on Windows at `C:\Users\isaac\Herd\ForMyNieces`; deployed on a
Linode VPS via Docker. Boost MCP is configured and used to introspect the live dev
database.

Core architecture (established in earlier sessions):
- Students are `users` rows with synthetic emails (`username@students.formynieces.com`),
  so one Breeze auth serves everyone. Student routes are **`auth`-only, never
  `verified`** (synthetic emails can't verify). Post-login routing lives in
  `AuthenticatedSessionController::redirectTo()`.
- 90 syllabus modules (51 Math, 39 ELA, ids 1–90). Subjects at module level are only
  **Math and ELA**; Writing is a topic prefix inside ELA (the engine treats Writing
  as a third subject for scoring/planning only). Every topic follows `"Prefix: Specific"`.
- Build method (strict): **one scenario per loop — failing test → minimum code → run
  → verify → commit.** Verify against the real DB/engine (via Boost) before writing
  tests. Exact file paths for every change; full-file replacements for model/class edits.

---

## 1. Previously, in build order (grounded recap)

- **Slice 1 — schema.** Five migrations (`diagnostic_sessions`, `diagnostic_responses`,
  `anchor_questions`, `anchor_question_module`, `student_progress`, etc.). Guardian
  onboarding (5 scenarios) built and verified.
- **Slice 2a — prerequisite graph.** `module_prerequisites` seeded: **150 acyclic edges**
  (86 Math, 64 ELA), validated (no dupes, no self-loops, acyclic via DFS).
- **Slice 2b–2d — anchor banks.** Three banks totalling **120 active anchors** (Math 65,
  ELA, Writing 12), YAML-sourced (`database/data/*_anchor_bank.yaml`), ≥3× module coverage,
  CC-BY-NC provenance carried in `distractor_notes.meta`.
- **Slice 2e — diagnostic engine.** Four services: `MasteryInference`, `SessionPlanner`,
  `ItemWalk`, `SessionLifecycle`. Adaptive walk, conservative inference with a Writing
  firewall, idempotent completion that writes the mastery map to `student_progress`.
  Four-status model: `mastered`, `inferred_mastered`, `needs_work`, `not_started`.
- **Slice 2f — diagnostic UI (the 17 June handoff).** Full diagnostic UI: cosmic/expedition
  themed intro → `DiagnosticWalk` Livewire component → `/my-map` student roadmap with a
  collapsible Subject→prefix→module hierarchy and a three-heart gauge. Suite reached 108.

**D5 (carried forward, important):** `SessionLifecycle::complete()` writes
`student_progress.score` as the highest difficulty rung demonstrated (1/2/3) for a
directly-mastered module, null otherwise. Its docblock explicitly says *"the learning
loop later overwrites score with its own"* — which this session did.

---

## 2. This session: the decision that set direction

Isaac asked whether to build the adventure map next. We talked it through and chose to
build the **learning loop FIRST**, deliberately ahead of the adventure map. Rationale:
the diagnostic produced a mastery map the student couldn't *act* on — tapping a module
did nothing. The learning loop is what delivers educational value (moving a module from
`needs_work` toward `mastered`). The adventure map is a navigation skin over content that
didn't exist yet, so building it first would mean a pretty journey to dead ends. Adventure
map deferred to `@v1.1`/`@roadmap`.

---

## 3. What was built (slices 3a + 3b)

### Slice 3a — the engine
| Loop | Deliverable |
|---|---|
| 3a.1 | `practice_questions` table + model + factory (hint, explanation, difficulty) |
| 3a.2 | `PracticeQuestions` resolver SEAM — module questions, difficulty climb, active-only |
| 3a.3 | `practice_attempts` table + `current_rung`/`current_streak` cols on student_progress |
| 3a.4 | `RecordPracticeAttempt` mechanic — rung climb, distinct-streak, mastery at rung 3 |

### Slice 3b — the UI + content
| Loop | Deliverable |
|---|---|
| 3b.1 | `PracticeWalk` Livewire component + `/practice/{module}` route |
| 3b.2 | `choose()` wiring — record attempt, show explanation, no-failure framing, climb indicator; fixed question re-serve within streak |
| 3b.3 | Practice question bank (4 modules, 36 questions) + YAML-driven seeder |
| 3b.4 | Mastery state — full ladder colour at mastery + celebration screen |
| 3b.5 | Practice entry link from `/my-map` on needs_work modules |

---

## 4. The mastery rule (LOCKED — flagged for revisit)

- Three rungs: difficulty 1 (easy) → 2 (medium) → 3 (hard), climbed bottom-up.
- Clear a rung by answering **3 DISTINCT questions correctly IN A ROW** at that rung.
- A repeat of a question already in the live streak does NOT count (and does not break it).
- A WRONG answer resets the streak to 0 but KEEPS the rung (no demotion).
- Clearing rung 3 = `mastered`.
- `score` = derived %: `((rung-1)*3 + streak) / 9 * 100`, 100 at mastery. `previous_score`
  holds the value before the latest write.
- **Content floor:** a fully masterable module needs ≥3 questions per rung = **9 minimum**.

**OPEN RULE QUESTION (parked, deliberate):** Isaac raised changing to "5 of the last 7
correct OR 3 in a row" with a visible performance bar. Parked to first confirm 3-in-a-row
works on screen (it now does). The rolling-window rule would need a stored window (not a
single int), rewritten mechanic tests, and a re-reconciled feature file — bigger than it
looks. Decide next: keep 3-in-a-row or rewrite.

---

## 5. Key files added / changed

**Services (`app/Services/Practice/`):**
- `PracticeQuestions.php` — the question-source SEAM. Downstream depends on this, never the
  table. `forModule($id)` (active, difficulty climb, sequence_order tiebreak, id final),
  `countForModule($id)`. Swapping question source later = changing this class only.
- `RecordPracticeAttempt.php` — the mechanic. `handle(studentId, questionId, chosenIndex)`
  records a PracticeAttempt, updates the climb, projects rung/streak/score/status onto
  student_progress, returns fresh StudentProgress. Distinctness via an explicit
  `streak_question_ids` list (NOT by replaying attempts — that approach was buggy, see §6).

**Models:**
- `PracticeQuestion.php` — `options`→array cast; direct `module_id` FK (no pivot).
- `PracticeAttempt.php` — per-answer diary; `is_correct`→bool.
- `StudentProgress.php` — added `current_rung`, `current_streak`, `streak_question_ids` to
  $fillable + a $casts block (integers + streak_question_ids as array).

**Livewire + view:**
- `app/Livewire/PracticeWalk.php` — uses the diagnostic layout. Resolves the student's climb
  in mount() (rung/streak/isMastered), serves current-rung questions skipping streak ids,
  `choose()` records + shows feedback, `next()` advances. `isMastered` short-circuits to celebration.
- `resources/views/livewire/practice-walk.blade.php` — `pw-` prefixed (own stylesheet).
  States: question / feedback (explanation + Next) / mastery celebration / coming-soon
  (thin rung). Ladder pips + streak dots = the visible climb. Reuses cosmic layout tokens.

**Content:**
- `database/data/practice_question_bank.yaml` — 36 questions, modules 1, 3, 52, 73 (9 each,
  3 per rung). EDITABLE without touching PHP. Calibration treated as a LIVING process.
- `database/seeders/PracticeQuestionSeeder.php` — thin YAML loader, mirrors the anchor seeders
  (validate 4 options + valid correct_index + valid module id, idempotent clear-then-insert).
  Wired into `DatabaseSeeder` after the anchor seeders.

**Migrations:** create practice_questions; create practice_attempts; add current_rung/
current_streak to student_progress; add streak_question_ids to student_progress.

**Routes (`routes/web.php`, `auth`-only group):** `practice.walk` (GET /practice/{module}),
route-model bound on id, full-page PracticeWalk.

**Controller + map:** `DashboardController::buildRoadmap()` now includes `'id'` per item;
`dashboard.blade.php` renders a pink "Practice →" link on needs_work leaves (`.fmn-practice-link`).

**Tests (all green):** PracticeQuestionTest, PracticeResolverTest, PracticeSchemaTest,
RecordPracticeAttemptTest (4), PracticeWalkTest (component/choose/no-failure/re-serve/mastery),
+ a needs_work-links-to-practice scenario in StudentMapTest.

---

## 6. Bugs found + fixed (lessons)

1. **Question re-serve within a streak** — `firstWhere('difficulty', rung)` always returned the
   same question. Caught by a deliberately-failing test. Fixed by skipping `streak_question_ids`.
2. **Streak distinctness reconstruction was buggy** — replaying attempt rows after inserting the
   current one mis-skipped; a repeat counted. Replaced with the explicit `streak_question_ids` list.
3. **Ladder didn't fully colour at mastery** — pip `done` only when `r < currentRung`; rung 3 could
   never be done. Fixed: `done` when `isMastered || r < currentRung`.
4. **"Coming soon" showed at mastery** — empty-rung state fired on a mastered module. `isMastered`
   now short-circuits to celebration.
5. **Stale-state confusion in manual testing** — accumulated student_progress made re-visits resume
   mid-climb, looked like "ladder advanced early." Old state, not a bug. → motivates `practice:reset`.
6. **Model edit fragment-merge** — a split "add this line" StudentProgress edit landed key-value pairs
   in $fillable. → use full-file replacements for model edits.
7. **Misnamed view file** — saved as `livewire.practice-walk.php` not `practice-walk.blade.php`.

---

## 7. Environment facts (corrected / confirmed this session)

- **Laravel 13** (earlier notes said 11 — wrong), PHP 8.3, SQLite, Filament 4.
- `.feature` files live at **`formynieces-spec/features/`** (subfolder of the app).
- Gherkin validation is the **Node `gherkin-official` CLI**; `behat/gherkin` (PHP) is NOT installed.
- **Tests use in-memory SQLite (`:memory:`); the dev DB (`database/database.sqlite`) is separate**
  and needs explicit `php artisan migrate` / `migrate:fresh --seed`. (A "no such table" during manual
  testing came from the dev DB being un-migrated while tests were green.)
- Verify: `migrate:fresh --seed` → 90 modules / 150 edges / 120 anchors / 36 practice questions;
  `php artisan test` → 122 passing.
- Manual testing needs: Vite (`npm run dev`) for styling; a student with `onboarding_completed_at`
  set and a `needs_work` student_progress row to see the map populate.

---

## 8. Next step options (rough value order)

1. **Lesson view** — Scenario 1 of learning_loop: show module `description` + `resources` (both
   already in the DB) before practice. Makes it a *learning* loop, not a quiz. Cheap. Slots in front
   of `/practice/{module}`.
2. **`practice:reset {student} {module?}` command** — small; removes the manual-reset friction hit
   repeatedly this session. Do early.
3. **Tier-two content** — only 4 modules seeded; the climb only works on those. Scale to ~30–40
   early-pacing-week modules, ≥9 questions each, generated in reviewable batches (Isaac reviews for
   curriculum accuracy). Append to the YAML bank.
4. **5-of-7 rule decision** (see §4) — now that 3-in-a-row works on screen.
5. **Save the reconciled learning_loop.feature to disk** — it was rewritten in-conversation to match
   the built rung/streak rule (climb scenarios, /my-map entry, no-failure framing folded into the
   wrong-answer scenario, lesson scenario `@pending`, `@v1.1` decay untouched) but NOT yet written to
   the file. Re-validate with the Node CLI after saving.
6. **PARKED: `specs:trace` tool** — Artisan command to reconcile Gherkin scenarios with Pest tests
   (presence/orphan/mistagged-pending deltas). Convention designed: `@scenario:<id>` + `@pending` tags
   on scenarios, `->group('scenario:<id>')` on tests, dependency-free tag parser, `pest --list-groups`
   format `- name (N tests)`. All tests currently untagged (`default`). Resume: presence-only vs
   pass/fail report, then tag.
7. **Adventure map** — the deferred game-like navigation; better-informed now the loop exists. `@v1.1`.

---

## 9. Working preferences (continuity)

- Short, direct; step-by-step; one scenario per loop.
- Strict BDD; verify against the real DB/engine (Boost) before writing tests.
- Exact file paths per change; per-file breakdown for multi-file guidance.
- **Full-file replacements for model/class edits.**
- Commit boundaries verified live in the browser before moving on.
- Question-difficulty calibration is a LIVING process, not a blocking gate; YAML bank chosen so
  content edits don't touch PHP.
