@mvp @student @system
Feature: Parallel writing track
  Writing is a parallel track for the 20%-weight Writing paper: short prompts on
  Monday, Wednesday and Friday with warm AI rubric feedback, reached from the
  Captain's Brief rather than from a stop on the map. On those days writing is one
  of the day's minimum duties and a same-day soft gate to advancing on the map;
  weekends are off. Writing never enters the module mastery model and never
  reduces to a grade or a pass/fail status.

  @scenario:WR-01
  Scenario: The day's prompt is reached from the Captain's Brief on a writing day
    Given a student on a writing day (Monday, Wednesday or Friday)
    When she opens the writing duty in her Captain's Brief
    Then she sees today's prompt adapted from past Creative Writing papers

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

  @scenario:WR-06
  Scenario: Writing runs Monday, Wednesday and Friday, with weekends off
    Given a study week
    When the writing schedule is read
    Then a writing prompt is due on Monday, Wednesday and Friday
    And no writing is due on Saturday or Sunday
    And completing a day's writing checks off the writing duty and advances her writing sub-streak

  @scenario:WR-07
  Scenario: Writing is the writing-day gate to advancing on the map
    Given today is a writing day and her writing is not yet done
    When she tries to open a new level on the map
    Then she is invited to finish today's writing first, without lockout language
    And once her writing is done the new level opens
    And writing is no longer shown as a stop on the map
    # Authoritative gate behaviour is CO-05; the map side is adventure_map AM-11.

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
