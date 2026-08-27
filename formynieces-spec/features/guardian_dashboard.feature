@mvp @guardian
Feature: Guardian dashboard — the honest layer
  The Guardian Bridge — a sidebar app on the light editorial brand system. Its
  Overview is a high-level summary answering the four Sunday questions honestly
  (target completed, position against pace, the exam agent's recommendation, and
  the latest writing feedback), and the heavier functions live in their own
  sidebar sections: This week (topics + reading + writing), Pace (per-subject
  bars, trajectory, and a collapsible year → month → week calendar), Progress
  (the drill-down buckets), Estimator (projected SEA placement), and Rewards &
  controls (perks, granting a reward, pause/resume, retake, pause history).
  Pace is measured against the STUDENT's own journey (PacingClock, journey_start),
  never a global calendar, and is recalculated once a week and dated on screen.
  Every figure is labelled so it cannot be misread; the honest layer never
  borrows the student's motivational styling.

  @scenario:GD-01
  Scenario: The dashboard answers the four Sunday questions
    Given a guardian whose student has an active roadmap
    When she opens the guardian dashboard
    Then she sees whether this week's target was completed
    And she sees the student's position against the 30-week pace weighted 50/30/20 by paper
    And she sees the exam agent's single concrete recommendation for next week
    And she sees a pointer to the latest writing feedback

  @scenario:GD-02
  Scenario: The drill-down groups modules into honest buckets
    Given a guardian whose student has an active roadmap
    When she opens the progress drill-down
    Then modules are grouped per subject as mastered, in review, working on, and upcoming
    And modules credited by inference are shown as in review, never as mastered
    And Writing is shown as a paper awaiting its own assessment track

  @scenario:GD-03
  Scenario: An on-track week reads as calm affirmation
    Given a student who completed her target and is on pace
    When her guardian opens the guardian dashboard
    Then the dashboard leads with affirmation
    And no action items are presented

  @scenario:GD-04
  Scenario: A significantly-behind student gets triage, not panic
    Given a student who is 4 or more weeks behind the pacing calendar
    When her guardian opens the guardian dashboard
    Then the recommendation prioritises Mathematics per its 50% placement weight
    And the catch-up plan is presented as feasible weekly steps rather than a deficit total

  @scenario:GD-07
  Scenario: The dashboard is headed by the child it is about
    Given a guardian whose student has an active roadmap
    When she opens the guardian dashboard
    Then the screen is headed by the student's name and the week it covers
    And when the guardian has more than one student she can switch between them
    And every screen in the honest layer names which child it is about

  @scenario:GD-08
  Scenario: The drill-down leads with what needs attention, not the whole syllabus
    Given a guardian whose student has an active roadmap
    When she opens the progress drill-down
    Then it leads with the buckets she can act on — working on, in review, and anything struggling
    And a per-subject summary shows how many modules are mastered out of the total
    And the large "upcoming" list is collapsed to a count she can expand on demand
    And no bucket dumps the full module list ahead of the actionable ones

  @scenario:GD-09
  Scenario: The dashboard is where the guardian acts
    Given a guardian whose student has an active roadmap
    When she opens the guardian dashboard
    Then she can pause or resume the journey from here
    And she can grant a reward to her student from here
    And she can request a diagnostic retake from here
    And these controls live in the honest layer and are never shown to the child
    # Pause/resume is weekly_targets WT-04/05; granting a reward is streak_economy SE-15.

  @scenario:GD-10
  Scenario: A pending reconciliation is surfaced for decision, not left waiting
    Given the diagnostic cleared a strand the guardian flagged and she has not yet decided
    When she opens the guardian dashboard
    Then a prominent banner tells her a decision is needed and that her student is waiting
    And the banner offers to proceed with the diagnostic result or keep her stated weak areas
    And resolving it here lifts the student's waiting hold
    # The hold itself is roadmap_reveal RR-11; the out-of-app nudge is RR-13.

  @scenario:GD-11
  Scenario: Every pace figure is labelled so it cannot be misread
    Given a guardian whose student is behind on some subjects
    When she reads the pace section
    Then each number states what it counts — modules mastered, and modules against this week's pace
    And a plain-language sentence leads before any raw count
    And a large "behind" figure is never shown bare without its feasible catch-up framing
    # Triage-not-panic is GD-04; this is the labelling that keeps the numbers honest, not alarming.

  Rule: Motivational styling never substitutes for data

    @scenario:GD-05
    Scenario: Pace and readiness sections exclude the motivational layer
      Given a guardian whose student has an active streak
      When she opens the guardian dashboard
      Then the pace and readiness sections contain no streak counters or celebration styling

  @v1.1 @scenario:GD-06
  Scenario: A disengaged guardian receives the digest inline
    Given a guardian who has not opened the dashboard for 2 consecutive weeks
    When the weekly digest job runs
    Then the digest email includes the four answers inline
    And its nudge tone escalates gently and without guilt

  @scenario:GD-12
  Scenario: The estimator projects placement from her own history
    Given a guardian whose student has answered practice questions
    When she opens the Estimator section
    Then she sees the student's average score per subject over covered material
    And a projected SEA composite weighted 50/30/20
    And an indicative placement tier drawn from public SEA cut-off ranges
    And a confidence signal so a thin evidence base is never shown as a firm projection

  @scenario:GD-13
  Scenario: The dashboard is a sidebar app, not one long page
    Given a guardian whose student has an active roadmap
    When she opens the guardian dashboard
    Then the Overview shows a high-level summary and cards that jump into each function
    And the sidebar carries This week, Pace, Progress, Estimator, and Rewards & controls
    And selecting a section shows only that function's content
    And a pending reconciliation stays surfaced across every section

  @scenario:GD-14
  Scenario: Pace and progress are recalculated weekly and dated on screen
    Given a guardian whose student has an active journey
    When the weekly pace recalculation runs for every active student
    Then the student's weeks_behind, pace_status and required_pace are refreshed
    And the recalculation time is stamped on her journey
    And the dashboard shows when progress was last updated

  @scenario:GD-15
  Scenario: Pace is measured against the student's own journey, not a global calendar
    Given a student a few weeks into her own journey
    When the exam agent analyses her pace
    Then only the modules due by her current journey week are expected of her
    And she is never reported behind by the whole syllabus when her cycle sits
      outside the global term calendar
