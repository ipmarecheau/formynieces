@mvp @student
Feature: Week-based adventure map
  Each stop on the map represents one study week of the 30-week calendar.
  The map is the motivational layer: it is always kind and always moving
  forward; honest pace data lives only in the guardian's exam agent views.

  @scenario:AM-01
  Scenario: The map renders one stop per pacing week
    Given a student with a generated roadmap
    When she opens the map
    Then she sees one stop for each pacing week of the 30-week calendar
    And each stop shows a state of completed, current, upcoming, or locked

  Rule: The map never shows the student alarming states

    @scenario:AM-02
    Scenario: A behind-pace student sees a kind map
      Given a student who is 3 weeks behind the pacing calendar
      When she opens the map
      Then no stop is rendered in warning or failure styling
      And no placement weights, percentages, or pace deficits are displayed

    @scenario:AM-03
    Scenario: A partially finished week is shown as visited, not failed
      Given a student completed 2 of 4 target modules last week
      When she opens the map after the week rolls over
      Then the previous stop is shown as visited
      And the 2 unfinished modules appear inside the current stop

  @scenario:AM-04
  Scenario: An early finisher is offered, not assigned, a peek ahead
    Given a student completed all current-stop modules before Sunday
    When she opens the map
    Then the next stop is offered as an optional peek
    And her current weekly target is unchanged

  @roadmap @scenario:AM-05
  Scenario: The buffer switches the map to revision mode
    Given the current week is within 6 weeks of the exam date
    When a student opens the map
    Then revision stops resurfacing her weakest mastered modules are shown
    And no new-content stops are shown
