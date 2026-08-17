@mvp @guardian
Feature: Guardian dashboard — the honest layer
  One weekly screen, headed by the child's name and the week it covers, answers
  exactly four questions honestly: was the target completed, where is she against
  pace, what does the exam agent recommend, and what did her writing feedback say.
  Every figure is labelled so it cannot be misread, and this screen is also where
  the guardian acts — pausing, granting a reward, requesting a retake, and deciding
  a pending reconciliation. The honest layer never borrows the student's
  motivational styling.

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
