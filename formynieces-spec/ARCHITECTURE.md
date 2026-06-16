# ForMyNieces — System Architecture

**Last updated:** 16 June 2026
**Scope:** SEA exam-prep platform for primary students in Trinidad & Tobago.
This document describes the architecture as built through the diagnostic engine
(Slice 2 complete; diagnostic UI and learning loop pending).

---

## 1. Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 13.14 (PHP 8.3) |
| Admin / UI | Filament 4.11, Livewire 3.8, Alpine.js 3 |
| Auth scaffold | Laravel Breeze 2.4 (customised) |
| Testing | Pest 4.7 / PHPUnit 12 |
| Dev tooling | Laravel Boost 2.4 (MCP), Pail, Pint |
| AI features | Groq API (llama-3.3-70b-versatile) — for ExamAgent, not the diagnostic engine |
| Database | SQLite (dev + prod on Linode VPS) |
| Deploy | Docker on Linode VPS, port 8080, container `formynieces`, app root `/opt/formynieces` |

The diagnostic engine is **pure PHP domain logic** — no AI, no external calls.
Groq is used elsewhere (ExamAgent insights), deliberately kept out of the
scoring/inference path so diagnosis is deterministic and testable.

---

## 2. Domain model (the SEA curriculum as data)

### Modules (`syllabus_modules`) — 90 rows
The SEA 2025–2028 framework, decomposed into 90 teachable modules:
- **Math:** ids 1–51 (Number, Operations, Patterns, Fractions, Decimals,
  Percent, Problem Solving, Geometry, Measurement, Statistics).
- **ELA:** ids 52–90 — Section I mechanics (52–68: Spelling, Punctuation,
  Capitalisation, Grammar) and Section II comprehension (73–90: Comprehension,
  Poetry, Media).
- **Writing:** ids 69–72 (Narrative, Expository, Figurative Language,
  Organisation) — a subset of the ELA id range but treated as a separate
  diagnostic track (see §4 Writing firewall).

Key columns: `subject` (CHECK: 'Math' | 'ELA'), `sea_section`, `topic`,
`pacing_week`, `description`, `resources`.

### Prerequisite graph (`module_prerequisites`) — 150 edges
A directed acyclic graph. Each row is an edge **"B requires A"**
(`module_id` = B, `prerequisite_module_id` = A): mastering B implies mastering A.
- 86 Math edges, 64 ELA edges.
- Verified acyclic, no self-loops, no duplicates, all ids valid.
- DENSE by design — the density lives in the graph; the CAUTION lives in the
  inference engine (conservative propagation).
- Source of truth: `database/data/` is the YAML for anchors; the edge set is
  encoded directly in `ModulePrerequisiteSeeder`.

### Anchor questions (`anchor_questions`) — 120 rows
Multiple-choice diagnostic items. 65 Math, 43 ELA, 12 Writing.
- Each links to one module via `anchor_question_module` (many-to-many pivot,
  cascade FK).
- Columns: `subject`, `sea_section`, `strand`, `difficulty` (int 1–3),
  `prompt`, `options` (JSON array of 4), `correct_index` (0–3),
  `distractor_notes` (JSON: `misconceptions` map + `meta` provenance), `is_active`.
- Every distractor encodes a named misconception (drives future reteaching).
- All items authored fresh, curriculum-aligned, CC-BY-NC-4.0 — copied from no
  past paper. Provenance carried per-item in `distractor_notes.meta`.
- Coverage guarantee: every module is tested ≥3× (direct + indirect via the
  prerequisite graph). Bank is large; the adaptive walk shows only ~30 per child.

### Student progress (`student_progress`) — the mastery map (output)
One row per (student, module). The diagnostic's product, read by the (future)
adventure map and learning loop.
- `status` (CHECK, widened 16 Jun): `not_started` | `needs_work` |
  `inferred_mastered` | `mastered` | `diagnostic_passed` (legacy).
- `score` (int, nullable): for a directly-mastered module, the highest
  difficulty rung demonstrated (1–3); null for inferred/needs_work.
- `previous_score`: prior attempt's score, preserved on retake.

### Sessions & responses
- `diagnostic_sessions`: one per attempt. `student_id`, `status`
  (`in_progress`|`completed`), `item_plan` (JSON slot list), `current_item`
  (cursor), `completed_at`. (`writing_sample` column is DEAD — legacy from the
  abandoned free-text design; pending a drop migration.)
- `diagnostic_responses`: one per answered anchor. `chosen_index`, `is_correct`,
  `misconception` (the label from the chosen distractor).

---

## 3. The diagnostic engine (Slice 2)

Four pure-logic services in `app/Services/Diagnostic/`. Each is independently
unit-tested against the real seeded graph.

```
                 ┌─────────────────────┐
   start ───────▶│  SessionLifecycle   │  start / resume / complete
                 │  (orchestrator)     │
                 └──────────┬──────────┘
                            │ builds plan via
                 ┌──────────▼──────────┐
                 │   SessionPlanner    │  ~31 slots: 15 Math / 12 ELA / 4 Writing
                 │  (what to ask)      │  slot = {subject, strand, difficulty}
                 └──────────┬──────────┘
                            │ slots resolved at walk time
                 ┌──────────▼──────────┐
                 │      ItemWalk       │  present → record → adapt difficulty
                 │  (the adaptive loop)│  climb on correct, descend on wrong
                 └──────────┬──────────┘
                            │ responses feed
                 ┌──────────▼──────────┐
                 │  MasteryInference   │  conservative propagation over the graph
                 │  (what it means)    │  → mastery map written to student_progress
                 └─────────────────────┘
```

### MasteryInference — the core
Pure function: `(responses, graph) → mastery map`. No DB writes, no session
state, order-independent (recomputed wholesale, so answer order can't corrupt).
- **D3 (evidence rule):** only a correct answer on a MEDIUM+ anchor (difficulty
  ≥ 2) propagates inferred mastery to prerequisites. Easy-correct masters its
  own module only.
- **D6 (statuses):** mastered (direct+correct), inferred_mastered (implied),
  needs_work (direct+wrong), not_started (untouched).
- **Walk-back:** a wrong answer on a harder anchor un-marks *inferred* mastery
  on the modules it requires — but never a directly-earned fact. Conservative:
  contradicting evidence removes the guess, not the proof.
- **Writing firewall:** propagation never passes through a writing node (69–72)
  in either direction. Writing modules are mastered only by their own anchors.

### SessionPlanner — what to ask
Builds an ordered list of slots (allocation: 15 Math with Number heaviest, 12
ELA split evenly Section I / II, 4 Writing). Every slot starts at medium.
Slots are resolved to concrete anchors at WALK time (decision D2: fixed
sequence, late difficulty binding) via `resolveSlot()`, which uses
nearest-available difficulty (e.g. Percent has only a hard anchor) and excludes
already-used anchors.

### ItemWalk — the adaptive loop
Resolves the current slot, presents it, records the answer (with the chosen
distractor's misconception), steps the strand's difficulty (climb/descend,
capped 1–3), advances the cursor. **Per-strand difficulty is DERIVED from
response history**, not stored separately — so resume is automatic and state
can't desync. Fires an encouragement interstitial every 8th item. Returns null
when the plan is exhausted (ready to complete).

### SessionLifecycle — orchestration
- **start:** gated on `onboarding_completed_at`; creates + plans a session.
- **resume:** returns the existing in_progress session (no duplicate).
- **complete:** runs all responses through MasteryInference, writes the map to
  `student_progress` (transaction), stamps the session completed. Idempotent;
  preserves `previous_score` correctly across genuine retakes vs re-completion.

---

## 4. Key architectural decisions & rationale

| # | Decision | Why |
|---|---|---|
| Dense graph, cautious engine | 150 edges, but conservative propagation | Density gives rich inference coverage; caution prevents a false "mastered" that makes a child skip needed practice. Under-inference is safe; over-inference harms. |
| Writing as separate MCQ track | Writing (69–72) tested by its own MCQs, firewalled from propagation | A free-text sample can't be auto-scored reliably; MCQs *about* writing (essay types, figurative language) are diagnosable. Firewall keeps writing edges from corrupting reading inference. |
| Inference as pure function | No DB/session coupling | Trivially testable, order-independent, re-derivable at completion. |
| Difficulty derived from responses | Not a stored counter | Single source of truth (the response log); resume works for free. |
| Provenance in JSON, not columns | `distractor_notes.meta` | Avoided a migration; all items share uniform provenance. Flagged for first-class columns if filtering is later needed. |
| Groq kept out of scoring | Engine is deterministic PHP | Diagnosis must be reproducible and testable; AI is reserved for advisory features (ExamAgent). |
| Synthetic student emails | `@students.formynieces.com`, never verified | Students are created by guardians, never self-register; durable architectural rule. |

---

## 5. BDD / testing strategy

Strict one-scenario-per-loop BDD with Pest. `diagnostic.feature` (Gherkin)
holds the behavioural spec — 6 rules, 17 scenarios. Structural invariants
(graph acyclicity, edge counts, coverage) live in Pest structural tests, NOT
Gherkin, because they're developer invariants without an actor.

The id-bound scenarios (e.g. module 23 → infers 22, 15) double as regression
guards on the graph itself: a broken edge or walk fails a named test.

Current suite: 86 passing tests covering schema, seeders, all four engine
services, and login routing. (6 failing tests are stale Breeze scaffolding —
RegistrationTest and ProfileTest — testing default behaviour the app has moved
past; not part of the diagnostic.)

---

## 6. What is NOT built yet

- **Diagnostic UI (2f):** the warm, test-free presentation layer (Livewire/
  Filament) — adventure intro, question screen with progress dots, no score/
  timer, interstitials. The engine underneath is complete and tested.
- **Learning loop:** the post-diagnostic teaching cycle that reads the mastery
  map and reteaches `needs_work` modules.
- **Adventure map:** the student-facing progress visualisation.
- **ExamAgent insights:** Groq-powered guardian-facing analysis.

---

## 7. Deployment notes

- Docker on Linode VPS, container `formynieces`, port 8080, app root
  `/opt/formynieces`.
- Deploy runs `php artisan migrate --force` then `db:seed --force` against
  `DatabaseSeeder`, which calls (in order): SyllabusModuleSeeder →
  ModulePrerequisiteSeeder → Math/Ela/Writing AnchorQuestionSeeder. Verified to
  rebuild a complete database (90 modules / 150 edges / 120 anchors) from zero.
- Laravel Boost MCP connected for live project context during development.
