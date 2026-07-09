@mvp @system
Feature: Weekly targets and pacing
  The pacing engine keeps a 30-week Std 5 journey on schedule without crushing
  the child. Each Sunday one rollover job runs: modules mastered during the week
  drop off, modules left unmastered carry forward, and the remaining cap is
  filled from the prerequisite frontier of not-yet-mastered modules. Completion
  is never "archived" — mastery lives in student_progress and old target rows are
  simply superseded. Significant lag against the required pace is re-paced
  honestly, and only ever surfaced in the guardian layer.

  # Required pace = total modules (all Math + ELA + Writing) / 30 weeks.
  # With the current syllabus that is 90 / 30 = 3 modules per week, global —
  # the fixed Std-5-on-track yardstick every student is measured against.
  # It is the SOURCE of the lag number in WT-03, not a per-student schedule.
  #
  # Two clocks, per student, both set at onboarding:
  #   journey_start -> current_pacing_week = weeks_since(journey_start) + 1
  #                    (child-facing; drives which modules are served)
  #   exam_date     -> weeks_to_exam (dashboard; feeds the WT-03 lag math)
  # Messaging: "You started your journey X weeks ago and are working toward the
  # exam in Y weeks." No missed-week or deficit framing to the student.

  @scenario:WT-00
  Scenario: The pacing clock derives the current week and weeks to exam
    Given a student whose journey started 4 weeks ago
    And whose exam date is 26 weeks away
    When the pacing clock is read for that student
    Then the student's current pacing week is 5
    And the student's weeks to exam is 26

  @scenario:WT-00
  Scenario: An early starter is never pushed past week one before time passes
    Given a student whose journey starts today
    When the pacing clock is read for that student
    Then the student's current pacing week is 1

  @scenario:WT-01
  Scenario: Sunday rollover generates the next target when everything was mastered
    Given a student whose current weekly target modules are all mastered
    When the Sunday rollover job runs
    Then a new weekly target is generated from the prerequisite frontier of remaining modules
    And nothing is carried forward from the previous target
    And the new target does not exceed the weekly module cap

  @scenario:WT-02
  Scenario: Unfinished modules carry forward under the weekly cap
    Given a student left 3 target modules unmastered last week
    When the Sunday rollover job runs
    Then the new target includes the 3 unmastered modules first
    And the new target does not exceed the weekly module cap
    And overflow modules shift to downstream pacing weeks instead of inflating the target

  @scenario:WT-03
  Scenario: Significant lag triggers an honest re-pace in the guardian layer
    Given a student whose mastered count is 4 or more weeks of required pace behind
    When the Sunday rollover job runs
    Then a feasible re-paced plan is computed weighted by the 50/30/20 paper weights
    And the re-paced plan with its explanation is published to the guardian dashboard
    And the guardian is told how many weeks behind an on-track Std 5 student the child is
    And the student's map state contains no deficit language

  @v1.1
  Rule: A guardian can pause and resume the journey without shame

    @scenario:WT-04
    Scenario: Pausing stops target generation
      Given a guardian whose family is experiencing a disruption
      When she pauses her student
      Then no weekly targets are generated while paused
      And the student's streak is frozen

    @scenario:WT-05
    Scenario: Resuming re-paces from the resume date
      Given a student who has been paused for 2 weeks
      When her guardian resumes her
      Then pacing is recomputed from the resume date
      And no missed-week or catch-up framing is shown to the student
