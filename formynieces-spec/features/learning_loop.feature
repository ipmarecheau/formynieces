@mvp @student
Feature: Module learning loop
  A student masters the syllabus one module at a time: a short lesson built
  from human-vetted resources, followed by a quiz that updates her progress.

  Scenario: Opening a module shows its human-vetted lesson
    Given a module is in a student's current weekly target
    When she opens the module
    Then she sees its description and its human-vetted resources

  Scenario: A passing quiz masters the module
    Given a student is taking a target module's quiz
    When she submits answers scoring at or above the mastery threshold
    Then the module's status becomes "mastered" and her score is recorded
    And her prior score, if any, is preserved as the previous score
    And a celebration is shown
    And the module is checked off her weekly target

  Scenario: A failing quiz is framed as not-yet
    Given a student is taking a target module's quiz
    When she submits answers scoring below the mastery threshold
    Then the module remains available to retry
    And the feedback praises effort and points back to the lesson resources
    And no failure language is used

  @v1.1
  Scenario: Stale mastery in a weak strand decays into review
    Given a module was mastered more than 6 weeks ago in a strand the exam agent flags as weak
    When the weekly agent review runs
    Then the module's status becomes "in_review"
    And the module becomes eligible for a future weekly target
