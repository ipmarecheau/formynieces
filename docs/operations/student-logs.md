# Per-student activity log

A read-only, per-child **timeline** in the admin panel (*Website → Student Logs*). Pick a
student and see their recent journey newest-first, so the team can spot a struggling child
early without digging through tables.

Specified by **`student_activity_log.feature`** (scenarios `AL-01`–`AL-03`).

## What it shows

Composed by `App\Services\Admin\StudentActivityLog::forStudent()` from records the app
**already keeps** — it adds no new tracking:

| Source | Event |
| --- | --- |
| `practice_attempts` | Practice · topic · level · attempt — correct / incorrect |
| `student_progress` (mastered) | Mastered <topic> 🏆 |
| `writing_submissions` | Essay submitted / marked ✍️ |
| `daily_plans` (completed) | Completed the day's quota ⚓ |
| `support_tickets` | Raised a help issue/question/idea |

Each `ActivityEvent` carries a time, a one-line summary, and a tone (positive / neutral /
negative) that colours the timeline dot.

## Guarantees

- **Read-only.** Building the log writes nothing (`AL-03`, asserted by a query-log test).
- **Admin-only.** It is a page on the Filament panel, gated by `User::canAccessPanel()`
  (admins only).
- **Newest first**, capped at 60 events per child.

## Tests

`tests/Feature/StudentActivityLogTest.php` — ordering, span across all five sources, the
no-writes guarantee, the admin page render, and panel access gating. Tagged
`@group scenario:AL-0x`.
