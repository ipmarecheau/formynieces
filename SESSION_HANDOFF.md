# Session Handoff — 2026-08-25 · Lessons: option-shuffle, tour gating, syllabus/KAR layer

Durable note so the context window can be cleared. Readable by both Claude agents on this
repo. Delete/replace when the open items below are done.

## Standing state
- Branch `main`. **HEAD `813fbd3`, pushed to `origin/main`** (pushing auto-deploys to prod via
  GitHub Actions — this work IS now on the pipeline).
- Full suite: **617 passing** (`./vendor/bin/pest`).
- Dev server: `php artisan serve` on :8000 (dev SQLite DB). Prod Docker on :8080 — never touch.
- Review account: **`demo-student@smoothseas.test`** set to `tour_stage=done` + tour seen, so
  MATH-038 and lessons review **tour-free**. (Other student logins exist; this is the clean one.)

## Shipped & pushed this session — top of `origin/main`
```
813fbd3 fix(lessons): shuffle check options so the correct answer is never the default; gate the tour to first-run only
fe4d1a2 feat(syllabus): objective/KAR layer + read-only Syllabus page; upgrade MATH-038 to the lesson standard
e46981f docs(lessons): finalize lesson-development standard + just-in-time ground truth
```
1. **Correct answer never the default** — `LessonWalk::shuffleChoiceBlocks()` shuffles `check`/`fillblank`
   options on mount and remaps the answer index. Test: `tests/Feature/LessonInteractionTest.php`.
   Existing check-answering tests (`InteractiveLessonTest`, `LlReteachWalkTest`) now read the remapped
   index instead of hardcoding it — do the same in any new lesson test.
2. **Tour first-run-only** — `LessonTour` + `LoopCoach` open only when `onGuidedTour()` AND NOT
   `hasSeenGuide('tour')`. Added `User::forgetGuide()`; `VoyageTour::start()` forgets the flag so
   "Take the tour" replays every leg.
3. **Objective/KAR layer + Syllabus page** — `/syllabus` route (auth group, both roles), read-only
   coverage view; MATH-038 upgraded to the lesson standard (`database/data/lessons/math-038.json`).
4. **Lesson-development standard** — `formynieces-spec/lesson_development_guide.md` +
   `.claude/skills/lesson-authoring/SKILL.md`. Ground-truth bank at `/root/dev/sea-ground-truth-bank/`
   (26 PDFs, NOT in git).
5. Landing fix: restored `My Dashboard` label casing (`WelcomePageTest`).

## ⚠️ Awaiting Isaac's BROWSER verification (not yet done)
MATH-038 upgrade + the shuffle/tour-gating behaviour on dev (:8000). Do not mark verified until
he confirms in the browser.

## Open items / next up
- Nav links to `/syllabus` from the voyage + parent portal.
- Wire the LLM reasoning-grading engine to the AI-eval spec (rubric / canonical_solution /
  misconceptions) so it goes live during lessons.
- Upgrade the next lessons to the standard (MATH-038 is the reference implementation).
- Optional: "Preview lesson" link in the admin Lessons table.

## Coordination
- Only ever `git add <explicit files>` — never `git add -A`. Do NOT stage another agent's WIP
  (e.g. `.claude/settings.json`, any in-flight `ClarifyChat*`).
- Shared-file hotspots: `routes/web.php`, `database/seeders/DatabaseSeeder.php`,
  `formynieces-spec/04_FEATURE_INDEX.md`.
