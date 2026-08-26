# Lesson Development Guide (SmoothSeas)

The full reference for building **immersive, interactive, AI-evaluated** SmoothSeas lessons that teach a concept deeply, let the child *do* it, and evaluate **thinking and principle-recall — not just final answers**. Every lesson is mapped to SEA syllabus objectives and covers all three cognitive dimensions (Knowing, Applying, Reasoning).

Two companion docs:
- **`.claude/skills/lesson-authoring/SKILL.md`** — the enforcing operating rules (read first when authoring).
- **`formynieces-spec/lesson_authoring_guide.md`** — the block-field mechanics + the AI re-teach flow (the JSON reference).

This document is the *how to make a lesson good* standard.

---

## The five non-negotiables

A lesson is not finished unless it has all five.

1. **COHERENCE** — one skill per lesson; every block, widget and practice item teaches *that* skill (keeps the AI re-teach on target — see the authoring guide).
2. **IMMERSION** — one concrete T&T scenario, Smooth's warm first-mate voice, *do before read*, low reading load.
3. **INTERACTIVITY** — at least **half** the lesson is the child manipulating something (a widget, a drag, a tap, a typed answer), not tapping multiple choice. The widget must *be* the concept.
4. **KAR — all three dimensions** — every lesson has ≥1 **Knowing**, ≥1 **Applying**, ≥1 **Reasoning** block, each interactive block tagged. Overall aim for the exam's balance (Knowing ≈ 45%, Applying ≈ 35%, Reasoning ≈ 20%).
5. **AI EVALUATION OF THINKING** — Reasoning blocks are graded by Smooth against an **authored, verified canonical solution** on **method, reasoning, and principle** — never just the final number — and the child **states the principle in her own words**.

> Not in the lesson: **difficulty or pacing.** Every lesson is thorough; difficulty already lives in the D1/D3/D5 practice ladder and the mastery loop. If a time signal is needed, it is **system metadata on the module** (used by the daily-plan composer / shown as a soft "≈ X min"), never a label that shapes or truncates lesson content.

---

## 1. Shape of a lesson (immersive gradual release)

1. **Hook (immersion)** — 1–2 sentences dropping her into a real scene with a need.
2. **The principle (`key`)** — the one rule, in kid words, *named* so it can be recalled later (this is the sentence she will say back).
3. **Show it (`example`, worked)** — one problem solved step-by-step; for procedures, add a **faded** example (one step blanked).
4. **Do it (widget, Knowing → Applying)** — she manipulates the concept. No pure reading here.
5. **Stretch it (Reasoning)** — a multi-step / "why" task where she **shows working / justifies**; Smooth evaluates the thinking (§5).
6. **Recall the principle** — she states the rule in her own words (AI-judged; accepted after a few tries so she's never stuck).
7. **Wrap** — a warm line tying scene to skill; bridge to practice.

**6–10 blocks. ≥ half interactive. All three KAR dimensions present.**

## 2. Immersion

A world, not a worksheet (one T&T scenario throughout); *do before read*; Smooth's voice ("not yet", never "wrong"); short lines, one idea at a time; a scene question the skill answers, resolved at the end.

## 3. Cognitive dimensions (KAR)

| Dimension | Asks | Author it as |
|---|---|---|
| **Knowing (K)** | recall a fact/unit/rule; recognise/represent | a `key`; a recognition tap; "which is…" check |
| **Applying (A)** | use the rule, one straightforward step | a widget she operates; a single-step check/fillblank |
| **Reasoning (R)** | multi-step, non-routine, justify, compare, "why" | constructed response + drag-order/plan-it; AI-evaluated (§5) |

Reasoning is **required**, not optional — it is the exam's and the platform's weakest area.

## 4. Widget palette — pick the one that *is* the concept

| Concept | Widget(s) |
|---|---|
| fractions / decimals / part-whole | fraction bar, number line |
| number / rounding / place value | number line, place-value builder |
| area / perimeter | tile-the-grid, resizable rectangle |
| volume | stack-the-cubes cuboid |
| time | draggable clock, elapsed-time |
| mass / capacity | balance scale, graduated jug |
| angles | turn-the-ray (turns + right angle) |
| symmetry | complete-the-mirror, count-the-lines |
| solids | inspect faces/edges/vertices |
| data | readable bar chart / pictograph |
| multi-step procedure | drag-to-order the solution (+ a decoy) |
| reasoning / show working | constructed response + AI feedback |
| approach before solving | optional "Plan it" with Smooth |
| grammar / vocab in context | meaning-from-context, cloze, tag-parts-of-speech, fix-in-context |
| reading | passage / poem / poster + literal→inferential→evaluation items |
| principle / rule | justify-the-step + state-the-rule |

Never add a widget for variety alone; a mismatched interaction is incoherent.

## 5. The AI-evaluation contract

For any graded block (Reasoning; the re-teach; composition), the author supplies a fixed spec so Smooth **coaches against verified truth and never invents the maths**:

```json
{
  "type": "reasoning",
  "prompt": "Aunty has 24 m of fence. Compare a 6×6 and an 8×4 garden — which gives more grass, and how do you know?",
  "cognition": "reasoning",
  "principle": "Area is length × width; shapes with the same perimeter can have different areas.",
  "canonical_solution": ["6×6 = 36 m²", "8×4 = 32 m²", "36 > 32, so the 6×6 (square) gives more grass"],
  "rubric": [
    {"dimension": "method",    "look_for": "computes BOTH areas (36 and 32)"},
    {"dimension": "reasoning", "look_for": "compares the areas and concludes the square is larger"},
    {"dimension": "principle", "look_for": "notes the perimeter is the same but area differs"}
  ],
  "misconceptions": [
    {"if": "uses perimeter as area", "coach": "That's the fence (perimeter). Area is the grass inside — length × width."},
    {"if": "only one area computed", "coach": "Work out BOTH gardens before you compare."}
  ],
  "sample_answers": {"strong": "…", "partial": "…"},
  "canonical_examples_source": "<bank id / URL the solution was verified against>"
}
```

Evaluator rules the author writes to:
- **Grade the thinking, not just the number.** Score each rubric dimension present/absent with targeted per-dimension feedback. Correct answer + no working = *partial*.
- **Ground on `canonical_solution`.** The LLM checks the child's work against the authored steps; never generates new maths or accepts a wrong result.
- **Name the misconception** from the list when the error matches.
- **Principle recall** is judged separately: she restates the rule; the LLM judges "captures the main idea" (accept after a few tries — never stuck).
- **Kind and specific**; **degrade gracefully** to `sample_answers` + rubric keywords if the LLM/budget is unavailable.

Every interactive block also carries the re-teach fields (`rule` + ≥4 same-rule `practiceItems`) from the authoring guide.

## 6. Objective mapping (drives the badge + Syllabus page)

Each lesson declares as data:
- `objectives_direct` — objective code(s) it teaches (e.g. `M-MEA-38`).
- `objectives_indirect` — codes it reinforces (e.g. `M-MEA-37`, `M-MEA-45`).
- per-block `cognition` — `knowing | applying | reasoning`.

This single source drives the in-lesson **🎯 objective badge** (hover → direct/indirect + KAR) and the read-only **Syllabus page** (coverage + progress by objective, deep-linking to the voyage stop). Reference codes, never free-hand objective text.

## 7. Ground truth & just-in-time sourcing

**Every gradeable answer must be verified against a trusted source.** The `canonical_solution` is the anti-hallucination anchor; it must never be guessed.

### The bank
Local reference bank: **`/root/dev/sea-ground-truth-bank/`** — grep `MANIFEST.json` by `tier`, `level` (std4/std5), `subject`, `license`, `status`; see `DOWNLOADED.md` for the file list. It holds official exams with answers (UK KS2 — OGL, NAPLAN, DBE, KNEC, SEA specimen + framework) and open curricula (NCERT textbooks; Illustrative Math / Siyavula / CK-12 / Eureka referenced for per-topic pull).

### Approved sources (in priority order)
1. **Official exams** — MOE SEA (the target), UK KS2 (Open Government Licence), NAPLAN, DBE, KNEC — for format authenticity + verified answers.
2. **Open-licensed curricula** — Illustrative Mathematics (CC BY), Siyavula (CC BY), CK-12, Eureka/EngageNY, NCERT (govt) — for teaching sequences + worked solutions.
3. **Public-domain / CC reading** — Project Gutenberg, African Storybook (CC BY) — for ELA passages/poetry.

### PROHIBITED sources
- **Copyrighted competitor / tutor solutions** (paid or free-to-view tutor sites, e.g. individual SEA tutors' worked-solution PDFs) — do **not** ingest, bank, or reproduce. Free-to-view ≠ free-to-reuse, and these are competitors' IP.
- **Pirated textbooks/workbooks** (e.g. Scribd/pdfcoffee uploads of commercial Singapore-Math books). Use only if you hold a licence.

### Just-in-time procedure (build this flexibility in)
When authoring a module, **before** writing its `canonical_solution`:
1. **Check the bank** for a verified worked source matching the objective + level.
2. **If coverage is missing**, fetch additional ground truth **on demand from approved sources only** (§ Approved sources). Prefer the open-licensed curricula for worked solutions; official papers for format.
3. **Verify independently** — recompute the answer yourself and cross-check against ≥1 source; for ELA, confirm the passage is public-domain/CC.
4. **Add it to the bank** with metadata (tier, level, subject, license, provider, url/path) in `MANIFEST.json`, and record the reference in the lesson's `canonical_examples_source`.
5. **Only then author** the `canonical_solution` from the verified source.
6. **If no clean source can establish the answer**, do **not** ship an unverified Reasoning item — **flag the module for human review** instead.

> Note: the box has no `pdftotext`/OCR; bank PDFs are stored as-is. Extract/verify content manually or add poppler if bulk extraction is needed.

## 8. Per-strand recipes (the "per topic" layer)

Rather than a skill per topic, apply the strand recipe:
- **Number / Operations** — place-value/number-line widget; drag-order for multi-step; Reasoning: estimation + "is this sensible?"
- **Fractions / Decimals / Percent** — bar/number-line first (concrete→pictorial→abstract); Reasoning: multi-step word problem with working.
- **Measurement / Geometry** — the matching manipulative *is* the lesson; Reasoning: real-life/compound problem + "why the formula works".
- **Statistics** — readable graph widget; Reasoning: "what does the data tell us / what would you decide?"
- **Patterns / Algebra** — pattern widget; Reasoning-dominant: describe the rule, one unknown.
- **Grammar / Spelling / Punctuation** — always *in context* (tag-parts-of-speech, fix-in-context, cloze); Reasoning: "why is this correct?" (state the rule).
- **Vocabulary** — meaning-from-context with clue reveal; Reasoning: justify the meaning from clues.
- **Reading / Poetry / Graphic text** — passage/poem/poster + literal→inferential→evaluation ladder; evaluation items are constructed + AI-graded.
- **Writing** — plan → draft an opening → AI feedback on Content / Language / Grammar / Organisation; scaffold that fades.

## 9. Per-module data stub (thin, just-in-time — no per-module prose)

Fill once per module, as the first step of authoring it:

```json
{
  "module": "MATH-038",
  "objectives_direct": ["M-MEA-38"],
  "objectives_indirect": ["M-MEA-37", "M-MEA-40", "M-MEA-45"],
  "scenario": "tiling Aunty's kitchen floor",
  "widgets": ["tile-grid", "resizable-rectangle"],
  "kar_targets": {"knowing": 1, "applying": 2, "reasoning": 1},
  "misconceptions": ["uses perimeter as area", "forgets square units"],
  "canonical_examples_source": "bank: uk-ks2-2024 maths; im-grade-5 area"
}
```

## 10. Pre-publish checklist (run every lesson)

- [ ] One skill; every block/widget/item is about it (coherence).
- [ ] Opens in a concrete scenario; Smooth's voice; do-before-read; low reading load (immersion).
- [ ] ≥ half the blocks are manipulable widgets; the widget fits the concept (interactivity).
- [ ] ≥1 **Knowing**, ≥1 **Applying**, ≥1 **Reasoning** block; each interactive block tagged `cognition`.
- [ ] Each Reasoning/graded block has `principle`, `canonical_solution`, `rubric` (method/reasoning/principle), `misconceptions`, `sample_answers`, `canonical_examples_source`.
- [ ] A **state-the-principle** step is present and AI-judged.
- [ ] Every interactive block has `rule` + ≥4 same-rule `practiceItems`.
- [ ] `objectives_direct` + `objectives_indirect` set by code; no free-hand objective text.
- [ ] **Every answer verified against the bank/approved source**; provenance recorded; no prohibited (competitor/pirated) sources used.
- [ ] No difficulty/pacing baked into content.
- [ ] Language kind and age-appropriate; no `Topic:/Difficulty:` leakage.
- [ ] Practice bank has ≥15 questions at each of D1/D3/D5 for the module.
- [ ] **Walked in the admin Lesson preview** (LessonResource → *Preview* / *Re-teach*, LE-11): interactions gate, the re-teach flow reads right, nothing recorded — the ongoing verification path.
