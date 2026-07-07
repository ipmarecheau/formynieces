@roadmap @student
Feature: Exam readiness mode
  In the final months, practice converges on the real papers' input styles and
  timing so the exam holds no format surprises. Mock results feed the exam
  agent's readiness view, never the adventure map.

  @scenario:ER-01
  Scenario: Fill-in answers return for Math practice
    Given a student practising a Math module in exam-readiness mode
    When a practice item is presented
    Then she types her answer instead of choosing from options

  @scenario:ER-02
  Scenario: A timed mock follows the real paper's shape
    Given the revision buffer has begun
    When a student starts a mock paper
    Then the mock follows the real paper's structure, marks, and time limit
    And on completion its result is recorded to the exam agent readiness view
    And no adventure map state changes

  @scenario:ER-03
  Scenario: Exam week is quiet and warm
    Given it is the week of the SEA exam
    When a student logs in
    Then no weekly target is presented
    And the dashboard shows a single calm good-luck state
