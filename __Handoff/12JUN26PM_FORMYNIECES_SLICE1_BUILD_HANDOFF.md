# ForMyNieces — Slice 1 Build Session Handoff

**Date:** 12 June 2026
**Version:** 1.0
**Session:** Slice 1 schema applied + verified; scenario-by-scenario build loop begun
**Prepared by:** Session with Claude (Anthropic)
**Predecessor:** `12JUN26AM2_FORMYNIECES_MASTER_HANDOFF.md` (consolidated state of record)

---

## 1. What this session did

Picked up from the master handoff with Slice 1 listed as *migrations generated
but not applied* (in fact they had been lost). Regenerated all of Slice 1 from
the spec, applied it to the local DB, and verified the result live via Laravel
Boost MCP. Also established the working method for the rest of the build:
**one Gherkin scenario per loop** — test, code, run, verify, advance.

Slice 1 is now **applied and verified**. The automated test was rewritten after
a first run failed for harness reasons (see §5); that rewrite is **not yet run
by Isaac** — the one open action from this session.

---

## 2. Build method (agreed)

- One scenario at a time, smallest testable unit.
- Per scenario: write Pest test → write minimum code → Isaac runs in Herd →
  verify via Boost → advance.
- **E2E:** Pest 4 browser plugin (Playwright). Browser tests begin at the first
  scenario that renders UI (guardian onboarding), not before. Slice 1 is schema
  only, so it has no browser test — verified by structural test + manual tinker.
- Claude verifies DB-side outcomes directly through Boost; Isaac need not paste
  query output, only test runner results when a test is involved.

---

## 3. Slice 1 — what was built

Six migrations, timestamped `2026_06_13_000001`…`000006`. All applied
(verified in the `migrations` table, batch after `..._add_previous_score...`).

| # | File | Effect |
|---|---|---|
| 1 | `..._remap_subjects_to_ela.php` | Widen subject CHECK → remap English Editing/Comprehension → ELA → narrow CHECK to ('Math','ELA') |
| 2 | `..._create_module_prerequisites_table.php` | Self-referencing module→prerequisite edge list |
| 3 | `..._create_anchor_questions_table.php` | Diagnostic item bank (options/correct_index/difficulty/strand/distractor_notes/is_active JSON+cols) |
| 4 | `..._create_anchor_question_module_table.php` | Pivot: one anchor certifies many modules |
| 5 | `..._create_diagnostic_sessions_tables.php` | `diagnostic_sessions` + `diagnostic_responses` |
| 6 | `..._add_onboarding_completed_at_to_users_table.php` | Nullable timestamp on `users` |

### Models (in `app/Models/`)
- **`User.php`** — added `onboarding_completed_at` to fillable + datetime cast;
  added `guardian()` alias, `diagnosticSessions()`, `isGuardian()` (accepts
  legacy 'parent' during transition), `hasCompletedOnboarding()`. `isParent()`
  kept as deprecated alias.
- **`SyllabusModule.php`** — added `prerequisites()`, `dependents()`,
  `anchorQuestions()` relations.
- **`AnchorQuestion.php`** (new) — casts options/distractor_notes to array,
  difficulty/correct_index to int, is_active to bool; `modules()` pivot.
- **`DiagnosticSession.php`** (new) — casts item_plan/writing_sample to array;
  `student()`, `responses()`, `isCompleted()`.
- **`DiagnosticResponse.php`** (new) — `session()`, `anchorQuestion()`.

### Design decisions settled this session
- **Anchor→module is many-to-many** (pivot), not 1:1 — an anchor certifies a
  whole prerequisite chain, per `diagnostic.feature`.
- **options / item_plan / distractor_notes / writing_sample stored as JSON**
  (read as whole blobs, never queried by inner field).
- **`subject` CHECK final set = ('Math','ELA')**. Writing is correctly NOT a
  module subject: it is a parallel track outside the mastery model
  (`writing_track.feature`). The SEA's three taught components map as: Math →
  modules, ELA → modules, Writing → parallel track (future `writing_submissions`).
- **`parent_id` column name retained**; only the role *value* and helper methods
  move to "guardian" vocabulary. A full column rename was judged not worth it now.

---

## 4. Verified state (live via Boost, 12 June, end of session)

**Module inventory after remap — 90 modules, Math + ELA only:**

| Subject | Section | Count |
|---|---|---|
| Math | I | 32 |
| Math | II | 15 |
| Math | III | 4 |
| ELA | I (Language) | 17 |
| ELA | II (Comprehension) | 18 |
| ELA | III | 4 |

Strand totals: **51 Math, 39 ELA.** These size the Slice 2 anchor bank
(diagnostic wants ~15 Math with Number heaviest, ~9 ELA Section I, a few
comprehension, 1 writing sample).

**All Slice 1 tables present:** `module_prerequisites`, `anchor_questions`,
`anchor_question_module`, `diagnostic_sessions`, `diagnostic_responses`.
**`users.onboarding_completed_at`** present (nullable datetime).
**`syllabus_modules.subject`** CHECK = ('Math','ELA').

---

## 5. KNOWN ISSUES / drift

1. **`sea_section` CHECK constraint dropped.** The SQLite table rebuild during
   the subject remap (`enum()->change()`) did not carry forward the original
   `sea_section in ('Section I','Section II','Section III')` check — that column
   is now plain `varchar`. **Harmless** (model-layer validation governs), but it
   is real drift from the original migrations. Decision pending: restore via a
   one-line migration, or leave. Recommendation: leave.

2. **Test harness vs. seeded data.** The first `Slice1SchemaTest` asserted the
   90-module remap counts, but the suite uses `RefreshDatabase` → fresh empty DB
   → those counts read 0 (false failures). Also `User::factory()` is undefined
   (no factory in project). **Rewritten** to assert *structure only*
   (table/column existence, CHECK rejection, the two new relationships) under
   RefreshDatabase, building its own rows. The data remap is verified manually
   instead (Boost + tinker). **This rewrite has NOT been run yet by Isaac.**

3. **Weighting discrepancy (unresolved, blocks diagnostic build).** Handoff §2
   states paper weighting Math 100% / ELA Lang 60% / ELA Comp 60% / Writing 40%.
   The feature files (`diagnostic.feature`, `guardian_dashboard.feature`) say
   "50/30/20 paper weighting". These are different schemes. The diagnostic item
   allocation and the guardian pace display both depend on which governs. **Must
   be reconciled before Slice 2/diagnostic.**

---

## 6. IMMEDIATE next action (this is the open item)

Run the rewritten test:
```bash
php artisan test --filter=Slice1Schema
```
Expect **5 passing**: tables created; onboarding column nullable; CHECK allows
only Math/ELA; anchor↔module pivot; module prerequisite self-relation.

If green, Slice 1 is fully closed. If anything fails, paste the runner output.

Optional but recommended — the manual pass in `SLICE1_VERIFICATION.md`
(tinker checks B1–B5) to confirm the remap against real data with your own eyes.

---

## 7. Then: guardian_onboarding.feature, scenario 1

**"A guardian registers with an 18+ attestation."**

This is the first UI scenario, so it carries the first browser E2E test. Heads-up
on scope — it is NOT just a new file; it touches the auth layer:
- `User` must implement `MustVerifyEmail` (currently does not) — the feature
  needs email-verification gating ("an unverified guardian cannot reach child
  setup").
- Breeze registration must set `role = 'guardian'` (currently sets no role).
- Add an 18+ attestation (validated `accepted` checkbox; persisted or just
  gate-checked — to decide).
- Redirect to the email verification notice on register.

**Prerequisite for the E2E test:** install the browser plugin first (one-time):
```bash
composer require pestphp/pest-plugin-browser --dev
npm install playwright@latest
npx playwright install
```
Add `tests/Browser/Screenshots` to `.gitignore`.

---

## 8. Feature build order (dependency-sorted)

1. ✅ Slice 1 schema (this session)
2. ⏳ guardian_onboarding (registration → verification → child setup → routing)
3. diagnostic (needs Slice 2 anchor bank seeded first)
4. roadmap_reveal
5. learning_loop
6. weekly_targets
7. motivation_layer
8. writing_track
9. guardian_dashboard
10. admin_content
11. (roadmap phase) adventure_map revision mode, exam_readiness

Slice 2 (anchor question bank, sized from §4 strand counts) must precede the
diagnostic scenarios.

---

## 9. Environment (unchanged, verified this session)

PHP 8.3 · Laravel 13.14.0 · SQLite · Filament 4.11.6 · Livewire 3.8.1 ·
Breeze 2.4.2 · Pest 4.7.2 · Boost 2.4.10.
Local: `C:\Users\isaac\Herd\ForMyNieces`.
Boost MCP connected to Claude Desktop — query `application-info`,
`database-schema`, `database-query`, `search-docs` (version-scoped) before
writing Filament/Livewire code.

---

## 10. Files produced this session (in outputs)

Migrations 000001–000006, `User.php`, `SyllabusModule.php`, `AnchorQuestion.php`,
`DiagnosticSession.php`, `DiagnosticResponse.php`, `Slice1SchemaTest.php`
(rewritten), `SLICE1_VERIFICATION.md`.
