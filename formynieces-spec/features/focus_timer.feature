@roadmap @student
Feature: Focus timer — Pomodoro blocks
  A student can time-block her practice with an optional focus timer. Completing
  a focused block earns bonus XP, encouraging steady attention without racing
  comprehension: reading a lesson or a tutorial is never timed, the timer never
  blocks her from moving on, and leaving a block early costs nothing.

  Background:
    Given a student is practising a module

  @scenario:FT-01
  Scenario: She can start an optional focus block on practice
    When she starts a focus block
    Then a countdown begins for the practice session
    And starting it is her choice, never required

  @scenario:FT-02
  Scenario: Completing a focus block earns bonus XP
    Given she has started a focus block
    When the block runs to completion
    Then she is awarded bonus focus XP

  @scenario:FT-03
  Scenario: The timer is advisory and never blocks her
    Given a focus block is running
    When she chooses to move on before it ends
    Then she can leave freely
    And nothing in the loop is locked behind the timer

  @scenario:FT-04
  Scenario: Reading a lesson or tutorial is never timed
    When she is on the lesson or tutorial stage
    Then no focus timer runs
    And she can read at her own pace

  @scenario:FT-05
  Scenario: An abandoned focus block costs nothing
    Given she has started a focus block
    When she ends it early
    Then she keeps all the XP she has already earned
    And there is no penalty and no failure language
