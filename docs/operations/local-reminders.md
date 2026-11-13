# Local reminders (on-device)

Local reminders nudge a child through the day's voyage from **their own phone** — no server,
no internet, and **no Apple or Google account**. They are the on-device alternative to push:
they cover "do today's work" entirely on their own, needing only the local-notifications
plugin compiled into the Android app.

Specified by **`local_reminders.feature`** (scenarios `LR-01`–`LR-07`).

!!! note "Status"
    **Server + spec layer built and tested.** The native plugin and the JS bridge (the client
    half) are a separate step that needs an APK rebuild — see *What's left* below. Until then
    the reminder plan is fully computed and served; nothing yet schedules it on a device.

## The four reminders

| Reminder | Time | Days | Conditional? |
| --- | --- | --- | --- |
| Morning Muster — orders ready | 15:30 | every day | no |
| Writing Mission | 16:00 | Mon / Wed / Fri (writing days) | no |
| Outstanding Tasks — evening nudge | 19:00 | every day | **yes** — only if the day is unfinished |
| Streak-Saver | 20:30 | every day | **yes** — only if the daily minimum is unmet |

Writing days come from the single source of truth, `DailyPlanComposer::WRITING_DAYS` (`[1,3,5]`).

## Why "conditional" matters

A local notification is scheduled **ahead of time with fixed words** — it cannot check server
state at fire time. So the two conditional reminders are handled two ways:

1. **Not scheduled for a day already finished.** When the plan is built, today's
   Outstanding-Tasks and Streak-Saver are skipped if today's `DailyPlan` already meets the
   minimum (`LR-04`, `LR-05`).
2. **Cancelled on completion.** When the child completes the day, the app cancels today's
   conditional reminders on-device — so a child who has done their work is never nagged.

The predictable reminders (Morning Muster, Writing Mission) are always scheduled.

## Architecture

```mermaid
flowchart LR
  subgraph app[Our Laravel app]
    B[ReminderScheduleBuilder\nnext 7 days from the real plan + guardian controls]
    F[GET /reminders/feed\nself-contained JSON]
    B --> F
  end
  F -->|on launch| Bridge[Native JS bridge\nCapacitor.isNativePlatform()]
  Bridge -->|schedule / replace| LN[@capacitor/local-notifications\non the device]
  LN --> Phone[Fires offline, app closed]
```

- **`ReminderScheduleBuilder`** — pure computation. Given a child and a date range, it emits
  the next ~7 days of `ScheduledReminder`s, honouring writing days, the conditional rules,
  the guardian's `reminders_opt_out`, and the child's quiet hours. No gateway, no device —
  fully unit-tested.
- **`ScheduledReminder`** — a self-contained value object (type, fire time, title, body,
  conditional) with a **stable integer id per (type, day)** so re-scheduling replaces rather
  than duplicates (`LR-01`).
- **`GET /reminders/feed`** (auth) — returns the plan as JSON for the bridge to write to the
  device. Each item carries its own time and words so it fires with the app closed and no
  server call at fire time (`LR-07`).

## Guardian controls (`LR-06`)

- **`users.reminders_opt_out`** — a guardian switch that silences all reminders for a child.
- **Quiet hours** reuse `users.quiet_hours_start` / `quiet_hours_end` (shared with push). No
  reminder is ever placed inside the window; the midnight-wrap maths lives on
  `User::isWithinQuietHours()`.

## Tests

`tests/Feature/LocalRemindersTest.php` — one Pest test per scenario, tagged
`@group scenario:LR-0x`, all green. The builder is pure, so the suite runs fully offline.

## What's left (the native half)

1. **Install `@capacitor/local-notifications`** in the `capacitor-shell` worktree (both apps)
   and add the Android `POST_NOTIFICATIONS` permission — requires an **APK rebuild**.
2. **JS bridge** in the Laravel app (deploys like any web change, gated behind
   `Capacitor.isNativePlatform()`): on launch it reads `/reminders/feed`, cancels the prior
   set, and schedules the new one; on day-completion it cancels today's conditional reminders.
3. **Dev-DB migration** for `reminders_opt_out` (needs approval — shared DB).
4. **Device verification** → `specs:verify` for `LR-01`–`LR-07`.
