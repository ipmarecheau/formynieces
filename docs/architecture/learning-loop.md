# The Learning Loop

The learning loop is the core teaching engine. Everything else (the Voyage map, pacing, motivation,
the guardian dashboard) orbits it. It is deterministic PHP — the same inputs always produce the same
mastery decision — with AI used only to *coach*, never to *grade the path*.

## The loop, end to end

```mermaid
graph TD
    enter["Open a level<br/>(ModuleEntry)"] --> check["Competency check<br/>D1/D3/D5 test-out"]
    check -->|pass all| mastered["Mastered 🎉"]
    check -->|miss| choose["Choose: lesson / tutorial / practice"]
    choose --> lesson["Interactive lesson<br/>(LessonWalk)"]
    lesson --> practice["Practice at current rung<br/>(PracticeWalk)"]
    practice -->|streak at D5| mastered
    practice -->|hard miss| reteach["Re-teach<br/>(ReteachWalk + Smooth chat)"]
    reteach --> practice
    mastered --> voyage["Back to the Voyage<br/>next level unlocks"]
```

## Rungs and mastery

- Practice is served at the student's **current rung** — difficulty **D1 → D3 → D5**.
- Mastery is earned by a **first-try streak at D5**; the engine advances the rung as she succeeds and
  routes her to a re-teach on a hard miss.
- A **content-hash no-repeat** rule (LL-18) guarantees a question is never served twice. This is why
  the content pools must be deep (see [Runbooks → reading pool](../operations/runbooks.md)).
- Exposure is recorded **on answer, not on serve** — viewing/refreshing never burns a question.

!!! warning "No-repeat is context-scoped"
    The tutorial draws worked examples from the **same** D1 practice bank. Practice/check no-repeat
    therefore excludes `context = 'tutorial'` exposures (`QuestionExposure::pickUnseen(..., excludeContexts: ['tutorial'])`),
    so teaching views can't drain the practice pool. Removing that scoping dead-ends practice on
    "more practice coming soon".

## The re-teach

When practice pulls her back, she re-walks *this lesson*. Each interactive block carries its own
`rule` + ≥4 same-rule `practiceItems`, so the chat remediates the **failed block's** rule specifically.
Reasoning blocks additionally carry `principle`, `canonical_solution`, `rubric`, `misconceptions`, and
`sample_answers` — the exemplars the LLM judge grades thinking against (and the fallback when the LLM
is unavailable).

## AI at the edges

AI never decides mastery. It is used for: **essay scoring**, **re-teach / clarify chat**,
**worked-example generation**, and **school-journal OCR**. Every call is metered by
[`LlmBudget`](https://github.com/ipmarecheau/formynieces/blob/main/app/Services/LlmBudget.php):

- **Discretionary** (chat, re-teach, worked examples) stops at a soft monthly cap.
- **Essential** (essay grading, guardian summaries) runs to a hard monthly cap.
- Caps are **per student per month**; the provider is OpenRouter (`services.llm.*`). Tests stub/fallback
  so the suite never needs a live key.

## Gates that return to the map (never silent)

Opening a level (`ModuleEntry`, route `practice.enter`) can legitimately **sail the student
back to the Voyage map** instead of into the lesson:

- **Writing gate** (WR-07 / CO-05): on a writing day, a **new** level waits until the day's
  Writer's Log is done (an already-started level is never gated). Flashes `writingGate`.
- **Locked stage** (LE-03 / LE-06): practice is locked until the worked examples are done,
  worked examples until the lesson is done. `PracticeWalk` / `TutorialWalk` flash
  `lockMessage` and bounce to `ModuleEntry`, which shows it as a lock note.
- **Maintenance window** (LL-23): a freshly-mastered level is held for ~2 weeks and shows a
  "come back in N days" screen rather than the loop.

!!! warning "No silent bounce"
    Every one of these redirects **must surface its reason**, or it reads as "the lesson is
    broken." The student-facing voyage pages (`voyage/overworld.blade.php`,
    `voyage/island.blade.php`) include **`partials/voyage-flash`**, a dismissible toast that
    renders `writingGate` / `lockMessage` / `voyageNote` on arrival. When you add a new gate
    that redirects to the map, **flash one of those keys** (or `voyageNote`) so the toast
    picks it up. This is why `writingGate` was invisible until the toast was added — the
    redirect worked, but the message had nowhere to show.

## Related specs

- Gherkin: `formynieces-spec/features/learning_loop.feature`, `lesson.feature`, `diagnostic.feature`
- Authoring: `formynieces-spec/lesson_development_guide.md`, the `lesson-authoring` skill
