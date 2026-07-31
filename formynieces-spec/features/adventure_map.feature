@mvp @student
Feature: Syllabus adventure map — the Voyage
  The student's map is a playful sea voyage, never a chart of percentages and
  pace. It is a world of painted islands along a trail, each island a stretch of
  the syllabus in curriculum order, holding a chain of levels — one per module.
  Islands are conquered in order: an island opens once the one before it is fully
  mastered, so what she sees is always real, earned progress. The map is
  interactive: she taps a level to play it.

  @scenario:AM-01
  Scenario: The voyage is a trail of painted islands, each holding a chain of levels
    Given a student with a generated roadmap
    When she opens her voyage
    Then she sees the painted islands along the sea trail
    And each island holds a chain of levels, one per module in that island,
      in curriculum order
    And each island shows how many of its levels she has conquered, never a percentage

  Rule: An island is conquered in order, never by the calendar

    @scenario:AM-02
    Scenario: An island opens once the island before it is fully conquered
      Given every level on the previous island is mastered
      When she opens her voyage
      Then the next island is shown open and playable

    @scenario:AM-03
    Scenario: An island stays locked while the one before it is unfinished
      Given a level on the previous island is not yet mastered
      When she opens her voyage
      Then the next island is shown locked
      And she can still see it further along the trail ahead of her
      And trying to enter it sails her back to the overworld

  @scenario:AM-04
  Scenario: Tapping a level plays it
    Given a playable or already-mastered level on an open island
    When she taps it
    Then she is taken to play that module

  @roadmap @scenario:AM-05
  Scenario: This week's suggested levels carry a star, without blocking the rest
    Given a weekly target naming specific modules
    When she opens her map
    Then those modules' levels carry a suggested-this-week star
    And every other unlocked level on any island remains fully playable

  Rule: The map never shows the student alarming states

    @scenario:AM-06
    Scenario: A behind-pace student sees the same kind map
      Given a student who is behind the pacing calendar
      When she opens her voyage
      Then no island or level is rendered in warning or failure styling
      And no placement weights, percentages, or pace deficits are displayed
      And every island's state reflects only her mastery, never her pace

  @roadmap @scenario:AM-07
  Scenario: The buffer switches the map to revision mode
    Given the current week is within 6 weeks of the exam date
    When a student opens the map
    Then revision levels resurfacing her weakest mastered modules are shown
    And no new-content levels are shown
