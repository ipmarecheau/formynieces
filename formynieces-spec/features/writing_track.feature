@mvp @student @system
Feature: Parallel writing track
  Writing is a parallel track for the 20%-weight Writing paper: weekly prompts
  with warm AI rubric feedback. Writing never enters the module mastery model
  and never reduces to a grade or a pass/fail status.

  @scenario:WR-01
  Scenario: The weekly prompt is reachable from the dashboard
    Given a student in an active study week
    When she opens the writing card on her dashboard
    Then she sees this week's prompt adapted from past Creative Writing papers

  @scenario:WR-02
  Scenario: A submission returns a four-criterion rubric profile
    Given a student has drafted a response to this week's prompt
    When she submits her writing
    Then it is scored against Content, Language Use, Grammar and Mechanics, and Organisation
    And the feedback names two things she did well and one thing to try next time
    And no letter grade or pass/fail status is shown
    And no module's mastery status changes

  @scenario:WR-03
  Scenario: An AI outage degrades gracefully
    Given the AI scoring provider is rate-limited or unavailable
    When a student submits her writing
    Then her submission is saved and queued for scoring
    And she is told her feedback is on its way

  @v1.1 @scenario:WR-04
  Scenario: The history view shows rubric growth
    Given a student with 4 or more scored submissions
    When she opens her writing history
    Then she sees her rubric profile trend across the four criteria

  @v1.1 @guardian @scenario:WR-05
  Scenario: A guardian reads the latest feedback
    Given a student with at least one scored submission
    When her guardian opens the guardian writing view
    Then the latest submission, its rubric profile, and its feedback text are shown
