# ForMyNieces — Dashboard Design Handoff
**Date:** 09 June 2026  
**Version:** 1.0  
**Status:** Ready for development  
**Prepared by:** Design session with Claude (Anthropic)

---

## 1. Context and purpose

ForMyNieces is an SEA exam preparation platform for primary school girls in Trinidad and Tobago. This document captures all design decisions made during the dashboard brainstorm session, including the student adventure map, guardian communication model, scenario analysis, and full screen inventory. It is intended to hand off to a developer continuing the Laravel build.

The existing technical handoff (`08JUN26_FORMY_NIECES_HANDOFF.md`) covers the backend stack, database schema, models, routes, and controllers. This document covers the **frontend UX architecture** only.

---

## 2. SEA exam structure — what the platform must reflect

The SEA comprises three papers with asymmetric weighting. This weighting must be visible throughout the platform — never hidden behind a single overall score.

| Paper | Content | Weight | Assessment type |
|---|---|---|---|
| Mathematics | Number, Geometry, Measurement, Statistics | 100% | 40 MCQ items |
| ELA (Language) | Spelling, Punctuation/Capitalisation, Grammar | 60% | 18 items (Section I) |
| ELA (Comprehension) | Fiction/Non-fiction, Poetry, Graphic text | 60% | 18 items (Section II) |
| ELA Writing | Narrative or expository composition | 40% | 1 open-ended item, human-marked |

**Critical design constraint:** Math carries the highest weight. A student who looks strong in ELA but is behind in Math is in serious trouble. The dashboard must never allow a single aggregated status to mask a subject-specific gap.

### ELA paper sub-structure

**Comprehension thinking levels** (Section II — 34 marks):
- Literal — 28% of comprehension marks
- Inferential — 44% of comprehension marks ← highest leverage gap to fix
- Evaluation/Appreciation — 28% of comprehension marks

**Comprehension text types:**
- Fiction/Non-fiction — 13 marks
- Poetry — 13 marks
- Graphic text — 8 marks

**Math strands** (75 marks total):
- Number — 34 marks (dominant strand, prioritise first)
- Measurement — 18 marks
- Statistics — 12 marks
- Geometry — 11 marks

### Writing — special handling required

Writing **cannot be auto-scored**. It requires:
- Weekly open-ended practice submissions
- AI rubric feedback across four dimensions: Content, Language Use, Grammar & Mechanics, Organisation
- A separate progress model — not module completion, but rubric score trajectory
- Minimum one practice per week — non-negotiable even under compressed timelines
- Its own persistent status indicator on the dashboard, separate from the map

---

## 3. User roles and account rules

| Role | Description | Requirements |
|---|---|---|
| Student | Primary user — 10-11 year old SEA candidate | Must be linked to a verified guardian account |
| Guardian | Parent, grandparent, older sibling, or any trusted adult | Must be 18+, verified phone (WhatsApp preferred) + verified email |
| Admin | Platform administrator | Filament v4 panel at `/admin` |

**Registration rules:**
- Guardian registers first — phone and email must be verified before student account is created
- Age gate: date of birth field + declaration checkbox ("I confirm I am 18 or older")
- Phone verification via SMS OTP or WhatsApp OTP (WhatsApp preferred for T&T)
- Student account cannot be fully activated without a linked guardian account
- A student account with no guardian engagement is a risk signal — treat it in the same category as Scenario 7 (guardian registered, never engaged)

---

## 4. The adventure map — design decisions

### What the map shows

The map uses **Option A: week-based progression**. Each stop on the map represents one study week, not a topic cluster.

- Total stops = weeks remaining when the student joins (not a fixed 30)
- A standard joiner (September) gets a 30-stop map
- A late joiner (March, 15 weeks left) gets a 15-stop map
- The SEA Kingdom is always the final stop, always visible, always on the horizon
- The map advances once per week — on Monday — regardless of whether the student completed everything

**Why week-based, not topic-based:** The map's job is motivational. Students need to see forward movement frequently. A topic-cluster map (Option D) can leave a student stuck in the same region for 5–6 weeks with no visible progress, which is demotivating. The calendar always advances — so the map always advances.

### What lives inside each stop

Each week-stop contains modules from all three trackable papers bundled together — exactly as the syllabus pacing schedules them. For example:

> Week 9 — The Bridge of Fractions  
> • Mathematics · Equivalent fractions  
> • ELA Language · Commas in lists  
> • ELA Comprehension · Inferential questions — fiction text

**Future stops are dynamically filled by the ExamAgentService** — not fixed at registration. If a student falls behind, the agent resequences the content of upcoming stops to prioritise the highest-value modules. Past stops are frozen — they show what was actually done.

### Map visual states per stop

| State | Visual | Meaning |
|---|---|---|
| Completed, on pace | Green fill, check icon | Done, no issues |
| Completed, with stall | Amber dot indicator | Done but a stall was recorded that week |
| Current, on track | Blue ring, pulsing | This week, healthy |
| Current, behind | Amber ring | This week, behind schedule |
| Current, at risk | Red ring | This week, significantly behind |
| Future | Dimmed, locked | Not yet reached |
| SEA Kingdom | Gold, always visible | Final destination |

### The two-layer model

The map is **Layer 1 — motivational navigation.** It answers "where am I in the journey?"

Below the map is **Layer 2 — the detail panel.** It answers "what do I actually do this week?" This panel shows the current stop's modules broken down by subject, with per-subject and per-strand health indicators. This is where sub-skill gaps (e.g. inferential vs literal, Number vs Statistics) become visible.

The map never lies, but it also never panics. The detail panel is where the honest, specific information lives.

---

## 5. The adaptive pacing model

### How pacing works

The `ExamAgentService` already computes `current_week`, `weeks_to_exam`, `behind_modules`, and `overall_status`. The dashboard extends this with a **triage output mode** triggered when:
- `weeks_to_exam` < `expected_weeks_remaining`, OR
- `total_behind` >= 3 weeks, OR
- Student joins with fewer than 20 weeks remaining

### The triage decision

When triage mode activates — most importantly on first login for a late joiner — the agent generates a prioritised plan:

1. Presented to both student and guardian simultaneously (guardian must acknowledge)
2. Sets the deprioritisation decisions explicitly — what is lighter coverage, what is essential
3. Becomes the "agreed plan" artifact visible in the guardian's Triage Plan screen
4. The map is generated only after the plan is acknowledged

**Triage priority order (based on mark weight):**

1. Mathematics — Number strand first (34/75 Math marks)
2. Mathematics — Measurement (18/75)
3. ELA Language — Punctuation first (fastest gains), then Spelling, then Grammar
4. ELA Comprehension — Literal questions first across all text types
5. Writing — 1 practice/week, non-negotiable regardless of compression
6. Mathematics — Reasoning objectives (deprioritise under time pressure)
7. Comprehension — Evaluation/Appreciation questions (deprioritise under time pressure)

### The GPS metaphor

The road (map path) does not change. If she misses a turn (falls behind), the route recalculates — the contents of upcoming stops shift to account for the gap. She always knows where she is. She just gets slightly different directions for the next stretch. The student never sees the recalculation — she only sees "here is what is in your next stop."

---

## 6. Writing — parallel track model

Writing runs alongside the map as a **standing weekly obligation**, not a stop on the journey.

Every week, regardless of which map stop the student is on, a writing practice is available and expected. The writing track shows the last 7 weeks as dots:

- Green dot = submitted
- Red dot = missed
- Blue dot = current week (pending)

**Rubric dimensions tracked per submission:**
- Content (ideas, detail, relevance)
- Language Use (vocabulary, expression, descriptive language)
- Grammar and Mechanics (accuracy, spelling, punctuation)
- Organisation (structure, paragraphing, coherence)

The guardian sees rubric score trajectories in the weekly briefing — not just "submitted" or "not submitted." A flat Organisation score for three consecutive weeks is flagged explicitly.

**Alert rules:**
- 2 consecutive missed writing practices → amber alert to student + guardian
- 4 consecutive missed practices → escalate to SMS/WhatsApp
- Rubric dimension flat for 3+ weeks → flag in weekly briefing with specific guardian action

---

## 7. The eight scenarios

These are the meaningful distinct student-guardian combinations the platform must handle. Every design decision was evaluated against all eight.

| # | Name | Student state | Guardian state | Platform priority |
|---|---|---|---|---|
| 1 | On track, engaged | Steady, balanced | Active, weekly | Maintain momentum, surface next challenge |
| 2 | Behind, recoverable | 1–2 wks behind in Math | Underestimates Math weight | Show 100% weighting explicitly, daily catch-up target |
| 3 | Significantly behind, disengaging | 3+ wks behind, avoiding writing | Panicking or absent | Triage, small wins, SMS escalation, 3 specific daily actions |
| 4 | Late joiner, motivated | 15 wks left, 20 wks of work | Urgent, willing | Triage on first login, agreed plan before map generates |
| 5 | Ahead but unevenly | Math strong, comprehension/writing weak | Sees "ahead", disengages | Never show single overall status, flag Writing gap urgently |
| 6 | Disrupted — external stall | Was on track, 2-wk stall | May have caused disruption | Soft re-entry plan, no panic, Carnival pre-built into Feb pacing |
| 7 | Guardian registered, never engaged | Any pace | Verified at registration, silent 3+ wks | SMS/WhatsApp escalation, treat silence as risk factor |
| 8 | Conflicted household | Completing modules, failing diagnostics | Pushing on completion numbers | Surface completion vs mastery as two separate metrics |

---

## 8. Per-subject scenario evaluation summary

### What works well across all subjects
- The map advances reliably for on-track students
- Per-paper health indicators with explicit weightings inform guardians correctly
- Writing as a parallel track prevents avoidance from being invisible

### Known map limitations and fixes

| Scenario | Map limitation | Required fix |
|---|---|---|
| Math at-risk | Map advances while she falls behind — false confidence | Red ring on stop + explicit triage card below map |
| Math uneven strands | Single Math status hides strand-level gap | Strand breakdown inside each Math stop |
| Writing avoidance | Invisible on map if writing has no dedicated indicator | Persistent writing track outside map, alert after 2 missed |
| Writing not improving | Map has no rubric score trajectory | Separate writing progress view with 6-submission chart |
| Comprehension inferential gap | Thinking level invisible at map level | Thinking-level breakdown inside comprehension stop |
| Late joiner compressed | Full 30-stop map is dishonest for late joiners | Map length = weeks remaining at registration |
| Grammar weak | Home language may reinforce non-standard grammar | Briefing frames as "exam English" — tactful, not corrective |
| Comprehension all text types behind | Platform alone insufficient | Guardian action: 20 mins reading daily — explicit in briefing |

---

## 9. Guardian communication model

### Three layers of guardian communication

**Layer 1 — Guardian dashboard**
Real-time view. Per-paper health with explicit weightings. Overall status. Link to map view, weekly briefing, and triage plan.

**Layer 2 — Weekly briefing**
Generated every Monday. Three sections:
1. What went well this week (1–2 sentences)
2. What needs attention (specific, named gaps — not "she is behind")
3. Three numbered actions the guardian can take this week

The briefing must always end with exactly three numbered guardian actions. Status without action is not acceptable.

**Layer 3 — Escalation (SMS/WhatsApp)**
Triggered when:
- Guardian has not read weekly briefing after 48 hours
- Student is at-risk (Scenario 3) AND guardian is silent
- 4 consecutive missed writing practices
- Student has not logged in for 7+ days

### Tone rules for guardian communications

- Never use the word "failing" — use "behind" or "needs support"
- Grammar weakness: frame as "exam English is different from everyday speech" — not a correction of the household
- Comprehension behind: frame reading at home as enjoyable, not remedial
- Triage plan: explain what is deprioritised AND why — guardians need to understand the trade-off, not just the directive
- Conflicted household (Scenario 8): surface completion vs mastery neutrally — never make the student feel surveilled

---

## 10. Screen inventory — 21 screens

### Auth flow (7 screens)

| Screen | Route | Description |
|---|---|---|
| Landing page | `/` | Public marketing page. SEA countdown, subject pills, feature grid, CTA |
| Login | `/login` | Standalone dark theme. Email + password. Routes by role. |
| Forgot password | `/forgot-password` | Reset link via email |
| Register — guardian | `/register` (step 1) | Guardian registers first. Name, email, phone, DOB, 18+ declaration |
| Verify phone + email | `/verify` | SMS/WhatsApp OTP + email verification |
| Register — student | `/register` (step 2) | Student profile linked to verified guardian. SEA year selection. |
| Onboarding / triage | `/onboarding` | First login. AI triage assessment. Agreed plan. Both guardian and student must acknowledge before map generates. |

### Student screens (8 screens)

| Screen | Route | Description |
|---|---|---|
| Student dashboard | `/dashboard` | Hero card, stats row, adventure map, current stop detail, writing track, exam agent summary |
| Map stop detail | `/dashboard/week/{n}` | Full module list for a given week-stop. All three papers. Module status. Tap to start diagnostic. |
| Diagnostic quiz | `/diagnostic/{module}` | 8-item quiz. Progress bar. Multiple choice. |
| Diagnostic result modal | (modal on quiz completion) | Score, status update (not started → diagnostic passed → mastered), next action buttons |
| Writing practice | `/writing` | Weekly prompt (narrative or expository). Text input. Word count. Submit for feedback. |
| Writing feedback | `/writing/{submission}/feedback` | AI rubric scores across 4 dimensions. Trend lines. Specific feedback from exam agent. |
| Exam agent | `/exam-agent` | Full exam agent view. Per-paper status, weeks behind, 3 weekly actions, study timetable |
| Student settings | `/settings` | Name, email, linked guardian, SEA date, sign out |

### Guardian screens (5 screens)

| Screen | Route | Description |
|---|---|---|
| Guardian dashboard | `/guardian` | Per-paper health with explicit weightings. Stats. Links to all guardian views. |
| Guardian map view | `/guardian/map` | Read-only map with parent overlay. Status rings. Historical stall indicators. Current stop breakdown. |
| Weekly briefing | `/guardian/briefing` | What went well, what needs attention, 3 numbered actions. Generated weekly. |
| Triage plan | `/guardian/plan` | Agreed prioritisation plan for late joiners. What is prioritised and why. What is deprioritised and why. |
| Guardian settings | `/guardian/settings` | Name, email, phone (verified badge), linked student, notification preferences (email/SMS/WhatsApp), sign out |

### Admin (1 screen)

| Screen | Route | Description |
|---|---|---|
| Admin panel | `/admin` | Filament v4. Users, Syllabus Modules, Student Progress, Weekly Targets resources. |

---

## 11. Navigation and transition map

```
Landing
├── → Login
│   ├── → Student dashboard    (role = student)
│   ├── → Guardian dashboard   (role = guardian)
│   ├── → Admin panel          (role = admin)
│   └── → Forgot password → Login
└── → Register — guardian
    └── → Verify phone + email
        └── → Register — student
            └── → Onboarding / triage
                └── → Student dashboard (student)
                    └── Guardian dashboard (guardian, simultaneous first session)

Student dashboard
├── → Map stop detail → Diagnostic quiz → Result modal → Map stop detail
├── → Writing practice → Writing feedback → Student dashboard
├── → Exam agent → Student dashboard
└── → Student settings → Login (sign out)

Guardian dashboard
├── → Guardian map view → Guardian dashboard
├── → Weekly briefing → Guardian dashboard
├── → Triage plan → Guardian dashboard
└── → Guardian settings → Login (sign out)
```

---

## 12. Key design principles (do not compromise)

1. **Never show a single overall status.** Always show per-paper health. A student who is ahead in ELA but behind in Math is at risk — the headline cannot hide this.

2. **Math weighting must be explicitly stated everywhere** it is mentioned in guardian communications. "Math carries 100% of the SEA weight" should appear in the guardian dashboard, weekly briefing, and any at-risk alert involving Math.

3. **Writing is a parallel track, not a module.** It cannot be completed, only practiced. It has no "done" state — only "consistent," "patchy," or "avoided."

4. **Guardian communications must end with actions, not status.** A red flag with no action is useless. Every guardian-facing output ends with numbered, specific, achievable tasks for that week.

5. **The triage plan is a contract, not an algorithm.** Both student and guardian must acknowledge it before the map is generated. Subsequent recalculations should be surfaced transparently — not silently applied.

6. **The map is the face; the agent is the brain.** They are separate concerns. The map provides the emotional journey. The agent provides honest, adaptive, actionable guidance. Never let the map carry information the agent should carry, and vice versa.

7. **Completion ≠ mastery.** Always surface both metrics separately. A student who has completed 80% of modules but is scoring 45% on diagnostics is not on track.

8. **Carnival disruption is predictable.** The February pacing for all students should pre-account for a 1-week disruption. A two-week stall in February should not trigger an at-risk alert — it should trigger a soft re-entry plan.

---

## 13. What is not yet built (next development tasks)

These tasks follow directly from this design session and extend the existing "What's Next" list from the technical handoff:

**High priority — required for student-facing MVP:**
- [ ] New `welcome.blade.php` — landing page (design complete, code ready)
- [ ] New `auth/login.blade.php` and `auth/register.blade.php` — branded auth views (design complete, code ready)
- [ ] Update `RegisteredUserController` to capture `role` field
- [ ] Guardian registration flow — add phone field, DOB field, 18+ checkbox
- [ ] Phone verification — integrate SMS/WhatsApp OTP (Twilio or Vonage)
- [ ] Student–guardian account linking — guardian registers first, invites student
- [ ] New student `dashboard.blade.php` — replace current with adventure map architecture
- [ ] New `map-detail.blade.php` — week stop drill-down view
- [ ] Diagnostic quiz flow — `DiagnosticController`, quiz view, result modal
- [ ] Writing practice flow — `WritingController`, submission view, AI rubric feedback via Anthropic API
- [ ] Update `ExamAgentService` — add triage output mode, strand-level Math breakdown, writing track status

**Medium priority — guardian layer:**
- [ ] Guardian dashboard view
- [ ] Guardian map view (read-only overlay on student map)
- [ ] Weekly briefing generator — scheduled job, AI-generated via Anthropic API, 3-action format
- [ ] Triage plan view — generated at onboarding, stored, acknowledgeable
- [ ] SMS/WhatsApp escalation — trigger conditions, Twilio/Vonage integration

**Lower priority — completeness:**
- [ ] Filament resource customisation — UserResource, StudentProgressResource, WeeklyTargetResource
- [ ] Filament dashboard widgets — cohort overview (on track / at risk / behind)
- [ ] Pest tests — DashboardTest, ExamAgentTest, DiagnosticTest
- [ ] Carnival disruption handling — pre-built February pacing adjustment
- [ ] Notification preferences — guardian settings screen

---

## 14. Database additions required

The current schema (see `08JUN26_FORMY_NIECES_HANDOFF.md`) needs the following additions to support this design:

```php
// users table — additions
$table->string('phone')->nullable();
$table->boolean('phone_verified')->default(false);
$table->date('date_of_birth')->nullable();       // guardian only
$table->boolean('age_verified')->default(false); // guardian only

// writing_submissions (new table)
$table->id();
$table->foreignId('student_id')->constrained('users');
$table->unsignedInteger('week_number');
$table->enum('type', ['narrative', 'expository']);
$table->text('prompt');
$table->text('content');
$table->tinyInteger('score_content')->nullable();
$table->tinyInteger('score_language')->nullable();
$table->tinyInteger('score_grammar')->nullable();
$table->tinyInteger('score_organisation')->nullable();
$table->text('ai_feedback')->nullable();
$table->timestamps();

// triage_plans (new table)
$table->id();
$table->foreignId('student_id')->constrained('users');
$table->json('priorities');         // ordered array of subject/strand priorities
$table->json('deprioritised');      // what is lighter coverage
$table->boolean('student_acknowledged')->default(false);
$table->boolean('guardian_acknowledged')->default(false);
$table->timestamp('agreed_at')->nullable();
$table->timestamps();
```

---

## 15. Reference: colour system for subject coding

These colours are established in `FMN_DESIGN_SYSTEM.md` and must be applied consistently:

| Subject | Primary colour | Badge background | Badge text |
|---|---|---|---|
| Mathematics | Teal `#0d9488` | `#E1F5EE` | `#085041` |
| ELA Language | Pink `#db2777` | `#FBEAF0` | `#72243E` |
| ELA Comprehension | Purple `#9333ea` | `#EEEDFE` | `#3C3489` |
| ELA Writing | Green `#16a34a` | `#EAF3DE` | `#27500A` |

Status colours (consistent across all views):

| Status | Background | Text | Usage |
|---|---|---|---|
| On track / mastered | `#EAF3DE` | `#27500A` | Green — good |
| Slight risk / behind | `#FAEEDA` | `#633806` | Amber — watch |
| At risk / avoided | `#FCEBEB` | `#791F1F` | Red — act now |
| Current / in progress | `#E6F1FB` | `#0C447C` | Blue — active |
| Triage / compressed | `#E6F1FB` | `#0C447C` | Blue — information |
| Locked / upcoming | `var(--color-background-secondary)` | `var(--color-text-tertiary)` | Gray — neutral |

---

*End of handoff document. For technical stack details see `08JUN26_FORMY_NIECES_HANDOFF.md`.*
