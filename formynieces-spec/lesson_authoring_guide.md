# Lesson Authoring Guide (SmoothSeas)

A guide for writing **coherent, effective interactive lessons** for the SmoothSeas learning loop —
whether by hand or by generating them with an LLM. Feed this whole document to an LLM as context
when generating lessons; follow the **Coherence rule** and the **pre-import checklist** every time.

Lessons are imported as JSON keyed by module code (see the in-app **Lesson Import Guide**, LB-04).
This document is about writing *good* lessons; that one is about the file mechanics.

---

## 0. The one rule: COHERENCE

**Every part of a lesson must be about the same single skill — the module's skill — and nothing else.**

That means the **title**, the **module** it's imported to, and **every block** (explanation, rule,
worked example, inline check, fill-in-the-blank, mark-the-words, match-pairs, order-the-steps) all
teach and test *that one skill*, using the *same kind of example*.

**Why this matters more than it looks — the AI depends on it.** Smooth (the clarify chat, and the
per-block reinforcement in an AI-assisted re-teach) is **grounded on the module's topic AND the
lesson's text**. If the title/topic and the block content disagree, Smooth gets a contradictory
context and asks the child off-topic questions.

> Real failure this guide exists to prevent: a module titled *"Spelling: Plural Forms — y→i, f→v,
> -es endings"* was given generic filler blocks ("The cat ___ on the mat", "tap the verb", "order the
> addition steps"). When the child finished the fill-in-the-blank, Smooth — seeing the *plurals*
> topic but a *cat-sat* block — asked "What is the plural of *baby*?", which had nothing to do with
> the block in front of her. **Coherent content would have kept Smooth on the exact block.**

If you remember nothing else: **a lesson about plurals contains only plurals — in its explanation,
its worked example, its checks, and every interaction.**

---

## 1. What a lesson is

A lesson is a `title` plus an ordered list of `blocks`. It teaches one module's skill on-platform,
authored in advance (never generated live). The student walks it block-by-block; interactive blocks
must be answered correctly before she can advance.

```json
[
  {
    "module": "ELA-014",
    "title": "Making plurals when a word ends in y",
    "is_published": true,
    "blocks": [ /* ordered blocks — see §3 */ ]
  }
]
```

- `module` — the stable module **code** (e.g. `MATH-003`, `ELA-014`). One lesson per module.
- `title` — names the **exact skill**, in plain words. Not the broad syllabus heading. Good:
  *"Making plurals when a word ends in y"*. Bad: *"Spelling"*, *"Plural Forms — y→i, f→v, -es endings"*
  (too many skills at once → incoherent blocks).
- `is_published` — `true` to serve it to students.
- `blocks` — the lesson, in order.

**One skill per lesson.** If a module truly covers several sub-rules (y→i, f→v, -es), that's a sign
to keep each lesson tightly focused: teach one rule thoroughly, or sequence the rules as clearly
separated sections with their own worked example + checks — never mix them in one block.

---

## 2. The shape of a good lesson (gradual release)

Follow the "I do → we do → you do" arc. A reliable skeleton (mirrors `LessonTemplate::scaffold`):

1. **Hook** (`text`) — one or two friendly sentences: what this skill is and why it's useful.
2. **Rule** (`key`) — the core rule in one simple sentence.
3. **Worked example** (`example`) — one problem solved step by step, using the rule.
4. **Check** (`check` / `fillblank`) — she applies the rule to one item.
5. **A second idea or twist** (`heading` + `text`) — only if it's the *same* skill.
6. **Interaction** (`markwords` / `matchpairs` / `ordersteps` / `fillblank`) — active practice of the
   same skill.
7. **Wrap** (`text`) — warm one-liner: she's got the trick, time to practise.

Keep it to **6–10 blocks**. Every block must pull toward the one skill. Aim for **at least two
interactive blocks** (checks or interactions) so she does, not just reads.

---

## 3. Block types — when to use each

Every block is `{ "type": "...", ... }`. Interactive types (marked ⛓ GATES) must be answered
correctly before she advances, so their answer must be unambiguous.

| Type | Use it to… | Required fields | Notes |
|---|---|---|---|
| `heading` | title a section | `content` | keep short |
| `text` | explain in kid words | `content` | 1–3 short sentences |
| `key` | state the rule to remember | `content` | one sentence |
| `example` | show it worked, step by step | `content` (+ `steps[]`) | steps are short lines |
| `visual` | show an image | `content` (URL) | full https URL |
| `check` ⛓ | quick multiple-choice | `question`, `options[]`, `answer` (0-based) | `answer` indexes `options`; optional `explain` |
| `fillblank` ⛓ | complete a sentence | `prompt` (with `___`), `answer` | optional `options[]` word bank, `explain`; match is case-insensitive |
| `markwords` ⛓ | tap the target word(s) | `instruction`, `text` (wrap targets in `*asterisks*`) | she must tap exactly the marked words |
| `matchpairs` ⛓ | match items to partners | `instruction`, `pairs[]` of `{left,right}` (≥2) | rights shuffled for her |
| `ordersteps` ⛓ | order a sequence | `instruction`, `items[]` in the CORRECT order (≥2) | shown shuffled |

**Choosing an interaction that fits the skill** (coherence in the interaction itself):
- Spelling / word-formation → `fillblank` (type the correct spelling) or `markwords` (tap the
  correctly/incorrectly spelled word).
- Grammar (parts of speech, errors) → `markwords` (tap the verb / the mistake).
- Vocabulary, synonyms, term↔meaning → `matchpairs`.
- Procedures, steps, sequence, story order → `ordersteps`.
- Concept recall, "which is right?" → `check`.

Don't reach for an interaction that doesn't fit the skill just for variety — a mismatched
interaction is incoherent.

---

## 4. Writing for the learner

The reader is a girl in Standard 4/5 in Trinidad & Tobago, ~10–12, often on a phone.

- **Short, plain sentences.** One idea at a time. No jargon; if a term is needed, define it simply.
- **Concrete and familiar.** Use everyday, T&T-relevant examples (mangoes, doubles, maxi-taxis,
  cricket) over abstract ones.
- **Warm and encouraging.** "Let's try one", "You've got this". Never "wrong" — the app says
  "not yet".
- **Examples must be real and correct.** Every check/fill-blank answer must be genuinely correct;
  double-check spelling and math.

---

## 5. How Smooth (the AI) uses the lesson — why coherence keeps it smart

Smooth appears beside the lesson (the **clarify chat**) and, during an **AI-assisted re-teach**,
asks the child a quick reinforcing question about **each block as she finishes it**. Smooth's context
is built from **the module topic + the lesson's block text**. So:

- **Coherent lesson** → Smooth's questions and answers stay exactly on the block and the skill.
- **Incoherent lesson** (title says X, blocks are about Y) → Smooth blends the two and asks
  confusing, off-topic questions.

Coherence isn't a nicety here — it's what makes the AI tutor usable.

---

## 6. Pre-import checklist (run through EVERY lesson)

- [ ] **Title names one exact skill** (not a broad heading, not several skills).
- [ ] **Every block is about that one skill** — explanation, rule, example, checks, interactions.
- [ ] **The worked example and the checks use the same kind of problem.**
- [ ] Each `check` `answer` is the correct 0-based index into its `options`.
- [ ] Each `fillblank` `answer` is exactly what should fill the `___` (and is in `options` if a word
      bank is given).
- [ ] Each `markwords` `text` wraps **only** the correct target word(s) in `*asterisks*`.
- [ ] Each `matchpairs` has ≥2 correct `{left,right}` pairs; each `ordersteps` lists `items` in the
      **correct** order (≥2).
- [ ] Language is short, kind, and age-appropriate; examples are correct.
- [ ] `module` is the right code; `is_published` is set as intended.
- [ ] 6–10 blocks, at least two interactive.

---

## 7. A complete, coherent example

The same topic that was done badly in the demo — now coherent. Notice how **every** block is about
making plurals of words ending in *y*, and nothing else:

```json
[
  {
    "module": "ELA-014",
    "title": "Making plurals when a word ends in y",
    "is_published": true,
    "blocks": [
      { "type": "text", "content": "Lots of words end in the letter y. Let's learn the trick for making them mean 'more than one'." },
      { "type": "key", "content": "If a word ends in a consonant + y, change the y to i and add es." },
      { "type": "example", "content": "Make 'baby' mean more than one.", "steps": ["'baby' ends in b (a consonant) + y.", "Change the y to i: 'babi'.", "Add es: 'babies'."] },
      { "type": "check", "question": "What is the plural of 'city'?", "options": ["citys", "cities", "cityes"], "answer": 1, "explain": "t is a consonant, so change y to i and add es: cities." },
      { "type": "fillblank", "prompt": "One puppy, two ___ .", "answer": "puppies", "options": ["puppys", "puppies"], "explain": "Consonant + y, so y becomes i and we add es." },
      { "type": "text", "content": "But watch out! If a vowel (a, e, i, o, u) comes before the y, just add s — no change." },
      { "type": "markwords", "instruction": "Tap the word that is spelled correctly", "text": "The two *boys* played. The two boies played.", "explain": "'boy' has a vowel (o) before y, so we just add s: boys." },
      { "type": "matchpairs", "instruction": "Match each word to its plural", "pairs": [ {"left": "lady", "right": "ladies"}, {"left": "key", "right": "keys"}, {"left": "story", "right": "stories"} ] },
      { "type": "text", "content": "Nice work! Remember: consonant + y → change to i and add es; vowel + y → just add s. Time to practise!" }
    ]
  }
]
```

---

## 8. Generating lessons with an LLM

Because the format is plain JSON, you can draft lessons offline with an LLM, vet them, and bulk
import. Use a prompt like this (paste §3's block table, §6's checklist, and §7's example alongside):

> You are authoring one interactive lesson for the SmoothSeas SEA-prep app, for a Standard 4/5 girl
> in Trinidad & Tobago (~10–12, on a phone). The module is **[CODE] — [exact skill]**. Produce ONE
> lesson as a JSON bundle (a list with a single lesson object: `module`, `title`, `is_published:
> true`, `blocks`).
>
> Hard rules:
> - COHERENCE: every block — explanation, rule, worked example, checks, and interactions — must be
>   about **[exact skill]** and use the same kind of example. Nothing off-topic.
> - Follow the gradual-release shape: hook → rule → worked example → check → (optional same-skill
>   twist) → interaction → wrap. 6–10 blocks, at least two interactive.
> - Use only these block types and their fields: [paste §3 table]. Every `check.answer` /
>   `fillblank.answer` must be correct; `markwords` wraps only the correct target(s) in *asterisks*;
>   `matchpairs` ≥2 correct pairs; `ordersteps` items in correct order.
> - Short, warm, kid-friendly language; correct spelling and facts.
>
> Return only the JSON.

Then run it through the **pre-import checklist (§6)** before importing. Use **Preview only** in the
importer first to catch any structural errors.
