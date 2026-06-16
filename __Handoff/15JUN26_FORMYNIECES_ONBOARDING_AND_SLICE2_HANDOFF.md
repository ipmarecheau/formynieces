# ForMyNieces — Guardian Onboarding Complete + Slice 2 Prerequisite Graph Handoff

**Date:** 15 June 2026
**Version:** 1.0
**Session:** guardian_onboarding scenarios 1–5 built & verified; SEA scoring model
reconciled; Slice 2 begun (prerequisite graph designed, awaiting seeder)
**Prepared by:** Session with Claude (Anthropic)
**Predecessor:** `12JUN26PM_FORMYNIECES_SLICE1_BUILD_HANDOFF.md`

---

## 1. What this session accomplished (headline)

1. **Closed Slice 1** — fixed the `Slice1SchemaTest` failures left open in the prior
   handoff. All 5 structural tests pass.
2. **Built and verified guardian_onboarding scenarios 1–5** — the entire core
   onboarding flow now works end to end, automated tests green, manually confirmed
   in the browser, deployed to the live Linode server.
3. **Reconciled the SEA scoring-model discrepancy** (the §5 blocker from the prior
   handoff) using the official MoE booklets. Canonical weighting locked into the spec.
4. **Began Slice 2** — designed the full dense prerequisite graph (~141 edges,
   Math + ELA). Edges reviewed/approved by Isaac. Seeder NOT yet built (the open item).

---

## 2. Slice 1 — how it was closed

The prior handoff left `Slice1SchemaTest` rewritten but unrun. Running it surfaced a
chain of real issues, all now fixed:

- **`users.role` CHECK rejected 'guardian'.** The original migration
  `2026_06_05_174838_add_role_and_parent_to_users_table.php` defined
  `enum('role', ['parent','student'])`. Edited in place to
  `['guardian','parent','student']`. (Live DB had lost the CHECK via SQLite rebuild
  drift, but the in-memory test DB rebuilds from migrations, so the test caught it.)
- **Seeder used legacy subject vocabulary.** `SyllabusModuleSeeder` inserted
  `'English Editing'` / `'English Comprehension'`, which the final
  `subject IN ('Math','ELA')` CHECK rejects on a fresh `migrate:fresh`. Fixed by
  normalizing in the insert loop: both collapse to `'ELA'`; the Editing/Comprehension
  distinction is preserved in `sea_section` (Section I vs II). 90 modules seed clean
  (51 Math, 39 ELA).
- **`User::factory()` undefined.** The model was missing the `HasFactory` trait.
  Added `use HasFactory, Notifiable;` and the import. (The `UserFactory.php` file
  existed all along — the prior handoff's "factory undefined" note was about the
  missing trait, not a missing file.)

**Result:** `php artisan test --filter=Slice1Schema` → 5 passed. Slice 1 fully closed.

---

## 3. guardian_onboarding — scenarios 1–5 (ALL DONE)

Build method held throughout: one scenario per loop, test → code → run → verify.
Feature tests for redirect/data logic; one browser (Pest 4 + Playwright) test for the
registration UI.

### Scenario 1 — Guardian registers with 18+ attestation ✅
- **Migration:** `add_age_attested_at_to_users_table` — `age_attested_at` nullable timestamp.
- **User model:** implements `MustVerifyEmail`; `age_attested_at` added to `$fillable`
  and cast `datetime`. (Watch: a copy-paste bug initially put `'age_attested_at' =>
  'datetime'` *inside* `$fillable` — cast syntax in the wrong array — which silently
  dropped the field. Fixed: plain string in `$fillable`, cast in `casts()`.)
- **Controller** (`RegisteredUserController@store`): validates `age_attestation` as
  `accepted`; sets `role => 'guardian'` and `age_attested_at => now()`; redirects to
  `verification.notice`.
- **View:** `register.blade.php` is a hand-built standalone HTML page (NOT Breeze
  components) with the project's purple/pink starfield theme. Removed the pre-existing
  student/parent role radios (registration is guardians-only); added the 18+ checkbox.
  **Submit button text is "Create Account 🌟"** — tests target that, not "Register".
- **Test:** `tests/Browser/GuardianOnboardingTest.php` — browser test. Key gotchas
  learned: use `#id` selectors not bare field names; use `click()` + separate
  `assertPathContains('verify-email')` rather than chaining `press()->assertPathIs()`
  (the latter races against the post-submit navigation); `assertUrlContains` does NOT
  exist in the plugin — use `assertPathContains`.

### Scenario 2 — Unverified guardian cannot reach child setup ✅
- Pure redirect gate → built as a **Feature test**, not browser (no UI to drive).
- Route `/child-setup` placed inside the existing `['auth','verified']` group in
  `routes/web.php`. The `verified` middleware auto-redirects unverified users to
  `verification.notice`. (Initial bug: route was added OUTSIDE the group → 200 instead
  of redirect. Moving it inside fixed it.)
- Minimal placeholder view `resources/views/guardian/child-setup.blade.php`.
- **Pest config:** `tests/Pest.php` `->in('Feature')` was extended to
  `->in('Feature','Browser')` so browser tests get `TestCase` + `RefreshDatabase`.
- Test: `tests/Feature/GuardianOnboardingGateTest.php` — uses `factory()->unverified()`.

### Scenario 3 — Verified guardian with no child → routed to child setup ✅
- Logic placed in `AuthenticatedSessionController` via a private `redirectTo($user)`
  method (deliberately, so scenario 5 extends the same decision point):
  ```php
  private function redirectTo($user): string {
      if ($user->isStudent() && ! $user->hasCompletedOnboarding())
          return route('diagnostic.intro');          // scenario 5
      if ($user->isGuardian() && $user->students()->doesntExist())
          return route('child.setup');                // scenario 3
      return route('dashboard');
  }
  ```
  `store()` returns `redirect()->intended($this->redirectTo($request->user()))`.
- Test: `tests/Feature/GuardianLoginRoutingTest.php`.

### Scenario 4 — Guardian creates a child profile ✅ (the substantial one)
Design decisions (all settled this session):
- **Student = `users` row**, `role='student'`, linked via `parent_id` (column name
  retained from Slice 1). Confirmed against `02_OBJECT_MODEL.md`.
- **Login = guardian sets username + password.** Stored as a SYNTHETIC EMAIL:
  `username@students.formynieces.com`. Decision rationale: keeps ONE auth system
  (Breeze email login) — students log in with the synthetic address; no separate
  student-auth path to build/maintain. **Domain chosen: `students.formynieces.com`**
  (Isaac will own formynieces.com; never set MX for the `students.` subdomain so it
  stays undeliverable). Username collision handled: the derived email must be unique;
  a clash returns a friendly "username taken" error.
- **`target_sea_year`** — new nullable `unsignedSmallInteger` on users.
- **`known_weak_areas`** — new nullable JSON on users. Guardian-supplied hints from
  checkboxes of syllabus strands (derived from the `Strand: Topic` prefix in
  `syllabus_modules.topic` via `SyllabusModule::strandsBySubject()`). **This field is
  NOT discarded** — see §4 (reconciliation feature) for why it persists.
- **`onboarding_completed_at` stays null** = "onboarding not yet completed" ✓.
- **Credentials shown once** — controller flashes `student_credentials` to session;
  the view shows a one-time green panel (username / login ID / password).
- Migration: `add_student_setup_fields_to_users_table` (target_sea_year + known_weak_areas).
  **Watch:** this migration got accidentally created twice (`211907` + `212123`); the
  duplicate was deleted. Only `211907` is live.
- Model: both fields added to `$fillable`; `known_weak_areas` cast `array`.
  `SyllabusModule::strandsBySubject()` helper added (groups distinct strand prefixes
  by subject for the form checkboxes).
- Controller: `app/Http/Controllers/ChildSetupController.php` — `create()` (renders
  form with strands) + `store()` (validates, derives synthetic email, uniqueness check,
  creates student, flashes credentials). GET `/child-setup` now points at
  `ChildSetupController@create`; POST `/child-setup` → `@store` named `child.store`.
  Both routes inside the `['auth','verified']` group.
- View: full `child-setup.blade.php` rebuilt with the theme — form + one-time
  credentials panel (two states in one file via `@if(session('student_credentials'))`).
- Test: `tests/Feature/ChildSetupTest.php` — asserts student created, synthetic email,
  parent_id link, target year, weak-areas array, onboarding null, credentials flashed.
- **Manually verified** via tinker: a real `child1@students.formynieces.com` student
  row exists with all fields correct.

### Scenario 5 — New student routed to diagnostic at first login ✅
- Added the student branch to `redirectTo()` (see scenario 3 code above).
- Route `/diagnostic` (`diagnostic.intro`) placed in an **`auth`-only** group — NOT
  `['auth','verified']` — because students have synthetic unverifiable emails; the
  `verified` middleware would bounce them forever. **This is an important design point
  for all future student-facing routes: they must be `auth`-only, never `verified`.**
- Placeholder view `resources/views/student/diagnostic-intro.blade.php`.
- Test: `tests/Feature/StudentLoginRoutingTest.php`. Manually verified: logging in as
  child1 lands on /diagnostic.

### Still deferred in guardian_onboarding (NOT built):
- `@v1.1` phone verification scenario.
- `@roadmap` second-guardian (read-only) Rule and its two scenarios.

---

## 4. Spec changes made this session

### 4a. Weak-area reconciliation (new, in roadmap_reveal.feature)
Isaac's product decision: the guardian's stated weak areas are reconciled against the
diagnostic's findings AFTER the diagnostic runs. Three cases:
- Diagnostic confirms guardian's list exactly → proceed with diagnostic, no decision.
- Diagnostic finds those PLUS more → proceed with diagnostic, no decision.
- Diagnostic finds FEWER than guardian flagged → show the guardian the difference and
  let her choose: trust the diagnostic, or keep her flagged areas (those strands'
  modules treated as not-started). Reveal does not complete onboarding until she chooses.

Added as a `Rule:` block with 4 scenarios in `roadmap_reveal.feature` (after the
progress-seeding scenario). `02_OBJECT_MODEL.md` updated: `known_weak_areas` noted as
nullable JSON consumed by this reconciliation, plus a schema-delta row. This is why
scenario 4 PERSISTS the weak areas rather than discarding them. **Build belongs in the
roadmap_reveal stretch, NOT now.**

### 4b. SEA scoring model reconciled (the §5 blocker — RESOLVED)
The prior handoff had conflicting weightings (Math 100/ELA 60/60/Writing 40 vs.
50/30/20). Verified against the official MoE **SEA 2025 & 2023 Information Booklets**
and the 2025–2028 Assessment Framework. Findings:
- **Weighting is 100:60:40 (Math:ELA:Writing) = 50/30/20 normalised.** Both prior
  numbers were the same ratio in different units. Discrepancy was a units artifact.
- **Math = 50% of the total weighting, equal to ELA + Writing combined.** Math is the
  single heaviest paper.
- **CRITICAL CONSTRAINT:** the composite is a STANDARD-SCORE composite (each paper's
  raw score → scaled to 100 → z-scored against that year's national mean/SD → weighted
  → summed). The national mean/SD are released per cohort and NOT public. Therefore
  **the real composite cannot be computed from public data** (proven: the sample
  report's 234.567 is not reproducible from the printed 95/96/19). **The platform must
  produce an ESTIMATE OF PREPAREDNESS over 50/30/20, never a predicted composite or
  placement.** Only MoE-defined anchor usable for messaging: composite ≤ 30% triggers
  mandatory re-sit.
- A canonical comment block documenting all of the above was added to the TOP of
  `diagnostic.feature`. The item-allocation scenario was corrected: ELA is a **roughly
  even 18/18 split** between Section I (mechanics) and Section II (comprehension), NOT
  the old "Section-I-heavy, a few comprehension" phrasing.
- Confirmed paper structure for anchor sizing: Math 75 marks/40 items (Number heaviest,
  ~19 items); ELA 64 marks/36 items (Section I 18, Section II 18); Writing 1 sample/20.

(A separate analysis doc, `SEA-Composite-Score-Analysis.md`, explored a four-coefficient
linear-regression recovery of the composite. Conclusion: mathematically sound but NOT
actionable — needs many real score→composite pairs that can't be ethically obtained.
The "Writing has high per-mark leverage" claim in it is internally valid but rests on
unknown per-paper SD; treat as "don't neglect Writing", not a hard rule.)

---

## 5. Slice 2 — prerequisite graph (DESIGNED, NOT YET SEEDED)

### Why this comes first
`diagnostic.feature` requires that answering an anchor marks "the anchor's prerequisite
modules as inferred mastered." That propagation walks `module_prerequisites` — which is
currently EMPTY (0 edges). So the graph must exist before the diagnostic's core mechanic
works. Decision: build the graph first, then author anchors on top of it.

### Density decision: DENSE graph (~141 edges)
Isaac chose the dense option (all plausible edges, aggressive inference) over lean.
**Paired safety rule (agreed):** the density lives in the GRAPH; the CAUTION lives in
the diagnostic ENGINE. The engine uses conservative propagation — only marks a module
mastered on unambiguous evidence, and a failed harder item un-marks the chain between
(the "contradicting harder item blocks lucky propagation" scenario). This honors the
spec's conservatism while keeping a rich graph. **Rationale for caution:** a false
"mastered" makes a child skip needed practice (harmful); under-inference is safe.

### Writing modules (69–72) decision: FULLY INTEGRATED
Isaac chose "full edges in and out" for the ELA Writing modules (the separate 3rd SEA
paper). FLAGGED TENSION: spec elsewhere treats Writing as a rubric-scored parallel track
that "never produces mastered/not." Edges INTO writing (writing depends on grammar) are
clean; edges OUT of writing are the debatable ones. **Resolution for the engine builder:**
even with out-edges present, the diagnostic engine MAY decline to propagate mastery
THROUGH Writing nodes, preserving the parallel-track rule. The out-edges are labelled in
the edge doc so they can be struck later.

### The edge set (reviewed/approved by Isaac this session)
- **Math: ~77 edges** — see `math_prerequisite_edges.md`. Within-strand backbone +
  cross-strand applied edges. Roots: 1 (Place Value), 6 (Add/Subtract). Apexes: the
  Section III multi-step modules 27, 35, 45, 51.
- **ELA: ~64 edges** — see `ela_prerequisite_edges.md`. Section I mechanics chains,
  Section II comprehension/poetry/graphic, Writing in+out. Roots: 52, 58, 62, 73.
  Apexes: 72, 79, 86, 90.
- **Total: ~141 edges.**

Module id reference:
- Math 1–51 (Number 1–8, Patterns/Relationships 9–11, Fractions 12–16, Decimals 17–21,
  Percent 22–24, Problem Solving 25–27, Geometry 28–35, Measurement 36–45,
  Statistics 46–51).
- ELA 52–90 (Spelling 52–57, Punctuation 58–60, Capitalisation 61, Grammar 62–68,
  Writing 69–72, Reading Comprehension 73–79, Poetry 80–86, Graphic Text 87–90).

---

## 6. IMMEDIATE next action (the open item)

**Build the prerequisite-graph seeder + structural test.** Steps:
1. Create `database/seeders/ModulePrerequisiteSeeder.php` loading all ~141 approved
   edges into `module_prerequisites` (each row: module_id, prerequisite_module_id).
   Source the edges from the two reviewed edge docs.
2. Write a structural test asserting: every edge references a real module id; no
   self-loops (module ≠ its own prerequisite); **the graph is ACYCLIC** (a cycle breaks
   mastery inference — especially possible given Writing in+out edges); expected edge
   count seeded.
3. Run, verify via Boost that `module_prerequisites` is populated and acyclic.

Then: register the seeder in `DatabaseSeeder` (and confirm `migrate:fresh --seed`
still produces a clean state with both module seed + prerequisite seed in the right
order — modules MUST seed before prerequisites).

**After the graph:** author the anchor question bank (~30 MCQ anchors). See §7.

---

## 7. Then: anchor question bank (the rest of Slice 2)

### Decisions already made
- **Conversion approach:** real SEA (and similar exam) free-response items → MCQ anchors
  with AI- or hand-authored distractors. The diagnostic is deliberately MCQ
  (auto-scorable), even though the real SEA is mostly free-response. Past papers are the
  SOURCE of content/difficulty, NOT a format to reproduce.
- **Authoring format:** YAML (see `anchor_questions_template.yaml`) → seeder → DB. Each
  anchor carries `source` + `license` provenance fields so items can be filtered later
  if the product charges. KaTeX `$...$` for fractions; Markdown for text; image-asset
  refs for diagram-dependent items (clocks, scales, graphs — a LARGE fraction of SEA
  Math is image-dependent); shared passage records for comprehension (TODO in template).
- **Distractors are the real work** — each must encode a named misconception (drives
  the "misconception encoded by her chosen distractor is recorded" scenario).
- **Coverage target (~30 anchors):** Math ~15 (Number heaviest), ELA roughly even
  Section I / Section II, Writing 1 sample (handled separately, not an MCQ anchor).
- **Difficulty spread per strand** is required for the adaptive walk to climb/descend.

### Sourcing / licensing (researched this session)
- **Commercial-safe:** Illustrative Mathematics curriculum tasks (CC BY 4.0, attribute);
  US state released items — NY State (`nysedregents.org/ei/math/<year>/...`) and
  California (public). SEA past papers (MoE T&T, gov public).
  - NOTE: NYSED asserts copyright on released items despite public release — safest to
    use as INSPIRATION (study style/difficulty, author fresh) rather than verbatim copy
    for a commercial product.
- **AVOID copying:** Singapore PSLE papers, UK commercial sites, Scribd uploads
  (copyrighted or unlicensed).
- **Commercial intent:** UNDECIDED. Prefer CC BY / public-domain; avoid NonCommercial
  (CC BY-NC-SA, e.g. EngageNY) until decided.

### The 1000-question general bank (PARKED)
Isaac raised a separate goal: ingest past papers at scale, AI-convert to MCQ, build a
1000+ question bank. **Explicitly deferred** — it's a practice/quiz pool, a DIFFERENT
artifact from the ~30-anchor diagnostic instrument. Revisit after the diagnostic.
Key open question when resumed: does the big bank replace, feed, or sit alongside the
diagnostic anchors? And AI is better used to GENERATE original items (sidesteps
licensing) than only to write distractors.

---

## 8. Feature build order (updated)

1. ✅ Slice 1 schema
2. ✅ guardian_onboarding scenarios 1–5 (core flow)
3. ⏳ **Slice 2a: prerequisite graph seeder + test** ← IMMEDIATE NEXT
4. ⏳ Slice 2b: anchor question bank (~30 MCQ anchors, YAML → seeder)
5. diagnostic (intro, adaptive walk, propagation engine — the conservative engine that
   reads the dense graph)
6. roadmap_reveal (incl. the new weak-area reconciliation Rule)
7. learning_loop
8. weekly_targets
9. motivation_layer
10. writing_track
11. guardian_dashboard
12. admin_content
13. (roadmap phase) adventure_map revision mode, exam_readiness

Deferred: guardian_onboarding @v1.1 (phone) and @roadmap (second guardian).

---

## 9. Deployment state (live, Linode)

- App is deployed at `http://172.233.163.6:8080` via Docker on a Linode VPS.
- Deploy dir on host: `/opt/formynieces`. App root in container: `/var/www/html`.
- Container name: `formynieces` (image `formynieces:latest`). Also runs Kavita
  (unrelated) on the same host.
- **Deploy process:** `cd /opt/formynieces && ./deploy.sh` — pulls main, copies
  `.env.production` → `.env`, `docker build`, recreates container, runs
  `migrate --force` + `db:seed --force` + caches.
- **CRITICAL data-loss caveat:** the SQLite DB is NOT on a mounted volume (only
  `storage` is mounted, at `/opt/formynieces-data/storage`). The DB lives in the
  container's writable layer and is created fresh by the Dockerfile's
  `touch database/database.sqlite`. **Every deploy wipes runtime data** (users,
  students) and re-seeds the 90 modules. Acceptable now (no real users) but MUST be
  fixed before real users: move `DB_DATABASE` onto the mounted storage volume.
- **APP_KEY:** was missing → caused a 500. Generated and added to `.env.production`.
  A guard was added to `deploy.sh` (generate-once-if-missing, never overwrite). Verify
  it was committed/deployed.
- **Mail:** `.env.production` set to `MAIL_MAILER=log` and `LOG_LEVEL=debug` so the
  email-verification link can be read from `storage/logs/laravel.log` (no real mail
  service configured — testing only).
- **Local dev:** Herd serves the site at `http://formynieces.test` (HTTP only — run
  `herd secure formynieces` if HTTPS wanted). Was not linked initially; `herd link`
  fixed it.

---

## 10. Environment

PHP 8.3 · Laravel (13.x) · SQLite · Pest 4.7.2 · Pest browser plugin + Playwright
(installed this session) · Filament 4.x · Livewire 3.x · Breeze · Boost 2.4.x.
Local: `C:\Users\isaac\Herd\ForMyNieces`. Boost MCP connected.
Groq API key present in `.env` (ExamAgentService) — for future AI use (distractors /
ExamAgentInsight).

---

## 11. Files produced this session (in outputs)

- `register.blade.php` (rebuilt — role radios removed, 18+ checkbox added)
- `verify-email.blade.php` (restyled to theme)
- `child-setup.blade.php` (full form + one-time credentials panel)
- `anchor_questions_template.yaml` (anchor authoring format with 3 worked conversions)
- `math_prerequisite_edges.md` (~77 edges, reviewed/approved)
- `ela_prerequisite_edges.md` (~64 edges, reviewed/approved)
- Plus in-repo: migrations, model edits, controllers, routes, and 5 test files
  (GuardianOnboardingTest, GuardianOnboardingGateTest, GuardianLoginRoutingTest,
  ChildSetupTest, StudentLoginRoutingTest) — all green.
- Spec edits in-repo: `diagnostic.feature` (scoring comment block + ELA allocation fix),
  `roadmap_reveal.feature` (reconciliation Rule), `02_OBJECT_MODEL.md` (known_weak_areas).

---

## 12. Key design decisions to remember (quick reference)

- Students = users rows, synthetic email `username@students.formynieces.com`, ONE auth system.
- Student-facing routes are `auth`-only, NEVER `verified` (synthetic emails never verify).
- `parent_id` column name retained; role VALUE is "guardian"; `isGuardian()` still
  accepts legacy 'parent'.
- `known_weak_areas` persists (JSON) — consumed by reveal reconciliation, not discarded.
- SEA readiness = ESTIMATE over 50/30/20, never a predicted composite/placement.
- Prerequisite graph is DENSE; the diagnostic engine must be CONSERVATIVE in propagation.
- Writing modules have full edges but the engine may decline to propagate through them.
- Post-login routing all lives in `AuthenticatedSessionController::redirectTo()`.
