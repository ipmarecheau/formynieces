# Diagnostic Engine — Build Plan (Scoping)

**Purpose of this document.** Turn the 17 scenarios in `diagnostic.feature` into a
concrete build plan: what gets built, in what order, the decisions you need to make,
and where the genuinely hard parts are. No engine code is written yet — this is the
sketch before the bricks.

---

## 1. What already exists (the head start)

You do **not** need new tables. The schema is in place:

- **`diagnostic_sessions`** — one per attempt. Has `student_id`, `status`
  (default `in_progress`), `item_plan` (text — the planned anchor sequence),
  `current_item` (an integer cursor), `writing_sample` (text, legacy — unused now
  that Writing is MCQ), `completed_at`.
- **`diagnostic_responses`** — one per answered anchor. Has `diagnostic_session_id`,
  `anchor_question_id`, `chosen_index`, `is_correct`, `misconception` (the label
  from the distractor she picked).
- **`student_progress`** — the OUTPUT. One row per (student, module) with a `status`
  and `score`. This is what the engine ultimately writes — the mastery map that the
  adventure map reads from. Unique on (student_id, module_id), default `not_started`.

So the engine is **logic, not schema**. It reads anchors + the prerequisite graph,
drives a session through the two session tables, and writes the mastery map into
`student_progress`.

One cleanup note: `diagnostic_sessions.writing_sample` is a leftover from the
old free-text-sample design. Writing is now MCQ. We leave the column (harmless,
nullable) but the engine never writes it. Flag for a future migration to drop.

---

## 2. The pieces to build (six components)

Listed in dependency order — each builds on the one before.

### 2a. Session planner — "what questions, in what order?"
Builds the `item_plan` when a session starts. Per the spec's allocation scenario:
~15 Math anchors (Number heaviest), an even ELA Section I / Section II split, and a
small Writing set. The planner picks anchors from the bank to fill those slots,
starting each strand at **medium** difficulty (the climb/descend ladder needs a
middle starting rung).

**Output:** `item_plan` stored as JSON — an ordered list of anchor ids (or strand+
difficulty slots resolved to anchor ids as the walk proceeds; see decision D2).

### 2b. Item walk — "given her last answer, what next?"
The adaptive loop. On a correct answer, the next anchor in that strand steps **harder**;
on wrong, **easier**. Records each answer into `diagnostic_responses` (including the
chosen distractor's misconception). Advances `current_item`.

This is where scenarios "climbs the difficulty ladder" / "descends" live.

### 2c. Mastery inference — "what does this answer tell us about other modules?"
The conservative propagation engine. When an anchor for module B is answered
correctly, mark B mastered, then walk the prerequisite graph downward marking B's
prerequisites **inferred-mastered** — EXCEPT never propagating through a Writing
node (69–72). This is the heart of the system and the part the prerequisite graph
was built for.

Scenarios: "infers direct prerequisites", "transitive along a chain", "unambiguous
evidence", plus all three Writing-node scenarios.

### 2d. Conservative walk-back — "un-mark on contradiction"
When a HARDER anchor in a chain is answered **wrong** after an easier one inferred
some modules mastered, un-mark the inferred modules between the two. Bounded to the
contradicted chain (don't touch unrelated strands).

Scenarios: "un-marks a previously inferred module", "walk-back is bounded".

### 2e. Session lifecycle — "start, resume, finish"
Starting a session (only if onboarding complete), resuming an interrupted one
(status stays `in_progress`, responses preserved), and completing it (write final
`student_progress`, set `status=completed`, `completed_at`). Also the every-8th-item
encouragement interstitial.

Scenarios: "interrupted session resumes", "interstitial every eighth item",
"guardian initiates retake" (@v1.1 — defer).

### 2f. Presentation layer — "the warm, test-free UI"
The Livewire/Filament screens: adventure-framed intro, question screen with progress
dots and NO score/timer, interstitials. Reads from the engine; writes answers back.

Scenarios: "intro uses adventure framing", "question screen hides scoring".

---

## 3. Open decisions (your input needed before / during build)

These are the things `diagnostic.feature` leaves open. My recommendation given in each,
but they're yours to set.

**D1 — Session length / stopping rule.** The spec says "roughly 30 anchor items."
Fixed count, or stop early once mastery is confident? *Recommendation: fixed plan of
~30 for v1 (simpler, predictable for a child); adaptive stopping is a v1.1 refinement.*

**D2 — Plan-ahead vs decide-on-the-fly.** Does the planner fix all ~30 anchor ids up
front, or fix the strand/difficulty slots and pick the actual anchor when the walk
reaches each slot (so difficulty can adapt)? *Recommendation: hybrid — fix the strand
sequence and slot count up front, resolve each slot to a concrete anchor at walk time
based on her current difficulty in that strand. This is what makes climb/descend real.*

**D3 — "Unambiguous evidence" definition.** The spec has a scenario that a lucky guess
shouldn't propagate, but never defines "lucky." Options: (a) drop the notion — any
correct answer propagates (simplest); (b) require the anchor be at/above medium
difficulty to propagate; (c) capture response-time/confidence signals (most complex,
needs UI support). *Recommendation: (b) for v1 — only correct answers on medium+
anchors propagate. Mark the response-time scenario @v1.1. This needs your sign-off
because it changes the spec.*

**D4 — Difficulty ladder mechanics.** Three levels (easy/medium/hard) — when she's at
hard and gets it right, what then? Stay at hard / move to next strand / harder module?
*Recommendation: cap at hard; a correct hard answer ends that strand's walk and banks
the strongest inference.*

**D5 — Score semantics in `student_progress.score`.** It's a nullable int. What does it
mean — % correct, a mastery confidence 0–100, a difficulty level reached?
*Recommendation: store the highest difficulty level mastered (1–3) for diagnostic
purposes; the learning-loop later overwrites with its own score. Needs your call since
it affects how the adventure map renders.*

**D6 — Mastery status vocabulary.** `student_progress.status` is a free string. Define
the set the engine writes, e.g. `not_started`, `mastered`, `inferred_mastered`,
`needs_work`. *Recommendation: those four. "inferred" kept distinct from directly-tested
so the walk-back knows what it may un-mark and the UI can show confidence.*

---

## 4. Hard parts (where care is needed)

- **Propagation + walk-back interaction (2c + 2d).** The order of operations matters:
  a session answers many items; inference and walk-back must produce the same final
  state regardless of incidental ordering, or be explicitly order-defined. *Plan: run
  inference incrementally but recompute the final mastery map from ALL responses at
  session completion — a deterministic re-derivation, so order can't corrupt it.*
- **The Writing-node firewall.** Easy to get a propagation that "leaks" through 69–72.
  The graph queries must exclude writing nodes as pass-through. Already encoded as
  test scenarios — build to those.
- **Resume correctness.** Re-entering mid-session must not double-count responses or
  replan. Keyed off `status=in_progress` + `current_item`.
- **Determinism for tests.** The id-bound scenarios (module 23→{22,15}, 27→chain, etc.)
  require the planner to be seedable/deterministic in tests so the same anchor is
  presented. *Plan: accept an optional fixed seed / forced item plan in the engine's
  entry point for tests.*

---

## 5. Suggested build order

1. **Mastery inference (2c)** first, as a pure service with no UI — it's the core, and
   the id-bound `diagnostic.feature` scenarios can drive it immediately against the
   real graph. Highest value, most testable.
2. **Walk-back (2d)** — extends 2c; same test style.
3. **Session planner (2a)** — produces item plans; test the allocation scenario.
4. **Item walk (2b)** — ties planner + inference together; climb/descend scenarios.
5. **Lifecycle (2e)** — start/resume/complete; interstitial.
6. **Presentation (2f)** — the warm UI last, once the engine underneath is green.

Each step turns a named group of `diagnostic.feature` scenarios green. By the end,
all 17 (minus the two @v1.1) pass, and a girl can take a real diagnostic that lights
up her adventure map.

---

## 6. What I need from you to start

Minimum to begin building step 1 (inference):
- **D3** (what counts as evidence to propagate) — affects the core logic directly.
- **D6** (status vocabulary) — the strings the engine writes.

D1, D2, D4, D5 can be decided when we reach their step. D3 changes the spec, so it
needs a deliberate yes.
