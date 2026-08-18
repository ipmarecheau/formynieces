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
*Backed by:* `syllabus_modules` (`code` 🆕, subject, topic, sea_section, sequence_order, pacing_week, description, resources)

| Attributes | Verbs | Relationships |
|---|---|---|
| **code** (stable short id — MATH-001…051, ELA-001…039), subject (Math / ELA), section, strand, pacing week, description, vetted resources | open lesson, start quiz | has many Prerequisites 🆕 (Slice 1), AnchorQuestions 🆕, ProgressRecords, one Lesson |

🆕 `code` — a stable, human-readable module key (per subject, in sequence order), the identifier **lesson imports bind to** (survives topic renames). Backfilled by migration + assigned by the seeder.

🔧 Writing subject has **no modules yet** — must be authored before the writing diagnostic path is meaningful (tracked in ROADMAP Phase 2).

### Lesson 📖 ✅ (the interactive teaching page — one per module; LE-01…10 BUILT)
*Backed by:* `lessons` (module_id, title, `blocks` JSON, is_published) — authored in advance, never generated in real time. Authored in Filament via the **LessonResource** Builder (LE-05).

| Attributes | Verbs | Relationships |
|---|---|---|
| module_id, title, ordered `blocks`, is_published | author (admin, Builder), open, walk through (unscored), ask **clarify chat** (LLM tutor — explains only, never scores) | belongs to one SyllabusModule; part of the **gated sequence** lesson → worked examples → practice (`LE-03`/`LE-06`, see ModuleStageCompletion) |

**Lesson block format (single authority `App\Support\LessonBlockSchema`):** a lesson is `title` + an ordered `blocks` array; each block is FLAT `{type, …fields}`. Explanation/media types: `heading` · `text` · `key` · `example` (worked, with `steps`) · `visual` (image). Interactive (gating) types — she must answer correctly to advance: `check` (MC), `fillblank` (LE-07), `markwords` (tap targets, LE-08), `matchpairs` (LE-09), `ordersteps` (LE-10). Interactive blocks also carry re-teach content 🆕 (LL-24): `rule` (one-sentence rule, kid words) + `practiceItems[]` of `{prompt, answer}` (same-rule words Smooth remediates with). `LessonBlockSchema` defines each type's required/optional fields + validation; the importer, the authoring Builder, and the renderer (`LessonWalk`) all share it. `App\Support\LessonTemplate::scaffold()` still gives a starter skeleton.

### ModuleStageCompletion 🔒 ✅ (the learning gate — LE-03/LE-06)
*Backed by:* `module_stage_completions` (student_id, module_id, `stage` ∈ {lesson, tutorial}, completed_at; unique per student×module×stage). Permanent "done once" markers. `App\Services\Practice\LearningGate` reads them: the sequence **lesson → worked examples → practice** locks each stage until the prior is done, and only applies to a module that has BOTH a published lesson AND ≥1 D1 practice question. A locked stage tapped (or its link opened) sends her back to the module entry with a child-friendly message; unlock is permanent.

### ReteachSession 🔁 🆕 (an AI-assisted re-teach in progress — LL-14/15/16/22/24..27)
*Backed by:* `reteach_sessions` (student_id, module_id, `trigger` ∈ {streak, window}, correct_count, started_at, completed_at). Opened when practice pulls her back (2 hard misses in a row, or 5 of 7). `App\Services\Practice\Remediation` owns trigger detection + lifecycle; the re-walked lesson (`LessonWalk`) + Smooth's chat (`ClarifyChat`) drive the structured, **same-rule** remediation (from the block's `rule`/`practiceItems`), and the D1 proof (`ReteachWalk`, 3 correct → resume solo at D3) completes it. 🆕 A block missed through **three remediation cycles** leaves the lesson **"in progress"** for her (she may move on; it returns daily until done — resurfacing, plus parent notification + worksheet, are **Phase 2 / roadmap**).

### StudentLlmUsage 🪙 🆕 (the per-student monthly LLM budget ledger — AG-01..04)
*Backed by (proposed):* `student_llm_usage` (student_id, period `YYYY-MM`, input_tokens, output_tokens, cost_usd; unique per student+period).

| Attributes | Verbs (system) | Notes |
|---|---|---|
| month-to-date input/output tokens + accumulated cost_usd | check budget BEFORE a call, record real usage AFTER | Two thresholds: **USD 1.00** discretionary stop (clarify chat, re-teach, worked examples) · **USD 1.50** hard ceiling (essential too: essay grading, guardian summaries). Cost from the provider's returned usage (OpenRouter `usage.cost`, else tokens × config price). Feeds the admin AI-usage panel. |

### GuidedTime ⏳ 🆕 (the 2-hour daily active-time pool — AG-05..07)
*Backed by (proposed):* `student_guided_time` (student_id, day, active_seconds; unique per student+day).

| Attributes | Verbs (system) | Notes |
|---|---|---|
| active seconds spent today on guided, LLM-tailored activities | accrue on active engagement, reset daily | Counts lessons, tutorials, clarify chat, re-teach — never practice (the MC climb is unlimited, no LLM). At 2h, guided activities lock kindly for the day; practice stays open. |

### LearningProfile 🧭 🆕 (compact per-student tailoring signal — AG-08)
*Backed by (proposed):* a small `learning_profile` JSON on the student (derived tags — weak strands, misconceptions, style), never chat transcripts, never PII. Injected into AI tutor prompts so guidance stays personal across ephemeral sessions.

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

### ReadingPassage 📰 🆕 (the authored daily-reading atom — DR)
*Backed by (proposed):* `reading_passages` (title, body, reading_level, comprehension questions json) — authored or imported in advance, keyed by reading level, never generated in real time. Comprehension questions mix literal recall, inference, and one short written-response prompt.

| Attributes | Verbs | Relationships |
|---|---|---|
| title, body, reading_level, comprehension questions, marked vocabulary words | admin: author/import, retire · system: serve one unseen passage matched to level | has many VocabularyWords; serves into DailyReadingAssignment |

### DailyReadingAssignment 📖 🆕 (the per-student per-morning served passage — DR)
*Backed by (proposed):* `daily_reading_assignments` (student_id, passage_id, date, answers json, resume/position state, started_at, completed_at, `comprehension_score` 0–100, `words_per_minute`) — one per student per day; resumable mid-commute. Comprehension IS scored and kept (DR-07); reading pace = passage word count over the started→completed reading time (DR-08).

| Attributes | Verbs (student/system) | Notes |
|---|---|---|
| date, passage, answers, resume position, comprehension_score, words_per_minute, started_at, completed_at | serve (matched to level), answer, resume, score, complete | Difficulty tracks her reading level (DR-04). Completion advances the daily **Streak**; the running comprehension average is tracked toward a **95% goal** and pace against an **age band** (DR-08) — a kind signal that earns perks, never a gate or a grade shown to the child (DR-03/09). Never serves a seen passage. Sized to ~10–15 min (DR-10). |

### VocabularyWord 🔤 🆕 (a word drawn from a passage — DV)
*Backed by (proposed):* `vocabulary_words` (passage_id, word, definition, context_sentence). Today's words come from that morning's passage; each is shown in the sentence it appeared in.

| Attributes | Verbs (student) | Relationships |
|---|---|---|
| word, definition, context sentence | confirm meaning in context, use in her own sentence (writing practice, unscored) | belongs to a ReadingPassage; has one VocabularyReview per student |

### VocabularyReview 🔁 🆕 (per-student spaced-repetition state — DV)
*Backed by (proposed):* `vocabulary_reviews` (student_id, word_id, ease/interval, due_at, last_seen_at) — one per student×word. Words she keeps getting right return less often; words she keeps missing return sooner. Formative only.

### SchoolJournalEntry 🏫 🆕 (a digitised classroom assessment — SJ)
*Backed by (proposed):* `school_journal_entries` (student_id, `uploaded_by` ∈ {student, guardian}, image_path, assessment_date, term, subject, strand, assessment_type, score, teacher_comment, `ocr_text`, `ocr_confidence` json, `digitisation_status` ∈ {pending, digitised, confirmed}, created_at) **plus `school_journal_questions`** (entry_id, number, prompt, student_answer, correct_answer, is_correct, `syllabus_module_id` FK, topic_label, topic_confidence, reasoning_note, clip_path, clip_box — SJ-11..13). A student or guardian uploads a photo (SJ-01); a vision/OCR pipeline fills the structured fields (SJ-07), low-confidence fields flagged for a quick confirmation (SJ-02). The OCR sits behind an **`OcrService` seam** (nova-lite vision with a benchmarked fallback chain today; a real CNN/HTR model later) so the pipeline is swappable. Each question is aligned to a syllabus module by the **`TopicMatcher`** (module code → fuzzy topic, confidence-gated), clipped with its marked solution (`JournalDigitiser`, GD-optional with a box fallback), and carries the AI's read of the child's reasoning — honest layer only.

| Attributes | Verbs | Notes |
|---|---|---|
| image, assessment_date, term, subject, strand, type, score, comment, ocr_text, confidence, status | upload (student/guardian), digitise (system OCR), confirm/correct (guardian), weigh (engine) | Strong performance on a covered strand records a **corroborating confidence signal** (SJ-08) — supports inferred understanding in the honest layer, never auto-masters or overrides platform mastery, never a gate. Feeds the weekly summary (SJ-04), the daily plan's gentle focus (SJ-05), and per-strand trends across terms (SJ-09). Guardian/system layer; never a judgement in the child's world (SJ-06). |

### ExamAgentInsight 🤖 (computed, optionally cached)
Honest layer: pace vs 30-week calendar, weighted readiness (50/30/20), next-week recommendation, weak strands. Groq `generateSummary()`. Cache per student×week to respect free-tier limits (30 req/min, 14.4k/day).

### Streak 🔥 🔧 (master + sub-streaks — ML + SE)
current_days, best, per stream. Motivational layer only — never shown to guardian as a judgement metric. A **master Voyage streak** (completing the day's minimum — CO-07/SE-01) sits over **sub-streaks** per thread (reading DR, vocabulary DV, writing WR, map/mastery ML-05), plus the existing login (ML-04) and weekly on-pace (ML-06) streaks. Advanced by completing the daily minimum; frozen by a guardian pause (ML-03) or an Anchor reward (SE-08); a 3-day starter grace protects a new voyager (SE-03). Surfaced in the Ship's Log (SL-02) and on the Voyage home (SH-04).
*Backed by (proposed):* `student_streaks` (student_id, `stream` ∈ {voyage, reading, vocabulary, writing, map, login, on_pace}, current_days, best_days, last_credited_on, frozen_until) — one row per student×stream.

### DailyMinimum / Captain's Brief 🧭 🆕 (the day's required duties — CO)
*Backed by (proposed):* `daily_plans` (student_id, date, `duties` json {vocabulary, reading, map, writing?} each with a done flag, is_writing_day, completed_at) — one per student per day. The Captain's Brief is the student-facing view; the morning brief sets the duties, the evening brief shows what remains + an optional review (CO-02/CO-06). Writing appears only on Mon/Wed/Fri (CO-03) and soft-gates map advancement that day (CO-05). Weekends stand down to rest when on pace, or offer bounded, kind catch-up when behind (CO-08/CO-09). Never shows the child pace/percentage numbers (CO-10).

| Attributes | Verbs (student/system) | Notes |
|---|---|---|
| date, per-duty done flags, is_writing_day, completed_at | compose (system, daily), check off a duty, complete the day | Completing the full minimum advances the master Voyage streak (CO-07/SE-01). Map progress = any map activity that day. |

### StreakReward / Captain's Locker 🎁 🆕 (protective reward inventory — SE/SL)
*Backed by (proposed):* `streak_rewards` (student_id, `type` ∈ {shore_leave, anchor, tailwind, lifebuoy}, quantity, source ∈ {ahead, milestone, guardian, xp}) — the child's Locker, shown in the Ship's Log (SL-05). Effects (SE-07..11): **Shore Leave** excuses one duty for a day, on pace only, without fabricating progress; **Anchor** freezes all streaks for a day, even behind pace; **Tailwind** raises the accelerate cap to two days ahead; **Lifebuoy** revives a just-reset streak once. Earned four ways (SE-13..16): getting ahead, milestones, guardian-granted (the one guardian→student crossing, SE-15), or bought with XP (@roadmap). A separate **cosmetic** track (Smooth's wardrobe, Captain's rank) is @roadmap (cosmetic_rewards CR).

| Attributes | Verbs | Notes |
|---|---|---|
| type, quantity, source | earn (system/guardian), hold, spend (SL-06) | Honest + never-negative: protection saves streaks, never falsifies pace; a lost streak restarts kindly (SE-12, ML-02). |

### Pace / on-pace ⚖️ (derived from WeeklyTarget — no new table)
"On pace" = mastered work meets or leads this week's target (SE-04; the weekly yardstick in weekly_targets, on-pace streak ML-06). A guardian-layer truth that reaches the child only as kind flexibility — weekend rest and Shore Leave eligibility — never as a deficit (SE-05/06, CO-09, WT-03).

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
| `lessons` table (module_id, title, blocks JSON, is_published) | `@mvp` ✅ built |
| `module_stage_completions` table (the learning gate, LE-03/06) | `@mvp` ✅ built |
| `syllabus_modules.code` (stable module code for lesson imports) | `@mvp` ✅ built |
| `writing_submissions` table | `@mvp` |
| `reading_passages` + `daily_reading_assignments` tables (daily reading, DR) | `@mvp` |
| `vocabulary_words` + `vocabulary_reviews` tables (daily vocabulary + spaced repetition, DV) | `@mvp` |
| `daily_reading_assignments` add comprehension_score, words_per_minute, started_at (DR score + pace) | `@mvp` |
| `school_journal_entries` table (student/guardian upload + OCR fields + corroboration signal, SJ) | `@mvp` |
| `OcrService` seam (stub → real CNN/vision/LLM-vision later) for school-journal digitisation | `@mvp` |
| `student_progress.status` add `in_review` (or equivalent) | `@mvp` (decision needed) |
| student `target_sea_year` | `@mvp` |
| guardian phone + verification fields | `@v1.1` |
| pause/resume fields (paused_at, resumed weeks offset) | `@v1.1` (S6) |
| second-guardian link table (read-only role) | `@roadmap` (S8) |
| notifications/digest log | `@v1.1` (S7) |
| student `known_weak_areas` (nullable JSON, guardian hint, reconciled at reveal) | `@mvp` |
| `daily_plans` table (the day's Captain's Brief minimum + completion, CO) | `@mvp` |
| `student_streaks` table (master Voyage + per-stream sub-streaks, ML/SE) | `@mvp` |
| `streak_rewards` table (Captain's Locker: shore_leave/anchor/tailwind/lifebuoy, SE) | `@mvp` |
| cosmetics (Smooth wardrobe, Captain's rank) | `@roadmap` (CR) |