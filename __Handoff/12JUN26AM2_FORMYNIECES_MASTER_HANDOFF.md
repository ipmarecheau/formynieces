# ForMyNieces — Master State-of-Record Handoff

**Date:** 12 June 2026
**Version:** 1.0
**Status:** Consolidated. Local environment facts verified live via Laravel Boost MCP on this date.
**Prepared by:** Session with Claude (Anthropic)

---

## 1. Purpose and document lineage

This document consolidates the current state of the ForMyNieces project across all
prior handoffs and records what is verified, what is pending, and what is blocked.
Where this document conflicts with an earlier handoff on **current state**, this
document wins. The spec suite (12 June) remains the authority on **product
behaviour**; the earlier handoffs remain the detailed record for their domains.

| Document | Date | Domain | Status |
|---|---|---|---|
| `08JUN26_FORMY_NIECES_HANDOFF.md` | 08 Jun | Backend stack, schema, models, routes | Superseded on current DB state by §4 below |
| `09JUN26_FORMYNIECES_DASHBOARD_HANDOFF.md` | 09 Jun | Dashboard UX, adventure map, 8 scenarios, 21-screen sitemap | Current for UX |
| `11JUN26_FORMYNIECES_DEPLOYMENT_HANDOFF.md` | 11 Jun | Docker/VPS deployment | Current for infra; blocker still open |
| `13JUN26_FORMYNIECES_ONBOARDING_HANDOFF.md` (11 Jun PM session) | 11 Jun | Onboarding/diagnostic Slices 1–4 | Slice 1 migrations generated, **not yet applied** |
| `12JUN26_FORMYNIECES_SPEC_HANDOFF.md` + `formynieces-spec.zip` | 12 Jun | Spec of record: journeys, OOUX, Gherkin, roadmap | Current; ready to commit to repo |
| **This document** | 12 Jun | Consolidated state of record | Current |

---

## 2. Product summary

ForMyNieces is an SEA (Secondary Entrance Assessment) exam preparation platform
for primary school girls in Trinidad and Tobago. Laravel monolith, personal
project, built on Laravel Herd (Windows) locally and deployed via Docker to a
Linode VPS.

Core design decisions (settled, see 09 June handoff for detail):

- **Week-based adventure map** — each stop is a study week, not a topic cluster.
- **Two-layer model** — motivational navigation (map) separated from honest
  adaptive guidance (AI exam agent detail panel).
- **Writing is a parallel track** outside the module-completion model.
- **Every student must have a verified guardian account** (18+, verified phone
  and email). The term is "guardian," never "parent."
- **Eight student-guardian scenarios** the platform must handle: on-track,
  behind-recoverable, significantly behind, late joiner, ahead-but-uneven,
  disrupted, guardian-disengaged, conflicted-household.
- **Asymmetric paper weighting is always visible** — Mathematics 100%, ELA
  Language 60%, ELA Comprehension 60%, Writing 40%. No single aggregated score
  may mask a subject gap.
- Syllabus resources are **human-vetted**, not AI-generated.
- Pacing: 30-week calendar with a 6-week revision buffer. SEA 2027 expected
  late April / early May 2027 — exact date to be confirmed when MoE publishes.

---

## 3. Local environment (verified 12 June via Boost)

| Component | Version |
|---|---|
| PHP | 8.3 |
| Laravel | 13.14.0 |
| Database | SQLite |
| Filament | 4.11.6 |
| Livewire | 3.8.1 |
| Breeze | 2.4.2 |
| Alpine.js | 3.15.12 |
| Pest | 4.7.2 (PHPUnit 12.5.28) |
| Pint | 1.29.1 |
| Pail | 1.2.7 |
| laravel/mcp | 0.8.1 |
| laravel/boost | 2.4.10 |

Project path: `C:\Users\isaac\Herd\ForMyNieces`
Frontend: Tailwind v4 + daisyUI v5, Fredoka One + Nunito, custom CSS only.

### Filament v4 API reminder
Form components live in `Filament\Forms\Components`; layout components in
`Filament\Schemas\Components`; resource form signature is
`form(Schema $schema): Schema` with `$schema->components([...])`.

---

## 4. Local database state (verified 12 June via Boost)

**Last applied migration:** `2026_06_05_202946_add_previous_score_to_student_progress_table`

Application tables: `users` (with `role`, `parent_id`), `syllabus_modules`
(subject, topic, sea_section, sequence_order, pacing_week, description,
resources), `student_progress` (status, score, previous_score),
`weekly_targets`, plus framework tables (cache, jobs, job_batches, sessions,
failed_jobs, password_reset_tokens, migrations).

**Module inventory (live query):** 90 modules total.

| Subject | SEA section | Count |
|---|---|---|
| Math | Section I | 32 |
| Math | Section II | 15 |
| Math | Section III | 4 |
| English Editing | Section I | 17 |
| English Editing | Section III | 4 |
| English Comprehension | Section II | 18 |

### ⚠️ Slice 1 NOT yet applied locally

The five Slice 1 migrations (`2026_06_13_000001` … `000005`) generated in the
onboarding session have **not** been run. Evidence: subjects are still
"English Editing"/"English Comprehension" (remap to ELA Section I/II pending),
and there are no `module_prerequisites`, `anchor_questions`,
`diagnostic_sessions`, `diagnostic_responses` tables, nor
`onboarding_completed_at` on `users`.

**Next action:** drop the five files into `database/migrations/`, run
`php artisan migrate`, then verify the remap:

```php
App\Models\SyllabusModule::selectRaw('subject, sea_section, count(*) as n')
  ->groupBy('subject','sea_section')->get()
  ->each(fn($r)=>print($r->subject.' '.$r->sea_section.': '.$r->n.PHP_EOL));
```

Expected: only Math and ELA remain. Paste the strand counts back into the next
session — they size the anchor question bank for Slice 2.

---

## 5. Onboarding & diagnostic build (Slices)

| Slice | Content | Status |
|---|---|---|
| 1 | Schema: subject remap, module prerequisites, anchor questions, diagnostic sessions/responses, `onboarding_completed_at` | Migrations generated; **pending apply** (§4) |
| 2 | Anchor question bank (sized from post-remap strand counts) | Not started — blocked on Slice 1 verification |
| 3 | Adaptive diagnostic flow | Not started |
| 4 | Reveal → populated dashboard | Not started |

Rationale: production currently lets a fresh student log in to an **empty
dashboard** (0 mastered, "roadmap being prepared") because no onboarding flow
exists. The onboarding/triage screen is a joint guardian-and-student moment —
both must be present before the map is generated.

---

## 6. Production deployment (Linode VPS)

| Property | Value |
|---|---|
| Provider | Akamai/Linode |
| IP / URL | `http://172.233.163.6:8080` |
| OS | Ubuntu 22.04.5 LTS |
| App | Docker container `formynieces`, repo at `/opt/formynieces` |
| Persistent data | `/opt/formynieces-data/storage/` (DB is **not** persisted — see blockers) |
| Co-tenants (do not touch) | Kavita :5000, Calibre server :8083, Calibre-Web :8081 |

Deploy workflow: push to `main`, then on VPS `cd /opt/formynieces && ./deploy.sh`.

### Open production blockers (Phase 0 gate)

- [ ] **SQLite `job_batches` clean rebuild** — Dockerfile fix committed approach:
      remove `touch database/database.sqlite` from build, run migrations only at
      deploy time; requires full `docker rmi` rebuild. Verify app loads after.
- [ ] **Database persistence/backup** — SQLite lives inside the container and is
      wiped on every redeploy. Hard gate: **no real child data** before this and
      HTTPS are solved.
- [ ] 69 pending apt updates incl. 5 security updates.
- [ ] Domain + HTTPS (Caddy or Certbot once domain obtained).
- [ ] GitHub Actions deploy on push (`.github/workflows/deploy.yml`).
- [ ] Seed and verify test users on production.

---

## 7. Spec suite (12 June) — spec of record

`formynieces-spec.zip`: 6 docs + 11 Gherkin feature files, all passing the
`gherkin-official` lint (G*W+T+, ≥1 When and ≥1 Then per scenario).

Derivation chain: GOAL → USER JOURNEYS → CADENCE NARRATIVES → OBJECT MODEL
(OOUX) → SCREENS + NAVIGATION → GHERKIN → ROADMAP.

To do: commit suite to repo at `specs/` + `specs/features/`; add lint step to CI;
reconcile doc 03 screen inventory against the 21-screen sitemap from 09 June
(B7 resume added; C8/D4–D7 deferred).

Open spec questions: `in_review` status semantics; weekly rollover cap
(suggested max 6 modules/week); mastery threshold for module quizzes;
`writing_submissions` migration design (Phase 1).

---

## 8. Roadmap

| Phase | Window | Theme | Hard gates |
|---|---|---|---|
| 0 | now → end Jun 2026 | Stabilise: job_batches fix, DB persistence/backup, security updates, domain+HTTPS, CI, apply Slice 1 | No real child data before persistence + HTTPS |
| 1 | Jul 2026 | MVP: Slices 2–4, writing track, rollover job, guardian dashboard, streaks → 2-week pilot with the nieces | All `@mvp` scenarios pass |
| 2 | Aug 2026 | v1.1: Writing modules, digest, pause/resume, retake, decay, Filament anchor UI, phone verify | |
| 3 | Sep–Dec 2026 | Exam readiness: fill-in Math input, timed mocks, ELA Section II depth, adaptive v2 | New-content learning starts by ~late Sep for full 24+6 weeks |
| 4 | Jan–May 2027 | Revision buffer mode, exam-week state, post-exam summary, scenario S8, scale decision | |

---

## 9. NEW — Laravel Boost MCP connected to Claude Desktop (12 June)

Claude Desktop can now query the local app directly: application info, database
schema, read-only SQL, log/error reading, and version-scoped documentation
search (Laravel/Filament/Livewire/Tailwind docs matched to installed versions).

Working `claude_desktop_config.json` entry:

```json
{
  "mcpServers": {
    "laravel-boost": {
      "command": "C:\\Users\\isaac\\.config\\herd\\bin\\php.bat",
      "args": [
        "C:\\Users\\isaac\\Herd\\ForMyNieces\\artisan",
        "boost:mcp"
      ]
    }
  }
}
```

Troubleshooting record (for future MCP setups):

1. Relative `artisan` fails (`Could not open input file`) — Claude Desktop has
   no project cwd. **Use the absolute artisan path.**
2. `mcp:start` without a handle dies with "Not enough arguments," which pollutes
   the JSON-RPC stream (`Unexpected token 'N'`). **Boost's command is
   `boost:mcp`.**
3. Config changes require a **full quit** of Claude Desktop from the system
   tray, not a window close.
4. Manual smoke test: run the command, paste a JSON-RPC `initialize` line, expect
   a `serverInfo` response. Stray Enter presses produce harmless `-32700` parse
   errors.

Implication for future sessions: before writing Filament/Livewire code, query
Boost's `search-docs` (version-scoped) and `database-schema` rather than relying
on general knowledge — Filament 4 and Tailwind 4 are recent enough that this
matters.

---

## 10. Immediate next actions (ordered)

1. Apply the five Slice 1 migrations locally; run the tinker verification; paste
   strand counts into the next session (unblocks Slice 2 sizing).
2. Resolve the production `job_batches` blocker (Dockerfile fix + clean rebuild)
   and verify the app loads at `http://172.233.163.6:8080`.
3. Decide and implement the SQLite persistence strategy (volume mount with init,
   or backup cron) — Phase 0 hard gate.
4. Run VPS security updates.
5. Commit the spec suite to the repo and add the Gherkin lint to CI.
6. Begin Slice 2 (anchor question bank) once Slice 1 is verified.

---

## 11. Reference

- Local project: `C:\Users\isaac\Herd\ForMyNieces`
- Production: `http://172.233.163.6:8080` (`/opt/formynieces` on VPS)
- Prior handoffs and `formynieces-spec.zip` as listed in §1.
- Exam anchor: SEA 2027, late Apr / early May 2027 (confirm with MoE; recompute
  30+6-week calendar from the published date).
