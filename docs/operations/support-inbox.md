# In-app help & support inbox

The in-app help channel lets **any** user — a parent on the dashboard, a child on the
voyage, or a signed-out visitor — report a problem the moment they hit it, without leaving
the page. It exists to catch friction and complaints early and fix them fast.

Specified by **`in_app_help.feature`** (scenarios `HC-01`–`HC-05`).

## What the user sees

A floating **Help** button sits bottom-left on every screen (bottom-left so it never
collides with the guest sales-chat bubble on the right). Opening it reveals a small panel
where the user can:

- pick what kind of thing it is — *Something's wrong* / *Question* / *Idea*;
- **type** a message; and/or
- **record a voice note** (via the browser `MediaRecorder` API) — important for a young
  child who cannot yet type their problem;
- send. They are thanked immediately.

The control is mobile-first (thumb-reachable, native text + microphone input, no hover
dependency) so it behaves identically once wrapped with Capacitor.

It is rendered from `resources/views/partials/help-button.blade.php` and included in all
four primary layouts:

| Layout | Who |
| --- | --- |
| `components/layouts/diagnostic` | student (voyage / learning loop) |
| `layouts/guardian` | parent |
| `layouts/app` | account pages |
| `layouts/public` (authed only) | public site |

## What is captured

Each submission creates a **`support_tickets`** row via `POST /help/ticket`
(`SupportTicketController`, throttled 20/min):

- `user_id` + `role` snapshot (`student` / `guardian` / `admin` / `guest`);
- `student_id` — the child in context, when there is one;
- `kind`, `message`, and/or `audio_path`;
- `url`, `user_agent`, and a `context` JSON (path, viewport);
- `status` — `open` → `triaged` → `resolved`, plus `admin_notes`.

### Voice notes are private

Voice notes are stored on the **private `local` disk** under `support-audio/`. They are
**never** publicly reachable — only the team, through the admin panel, can play them.
Transcription is **on demand** from the admin panel (so speech-to-text cost is only spent
on the notes worth reading), not automatic.

## Triage

Tickets are reviewed and resolved in the Filament admin panel — the **Help inbox**
(`SupportTicketsResource`, under *Website*). The list shows status, kind, who/where and the
child in context; the nav badge counts open tickets. Row actions move a ticket **open →
triaged → resolved** (resolve captures optional resolution notes), and **Play** streams a
voice note. A ticket with enough context — role, page, child — can usually be acted on
without a round-trip to the family.

Voice notes are served only through `GET /admin/support/{ticket}/audio`, gated `role:admin`,
straight off the private disk — they are never publicly reachable.

## Tests

`tests/Feature/SupportTicketTest.php` — the capture side, one Pest test per scenario, tagged
`@group scenario:HC-0x`. `tests/Feature/SupportTicketAdminTest.php` — the admin side (HC-05):
listing, triage, resolve-with-notes, and admin-only voice-note streaming. So
`php artisan specs:trace` shows each `HC` scenario mapped to its tests.
