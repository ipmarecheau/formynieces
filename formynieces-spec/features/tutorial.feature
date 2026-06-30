@mvp @student @pending
Feature: Module tutorial — guided worked examples
  Between understanding a concept and practising it alone, a student is walked
  through worked examples for the module. The tutorial shows how the method is
  applied, step by step. It is never scored and can be revisited freely — it
  teaches the method, it does not test it. Mastery is still earned only through
  practice.

  Background:
    Given a student has completed her diagnostic
    And her map shows a module with status "needs_work"
    And the module has human-vetted worked examples

  Scenario: The tutorial sits between the lesson and practice
    When she finishes reading the module's lesson
    Then she can start the worked examples from the lesson
    And from the worked examples she can move on to practice

  Scenario: A worked example reveals the method one step at a time
    Given she is viewing a worked example
    When she advances through it
    Then each step of the solution is revealed in order
    And the final answer is shown at the end

  Scenario: The tutorial is never scored
    Given she is working through the module's worked examples
    When she completes them
    Then her module progress is unchanged
    And her mastery status is unchanged

  Scenario: The tutorial can be revisited freely
    Given she has already been through the module's worked examples
    When she returns to them later
    Then she can work through them again with no penalty and no limit

  @v1.1
  Scenario: An interactive worked example asks her to drive the method
    Given a worked example has interactive steps
    When she is asked to choose the next step
    And she chooses a step that is not the expected one
    Then she sees a gentle nudge toward the method
    And her choice is not scored and does not affect her progress