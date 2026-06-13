@mvp @system
Feature: Weekly targets and pacing
  The pacing engine keeps a 30-week journey on schedule without crushing the
  child: targets are generated each Sunday, misses roll forward under a cap,
  and significant lag is re-paced honestly in the guardian layer only.

  Scenario: Sunday rollover generates the next target
    Given a student with an active roadmap and a fully completed weekly target
    When the Sunday rollover job runs
    Then the completed target is archived
    And a new weekly target is generated from the next pacing week's modules

  @scenario-S2
  Scenario: Unfinished modules roll forward under the weekly cap
    Given a student left 3 target modules unfinished last week
    When the Sunday rollover job runs
    Then the new target includes the 3 unfinished modules first
    And the new target does not exceed the weekly module cap
    And overflow modules shift downstream pacing weeks instead of inflating the target

  @scenario-S3
  Scenario: Significant lag triggers an honest re-pace in the agent layer
    Given a student is 4 or more weeks behind the pacing calendar
    When the Sunday rollover job runs
    Then the exam agent recomputes a feasible plan weighted by the 50/30/20 paper weights
    And the re-paced plan with its explanation is published to the guardian dashboard
    And the student's map state contains no deficit language

  @v1.1 @scenario-S6
  Rule: A guardian can pause and resume the journey without shame

    Scenario: Pausing stops target generation
      Given a guardian whose family is experiencing a disruption
      When she pauses her student
      Then no weekly targets are generated while paused
      And the student's streak is frozen

    Scenario: Resuming re-paces from the resume date
      Given a student who has been paused for 2 weeks
      When her guardian resumes her
      Then pacing is recomputed from the resume date
      And no missed-week or catch-up framing is shown to the student
