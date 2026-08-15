# 04 — Feature Index & Coverage Matrix

## 1. Feature files by priority

| Feature file | Default tag | Scenario-level overrides | Actor(s) |
|---|---|---|---|
| `guardian_onboarding.feature` | @mvp | phone verify @v1.1 · second guardian @roadmap | guardian |
| `diagnostic.feature` | @mvp | retake @v1.1 | student, system |
| `roadmap_reveal.feature` | @mvp | reveal animation @roadmap | student, system |
| `adventure_map.feature` | @mvp | revision mode @roadmap | student |
| `student_home.feature` | @mvp | — | student |
| `voyage_companion.feature` | @v1.1 | AI voice @roadmap | student |
| `smooth_guide.feature` | @mvp | — | student |
| `celebrations.feature` | @mvp | maintenance ack @v1.1 | student |
| `learning_loop.feature` | @mvp | mastery decay @v1.1 | student |
| `lesson.feature` | @mvp | — (LE-01…10 all built: interactive lesson, gated sequence, authoring engine, 4 interaction types) | student, admin |
| `lesson_bank.feature` | @mvp | — (LB-01…05 all built: bulk JSON import/export, seeder, import guide + template, and a Claude-Code creation guide) | admin, system |
| `daily_reading.feature` | @mvp | — | student, admin, system |
| `daily_vocabulary.feature` | @mvp | — | student, system |
| `ai_governance.feature` | @mvp | — | system, student, admin |
| `weekly_targets.feature` | @mvp | pause/resume @v1.1 | system |
| `writing_track.feature` | @mvp | trend view, guardian view @v1.1 · now M/W/F cadence + Captain's Brief entry & map gate (WR-06/07) | student, system |
| `guardian_dashboard.feature` | @mvp | digest @v1.1 | guardian |
| `landing_page.feature` | @mvp | — (LP-01…13 built: parent-pain hero with an auto-rotating jumbotron + Smooth, the eight pillars, how-it-works, gender-neutral copy, $200/month pricing with the two written guarantees, honest coming-soon marking for the school journal) | guardian, public |
| `about_page.feature` | @mvp | — (AB-01…04 built: origin story, four beliefs, why-a-turtle, funnels to the call) | guardian, public |
| `faq_page.feature` | @mvp | — (FQ-01…05 built: programme / child-experience / parent / money-safety questions, funnels to the call) | guardian, public |
| `contact_us.feature` | @mvp | — (CU-01…03 built: form → `contact_messages`, validation, admin inbox with mark-handled) | guardian, public, admin |
| `parent_onboarding_call.feature` | @mvp | — (OC-01…05 built: weekday-evening + Saturday windows in TT time, live availability, no double-booking, admin calendar with statuses) | guardian, public, admin |
| `school_journal.feature` | @mvp | — (SJ-01…06 specced for the MVP; build pending — graded-paper upload + structured capture, term timeline, feeds weekly summary and daily plan, two-layer separation) | guardian, system |
| `motivation_layer.feature` | @mvp | — | student |
| `captains_orders.feature` | @mvp | — | student, system |
| `ship_log.feature` | @mvp | — | student |
| `streak_economy.feature` | @mvp | XP-bought rewards @roadmap | student, system |
| `cosmetic_rewards.feature` | @roadmap | — | student |
| `exam_readiness.feature` | @roadmap | — | student |
| `admin_content.feature` | mixed | modules @mvp · anchors, monitor @v1.1 | admin |
| `question_bank.feature` | @v1.1 | — | admin |
| `writing_bank.feature` | @v1.1 | import/populate @v1.1 (WB-01/02 built) · serving, grading deferred (WB-03/04) | admin, student |
| `progression.feature` | @roadmap | XP, multipliers, weekly leagues | student |
| `focus_timer.feature` | @roadmap | optional Pomodoro blocks | student |

**Run the MVP suite:** `--tags @mvp` (Behat) or filter by group `mvp` (Pest).

## 2. Coverage against the eight scenarios

| Household scenario | Covered by | Earliest phase |
|---|---|---|
| S1 On-track | guardian_dashboard | MVP |
| S2 Behind, recoverable | adventure_map, weekly_targets | MVP |
| S3 Significantly behind | weekly_targets, guardian_dashboard | MVP |
| S4 Late joiner | roadmap_reveal | MVP |
| S5 Ahead but uneven | adventure_map (+ agent routing in weekly_targets) | MVP |
| S6 Disrupted | weekly_targets (pause), motivation_layer (freeze) | v1.1 |
| S7 Guardian disengaged | guardian_dashboard (digest) | v1.1 |
| S8 Conflicted household | guardian_onboarding (second guardian) | Roadmap |

## 3. Tooling recommendation

Use **Pest** with a thin Gherkin-style describe/it naming convention mirroring these files, or **Behat** if you want the `.feature` files executed verbatim. Given the Laravel 11/Filament 4 stack and solo velocity, Pest is the pragmatic choice: keep `.feature` files as the spec of record, and name Pest tests `it('rolls misses forward with a cap', ...)` 1:1 with scenario titles so traceability survives without a Gherkin runner.

Only behaviour with consequences gets a scenario (adaptive walk, propagation guard, rollover caps, two-layer separation). Plain CRUD does not — keep the spec maintenance-light.
