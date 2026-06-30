@mvp @student
Feature: Module learning loop
  A student masters the syllabus one module at a time. From her map she opens a
  needs_work module and practises a climb of increasing difficulty. Mastery is
  earned by sustained success at the hardest level, every wrong answer is framed
  as not-yet rather than failure, and reaching the top is celebrated.

  Background:
    Given a student has completed her diagnostic
    And her map shows a module with status "needs_work"

  Scenario: Opening a module shows its human-vetted lesson
    When she opens the module from her map
    Then she sees the module's description and its human-vetted resources
    And she can start practising from the lesson

  Scenario: She can reach practice for a needs_work module from her map
    When she views her map
    Then the needs_work module offers a way to start practising

  Scenario: Practice climbs three difficulty rungs
    Given she is practising a module starting at the easiest rung
    When she answers three distinct questions correctly in a row at her current rung
    Then she advances to the next rung
    And her progress increases

  Scenario: A wrong answer resets the current streak but keeps the rung
    Given she has answered two questions correctly in a row at her current rung
    When she answers the next question incorrectly
    Then her streak returns to zero
    And she remains on the same rung
    And she sees the question's explanation framed as not-yet, with no failure language

  Scenario: A repeated question does not pad the streak
    Given she has correctly answered a question that is part of her current streak
    When she answers that same question correctly again
    Then her streak does not increase

  Scenario: Mastery is earned by three in a row at the hardest rung
    Given she is practising at the hardest rung
    When she answers three distinct questions correctly in a row
    Then the module's status becomes "mastered"
    And her progress reaches one hundred
    And her prior score is preserved as the previous score
    And a celebration is shown

  @v1.1
  Scenario: Stale mastery in a weak strand decays into review
    Given a module was mastered more than 6 weeks ago in a strand the exam agent flags as weak
    When the weekly agent review runs
    Then the module's status becomes "in_review"
    And the module becomes eligible for a future weekly target