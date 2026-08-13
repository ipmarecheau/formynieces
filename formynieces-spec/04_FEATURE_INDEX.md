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
| `lesson_bank.feature` | @mvp | — (LB-01…04 all built: bulk JSON import/export, seeder, guide + template) | admin, system |
| `daily_reading.feature` | @mvp | — | student, admin, system |
| `daily_vocabulary.feature` | @mvp | — | student, system |
| `ai_governance.feature` | @mvp | — | system, student, admin |
| `weekly_targets.feature` | @mvp | pause/resume @v1.1 | system |
| `writing_track.feature` | @mvp | trend view, guardian view @v1.1 | student, system |
| `guardian_dashboard.feature` | @mvp | digest @v1.1 | guardian |
| `motivation_layer.feature` | @mvp | — | student |
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
