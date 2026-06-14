# 02 — Object Model (OOUX)

Derived from: 01 (every bold noun in the narratives).
Feeds: 03 (each core object earns a collection view and/or detail view; verbs become CTAs).
Mapped against the **existing schema** verified 13 June 2026 — new columns/tables are flagged.

Legend: ✅ exists today · 🔧 exists, needs change · 🆕 new

---

## 1. Core objects

### Student 👧 ✅
*Backed by:* `users` (role=student, parent_id→guardian, onboarding_completed_at 🆕 Slice 1)

| Attributes | Verbs (CTAs) | Relationships |
|---|---|---|
| name, target SEA year 🆕, login | log in, take diagnostic, view map, open module, take quiz, submit writing, view streak | belongs to Guardian; has many ProgressRecords, WeeklyTargets, DiagnosticSessions, WritingSubmissions |

🆕 needed: `target_sea_year` (or on a student_profile); `known_weak_areas` 🆕 (nullable JSON, guardian-supplied at child setup) — consumed by the reveal's reconciliation against diagnostic findings (see roadmap_reveal.feature), not just stored; pause state for S6 (`@v1.1`).

### Guardian 🧑 ✅
*Backed by:* `users` (role=guardian/parent)

| Attributes | Verbs | Relationships |
|---|---|---|
| name, email, email_verified_at ✅, phone 🆕(`@v1.1`), is 18+ attestation 🆕 | register, verify, create child, view child dashboard, pause/resume child (`@v1.1`), invite second guardian (`@roadmap`) | has many Students; has one weekly Digest (`@v1.1`) |

### SyllabusModule 📚 ✅ (the atom of the curriculum — 90 exist)
*Backed by:* `syllabus_modules` (subject, topic, sea_section, sequence_order, pacing_week, description, resources)

| Attributes | Verbs | Relationships |
|---|---|---|
| subject (Math / ELA), section, strand, pacing week, description, vetted resources | open lesson, start quiz | has many Prerequisites 🆕 (Slice 1), AnchorQuestions 🆕, ProgressRecords |

🔧 Writing subject has **no modules yet** — must be authored before the writing diagnostic path is meaningful (tracked in ROADMAP Phase 2).

### ProgressRecord 📈 ✅
*Backed by:* `student_progress` (status, score, previous_score)

| Attributes | Verbs (system) | Notes |
|---|---|---|
| status ∈ {mastered, diagnostic_passed, not_started} ✅ + `in_review` 🔧 proposed | seed from diagnostic, update on quiz, demote to review | One per student×module (90 rows seeded at reveal). The dashboard's mastered / in-review / upcoming buckets read `status`. |

### Week / AdventureMapStop 🗺️ (virtual — derived, not a table)
Computed from `pacing_week` × ProgressRecords × WeeklyTargets. States: completed · current · upcoming · locked · revision (buffer).

### WeeklyTarget 🎯 ✅
*Backed by:* `weekly_targets` (student_id, module_id, week_start_date, is_completed)

| Verbs | Notes |
|---|---|
| generate (system, Sunday rollover), complete (on mastery), roll forward (capped) | First target written at reveal (Slice 3). Rollover cap is a named constant (suggest: max 6 modules/week). |

### DiagnosticSession 🧭 🆕 (Slice 1)
sessions + responses + writing_sample. Verbs: start, answer, resume, complete, (retake `@v1.1`). Adaptive walk + conservative propagation per the 13 June handoff.

### AnchorQuestion ❓ 🆕 (Slice 1)
prompt, options(json), correct_index, difficulty, strand, distractor_notes. Admin verbs: author, deactivate (Filament, `@v1.1` UI; seeding is `@mvp`).

### WritingSubmission ✍️ 🆕
*Proposed table:* `writing_submissions` (student_id, prompt_id/week, body, rubric json {content, language_use, grammar_mechanics, organisation}, feedback text, created_at)

| Verbs | Notes |
|---|---|
| submit, view feedback, view history/profile | Scored by Groq via ExamAgentService. **Never** produces mastered/not — parallel track. Diagnostic writing sample currently lives on diagnostic_sessions (json); steady-state submissions need this table (`@mvp`). |

### ExamAgentInsight 🤖 (computed, optionally cached)
Honest layer: pace vs 30-week calendar, weighted readiness (50/30/20), next-week recommendation, weak strands. Groq `generateSummary()`. Cache per student×week to respect free-tier limits (30 req/min, 14.4k/day).

### Streak 🔥 (derived or small table)
current_days, best. Motivational layer only — never shown to guardian as a judgement metric.

### Digest 📬 🆕 `@v1.1`
Weekly guardian email. Needs: notifications table or mail log + scheduled job.

---

## 2. Object relationship sketch

```
Guardian 1──* Student 1──* ProgressRecord *──1 SyllabusModule 1──* AnchorQuestion
                │                                   │ *
                ├──* WeeklyTarget *──1 ─────────────┘ │
                ├──* DiagnosticSession 1──* DiagnosticResponse *──1 AnchorQuestion
                ├──* WritingSubmission                │
                └── ExamAgentInsight (computed)       └──* Prerequisite (self-join)
```

## 3. Schema deltas implied by this spec (beyond Slice 1)

| Change | Priority |
|---|---|
| `writing_submissions` table | `@mvp` |
| `student_progress.status` add `in_review` (or equivalent) | `@mvp` (decision needed) |
| student `target_sea_year` | `@mvp` |
| guardian phone + verification fields | `@v1.1` |
| pause/resume fields (paused_at, resumed weeks offset) | `@v1.1` (S6) |
| second-guardian link table (read-only role) | `@roadmap` (S8) |
| notifications/digest log | `@v1.1` (S7) |
| student `known_weak_areas` (nullable JSON, guardian hint, reconciled at reveal) | `@mvp` |