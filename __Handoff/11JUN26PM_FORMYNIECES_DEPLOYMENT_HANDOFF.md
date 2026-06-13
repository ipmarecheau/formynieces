# ForMyNieces — Onboarding & Diagnostic Build Handoff

**Date:** 13 June 2026
**Version:** 1.0
**Status:** Slice 1 (schema) ready to apply. Slices 2–4 pending.
**Prepared by:** Build session with Claude (Anthropic)

---

## 1. Context

The deployment is live and working at `http://172.233.163.6:8080` (see the
11 June deployment handoff for server details). A student can log in and
reach the dashboard. Production has all 90 syllabus modules, but a fresh
student lands on an empty dashboard (0 mastered, "roadmap being prepared")
because there is no onboarding flow yet.

This document covers the build of that flow: guardian setup, an adaptive
diagnostic, and a reveal that populates the dashboard.

---

## 2. Key decisions made this session

### SEA syllabus correction (confirmed against official MoE framework)

The official **SEA 2025–2028 Assessment Framework** defines three papers:

| Paper | Placement weight | As % of composite |
|---|---|---|
| Mathematics | 100 | ~50% |
| English Language Arts (ELA) | 60 | ~30% |
| ELA Writing | 40 | ~20% |

(The 100:60:40 weights are applied to *standardised* scores, not raw marks.
Expressed as a share of the placement decision, that is 50/30/20. Use
"relative placement weight," not "percent of the paper," when explaining.)

The old subject labels were corrected:

- `Math` → unchanged.
- `English Editing` → **ELA Section I** (Spelling, Punctuation/Capitalisation, Grammar).
- `English Comprehension` → **ELA Section II** (Reading Comprehension).
- `Writing` → new subject; modules to be authored (not yet built).

ELA structure from the framework: Section I = 30 marks (spelling 12,
punctuation/capitalisation 6, grammar 12); Section II = 34 marks
(fiction/non-fiction 13, poetry 13, graphic 8). Total 36 items, 64 marks.

Math structure: 40 items, three sections (I = 20 one-mark items,
II = 16 two/three-mark items, III = 4 four-mark items), across four strands
(Number, Geometry, Measurement, Statistics — Number is the largest).

### Diagnostic design decisions

- **Format:** Adaptive but sparse — ~30 items inferring the rest via a
  prerequisite graph, not testing all 90 modules.
- **Source:** Anchor questions adapted directly from real past papers
  (SEA Math 2019/2020, ELA 2024/2025, Creative Writing 2024/2025).
- **Input style:** All multiple choice (gentle on a young child,
  auto-gradable, no typing). NOTE: real SEA Math is fill-in-the-answer;
  MC is a deliberate divergence for the diagnostic only. Reintroduce
  fill-in later in practice mode for exam readiness.
- **Comprehension:** Converted to multiple choice for the diagnostic.
- **Writing:** Cannot be tree-walked. Handled as a short writing sample
  scored by Groq against Content / Language Use / Grammar & Mechanics /
  Organisation — produces a rubric profile, not a mastered/not status.
- **Anchor allocation (~30 items, by 50/30/20 weight):**
  ~15 Math (spread across the four strands, Number-heavy),
  ~9 ELA Section I (spelling/punctuation/grammar error-detection),
  a few comprehension MC items, plus one Writing sample.
- **Guessing guard:** Because MC allows a 1-in-4 guess, propagation is
  conservative — mark a module mastered only when an anchor is correct AND
  not contradicted by a harder item. Distractors encode real misconceptions
  so a wrong answer is itself diagnostic.

### Onboarding flow (three phases)

1. **Guardian setup** (not student-facing): guardian creates/verifies the
   account, enters child name + target SEA year + optional known weak areas.
2. **Diagnostic** (child-facing): warm framing ("a quick adventure to find
   your starting point", never "test"), adaptive MC quiz, encouraging
   interstitials, plus a short writing sample.
3. **Reveal:** diagnostic results seed `student_progress` for all 90
   modules, compute the starting week on the adventure map, and set the
   first `weekly_target`. Student lands on a populated dashboard.

---

## 3. Existing schema (verified this session)

```
syllabus_modules: id, subject, topic, sea_section, sequence_order,
                  created_at, updated_at, pacing_week, description, resources
student_progress: id, student_id, module_id, status, score,
                  created_at, updated_at, previous_score
weekly_targets:   id, student_id, module_id, week_start_date,
                  is_completed, created_at, updated_at
users:            id, name, email, email_verified_at, password,
                  remember_token, created_at, updated_at, role, parent_id
```

`student_progress.status` observed values: `mastered`, `diagnostic_passed`,
`not_started`. The dashboard's mastered/in-review/upcoming buckets read
from this column.

---

## 4. The sliced build approach

Build in small, independently verifiable pieces. Each slice is committed and
confirmed working against the real app before the next begins. The part that
needs to see existing Blade/Livewire code (the screens) comes last.

| Slice | Contents | Depends on your code? |
|---|---|---|
| **1. Schema** | Subject remap + 4 new tables + onboarding column | No |
| **2. Graph + anchor bank** | Prerequisite links + diagnostic questions from past papers | No |
| **3. Diagnostic engine** | Adaptive scoring + propagation, with tests | No |
| **4. Screens** | Guardian setup, quiz, reveal | Yes (Blade/Livewire) |

---

## 5. Slice 1 — schema (READY TO APPLY)

Five migration files to drop into `database/migrations/`:

| File | Purpose |
|---|---|
| `2026_06_13_000001_remap_subjects_to_sea_2025.php` | Editing→ELA Sec I, Comprehension→ELA Sec II. Data-only, idempotent. |
| `2026_06_13_000002_create_module_prerequisites_table.php` | Dependency graph (module_id, prerequisite_module_id). |
| `2026_06_13_000003_create_anchor_questions_table.php` | Static MC diagnostic bank (prompt, options, correct_index, difficulty, strand). |
| `2026_06_13_000004_create_diagnostic_tables.php` | diagnostic_sessions + diagnostic_responses. |
| `2026_06_13_000005_add_onboarding_completed_at_to_users.php` | Routing flag for fresh students. |

### Apply

```bash
php artisan migrate
```

### Verify the remap

```bash
php artisan tinker
```
```php
App\Models\SyllabusModule::selectRaw('subject, sea_section, count(*) as n')
  ->groupBy('subject','sea_section')->get()
  ->each(fn($r)=>print($r->subject.' '.$r->sea_section.': '.$r->n.PHP_EOL));
```

Expect only `Math` and `ELA` (no more English Editing/Comprehension).
Record the per-strand counts — they size the Slice 2 anchor bank.

### New tables summary

- **module_prerequisites:** `module_id`, `prerequisite_module_id`, unique pair.
- **anchor_questions:** `module_id`, `subject`, `strand`, `difficulty`,
  `prompt`, `options` (json), `correct_index`, `distractor_notes` (json),
  `active`.
- **diagnostic_sessions:** `student_id`, `status`, `items_answered`,
  `writing_sample` (json), `completed_at`.
- **diagnostic_responses:** `diagnostic_session_id`, `anchor_question_id`,
  `chosen_index`, `is_correct`.
- **users.onboarding_completed_at:** nullable timestamp.

---

## 6. Slice 2 — graph + anchor bank (NEXT)

- Add `prerequisites()` / `dependents()` relationships to the
  `SyllabusModule` model.
- Seed the dependency graph (obvious Math chains: place value → addition →
  multiplication → multi-step; fractions chain; etc. Clear slots left for
  manual extension).
- Seed a starter anchor bank adapted from the past papers, e.g.:
  - Math Number: place value (Q1 2019), prime (Q2 2019), improper→mixed
    (Q3 2019), expanded notation (Q3 2020), money totals, percent.
  - Math Measurement/Geometry/Statistics: ruler reading, area on grid,
    volume of cuboid, symmetry, mean/mode.
  - ELA Section I: spelling error-detection, punctuation, grammar lines
    (from ELA 2024/2025 Tasks 1–3).
  - Comprehension: a short passage with literal/inferential MC items.
- Distractors written to encode specific misconceptions.

Blocked on: Slice 1 verified + per-strand module counts.

## 7. Slice 3 — diagnostic engine

Standalone service (`DiagnosticService`) with unit tests:
- Picks anchors via tree-walk (start mid-difficulty per strand; correct →
  climb + mark prerequisites inferred; wrong → descend).
- Conservative propagation (guards against MC guessing).
- Writes `student_progress` statuses + first `weekly_target` on completion.
- Writing path scored separately via Groq (`ExamAgentService`).

## 8. Slice 4 — screens

Guardian setup, child-facing adaptive quiz, animated reveal. Requires the
existing dashboard Blade/Livewire components to match conventions.

---

## 9. Open items / notes

- [ ] Apply Slice 1 and verify the remap (Section 5).
- [ ] Provide per-strand module counts for Slice 2 sizing.
- [ ] Writing modules: the `Writing` subject has no modules yet — author or
      seed them before the Writing diagnostic path is meaningful.
- [ ] Decide whether the diagnostic is re-takable (schema supports multiple
      `diagnostic_sessions` per student already).
- [ ] Production DB is wiped on each redeploy (see deployment handoff) — apply
      a backup strategy before real students onboard, or onboarding data is lost.
- [ ] `migrate:fresh` locally will wipe any accumulated test progress data.

---

## 10. Reference files

- Past papers used: SEA Math 2019/2020, SEA ELA 2024/2025, SEA CW 2024/2025.
- Prior handoffs: `11JUN26_FORMYNIECES_DEPLOYMENT_HANDOFF.md`,
  `09JUN26_FORMYNIECES_DASHBOARD_HANDOFF.md`,
  `08JUN26_FORMY_NIECES_HANDOFF.md`.
