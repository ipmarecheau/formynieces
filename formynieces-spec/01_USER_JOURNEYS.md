# 01 — User Journeys & Cadence Narratives

Derived from: 00 §2 (goal, actors).
Feeds: 02 (object model — bold nouns below become objects), 03 (screens — frequency of appearance below drives navigation depth).

---

## 1. Journey backbones

### 1.1 Student backbone (left → right in time)

```
DISCOVER → ONBOARD → REVEAL → WEEKLY RHYTHM (×~24) → REVISION BUFFER (×6) → EXAM WEEK → AFTER
```

| Stage | What happens | Emotional job |
|---|---|---|
| Discover | Guardian shows her the platform. She doesn't sign up — her guardian does. | Curiosity, zero pressure |
| Onboard (diagnostic) | "A quick adventure to find your starting point" — ~30 adaptive MC items + one short writing piece. Never called a test. | Safety; effort is praised, not correctness |
| Reveal | Her **adventure map** appears: 30 stops, her flag planted at her computed starting **week**, first **weekly target** set. | "This was made for me" |
| Weekly rhythm | Each week: work through the target **modules**, take **quizzes**, submit one **writing piece**, watch the flag advance. | Momentum, visible progress |
| Revision buffer | Final 6 weeks: map switches to revision mode — weak **modules** resurface, past-paper style practice (fill-in answers return). | Sharpening, not learning new things |
| Exam week | Platform goes quiet and warm. No targets. A single good-luck state. | Calm |
| After | Celebration state; guardian closes the loop. | Pride |

### 1.2 Guardian backbone

```
SIGN UP → VERIFY → SET UP CHILD → WATCH REVEAL → WEEKLY CHECK-IN (×30) → EXAM WEEK → AFTER
```

| Stage | What happens | Emotional job |
|---|---|---|
| Sign up & verify | Creates account, verifies email (MVP; phone later). Confirms 18+. | Trust — this platform takes children seriously |
| Set up child | Enters child's name, target SEA year, optional known weak areas. Creates child login. | "I've done something concrete for her" |
| Watch reveal | Sees the same roadmap the child sees, plus the honest layer: where she actually starts and why. | Informed, not alarmed |
| Weekly check-in | One glance per week: did she hit the target, where is she vs pace, what the **exam agent** recommends, her latest **writing feedback**. | Confidence she's not guessing |
| Exam week | A "what to do this week as a guardian" note. | Useful, calm |
| After | Summary of the whole journey. | Closure |

---

## 2. Cadence narratives (the unit of design)

These narratives are the source of truth for screens and Gherkin. Names are illustrative.

### 2.1 Student — a school-day evening (Tuesday, ~25 min)

> Amara opens the site on her guardian's phone after homework. The **dashboard** greets her by name and shows this week's stop on the **adventure map**: *Week 9 — Fractions Forest*. Two of four target modules are done (✅✅⬜⬜). She taps the third — *Improper fractions to mixed numbers*. A short human-vetted **lesson** loads (description + vetted resources). She works through it, then taps **Start quiz**. Five questions; she gets four right. The module flips to *mastered*, confetti, her **streak** ticks to 5 days. The map's flag nudges forward. Total: ~25 minutes. She does **not** see anything alarming — honesty lives in the agent panel, not in her face.

Daily objects: dashboard, adventure map (current stop only), module, lesson, quiz, streak.

### 2.2 Student — Saturday (writing day, ~40 min)

> Saturday is the **writing track** day. The dashboard's writing card shows this week's prompt (adapted from past CW papers). Amara types a short piece. On submit, Groq scores it against the four-criterion rubric (Content / Language Use / Grammar & Mechanics / Organisation) and returns warm, specific **writing feedback** — two things she did well, one thing to try next time. Her **rubric profile** (a small radar/bars view) updates. No grade letter, no pass/fail.

Weekly objects: writing prompt, writing submission, writing feedback, rubric profile.

### 2.3 Student — Sunday (week rollover)

> Sunday evening the **pacing engine** closes the week: completed targets are archived, missed modules roll into next week's target (capped so the week never becomes a wall), the next stop on the map unlocks, and a one-line encouragement is generated. If she finished early, the map offers — never forces — a peek at next week.

### 2.4 Guardian — Sunday check-in (~5 min)

> Marsha opens the **guardian dashboard** Sunday night. One screen answers four questions: **(1)** Did Amara complete this week's target? **(2)** Where is she against the 30-week pace (the honest line, weighted 50/30/20 by paper)? **(3)** What does the **exam agent** recommend for next week? **(4)** What did her writing feedback say? If something needs action ("Measurement is consistently weak — consider 20 extra minutes Wednesdays"), it's phrased as one concrete suggestion, never a wall of charts.

### 2.5 Guardian — mid-week glance (~30 sec, optional)

> Wednesday lunchtime, Marsha checks the app: a single status line — "On track. 2 of 4 modules done, streak alive." Nothing else demands attention. (Push/email digest is `@v1.1`; MVP is pull-only.)

---

## 3. Anti-narratives (what must NOT happen)

- The student must never see raw placement-weight math, percentile language, or red warning states. (Two-layer model.)
- The guardian must never get a feel-good map view *instead of* the honest view.
- Missing a week must never produce a hole the child can see forever — recovery is always visible and bounded.
- The diagnostic must never be re-framable as a test by the UI (no timer visible to the child, no score shown during).

---

## 4. The eight scenarios (acceptance bar)

Every `@mvp` feature must behave sensibly in S1–S6; S7–S8 are primarily guardian-communication features (`@v1.1`+).

| ID | Scenario | The platform must… |
|---|---|---|
| S1 | **On-track** | Stay out of the way; celebrate rhythm. |
| S2 | **Behind, recoverable** | Roll misses forward gently; show guardian a credible catch-up plan. |
| S3 | **Significantly behind** | Re-pace honestly (agent layer); keep the map kind; give guardian a triage view weighted toward Math (50%). |
| S4 | **Late joiner** | Diagnostic computes a compressed starting week; map shows a shorter but complete journey, not a "you missed 12 weeks" graveyard. |
| S5 | **Ahead but uneven** | Let her run ahead on strong strands while the agent quietly routes weekly targets at weak strands. |
| S6 | **Disrupted** (illness, family event) | Support a guardian-triggered pause; resume re-paces without shame. |
| S7 | **Guardian disengaged** | Keep the child's loop self-sufficient; nudge the guardian (digest, escalating gently). |
| S8 | **Conflicted household** | Support a second guardian with read-only visibility so both adults see the same honest picture. |
