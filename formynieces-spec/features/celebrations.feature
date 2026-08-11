@mvp @student
Feature: Milestone celebrations — big animated moments
  Every real achievement is met with a big, animated celebration voiced by Smooth,
  bringing excitement at each step, then hands the student straight back into the
  flow. Celebrations fire only on genuine milestones (never participation alone),
  name what was achieved in the child's language, and respect reduced motion. This
  is the umbrella that the existing mastery and welcome-back moments fold into.

  Background:
    Given a signed-in student

  @scenario:CE-01
  Scenario: A milestone plays a big animated celebration with Smooth
    Given a student reaches a milestone
    When the celebration plays
    Then it fills the screen with an animated moment and Smooth cheering
    And it names what she achieved in her own language, with no pace or percentage
    And she can continue back into the flow

  @scenario:CE-02
  Scenario: Clearing a difficulty level is celebrated
    Given a student clears a difficulty level in practice
    When she advances to the next level
    Then a level-up celebration plays before the next question

  @scenario:CE-03
  Scenario: Mastering a module is a headline celebration
    Given a student answers three first-try-correct at the top level
    When the module becomes mastered
    Then the headline mastery celebration plays
    And it leads her back to her Voyage where the next stop is unlocked

  @scenario:CE-04
  Scenario: A streak milestone is celebrated
    Given a student's streak reaches a celebrated length
    When she next opens her Voyage
    Then a streak celebration plays naming the streak warmly, never as a metric

  @scenario:CE-05
  Scenario: Completing the week's targets is celebrated
    Given a student completes every module in this week's target
    When the last one is mastered
    Then a week-complete celebration plays

  @scenario:CE-06
  Scenario: Celebrations respect reduced motion
    Given a student whose device prefers reduced motion
    When a celebration is triggered
    Then it appears without motion
    And it still names the achievement and lets her continue

  @v1.1 @scenario:CE-07
  Scenario: A maintained competency is acknowledged
    Given a mastered competency was refreshed before it slipped to review
    When she completes the maintenance questions
    Then a brief, warm acknowledgement plays, never a pressured one
