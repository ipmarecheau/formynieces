# Session Handoff — 2026-08-27 · Guardian Bridge (dashboard) rebuild

Durable note so the context window can be cleared. Readable by both Claude agents on this
repo. Delete/replace when the open items below are done.

## Standing state
- Branch `main`. **Tree is DIRTY — this session's work is NOT committed** (held at Isaac's gate).
- Full suite: green (guardian/pace/estimator groups all passing; one `SpecsTraceMvpFilterTest`
  can time out under full-suite load — it passes in isolation, a git-subprocess flake, not code).
- Dev server: `php artisan serve` on :8000 (dev SQLite DB). Prod Docker on :8080 — never touch.
- Review accounts (dev DB, password `Guardian123!`):
  - **`verify-guardian@smoothseas.test`** → child **Amara** (level 5, real progress + seeded
    practice attempts + weekly targets) — the rich account to review.
  - `demo-guardian@smoothseas.test` → thin demo child.

## Built this session (the guardian "honest layer" = Guardian Bridge)
All on the light **editorial brand system** (`welcome.blade.php` tokens: `--ink/--paper/--teal/
--amber`, Fredoka + Nunito), NOT the dark login theme.

1. **Sidebar app** (was one long page). `GuardianDashboard` is a full-page Livewire component with
   `#[Url] $section` (+ `#[Url] $studentId`). Sidebar (in `layouts/guardian.blade.php`) uses
   `wire:navigate`; sections: Overview (summary + jump-in cards), This week, Pace, Progress
   (separate `GuardianProgress` route), Estimator, Rewards & controls. Reconciliation banner stays
   pinned across sections. Spec: **GD-13**.
   - GOTCHA fixed: a block with both `style="…"` and `@style([…])` renders **two** `style` attrs;
     browser keeps the first and drops `display:none`. Always merge into one `@style([...])`.
2. **This week's plan** — topics (WeeklyTarget) + writing prompt + reading assignments.
3. **Pace section** — per-subject bars, trajectory gauge, and a collapsible **year → month → week
   calendar** (Alpine) with a "Jump to this week" button.
4. **Estimator** — `App\Services\Estimator\PerformanceEstimator`: avg score/subject from
   `practice_attempts` + writing, projected SEA composite (50/30/20), indicative placement tier from
   PUBLIC SEA cut-off ranges (NOT fabricated schools; bands configurable), confidence signal.
   Spec: **GD-12**.
5. **Pace-calc BUG FIXED** — `ExamAgentService::analyse()` used hard-coded 2025-26 term constants;
   since dev-today (Aug 2026) is past that exam it clamped to week 36 (revision) → whole syllabus
   "expected" → "behind 88 (=total)". Now anchors on the student's own journey via
   `PacingClock::currentPacingWeek()`; no-journey students still use the global fallback. Spec:
   **GD-15**. Regression: `ExamAgentAnalyseTest`.
6. **Weekly recalculation** — new command `pace:weekly-recalculation` runs `WeeklyRollover` for every
   active student (skips paused), scheduled **Sunday 01:00** in `routes/console.php`. New column
   `student_journeys.pace_recalculated_at` (migration + model cast), stamped in `WeeklyRollover::rePace`.
   Dashboard header shows **"Progress updated <date> · recalculated weekly"**. Spec: **GD-14**.
   NOTE: pace (weeks_behind/pace_status) is the weekly snapshot; the Progress buckets + Estimator are
   still LIVE from actual mastery. Freezing the whole report to a weekly snapshot is a future option
   Isaac deferred.

Key files: `app/Livewire/GuardianDashboard.php`, `resources/views/livewire/guardian-dashboard.blade.php`,
`resources/views/livewire/guardian-progress.blade.php`, `resources/views/layouts/guardian.blade.php`,
`app/Services/ExamAgentService.php`, `app/Services/Estimator/PerformanceEstimator.php`,
`app/Services/Pacing/WeeklyRollover.php`, `app/Console/Commands/WeeklyPaceRecalculation.php`,
`routes/console.php`, `app/Http/Controllers/DashboardController.php` (guardians `/dashboard` →
`/guardian/dashboard`). Docs: `formynieces-spec/features/guardian_dashboard.feature` (GD-12..15).

## ⚠️ Awaiting Isaac's BROWSER verification (not yet done) → then commit
Everything above on dev (:8000) as `verify-guardian@smoothseas.test`. Do NOT `specs:verify` GD-12..15
(or re-verify GD-01..11) until he confirms in the browser. Then commit (`Executed-By: Claude`) + push.

## Also noted
- Other WIP appeared mid-session from a parallel agent (`welcome.blade.php`, `landing_page.feature`,
  `DemoStudentSeeder.php`, `public/reels/`, `scripts/`) — NOT this session's; left untouched.
- `DemoStudentSeeder` re-seeds `demo-guardian`'s password; reset to `Guardian123!` if it stops working.
