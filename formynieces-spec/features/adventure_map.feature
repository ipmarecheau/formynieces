@mvp @student
Feature: Syllabus adventure map
  The student's map is a playful, game-like alternative to a data dashboard —
  never a chart of percentages and pace. It is a world of islands, one per
  strand-family (Number Isle, Story Cove, Word Harbour, Writer's Bay), each
  holding a chain of levels — one per syllabus module, in prerequisite order.
  A level unlocks the moment its prerequisites are mastered, never by a
  calendar date, so what she sees is always real, earned progress. The map is
  interactive: she taps a level to play it.

  @scenario:AM-01
  Scenario: The map is a world of islands, each holding a chain of levels
    Given a student with a generated roadmap
    When she opens her map
    Then she sees one island per strand-world
    And each island shows a chain of levels, one per module in that island,
      ordered by the syllabus's prerequisite chain

  Rule: A level unlocks by mastery, never by the calendar

    @scenario:AM-02
    Scenario: A level unlocks once its prerequisites are mastered
      Given a module whose prerequisite modules are all mastered
      When she opens her map
      Then that module's level is shown playable

    @scenario:AM-03
    Scenario: A level stays locked while its prerequisites are unmet
      Given a module with an unmastered prerequisite
      When she opens her map
      Then that module's level is shown locked, as a silhouette
      And she can still see it sitting on the island ahead of her

  @scenario:AM-04
  Scenario: Tapping a level plays it
    Given a playable or already-mastered level
    When she taps it
    Then she is taken to play that module

  @scenario:AM-05
  Scenario: This week's suggested levels carry a star, without blocking the rest
    Given a weekly target naming specific modules
    When she opens her map
    Then those modules' levels carry a suggested-this-week star
    And every other unlocked level on any island remains fully playable

  Rule: The map never shows the student alarming states

    @scenario:AM-06
    Scenario: A behind-pace student sees the same kind map
      Given a student who is 3 weeks behind the pacing calendar
      When she opens her map
      Then no island or level is rendered in warning or failure styling
      And no placement weights, percentages, or pace deficits are displayed
      And every level's state reflects only her mastery, never her pace

  @roadmap @scenario:AM-07
  Scenario: The buffer switches the map to revision mode
    Given the current week is within 6 weeks of the exam date
    When a student opens the map
    Then revision levels resurfacing her weakest mastered modules are shown
    And no new-content levels are shown
