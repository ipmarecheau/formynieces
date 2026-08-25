---
name: lesson-authoring
description: Author (or generate + vet) immersive, interactive, KAR-balanced SmoothSeas lessons AND their practice-question banks that work with the learning loop and the AI-assisted re-teach — teaching a concept deeply, letting the child DO it with widgets, and evaluating thinking + principle-recall (not just answers) against verified ground truth. Use whenever creating/editing a lesson JSON bundle, choosing widgets, adding KAR/objective tags or AI-evaluation fields, sourcing ground truth for a module, importing practice questions, or deciding how many questions a topic needs. Covers the five non-negotiables, the AI-evaluation contract, objective mapping, just-in-time ground truth, the block flow, the re-teach fields, and the per-level question minimum.
---

# Authoring SmoothSeas lessons + banks

A lesson and its practice bank are one deliverable per module. Get them wrong and the AI re-teach
tests the wrong thing. The companion reference `formynieces-spec/lesson_authoring_guide.md` has the
full block table + a complete example — read it for block mechanics. THIS skill is the operating
rules that keep a lesson coherent with the **learning loop** and the **re-teach**.

> **Full standard: `formynieces-spec/lesson_development_guide.md`** — the immersion / KAR / AI-evaluation /
> widget-palette / ground-truth detail with the per-strand recipes and the per-module stub. This SKILL
> is the enforcing summary; the guide is the reference.

## The five non-negotiables (every lesson)

1. **Coherence** — one skill; every block, widget and item is about it (see §0 below).
2. **Immersion** — one concrete T&T scenario, Smooth's voice, *do before read*, low reading load.
3. **Interactivity** — **≥ half** the lesson is a manipulable widget/drag/tap/typed answer, not MC; the widget must *be* the concept (fraction bar, tile-grid, clock, drag-to-order, tag-in-context, …).
4. **KAR — all three** — ≥1 **Knowing**, ≥1 **Applying**, ≥1 **Reasoning** block; tag each interactive block `cognition: knowing|applying|reasoning`. Reasoning is required, not optional.
5. **AI evaluation of thinking** — every Reasoning/graded block carries `principle` + verified `canonical_solution` + a `rubric` scoring **method / reasoning / principle** (right answer, no working = *partial*) + `misconceptions` + `sample_answers`; and the child **states the principle in her own words** (LLM-judged, accepted after a few tries).

**Not in a lesson: difficulty or pacing.** Every lesson is thorough; difficulty lives in the D1/D3/D5 ladder. Any time signal is *system metadata on the module*, never content.

## Objective mapping (data — drives the badge + Syllabus page)

Set `objectives_direct` and `objectives_indirect` by **code** (never free-hand text) plus per-block `cognition`. One source feeds the in-lesson 🎯 badge and the read-only Syllabus/progress page.

## Ground truth — verify every answer, just-in-time

**Never guess a `canonical_solution`.** Anchor it to a verified source:

1. **Check the bank** `/root/dev/sea-ground-truth-bank/` (grep `MANIFEST.json` by strand/level/subject).
2. **If a module lacks a verified source, fetch more ground truth ON DEMAND — approved sources only:**
   official exams (**SEA**, UK KS2 [OGL], NAPLAN, DBE, KNEC) for format + verified answers; open curricula
   (Illustrative Math CC-BY, Siyavula CC-BY, CK-12, Eureka, NCERT) for worked solutions; public-domain / CC
   (Gutenberg, African Storybook) for ELA passages.
3. **Verify independently** (recompute; cross-check ≥1 source), **add it to the bank with metadata**, and record `canonical_examples_source` in the lesson.
4. **PROHIBITED:** copyrighted competitor/tutor worked-solutions (free-to-view ≠ free-to-reuse) and pirated textbooks. If no clean source can establish the answer, **flag the module for human review — never ship an unverified Reasoning item.**

## 0. The one rule: COHERENCE (never break this)

**Every part of a lesson teaches ONE skill, and every interactive block's re-teach content follows
that block's own rule.** A module that bundles sub-rules (e.g. *"Plural Forms — y→i, f→v, -es"*) still
means: each block teaches one rule, and its `rule` + `practiceItems` use only that rule. The chat
remediates the *failed block's* rule — if a y→i block carried an -es word, Smooth would teach the
wrong thing. This is enforced structurally by `practiceItems`; you author them correctly.

## 1. Lesson shape (gradual release)

`title` (the exact skill, plain words) + ordered `blocks`. Arc: hook (`text`) → rule (`key`) → worked
example (`example`) → check (`check`/`fillblank`) → optional same-skill twist → interaction
(`markwords`/`matchpairs`/`ordersteps`) → wrap (`text`). **6–10 blocks, ≥2 interactive.** Interactive
types GATE: she must answer correctly to advance.

### Distinct problems — the example NEVER pre-answers a question (enforced)

Same *skill*, **different instance** every time. A worked `example` teaches with one problem; every
`check`, `fillblank`, and `practiceItem` in the SAME lesson must use a **different** problem — a
different number to operate on (or, for word lessons, a different word/answer). If the example expands
`526`, no check/fillblank/practiceItem may also be about `526` — the child would just copy the answer
she was shown. This is the single most common authoring slip; treat it as a non-negotiable.

The signature is the **headline number** the item operates on (numeric lessons) or its **normalized
answer** (word lessons). Two items that share a headline number *or* an answer within one lesson are a
repeat — pick fresh operands.

**Verify before you ship:** `php artisan lessons:verify {--file=path}` scans a bundle (or all of
`database/data/lessons/`) and fails on any within-lesson repeat, naming the colliding blocks. The
`LessonImporter` runs the same guard, so an offending bundle is rejected at import — a repeat cannot
reach a learner. Run `lessons:verify` as the last step of authoring; a clean run is required.

## 2. Re-teach fields on interactive blocks (REQUIRED) — `rule` + `practiceItems`

Every interactive block (`check`, `fillblank`, `markwords`, `matchpairs`, `ordersteps`) MUST carry:

```json
{
  "type": "check",
  "question": "What is the plural of 'city'?",
  "options": ["citys", "cities", "cityes"],
  "answer": 1,
  "explain": "t is a consonant, so change y to i and add es: cities.",
  "rule": "If a word ends in a consonant then y, change the y to i and add es.",
  "practiceItems": [
    { "prompt": "the plural of 'baby'",  "answer": "babies" },
    { "prompt": "the plural of 'lady'",  "answer": "ladies" },
    { "prompt": "the plural of 'penny'", "answer": "pennies" },
    { "prompt": "the plural of 'story'", "answer": "stories" }
  ]
}
```

- `rule` — one kid-friendly sentence. Smooth **explains** it and asks the child to **say it back**.
- `practiceItems` — same-rule word→answer pairs, matched case-insensitively when typed.
- **Author ≥4 per interactive block.** The re-teach can spend them across three remediation cycles,
  the end-of-lesson review, AND the proof, all of which draw from these (never module-bank content).
  Every item MUST follow that block's rule. `prompt` reads naturally after "Type …" ("the plural of 'baby'").

## 3. How the lesson drives the re-teach (author for this flow)

When practice pulls her back into a re-teach she re-walks THIS lesson. On an interactive block:

1. **Two tries** in the lesson; a second miss opens Smooth's chat (a gentle hand-off splash first).
2. Smooth tests a `practiceItems` word (type-the-answer). Miss → **explains `rule`** → she **says the
   rule back** (LLM judges "close enough"; accepts after a few tries so she's never stuck).
3. Lesson re-asks the block. Wrong again → reveals the answer + tries the NEXT `practiceItems` word.
4. **Three cycles** → the lesson is left **"in progress"** (she moves on; it returns daily — Phase 2
   adds parent notification + worksheet).
5. End of lesson → a short **review** of `practiceItems` (guided type-the-answer, never gives the answer)
   → the **proof**: type ~3 more `practiceItems` words; 3 correct resumes solo practice at D3.

Implication for you: a lesson with weak/absent `practiceItems` breaks the re-teach. Rich, same-rule
`practiceItems` are the single most important thing you author.

## 4. Practice question bank — fields + the 15-per-level minimum

Practice is separate MC content (`practice_questions`), by module + difficulty (D1/D3/D5). The loop
**never repeats a question** (content-hash no-repeat, LL-18), so the pool must be deep.

**Minimum: 15 questions per topic PER difficulty level (D1, D3, D5) — 45+ per topic.** 15 is the hard
floor, 20+ is ideal. Why: no-repeat consumes each question once; a level needs enough to reach the
mastery streak, survive a re-teach detour, and — critically — **not dead-end**. When a level's fresh
pool runs out, the loop advances her to the next level with content (routing her through a re-teach
first if her accuracy at that level was poor); too few questions makes that happen far too early.

**Import fields:** `module` (code, e.g. `ELA-001`), `subject`, `difficulty` (1/3/5), `prompt`,
`options`, `correct_index` (0-based), `explanation`, optional `hint`. Questions need **no new fields
for the re-teach** (the lesson carries `rule`/`practiceItems`).

**Do NOT bake metadata into `explanation`.** Explanations must be just the rule + answer, e.g.
*"Words ending in s, x, ch, sh add -es. Answer: buses."* Never a `Topic: … Difficulty: X.` prefix —
it leaks into the child's feedback screen. Keep prompts/explanations as clean HTML or plain text.

## 5. Writing for the learner

Standard 4/5 girl in Trinidad & Tobago, ~10–12, often on a phone. Short plain sentences, one idea at
a time; concrete T&T examples; warm ("Let's try one", never "wrong" — the app says "not yet"); every
answer genuinely correct (double-check spelling + math).

## 6. Pre-publish checklist

- [ ] Title names ONE exact skill; every block is about it.
- [ ] 6–10 blocks, ≥2 interactive; worked example + checks are the same *kind* of problem but **never the same instance** (see §1 — the example must not reuse a number/answer any check, fillblank, or practiceItem uses).
- [ ] **`php artisan lessons:verify` passes** — no within-lesson problem repeats (example ↔ question ↔ practiceItem).
- [ ] **Every interactive block has `rule` (one sentence) + ≥4 same-rule `practiceItems` ({prompt, answer}).**
- [ ] Each `check.answer` / `fillblank.answer` correct; `markwords` marks only targets; `matchpairs`/`ordersteps` correct.
- [ ] Practice bank has **≥15 questions at each of D1, D3, D5** for the topic.
- [ ] No `Topic:/Difficulty:` metadata in any `explanation`.
- [ ] Preview-only import first to catch structural errors (LB-04).
