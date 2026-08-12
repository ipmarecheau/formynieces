# 05 — User Stories by Release (MVP · V1 · V2)

Derived from: 01 (journeys), 04 (feature index & tags).
Purpose: a persona-voiced narrative of the *whole* experience, banded by release, so
every feature can be checked against one coherent story instead of standing alone.

The release bands map 1:1 to the spec's own tags:

| Band | Tag | Theme |
|---|---|---|
| **MVP** | `@mvp` | The honest sea — the full loop, end to end |
| **V1** | `@v1.1` | Depth and warmth — the same shape, more human |
| **V2** | `@roadmap` | The horizon — motivation & exam-craft layers |

---

## 0. The one rule that makes it cohere

The platform has three actors and **one home each** — and no layer ever wears
another's clothes:

- **The child sails the Voyage.** Her home is the painted sea (`@student`,
  `adventure_map`). She never sees percentages, pace, or warning states.
- **The guardian reads the honest gauge.** A separate, calm screen answers a few
  true questions (`@guardian`, `guardian_dashboard`). It never borrows the child's
  motivational styling.
- **The admin tunes the engine.** Syllabus, pacing cap, per-student overrides, and
  the honest data (`@admin`, `admin_content`) — behind the curtain, touching neither
  the child's joy nor the guardian's calm room.

> **The core incoherence this document existed to fix — now RESOLVED (SH-01…06 built
> and verified).** An onboarded student once *landed* on `/my-map` — a
> percentages-and-tallies roadmap — the exact surface the Voyage spec forbids showing a
> child ("never a chart of percentages and pace"). The Voyage was treated as a
> "standalone alternative to the dashboard." It is now *the* dashboard: the six MVP
> seams in §4 (`student_home.feature`, `SH-01…SH-06`) closed the gap — the Voyage is
> her one front door, and the daily threads (this week's focus, her streak, her
> writing, her practice) all hang off it. Personas below match the shipped experience.

---

## 1. 🟢 MVP — *"The honest sea"*

The full loop works end to end: one home per person, real earned progress, calm
truth for the grown-up.

### 🌊 Maya — student
> My aunt hands me a login. My first time in, I sail a short **first voyage** (the
> diagnostic) and then my whole map is **revealed**, painted just for me. After that
> I **land on my Voyage every time** — my sea, my islands, never a page of numbers.
> This week's islands shimmer to show me where to sail. Every level is one **loop**:
> *explainer → competency check → (lesson → tutorials → practice) → competency check*.
> When I tap a level it first **explains itself in my words**, then the **competency
> check** gives me one question at each real difficulty — **D1, D3, D5**. Get all three
> right first try and I've **tested out** — mastered, no lesson needed. If I don't, I
> **choose my way in**: the interactive **lesson** (with a **clarify chat** I can ask),
> the **tutorials** (walked through by **Smooth**), or straight to **practice**. I climb
> three real rungs — **D1 → D3 → D5** — and every question gives me a **second try** so I
> can learn and move on. But to truly **master**, I have to get **three of the hardest
> (D5) right on the first try in a row**. If I stumble — **two in a row wrong at D3 or
> D5, or five of my last seven** — Smooth pulls me into an **AI-assisted re-teach**
> (lesson and/or tutorial) that keeps working with me until I get it, then drops me back
> into practice at **D3**, never at the bottom. Mastering set off a celebration —
> and I keep a competency by touching it again (**3 × D5 every two weeks**) or it
> quietly slips to **"review."** Wrong answers say **"not yet,"** never "wrong." My
> The first time I open any screen, **Smooth shows me how it works** — the levels,
> the two tries, how the map unlocks — and I can tap her to hear it again anytime, but
> she never nags. And **every win throws a party**: a big animated moment when I clear a
> level, master a stop, or finish my week — excitement at every milestone, then straight
> back to sailing. My **streak** grows when I keep coming back and
> **freezes**, not shatters, if my aunt pauses me. Every island has a **Writer's
> Log** stop where I get a weekly prompt and warm feedback — two things I did well,
> one to try — never a grade.

*Covers:* `roadmap_reveal` (RR-01…), `diagnostic` (DG), `adventure_map`
(AM-01…04, 06, 08), `learning_loop` (LL-01…22 — now the umbrella feature; the tutorial
`TU-01…04` folds in as the Tutorials stage, LL-08…11 pulled up from `@roadmap`, LL-19…22
add the explainer, the D1/D3/D5 test-out check, the choice-of-way-in, and the AI-assisted
re-teach), `lesson` (LE-01…05 — the interactive lesson + clarify chat, plus the H5P-grade
authoring engine), `motivation_layer` (ML), `writing_track` (WR-01…03), plus
`student_home` (SH-01…06).

### 🧭 Maya's aunt — guardian
> I **register (18+), verify my email, and set Maya up** — I see her login once. My
> screen is a separate, calm room that answers the **four Sunday questions**: was the
> target done, where is she against the 30-week pace (weighted 50/30/20 by paper),
> what's the one thing to focus on next, and what did her writing feedback say. When
> the **diagnostic disagrees** with the weak spots I named, I get to **decide**
> (proceed on the diagnostic, or keep my knowledge) — and Maya waits kindly until I
> do. Behind pace reads as **triage, not panic**. I can **pause and resume** her
> without guilt. I never see her streaks as a scoreboard.

*Covers:* `guardian_onboarding` (GO-01…05), `guardian_dashboard` (GD-01…05),
`roadmap_reveal` reconciliation (RR-02…11), `weekly_targets` pause/resume.

### ⚙️ Admin — me
> In Filament I hold the **90 modules**, the **global weekly cap**, and
> **per-student overrides** (lift a fast learner, ease a struggling one), plus cap
> reviews and the honest progress data. I tune the engine; I never touch her joy
> layer or the guardian's calm room.

*Covers:* `admin_content` (AC), `weekly_targets` pacing engine (WT).

---

## 2. 🟡 V1 — *"Depth and warmth"* (`@v1.1`)

Same shape, more honest and more human once the core is lived-in.

### 🌊 Maya — student
> My worked examples get **interactive** — instead of just watching, I **drive the
> method** step by step (TU-05). And the platform is honest with itself: if I
> mastered something weak long ago and haven't touched it, it quietly **slips back
> into "review"** so I refresh it rather than pretending I still own it (LL-07). A
> **companion** greets me on my Voyage now — welcomes me by name, cheers my streak,
> and tells me the plan for this week in my own words, never a number (VC-01…03).

### 🧭 Maya's aunt — guardian
> I can **read Maya's latest writing feedback myself** (WR-05) and see her **rubric
> growth over time** (WR-04). If I've gone quiet, the weekly **digest comes to me
> inline** rather than waiting for me to log in (GD-06). If her
> diagnostic looks stale, I can **initiate a retake** (DG-17), and I can **verify a
> phone number** for reminders (GO-06).

### ⚙️ Admin — me
> The **pacing clock gets precise** — it derives the current week and weeks-to-exam
> correctly, and an **early starter is never pushed past week one** before real time
> passes (WT-00). I also **curate the question bank**: whole banks arrive as **Moodle
> XML** and import safely with a dry-run preview and no duplicates (QB-01…05), I
> **author and edit** questions by hand (QB-06…08), and I **export** the bank back to
> Moodle XML so it stays portable (QB-09/10). The bank is **backed up daily and kept for
> a month**, so I can **delete everything to start fresh** or **restore to any recent day**
> without fear — a bad import is never permanent (QB-11…14). The same importer grows a
> separate **writing-prompt bank**: a Moodle **essay** export loads into a genre-keyed
> bank (Narrative → module 69, Report → module 70), carrying each prompt's **marking
> rubric**, and a single upload routes multichoice and essay to their right banks
> automatically (WB-01/02). *How* those prompts reach students — weekly, on-demand, or
> both — is a deliberately deferred decision (WB-03/04).

---

## 3. 🔵 V2 — *"The horizon"* (`@roadmap`)

Motivation and exam-craft layers — only after the honest core is rock-solid, so play
never outruns learning.

### 🌊 Maya — student
> **XP** rewards the *whole* loop — showing up, reading, focusing, answering — and
> **only ever rises**; a mistake costs a **combo multiplier**, never my banked points
> (XP-01…04). Weekly XP resets but my lifetime XP is kept (XP-05). I join a **small
> weekly league** of kids like me under **funny nicknames** — never real names — with
> promotion at the top and no permanent bottom (XP-06…08). I can start an **optional
> focus timer** for bonus XP; it's advisory, never blocks me, and leaving early costs
> nothing; reading is never timed (FT-01…05). When exam season nears, my map suggests
> **starred levels** (AM-05) and in the final buffer switches to a calmer **revision
> mode** (AM-07) — **Math practice becomes fill-in**, and I sit **timed mocks** shaped
> like the real papers, with **exam week kept quiet and warm** (ER-01…03). My first-run
> **reveal becomes an animation** — I watch my map paint itself and my flag plant at my
> starting stop (RR-12). My
> companion's voice gets **richer with AI** — but only ever from what's true on my
> map, and never keeping me waiting for my sea (VC-04, VC-05).

### 🧭 Maya's aunt — guardian
> The **league is my opt-in** — nothing social turns on without me (XP-09). Mock
> results feed my **readiness view**, never Maya's map. I can **invite a second
> guardian** who can **view but not change** anything (GO-07/08).

### ⚙️ Admin — me
> I gain the **exam-readiness machinery** (mock structures, timing, the revision
> buffer window) and the **league engine** (grouping, promotion/relegation, nickname
> pool). All behind the curtain; the child only ever meets it as *her* sea.

---

## 4. The six MVP seams (coherence backlog — ✅ COMPLETE)

These were the concrete gaps between the MVP story and the code. Each is a scenario in
`features/student_home.feature` (prefix `SH`). **All six are now built and verified
(`SH-01…06` = `ok current`)** — the Voyage is the student's single front door.

| Seam | Scenario | What it closes |
|---|---|---|
| Land on the Voyage | `SH-01` | Onboarded students land on the Voyage, not the `/my-map` percentages roadmap |
| This week's focus on the map | `SH-02` | The weekly target surfaces as highlighted levels on the Voyage, no pace language |
| One door into practice | `SH-03` | Practice is reached by tapping an island level — the competing map link is retired |
| Streak on the Voyage | `SH-04` | The streak shows within the Voyage, not on a separate dashboard |
| The Writer's Log goes live | `SH-05` | The island's Writer's Log stop opens this week's real writing prompt (relocates WR-01's entry point) |
| Welcome-back flows to the sea | `SH-06` | The streak-celebration splash continues into the Voyage, never the old dashboard |

**Superseded behaviour to retire as these land:**
- The student-facing `/my-map` percentage roadmap (`DashboardController::studentDashboard`).
  Its honest completion data belongs to the guardian layer, not the child.
- `WR-01`'s "writing card on her dashboard" entry point → moves to the Writer's Log
  stop on the Voyage (`SH-05`).

---

## 5. Newly identified MVP gaps (persona coherence review — ✅ RESOLVED)

Surfaced by walking each persona's story against the scenario bodies. **Both are now
built and verified (`ok current`).** (The Voyage companion, `VC-01…05`, came out of the
same review but is banded `@v1.1`/`@roadmap`, so it sits in `voyage_companion.feature`,
not this MVP list — `VC-01…03` are built.)

| Gap | Scenario | Status | What it closes |
|---|---|---|---|
| Island stops overlap unreadably | `AM-08` | ✅ built | Numbered stops on the map + a legend naming every stop and its status, leaking no pace — the fix for the live overlapping-labels bug |
| No-scoreboard invariant untested | `GD-05` | ✅ built | The guardian's pace/readiness sections must never show the child's streaks or celebration styling — promised at MVP, enforcing scenario now built |

---

## 6. Still outstanding (as of this review)

Reconciled against `specs:trace`. The stories above are the *intended* whole; these are
the parts not yet delivered, by band.

- **MVP — the learning-loop redesign (in progress).** The loop was reworked (2026-08-11,
  extended 2026-08-12) into *explainer → competency check → (lesson → tutorials → practice)
  → competency check*. Opening a level explains itself (`LL-19`), then a fast **test-out
  competency check** serves one D1 + one D3 + one D5 question — clear all three first-try
  and the module is mastered (`LL-20`); miss and she **chooses** lesson, tutorial or
  practice (`LL-21`). Practice keeps the D1/D3/D5 climb, first-try-only mastery (3-in-a-row
  at D5), and the two-attempt rule. Remediation is now trigger-based: **two missed in a row
  at D3/D5, or five of the last seven** (`LL-14`, `LL-22`) pulls her into an **AI-assisted
  re-teach** (lesson and/or tutorial) that pushes until she understands (`LL-15`), then
  returns her to practice at **D3** (`LL-16`). The **interactive lesson** is its own feature
  (`lesson.feature`, `LE-01…04`) — an authored-in-advance page plus an LLM **clarify chat**;
  the seam ships as a placeholder first, then the H5P-grade authoring engine (`LE-05`) as
  the same feature's larger second build (all `@mvp`). Build
  order: **core mechanic** (`LL-03/04/06/12/13`) → **entry: explainer + test-out check +
  choice** (`LL-19/20/21`) → **tutorial stage** (`TU-01…04`) + **stepper** (`LL-08…11`) →
  **interactive lesson + clarify chat** (`LE-01…04`, needs the `LlmService` surface) →
  **trigger-based re-teach** (`LL-14/15/16/22`) → **maintenance decay** (`LL-17`, needs a
  scheduler). The old 3-rung `1/2/3` mastery rule is retired.
- **MVP — AI governance (`ai_governance.feature`, new 2026-08-12).** Guardrails around every
  LLM the platform uses. **Cost:** a per-student monthly budget metered from real token usage —
  discretionary AI (clarify chat, re-teach, worked examples) stops at **USD 1.00**, essential AI
  (essay grading, guardian summaries) runs to a **USD 1.50** hard ceiling; budget is checked
  *before* each call so spend never overshoots (`AG-01…04`). **Time:** guided, LLM-tailored
  learning (lessons/tutorials/chat/re-teach) draws from a **2-hour daily active-time pool** (the
  Alpha "2-hour learning" model); practice is unlimited and never counts; at the cap guided locks
  kindly, practice stays open (`AG-05…07`). **Tailoring:** a compact derived-tag `learning_profile`
  (no transcripts, no PII) personalises the tutor prompts (`AG-08`). **Reporting:** a Filament
  AI-usage panel shows per-student tokens/spend against the caps + guided-time used, with a
  roll-up total (`AG-09/10`). Build order: cost governor + ledger + `LlmService::chat` → admin
  panel → (feeds `LE-01…04` and `LL-14…16`) → guided-time cap → learning profile.
- **V1 (`@v1.1`) unbuilt:** diagnostic retake (`DG-17`, test exists/unverified), writing
  history + guardian writing view (`WR-04/05`), guardian inline digest (`GD-06`), phone
  verify (`GO-06`), stale-mastery decay (`LL-07`).
- **V2 (`@roadmap`) unbuilt (expected):** XP/leagues (`XP-01…09`), focus timer
  (`FT-01…05`), exam readiness (`ER-01…03`), starred/revision map (`AM-05/07`), richer
  loop (`LL-08…11`), AI companion voice (`VC-04/05`), reveal animation (`RR-12`).
- **Writing bank serving/grading** (`WB-03/04`) — deferred pending a decision on how
  prompts reach students; the bank itself is populated (see the admin story, §2).
