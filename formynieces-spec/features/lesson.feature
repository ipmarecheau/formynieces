@mvp @student
Feature: Interactive module lesson
  When a student does not test out of a module's competency check, one of the ways in is
  the LESSON: an interactive teaching page for that module — as illustrative and captivating
  as a video, but as responsive and interactive as H5P content. Lessons are AUTHORED IN
  ADVANCE, one per module; they are never generated in real time. As she walks through a
  lesson she can open a clarify chat — an LLM tutor that explains and answers her questions
  but never generates the lesson and never scores her. The lesson is scaffolding: it changes
  nothing about her progress until she reaches practice. After the lesson she is walked
  through three tutorials, then arrives at practice. The seam ships first as an interactive
  placeholder with the clarify chat wired up, so the loop can be proven end to end; the
  rich authoring/interaction engine (LE-05) is the same MVP feature's larger second build.

  Background:
    Given a student has completed her diagnostic
    And her map shows a module with status "needs_work"
    And she has chosen the lesson after not testing out of the competency check

  @scenario:LE-01
  Scenario: A module's lesson is an interactive page authored in advance
    When she opens the module's lesson
    Then she sees an interactive lesson page authored for that module
    And the page is served from stored lesson content, not generated in real time

  @scenario:LE-02
  Scenario: The lesson is never scored
    Given she is working through the module's lesson
    When she completes it
    Then her module progress is unchanged
    And her mastery status is unchanged

  @scenario:LE-03
  Scenario: The lesson leads into three tutorials and then practice
    When she finishes the module's lesson
    Then she is walked through three tutorials for the module
    And after the tutorials she arrives at practice

  @scenario:LE-04
  Scenario: A clarify chat helps her understand the lesson without generating or scoring it
    Given she is working through the module's lesson
    When she asks the clarify chat a question about the lesson
    Then the LLM tutor explains the point she asked about
    And the chat never authors the lesson content
    And using the chat leaves her progress and mastery unchanged

  @scenario:LE-05
  Scenario: Lessons are authored with rich, H5P-grade interactions
    Given an author is building a module's lesson
    When they compose the lesson from the supported interaction types
    Then the lesson can mix explanation, media and interactive steps like H5P content
    And the finished lesson is stored for that module and served to every student who opens it
