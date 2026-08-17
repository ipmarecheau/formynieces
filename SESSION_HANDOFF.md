# Session Handoff — 2026-08-17 · Captain's Orders redesign + Morning Tide (reading/vocab)

Durable note so the context window can be cleared. Written by the orchestrator session that
built the daily-ritual redesign. Delete when the open items below are done.

## Standing state
- Branch `main`. **HEAD `53e11e1`. 10 commits ahead of `origin/main`, NOT pushed** (Isaac's call —
  push auto-deploys to prod via GitHub Actions). Nothing from this work is on prod yet.
- Full suite: **492 passing** (`./vendor/bin/pest`).
- Dev server: persistent `php artisan serve` on :8000 (SSH-tunnel browser-check target), dev SQLite DB.
- Demo student **Aaliyah** (`aaliyah@students.formynieces.com` / `formynieces`), `reading_level` 5, just
  reset for a fresh `/morning-tide` walk. **5 level-5 reading passages seeded in the DEV DB only**
  (via tinker — NOT a seeder yet; a `migrate:fresh` loses them → build `ReadingPassageSeeder`, task 13).

## ⚠️ CONCURRENCY — another agent is live on this same tree
Another LLM agent is building the **student paper upload / school_journal** feature RIGHT NOW.
Its uncommitted WIP is in the tree: `app/Models/SchoolStrandSignal.php`, `app/Services/SchoolJournal/`,
`database/migrations/2026_08_15_160000_create_school_strand_signals_add_focus_hint.php`, and `M config/services.php`.
**Do NOT edit any school_journal files, `SchoolJournalEntry`, or that migration.** Shared-file hotspots to
coordinate on: `routes/web.php`, `database/seeders/DatabaseSeeder.php`, `formynieces-spec/04_FEATURE_INDEX.md`.
Only ever `git add <explicit files>` — never `git add -A` — so you don't sweep the other agent's WIP.

## Shipped this session (committed, unpushed) — `origin/main..main`
```
5089e2f docs(specs): daily reading scoring + pace, vocab-in-context, school journal OCR respec
63b0088 chore(daily-ritual): data foundation — reading pool, assignments, vocab, reviews, school journal
8d7dd91 feat(daily-reading): serve/score/pace/adapt engine (DR-01/04/07/08/09)
522ca86 feat(daily-vocabulary): from-passage words + spaced-repetition engine (DV-01/03/05)
b79885e feat(morning-tide): reading+vocab as one ritual, merged daily duty (DR/DV)
6c6adc1 fix(morning-tide): clear selection/text between questions and words
99ecaf9 feat(morning-tide): choose-2 vocab with mastery-rotate, one re-read, encouraging-only
03be710 feat(morning-tide): honest comprehension + word-usage breakdown on the done screen
d21124c docs(specs): roadmap essay & word banks (curated, syllabus-aligned content)
53e11e1 feat(morning-tide): LLM comprehension scoring + word examples, with fallback
```
Earlier this session (already on origin, pushed): the whole Captain's Orders sidebar + streak economy
foundation + spec redesign — commits `cdf8a4e`, `e4fffcb`, `024a0d6`, `0e064c2`, `dd06c7a`, `2e80bc4`,
`97edb53`, `7158e95`, ledger `11adbc8`.

## Design decisions LOCKED this session
- **Captain's Orders** = collapsible sidebar on the Voyage (home only), FOUR tabs: **Orders / Locker /
  Journal / Logs**. Taller rail with full "Captain's Orders" label + edge arrow toggle; unroll animation.
- **Daily minimum** = **Morning Tide** (reading+vocab merged into ONE duty) + **map** progress +
  **writing** (Mon/Wed/Fri only). One minimum, morning+evening check-ins. Weekends: rest when on pace,
  bounded kind catch-up when behind. Never shows the child pace/percentage/deficit numbers.
- **Streaks**: master **Voyage** streak + per-thread sub-streaks (reuses `student_streaks`/`StreakService`).
  **Pace = weekly_targets**. 3-day starter grace. Rewards: **Shore Leave / Anchor / Tailwind / Lifebuoy**
  (Captain's Locker), earned 4 ways. Cosmetics track = @roadmap. Streak-economy ENGINE (freeze/accelerate/
  revive/reset) NOT yet built — only SE-01/13/14/15 done (grant/spend/master-streak).
- **Morning Tide ritual** (`/morning-tide`, `MorningTide` Livewire): read → comprehension (1 re-read) →
  **pick 2 words** (master-and-rotate at 3 successful uses) → build a sentence each → **2 examples shown
  per word** → **honest breakdown** (per-question ✓/✗ + best answer; per-word usage) + **encouraging,
  score-scaled** message (0% never says "well sailed").
- **Comprehension**: scored **LLM-first** (weighs written answer + MCs, returns summary) with a
  **deterministic MC-auto-grade fallback**; score kept, tracked toward **95%** + **reading pace (WPM)**;
  perks not a gate; reading is `essential: false` (respects the discretionary LLM budget).
- **Vocab**: choose 2 words/day, spaced repetition, master-and-rotate; LLM example sentences with the
  authored context sentence as fallback.
- **The Diary** (Isaac's ask): BOTH a **student view** (in the Ship's Log, to motivate) AND a **guardian
  dashboard view** (to measure). **NOT built yet (increment 3).** The data is already persisted:
  `daily_reading_assignments.{answers, vocab_sentences, comprehension_score, comprehension_feedback}`.
- **essay_word_bank** = @roadmap (curated, syllabus-aligned essays + words; LLM carries the load for now).
- **school_journal** respecced to student/guardian upload + OCR/CNN (`OcrService` seam) + understanding
  corroboration → **being built by the OTHER agent**.

## Where the code lives
- Services: `app/Services/Motivation/{StreakService, StreakEconomyService, DailyPlanComposer}`,
  `app/Services/Reading/{DailyReadingService, VocabularyService, ComprehensionScorer}`.
- Livewire: `app/Livewire/{CaptainsOrders, MorningTide}`; views `resources/views/livewire/{captains-orders,
  morning-tide}.blade.php`. Sidebar embedded in `resources/views/voyage/overworld.blade.php`.
- Tables added: `daily_plans`, `streak_rewards`, `reading_passages`, `vocabulary_words`,
  `daily_reading_assignments` (+score/feedback/vocab_sentences/wpm), `vocabulary_reviews`; `users.reading_level`.
  (`school_journal_entries` exists but SchoolJournal logic is the other agent's.)
- LLM: `app/Services/LlmService` — `completeJson()` returns `[]` on ANY failure (budget/outage/bad JSON),
  so the fallback pattern is just "did I get valid keys back?". Fake it in tests (see below).

## Verified in the ledger (specs:verify, commit 11adbc8)
CO-01/02/03/06/07/08/09/10/11, SL-01/02/03/05/06, SE-01/13/14/15. The DR/DV/Morning-Tide scenarios are
GREEN in Pest but **not yet specs:verified** — that's Isaac's pending browser-verification phase.

## Remaining work (open)
1. **Increment 3 — the Diary**: student Ship's Log view + guardian dashboard view over the persisted
   reading/vocab records (answers, sentences, scores, feedback).
2. **Task 7 — streak_economy engine**: SE-02..12 (pace-gating, Anchor freeze, Tailwind/accelerate,
   Lifebuoy revive, starter grace, kind reset).
3. **Task 13 — admin reading-pool authoring (E6) + `ReadingPassageSeeder`** (make the dev pool durable +
   reachable on prod; currently tinker-seeded only).
4. **Task 12 — school_journal upload + OCR** → OTHER AGENT.
5. Loose ends: CO-05 map writing-gate actual enforcement; SL-04/07; `writing_track` M/W/F re-verify.

## Gotchas discovered this session
- **Livewire DOM reuse**: repeated inputs (radios/textareas across questions/words) keep stale state unless
  each has a unique `wire:key` — that was the "sticky selection" bug.
- **assertSee escapes apostrophes** (`&#039;`) — assert apostrophe-free needles.
- **assertDontSee matches CSS class names AND comments** — `space-between` contains "pace"; a `/* This
  week's mission */` comment tripped `assertDontSee('This week')`. Use specific needles / rename comments.
- **`LlmService::completeJson` never throws** (returns `[]`) — no try/catch needed; check keys. In tests
  `$this->mock(LlmService::class, fn($m)=>$m->shouldReceive('completeJson')->andReturn([...]))` to avoid real HTTP.
- **Never `migrate:fresh`** on the shared dev DB (wipes seeded passages + everyone's work) — reset per-student.
- **specs:verify** refuses on any uncommitted change (an untracked `SESSION_HANDOFF.md` blocks it) — clean tree first.

## To push (Isaac's call)
`git push origin main` sends the 10 commits `5089e2f..53e11e1` and **auto-deploys to prod**. Held pending
Isaac's go-ahead and coordination with the other agent's in-flight school_journal work.
