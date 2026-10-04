# Runbooks

Operational procedures for production incidents. **Golden rule: act through the app or idempotent
commands; never hand-run destructive SQL on prod without a fresh backup.**

## Restore a student's lost progress

Learner progress lives in `student_progress` / `module_stage_completions`, which **cascade-delete**
with their module. If progress is lost:

1. **Back up live prod first:** `sqlite3 /opt/formynieces-data/db/database.sqlite ".backup '/opt/formynieces-backups/database_prerestore_$(date +%Y%m%d_%H%M%S).sqlite'"`
2. Find the last good backup in `/opt/formynieces-backups/` (4-hourly).
3. **Merge-restore** the affected students from the backup with `INSERT … SELECT … WHERE NOT EXISTS`
   keyed on each table's unique key, so rows added since are preserved. (Do not overwrite the whole DB.)
4. Verify per-student counts against the backup, and check the **Voyage island gate**: a level shows as
   the "current" stop only if every earlier level is `mastered` — a stray `needs_work` row locks
   everything after it.

## "More practice coming soon" / no morning reading

Both are usually **content-pool starvation**, not bugs — the no-repeat rule exhausted the pool.

- **Reading:** the pool must hold ≥30 passages per level. If a student has seen them all, `serve()`
  returns null. Fix by seeding more: `docker exec formynieces php artisan db:seed --class=ReadingPassageSeeder --force` (idempotent).
- **Practice:** ensure the module has ≥15 active questions per rung. Check tutorial exposures aren't
  gating practice (they shouldn't — context-scoped no-repeat).

Audit any time: `docker exec formynieces php artisan content:coverage`.

## The db:seed wipe (root cause, resolved)

Three progress wipes (Sep 2026) traced to `deploy.sh` running `php artisan db:seed --force`, whose
`SyllabusModuleSeeder` called `SyllabusModule::truncate()` → cascade-wiped all `student_progress`.
**Resolved:** seeder is now `updateOrCreate` (no truncate); `db:seed` removed from `deploy.sh`; content
is seeded manually per-class. **Never run the bare `db:seed` on production.**

## Guardian dashboard 500s

If a guardian's dashboard errors after a child has a scored essay, check blade echoes of array-cast
fields (e.g. `writingFeedback['did_well']` is an array — must be `implode`'d, not echoed raw). Reproduce
on **staging** against the sanitized copy, never by poking prod.

## General: reproduce before you fix

Use **staging** (`https://staging.smoothseas.org`) — same code as prod, realistic sanitized data, safe
to click through and log into as any family (`staging-walkthrough`). This is the right place to diagnose
live issues. See [Environments](environments.md).

## Backups

- Automatic: `/opt/backup-formynieces.sh` every 4 hours, 14-day retention, WAL-safe.
- Before any risky operation, take a manual `.backup` snapshot (see step 1 above).
