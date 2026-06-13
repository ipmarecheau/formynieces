@mvp @guardian
Feature: Guardian dashboard — the honest layer
  One weekly screen answers exactly four questions honestly: was the target
  completed, where is she against pace, what does the exam agent recommend,
  and what did her writing feedback say. The honest layer never borrows the
  student's motivational styling.

  Scenario: The dashboard answers the four Sunday questions
    Given a guardian whose student has an active roadmap
    When she opens the guardian dashboard
    Then she sees whether this week's target was completed
    And she sees the student's position against the 30-week pace weighted 50/30/20 by paper
    And she sees the exam agent's single concrete recommendation for next week
    And she sees a pointer to the latest writing feedback

  Scenario: The drill-down groups modules into honest buckets
    Given a guardian whose student has an active roadmap
    When she opens the progress drill-down
    Then modules are grouped as mastered, in review, and upcoming per subject

  @scenario-S1
  Scenario: An on-track week reads as calm affirmation
    Given a student who completed her target and is on pace
    When her guardian opens the guardian dashboard
    Then the dashboard leads with affirmation
    And no action items are presented

  @scenario-S3
  Scenario: A significantly-behind student gets triage, not panic
    Given a student who is 4 or more weeks behind the pacing calendar
    When her guardian opens the guardian dashboard
    Then the recommendation prioritises Mathematics per its 50% placement weight
    And the catch-up plan is presented as feasible weekly steps rather than a deficit total

  Rule: Motivational styling never substitutes for data

    Scenario: Pace and readiness sections exclude the motivational layer
      Given a guardian whose student has an active streak
      When she opens the guardian dashboard
      Then the pace and readiness sections contain no streak counters or celebration styling

  @v1.1 @scenario-S7
  Scenario: A disengaged guardian receives the digest inline
    Given a guardian who has not opened the dashboard for 2 consecutive weeks
    When the weekly digest job runs
    Then the digest email includes the four answers inline
    And its nudge tone escalates gently and without guilt
