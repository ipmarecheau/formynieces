# ForMyNieces — Product Specification Overview

**Date:** 13 June 2026
**Version:** 1.0
**Owner:** Isaac
**Status:** Living document — supersedes scattered handoff decisions; handoffs remain the technical/deployment record.

---

## 1. Purpose of this spec suite

This suite converts the high-level goal into executable, testable behaviour using a strict derivation chain. Each layer is derived from the one above it, so any change can be traced down (what breaks?) and any feature can be traced up (why does this exist?).

```
GOAL (why the product exists)
  └── USER JOURNEYS (the arc of each actor's life with the product)      → 01_USER_JOURNEYS.md
        └── CADENCE NARRATIVES (weekly + daily interactions)             → 01_USER_JOURNEYS.md
              └── OBJECT MODEL (nouns, attributes, verbs)                → 02_OBJECT_MODEL.md
                    └── SCREENS + NAVIGATION (derived from objects
                        and interaction frequency)                       → 03_SCREENS_AND_NAVIGATION.md
                          └── GHERKIN FEATURES (executable behaviour)    → features/*.feature
                                └── ROADMAP (sequenced delivery)         → ROADMAP.md
```

**Consistency rules:**
1. Every Gherkin `When` must be reachable from a screen in 03.
2. Every Gherkin `Then ... sees` must have a home on a screen in 03.
3. Every screen must be justified by at least one scenario; otherwise cut it.
4. Every object in 02 must appear in at least one narrative in 01.

---

## 2. The goal

> A primary school girl in Trinidad & Tobago walks into the SEA exam confident and prepared, having followed a 30-week adventure that met her where she started — and her guardian stayed genuinely informed the whole way without nagging, guessing, or being misled by feel-good metrics.

**Actors:**

| Actor | Definition |
|---|---|
| **Student** | A girl in Standard 4/5 preparing for SEA. Age ~10–12. Uses the platform on a phone or shared device. Must be linked to a verified guardian. |
| **Guardian** | An adult (18+) responsible for the student. Verified email (MVP) and phone (later). Replaces "parent" everywhere — covers aunts, grandmothers, older siblings. |
| **Admin** | Isaac (Filament panel). Authors/vets content, manages anchor questions, monitors the system. |
| **System** | The diagnostic engine, pacing engine, and AI exam agent (Groq) acting on schedules and events. |

**Design constraints (settled, do not relitigate):**
- SEA 2025–2028 framework: Math (placement weight 100, ~50%), ELA (60, ~30%), ELA Writing (40, ~20%).
- Week-based adventure map (each stop = one study week), not topic clusters.
- **Two-layer model:** the map is motivational and always kind; the AI exam agent panel is honest and adaptive. The two never contradict each other but serve different emotional jobs.
- Writing is a **parallel track** — rubric profiles, never a mastered/not-mastered status.
- All syllabus resources are human-vetted, never AI-generated.
- 30-week pacing calendar with a 6-week revision buffer before exam day.
- Eight student-guardian scenarios are the acceptance bar (see 01 §4).

---

## 3. Prioritization scheme

Priorities are expressed as **Gherkin tags** so the spec, the test suite, and the roadmap share one vocabulary. Tags apply at feature level by default and can be overridden per scenario.

| Tag | Meaning | Ship gate |
|---|---|---|
| `@mvp` | Required before the first real student (the nieces) onboards. Without it the core promise breaks. | Phase 1 |
| `@v1.1` | First fast-follow. The product works without it, but a real month of use will hurt. | Phase 2 |
| `@roadmap` | Valuable, sequenced later, tied to exam-readiness or scale. | Phase 3+ |

Secondary tags: `@student`, `@guardian`, `@admin`, `@system` (actor), and `@scenario-S1` … `@scenario-S8` (which of the eight household scenarios a Gherkin scenario exists to serve).

**MVP definition (one sentence):** one real student can be onboarded by her guardian, take the diagnostic, receive a personalised 30-week roadmap, complete weekly targets through the learning loop, submit writing for AI feedback, and her guardian can see an honest picture — all on the live VPS with a database that survives redeploys.

---

## 4. Document map

| File | Contents |
|---|---|
| `00_SPEC_OVERVIEW.md` | This file. Goal, actors, derivation chain, priority scheme. |
| `01_USER_JOURNEYS.md` | Journey backbones, weekly/daily narratives, the eight scenarios. |
| `02_OBJECT_MODEL.md` | OOUX object model mapped to the existing schema. |
| `03_SCREENS_AND_NAVIGATION.md` | Screen inventory with priorities and navigation map. |
| `features/*.feature` | Executable Gherkin specs, tagged by priority and actor. |
| `04_FEATURE_INDEX.md` | Feature ↔ priority ↔ scenario coverage matrix. |
| `ROADMAP.md` | Phased delivery plan against the SEA 2027 calendar. |

---

## 5. Out of scope (explicitly)

- Boys / co-ed positioning (name says it all for now; revisit only if the platform opens up).
- Peer features, leaderboards, or any child-to-child interaction (safety + scope).
- Native mobile apps. Responsive web only.
- Payments. This is a gift.
- AI-generated learning resources (human-vetted only; AI is used for summaries and writing feedback exclusively).
