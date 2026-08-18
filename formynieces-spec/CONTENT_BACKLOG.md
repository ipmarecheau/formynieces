# Content backlog

Behaviour is tracked by the `.feature` specs and `specs:trace`. **Content is not** —
"a lesson exists for every topic" is production status that drifts as you author, not a
fixed behaviour. So authored content is tracked here and by a living report:

```
php artisan content:coverage            # summary
php artisan content:coverage --details  # lists every missing / under-stocked item
```

The mechanism to load content already exists and is verified: **`lesson_bank` (LB-01..05)** —
import, export, seed, and generate-with-Claude. This backlog is about the **content that flows
through it**, so the app can run seamlessly and lean as little as possible on realtime AI.

> The `content:coverage` command is the source of truth for current numbers. The snapshot
> below is illustrative and will go stale — always re-run the command.

---

## The five content types, their unit, and "done when"

Targets are the constants in `App\Services\Content\ContentCoverageService` — tune them there.

| Content | Unit | Done when | Why this bar |
|---|---|---|---|
| **Lessons** | per **topic** (90 modules) | a published lesson for all 90 | LL-01/LE-01 — every module teaches on-platform |
| **Practice** | per **topic** | ≥3 active questions at each rung 1/3/5 (masterable floor) | the 3-in-a-row mastery rule needs 9 Qs/module |
| **Reading passages** | per **reading level** (3–7) | ~30 unseen passages per level | DR-06 / LL-18 — never repeat a passage in a term |
| **Vocabulary** | per **passage** | ≥3 words drawn from each passage | DV-01 — the day's words come from the day's passage |
| **Writing prompts** | per **study week / genre** | ~30 weekly prompts spanning the 4 genres | WR-01/06 — one shared prompt per Monday-anchored week |

**Note the different units.** Only *lessons* and *practice* are per-topic. Reading + vocabulary
are per **reading level**; writing is per **week/genre**. "Generated for all topics" literally
applies to lessons only — don't document the others as per-topic.

---

## Snapshot (illustrative — re-run `content:coverage`)

| Content | Coverage | Gap |
|---|---|---|
| Lessons | 4 / 90 | **86 to author** — the biggest gap |
| Practice (masterable) | 84 / 90 | 6 modules under the floor |
| Reading passages | level 5 only (5) | levels 3, 4, 6, 7 empty; level 5 needs ~25 more |
| Vocabulary | 15 words / 5 passages | grows with passages |
| Writing prompts | 2 / ~30 | narrative only; expository, descriptive, persuasive missing |

---

## How this fits the build loop

- **Not a `specs:trace` row.** Don't add `.feature` scenarios for content counts — they'd be
  permanently-changing red tests. This file + the command are the tracker.
- **A pending checkpoint.** Before calling a content-dependent feature "done", run
  `content:coverage` and confirm the relevant type clears its bar. A green loop with a thin
  content pool means the feature works but has nothing to serve.
- **Authoring path.** Use the `lesson-authoring` skill + `lesson_bank` import to add lessons;
  the reading/writing banks (essay_word_bank, writing_bank) for the rest.

---

## Open production efforts

1. **Lessons for all 90 topics** — author + vet a lesson per module (now 4/90). Highest priority.
2. **Reading + vocabulary pools** — stock ~30 passages per level across levels 3–7, each yielding
   its day's words.
3. **Writing prompts** — ~30 weekly prompts spanning narrative, expository, descriptive, persuasive.

> Next: an admin **Content Audit** page that reads `ContentCoverageService` and, given the
> syllabus, uses AI to recommend the *minimum* content set for seamless operation. (To brainstorm.)
